<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getRoles()
    {
        $roles = ['ROLE_USER'];
        
        if (in_array($this->getEmail(), ['per@bernhardt.ws', 'per.bernhardt@leanix.net', 'tim.bernhardt@germanschool.co.ke'])) {
            $roles[] = 'ROLE_ADMIN';
        }

        return $roles;
    }

    public function getDisplayName()
    {
        $parts = explode('@', $this->getEmail());
        $parts = explode('.', $parts[0]);

        return implode(' ', array_map('ucfirst', $parts));
    }

    public function getPassword()
    {}

    public function getSalt()
    {}

    public function getUsername()
    {
        return $this->getEmail();
    }

    public function eraseCredentials()
    {}
}
