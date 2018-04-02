<?php

namespace PhilETaylor\DoctrineEncrypt\Encryptors;

/**
 * Class for variable encryption
 * 
 * @author Marcel van Nuil <marcel@ambta.com>
 */
class Rijndael256Encryptor implements EncryptorInterface {

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $initializationVector;

    /**
     * {@inheritdoc}
     */
    public function __construct($key) {
        $this->secretKey = md5($key);
        $this->initializationVector = mcrypt_create_iv(
            mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB),
            MCRYPT_RAND
        );
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($data) {

        if(is_string($data)) {
            return trim(base64_encode(mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256,
                $this->secretKey,
                $data,
                MCRYPT_MODE_ECB,
                $this->initializationVector
            ))). "<ENC>";
        }

        return $data;

    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($data) {

        if(is_string($data)) {

            $data = str_replace("<ENC>", "", $data);

            return trim(mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                $this->secretKey,
                base64_decode($data),
                MCRYPT_MODE_ECB,
                $this->initializationVector
            ));
        }

        return $data;
    }
}
