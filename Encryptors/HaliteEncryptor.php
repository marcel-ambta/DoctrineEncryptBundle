<?php

namespace PhilETaylor\DoctrineEncrypt\Encryptors;

use MyJoomla\AuditTools\Audit\FilesInformation\RenamedToHide;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\Alerts\InvalidKey;

/**
 * Class HaliteEncryptor
 * @package PhilETaylor\DoctrineEncrypt\Encryptors
 */
class HaliteEncryptor implements EncryptorInterface
{

    /**
     * @var array of key name/filepaths
     */
    private $enc_keys;

    /**
     * @var HiddenString
     */
    private $enc_key;

    /**
     * @var string the name of the config param for the key to use
     */
    private $enc_key_name;

    /**
     * @var string
     */
    private $initializationVector;

    /**
     * {@inheritdoc}
     */
    public function __construct($keys)
    {
        $this->enc_keys = $keys;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($data)
    {
        if (is_string($data)) {
            $ciphertext = Crypto::encrypt(
                new HiddenString(
                    $data
                ),
                $this->enc_key
            );

            return $ciphertext . "<Ha>";
        } else {
            return $data;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($ciphertext)
    {
        if (is_string($ciphertext)) {
            $plaintext = Crypto::decrypt(
                $ciphertext,
                $this->enc_key
            );

            return $plaintext->getString();
        } else {
            return $ciphertext;
        }
    }

    /**
     * Choice a key from the available keys
     *
     * @param $key_name the key name to use
     *
     * @return void
     *
     * @throws CannotPerformOperation
     * @throws InvalidKey
     */
    public function setKeyName($key_name)
    {
        $this->enc_key_name = $key_name;
        $this->enc_key = KeyFactory::loadEncryptionKey($this->enc_keys[$key_name]);
    }
}
