#Usage

Lets imagine that we are storing some private data in our database and we don't want 
to somebody can see it even if he will get raw database on his hands in some dirty way. 
With this bundle this task can be easily made and we even don't see these processes 
because bundle uses some doctrine life cycle events. In database information will 
be encoded. In the same time entities in program will be clear as always and all 
these things will be happen automatically.

## Example

For example, we have some user entity with two fields which we want to encode in database.
We must import annotation `@Encrypted` first and then mark fields with it.

### Doctrine Entity

``` php
namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

// importing @Encrypted annotation
use Philetaylor\DoctrineEncrypt\Configuration\Encrypted;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User {
    
    ..
    
    /**
     * @ORM\Column(type="string", name="email")
     * @Encrypted
     * @var int
     */
    private $email;
   
    ..

}
```

It is as simple as that, the field will now be encrypted the first time the users entity gets edited.
We keep an <ENC> prefix to check if data is encrypted or not so, unencrypted data will still work even if the field is encrypted.

## Console commands

There are some console commands that can help you encrypt your existing database or change encryption methods.
Read more about the database encryption commands provided with this bundle.

#### [Console commands](https://github.com/ambta/DoctrineEncryptBundle/blob/master/Resources/doc/commands.md)