<?php
namespace YourBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PhilETaylor\DoctrineEncrypt\Configuration\Encrypted;

/**
 * This is an user entity which contains user information
 *
 * @author you <you@yourmail.com>
 * @ORM\Table(name="user")
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Encrypted
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @Encrypted
     * @ORM\Column(name="roles", type="text")
     */
    private $roles;

    /**
     * @ORM\Column(name="is_removed", type="boolean")
     */
    private $isRemoved;


    public function __construct()
    {

    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }


    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));

        return $this;
    }

    /**
     * Get user roles
     *
     * @return string[]
     */
    public function getRoles()
    {
        return explode(',', $this->roles);
    }

    /**
     * Set user roles
     *
     * @param string[] $roles
     *
     * @return $this
*/
    public function setRoles($roles)
    {
        $this->roles = implode(',', $roles);
        return $this;
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set is removed
     *
     * @param boolean $isRemoved
     *
     * @return $this
     */
    public function setIsRemoved($isRemoved)
    {
        $this->isRemoved = $isRemoved;

        return $this;
    }

    /**
     * Get is removed
     *
     * @return boolean
     */
    public function getIsRemoved()
    {
        return $this->isRemoved;
    }
}
