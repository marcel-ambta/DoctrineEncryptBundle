<?php

namespace PhilETaylor\DoctrineEncrypt\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Initialization of bundle.
 *
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PhilETaylorDoctrineEncryptExtension extends Extension {

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container) {

        //Create configuration object
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        //Set orm-service in array of services
        $services = array('orm' => 'orm-services');

        //If no secret key is set, check for framework secret, otherwise throw exception
//        if (empty($config['secret_key'])) {
//            if ($container->hasParameter('secret')) {
//                $config['secret_key'] = $container->getParameter('secret');
//            } else {
//                throw new \RuntimeException('You must provide "secret_key" for doctrine_encrypt or "secret" for framework');
//            }
//        }

        //Set parameters
        // Now cannot be set by user!
        $container->setParameter('phil_e_taylor_doctrine_encrypt.encryptor_class_name', '\PhilETaylor\DoctrineEncrypt\Encryptors\HaliteEncryptor');

        $container->setParameter('phil_e_taylor_doctrine_encrypt.keys', $config['keys']);

        //Load service file
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load(sprintf('%s.yml', $services['orm']));
        $loader->load('commands.yml');
    }

    /**
     * Get alias for configuration
     *
     * @return string
     */
    public function getAlias() {
        return 'doctrine_encrypt';
    }
}
