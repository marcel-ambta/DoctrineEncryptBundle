<?php
/*
 * @copyright  Copyright (C) 2017, 2018, 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com>
 * @see        https://github.com/PhilETaylor/mysites.guru
 * @license    MIT
 */

namespace Philetaylor\DoctrineEncryptBundle\Services;

class Encryptor
{
    /** @var \Philetaylor\DoctrineEncryptBundle\Encryptors\EncryptorInterface */
    protected $encryptor;

    public function __construct($encryptName, $key)
    {
        $reflectionClass = new \ReflectionClass($encryptName);
        $this->encryptor = $reflectionClass->newInstanceArgs([
            $key,
        ]);
    }

    public function getEncryptor()
    {
        return $this->encryptor;
    }

    public function decrypt($string)
    {
        return $this->encryptor->decrypt($string);
    }

    public function encrypt($string)
    {
        return $this->encryptor->encrypt($string);
    }
}
