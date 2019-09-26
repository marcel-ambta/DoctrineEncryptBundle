#Configuration Reference

All available configuration options are listed below.

``` yaml
ambta_doctrine_encrypt:
# Secret key for encrypt algorithm. All secret key checks are encryptor tasks only.
# We recommend an 32 character long key (256 bits), Use another key for each project!
# Store a backup of this key on a secure location, losing this key will mean losing your data!
    secret_key:           ~ # Required
#  If you want, you can use your own Encryptor. Encryptor must implements EncryptorInterface interface
#  Default: Philetaylor\DoctrineEncrypt\Encryptors\VariableEncryptor
    encryptor_class:      ~ #optional
```
