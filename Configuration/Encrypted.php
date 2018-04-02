<?php

namespace PhilETaylor\DoctrineEncrypt\Configuration;

/**
 * The Encrypted class handles the @Encrypted annotation.
 *
 * @author Victor Melnik <melnikvictorl@gmail.com>
 * @Annotation
 */
class Encrypted {

    /**
     * @var string the key nameindex to use for encryption/decryption
     */
    public $key_name;
}