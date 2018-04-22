<?php

namespace PhilETaylor\DoctrineEncrypt\Subscribers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use PhilETaylor\DoctrineEncrypt\Encryptors\EncryptorInterface;
use ReflectionClass;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Doctrine event subscriber which encrypt/decrypt entities
 */
class DoctrineEncryptSubscriber implements EventSubscriber
{

    /**
     * Encryptor interface namespace
     */
    const ENCRYPTOR_INTERFACE_NS = 'PhilETaylor\DoctrineEncrypt\Encryptors\EncryptorInterface';

    /**
     * Encrypted annotation full name
     */
    const ENCRYPTED_ANN_NAME = 'PhilETaylor\DoctrineEncrypt\Configuration\Encrypted';
    /**
     * Count amount of decrypted values in this service
     * @var integer
     */
    public $decryptCounter = 0;
    /**
     * Count amount of encrypted values in this service
     * @var integer
     */
    public $encryptCounter = 0;
    /**
     * Encryptor
     * @var EncryptorInterface
     */
    private $encryptor;
    private $decodedRegistry;
    /**
     * Annotation reader
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $annReader;
    /**
     * Secret key
     * @var array
     */
    private $secretKeys = [];
    /**
     * Used for restoring the encryptor after changing it
     * @var string
     */
    private $restoreEncryptor;

    /**
     * Initialization of subscriber
     *
     * @param Reader $annReader
     * @param string $encryptorClass The encryptor class.  This can be empty if a service is being provided.
     * @param string $secretKey The secret key.
     * @param EncryptorInterface|NULL $service (Optional)  An EncryptorInterface.
     *
     * This allows for the use of dependency injection for the encrypters.
     */
    public function __construct(Reader $annReader, $encryptorClass, array $secretKeys, EncryptorInterface $service = NULL)
    {

        $this->annReader = $annReader;
        $this->secretKeys = $secretKeys;

        if ($service instanceof EncryptorInterface) {
            $this->encryptor = $service;
        } else {
            $this->encryptor = $this->encryptorFactory($encryptorClass, $secretKeys);
        }

        $this->restoreEncryptor = $this->encryptor;
    }

    /**
     * Encryptor factory. Checks and create needed encryptor
     *
     * @param string $classFullName Encryptor namespace and name
     * @param string $secretKey Secret key for encryptor
     *
     * @return EncryptorInterface
     * @throws \RuntimeException
     */
    private function encryptorFactory($classFullName, $secretKeys)
    {
        $refClass = new \ReflectionClass($classFullName);
        if ($refClass->implementsInterface(self::ENCRYPTOR_INTERFACE_NS)) {
            return new $classFullName($secretKeys);
        } else {
            throw new \RuntimeException('Encryptor must implements interface EncryptorInterface');
        }
    }

    /**
     * @return string
     */
    public function getSecretKeys(): array
    {
        return $this->secretKeys;
    }

    /**
     * Get the current encryptor
     */
    public function getEncryptor()
    {
        if (!empty($this->encryptor)) {
            return get_class($this->encryptor);
        } else {
            return null;
        }
    }

    /**
     * Change the encryptor
     *
     * @param $encryptorClass
     */
    public function setEncryptor($encryptorClass)
    {

        if (!is_null($encryptorClass)) {
            $this->encryptor = $this->encryptorFactory($encryptorClass, $this->secretKeys);
            return;
        }

        $this->encryptor = null;
    }

    /**
     * Restore encryptor set in config
     */
    public function restoreEncryptor()
    {
        $this->encryptor = $this->restoreEncryptor;
    }

    /**
     * Listen a postUpdate lifecycle event.
     * Decrypt entities property's values when post updated.
     *
     * So for example after form submit the preUpdate encrypted the entity
     * We have to decrypt them before showing them again.
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {

        $this->checkAndReloadEntities($args);
//        $entity = $args->getEntity();
//        $this->processFields($entity, false);

    }

    /**
     * Checking and decrypg entities which have @Encrypted annotations
     *
     * @param LifecycleEventArgs $args
     *
     * @return void
     */
    private function checkAndReloadEntities(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$this->hasInDecodedRegistry($entity, $args->getEntityManager())) {
            if ($this->processFields($entity, false)) {
                $this->addToDecodedRegistry($entity, $args->getEntityManager());
            }
        }
    }

    /**
     * Check if we have entity in decoded registry
     * @param Object $entity Some doctrine entity
     * @param \Doctrine\ORM\EntityManager $em
     * @return boolean
     */
    private function hasInDecodedRegistry($entity, EntityManagerInterface $em)
    {
        $className = get_class($entity);
        $metadata = $em->getClassMetadata($className);
        $getter = 'get' . self::capitalize($metadata->getIdentifier());
        return isset($this->decodedRegistry[$className][$entity->$getter()]);
    }

    /**
     * Capitalize string
     * @param string $word
     * @return string
     */
    public static function capitalize($word)
    {
        if (is_array($word)) {
            $word = $word[0];
        }
        return str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $word)));
    }

    /**
     * Process (encrypt/decrypt) entities fields
     *
     * @param Object $entity doctrine entity
     * @param Boolean $isEncryptOperation If true - encrypt, false - decrypt entity
     *
     * @throws \RuntimeException
     *
     * @return object|null
     */
    public function processFields($entity, $isEncryptOperation = true)
    {

        if (!empty($this->encryptor)) {

            //Check which operation to be used
            $encryptorMethod = $isEncryptOperation ? 'encrypt' : 'decrypt';

            //Get the real class, we don't want to use the proxy classes
            if (strstr(get_class($entity), "Proxies")) {
                $realClass = ClassUtils::getClass($entity);
            } else {
                $realClass = get_class($entity);
            }

            //Get ReflectionClass of our entity
            $reflectionClass = new ReflectionClass($realClass);
            $properties = $this->getClassProperties($realClass);

            //Foreach property in the reflection class
            foreach ($properties as $refProperty) {

                if ($this->annReader->getPropertyAnnotation($refProperty, 'Doctrine\ORM\Mapping\Embedded')) {
                    $this->handleEmbeddedAnnotation($entity, $refProperty, $isEncryptOperation);
                    continue;
                }
                /**
                 * If followed standards, method name is getPropertyName, the propertyName is lowerCamelCase
                 * So just uppercase first character of the property, later on get and set{$methodName} wil be used
                 */
                $methodName = ucfirst($refProperty->getName());


                /**
                 * If property is an normal value and contains the Encrypt tag, lets encrypt/decrypt that property
                 */
                if ($AnnotationConfig = $this->annReader->getPropertyAnnotation($refProperty, self::ENCRYPTED_ANN_NAME)) {

                    if (property_exists($AnnotationConfig, 'key_name')) {
                        $this->encryptor->setKeyName($AnnotationConfig->key_name);
                    }

                    /**
                     * If it is public lets not use the getter/setter
                     */
                    if ($refProperty->isPublic()) {
                        $propName = $refProperty->getName();
                        $entity->$propName = $this->encryptor->$encryptorMethod($refProperty->getValue());
                    } else {
                        $nameConverter = new CamelCaseToSnakeCaseNameConverter();
                        $methodName = $nameConverter->denormalize($methodName);

                        //If private or protected check if there is an getter/setter for the property, based on the $methodName
                        if ($reflectionClass->hasMethod($getter = 'get' . $methodName) && $reflectionClass->hasMethod($setter = 'set' . $methodName)) {

                            //Get the information (value) of the property
                            try {
                                $getInformation = $entity->$getter();
                            } catch (\Exception $e) {
                                $getInformation = null;
                            }

                            /**
                             * Then decrypt, encrypt the information if not empty, information is an string and the <Ha> tag is there (decrypt) or not (encrypt).
                             * The <Ha> will be added at the end of an encrypted string so it is marked as encrypted. Also protects against double encryption/decryption
                             */
                            if ($encryptorMethod == "decrypt") {
                                if (!is_null($getInformation) and !empty($getInformation)) {
                                    if (substr($getInformation, -4) == "<Ha>") {
                                        $this->decryptCounter++;
                                        $currentPropValue = $this->encryptor->decrypt(substr($getInformation, 0, -4));
                                        $entity->$setter($currentPropValue);
                                    }
                                }
                            } else {
                                if (!is_null($getInformation) and !empty($getInformation)) {
                                    if (substr($entity->$getter(), -4) != "<Ha>") {
                                        $this->encryptCounter++;
                                        $currentPropValue = $this->encryptor->encrypt($entity->$getter());
                                        $entity->$setter($currentPropValue);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $entity;
        }

        return null;
    }

    /**
     * Recursive function to get an associative array of class properties
     * including inherited ones from extended classes
     *
     * @param string $className Class name
     *
     * @return array
     */
    function getClassProperties($className)
    {

        $reflectionClass = new ReflectionClass($className);
        $properties = $reflectionClass->getProperties();
        $propertiesArray = array();

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertiesArray[$propertyName] = $property;
        }

        if ($parentClass = $reflectionClass->getParentClass()) {
            $parentPropertiesArray = $this->getClassProperties($parentClass->getName());
            if (count($parentPropertiesArray) > 0)
                $propertiesArray = array_merge($parentPropertiesArray, $propertiesArray);
        }

        return $propertiesArray;
    }

    private function handleEmbeddedAnnotation($entity, $embeddedProperty, $isEncryptOperation = true)
    {
        $reflectionClass = new ReflectionClass($entity);
        $propName = $embeddedProperty->getName();
        $methodName = ucfirst($propName);

        if ($embeddedProperty->isPublic()) {
            $embeddedEntity = $embeddedProperty->getValue();
        } else {
            if ($reflectionClass->hasMethod($getter = 'get' . $methodName) && $reflectionClass->hasMethod($setter = 'set' . $methodName)) {

                //Get the information (value) of the property
                try {
                    $embeddedEntity = $entity->$getter();
                } catch (\Exception $e) {
                    $embeddedEntity = null;
                }
            }
        }
        if ($embeddedEntity) {
            $this->processFields($embeddedEntity, $isEncryptOperation);
        }
    }

    /**
     * Adds entity to decoded registry
     * @param object $entity Some doctrine entity
     * @param \Doctrine\ORM\EntityManager $em
     */
    private function addToDecodedRegistry($entity, EntityManagerInterface $em)
    {
        $className = get_class($entity);

        $metadata = $em->getClassMetadata($className);
        $getter = 'get' . self::capitalize($metadata->getIdentifier());
        $this->decodedRegistry[$className][$entity->$getter()] = true;
    }

    /**
     * Listen a preUpdate lifecycle event. Checking and encrypt entities fields
     * which have @Encrypted annotation. Using changesets to avoid preUpdate event
     * restrictions
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $reflectionClass = new ReflectionClass($args->getEntity());
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $refProperty) {
            if ($this->annReader->getPropertyAnnotation($refProperty, self::ENCRYPTED_ANN_NAME)) {
                $propName = $refProperty->getName();
                if ($args->hasChangedField($propName)) {
                    $args->setNewValue($propName, $this->encryptor->encrypt($args->getNewValue($propName)));
                }
            }
        }
    }

    /**
     * Listen a postLoad lifecycle event. Checking and decrypt entities
     * which have @Encrypted annotations
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$this->hasInDecodedRegistry($entity, $args->getEntityManager())) {
            if ($this->processFields($entity, false)) {
                $this->addToDecodedRegistry($entity, $args->getEntityManager());
            }
        }
    }

    /**
     * Listen to preflush event
     * Encrypt entities that are inserted into the database
     *
     * @param PreFlushEventArgs $preFlushEventArgs
     */
    public function preFlush(PreFlushEventArgs $preFlushEventArgs)
    {
        $unitOfWork = $preFlushEventArgs->getEntityManager()->getUnitOfWork();
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->processFields($entity);
        }
    }

    /**
     * Listen to postFlush event
     * Decrypt entities that after inserted into the database
     *
     * @param PostFlushEventArgs $postFlushEventArgs
     */
    public function postFlush(PostFlushEventArgs $postFlushEventArgs)
    {
        $unitOfWork = $postFlushEventArgs->getEntityManager()->getUnitOfWork();
        foreach ($unitOfWork->getIdentityMap() as $entityMap) {
            foreach ($entityMap as $entity) {
                $this->processFields($entity, false);
            }
        }
    }

    /**
     * Listen a prePersist lifecycle event. Checking and encrypt entities
     * which have @Encrypted annotation
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->processFields($entity);
    }

    /**
     * Realization of EventSubscriber interface method.
     *
     * @return Array Return all events which this subscriber is listening
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::postLoad,
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->checkAndReloadEntities($args);
    }
}
