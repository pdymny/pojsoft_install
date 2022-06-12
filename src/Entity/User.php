<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity
 * @Vich\Uploadable
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $password;

    /**
     * 
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=24, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserNotify", mappedBy="user")
     */
    private $userNotifies;


    public function __toString()
    {
        return (string) $this->username;
    }

    public function __construct()
    {
        $this->active = false;
        $this->isActive = true;
        $this->userNotifies = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function newPassword() 
    {
        // generowanie nowego hasła
        $number = round('100, 9999');
        $time = time();
        $pass = $time.''.$number;
        $pass = md5($pass);
        $pass = substr($pass, 1, 10);

        return $pass;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function getSalt()
    {
        // The bcrypt and argon2i algorithms don't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return Collection|UserNotify[]
     */
    public function getUserNotifies(): Collection
    {
        return $this->userNotifies;
    }

    public function addUserNotify(UserNotify $userNotify): self
    {
        if (!$this->userNotifies->contains($userNotify)) {
            $this->userNotifies[] = $userNotify;
            $userNotify->setIdUser($this);
        }

        return $this;
    }

    public function removeUserNotify(UserNotify $userNotify): self
    {
        if ($this->userNotifies->contains($userNotify)) {
            $this->userNotifies->removeElement($userNotify);
            // set the owning side to null (unless already changed)
            if ($userNotify->getIdUser() === $this) {
                $userNotify->setIdUser(null);
            }
        }

        return $this;
    }

}
