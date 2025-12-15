<?php

namespace App\Entity;

use App\Entity\Interface\UserInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(type: "string", unique: true, nullable: false)]
    private string $login;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $administrator = false;

    #[ORM\Column(type: "string", nullable: false)]
    private string $password;

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function isAdministrator(): bool
    {
        return $this->administrator;
    }

    public function setAdministrator(bool $administrator): self
    {
        $this->administrator = $administrator;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        // Hachage du mot de passe avant de le stocker
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }
}
