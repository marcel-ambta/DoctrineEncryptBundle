<?php
/*
 * @copyright  Copyright (C) 2017, 2018, 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com>
 * @see        https://github.com/PhilETaylor/mysites.guru
 * @license    MIT
 */

namespace Philetaylor\DoctrineEncryptBundle\Encryptors;

/**
 * Encryptor interface for encryptors.
 *
 * @author Victor Melnik <melnikvictorl@gmail.com>
 */
interface EncryptorInterface
{
    /**
     * Must accept secret key for encryption.
     *
     * @param string $secretKey the encryption key
     */
    public function __construct($secretKey);

    /**
     * @param string $data Plain text to encrypt
     *
     * @return string Encrypted text
     */
    public function encrypt($data);

    /**
     * @param string $data Encrypted text
     *
     * @return string Plain text
     */
    public function decrypt($data);

    /**
     * @param $data
     *
     * @return mixed
     */
    public function setKeyName($key);
}
