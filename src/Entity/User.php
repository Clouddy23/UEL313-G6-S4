<?php

namespace App\Entity;

use App\Entity\Interface\UserInterface;
use Doctrine\ORM\Mapping as ORM;
// ajout de l'use de l'entité Link pour la relation entre User et Link
use App\Entity\Link;
// ajout des use nécessaires pour les relations,utilisation de Doctrine pour la gestion des listes d'entités (links des utilisateurs)
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


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

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Link::class, orphanRemoval: true)]
private Collection $links;

    public function getId(): int // correction getID en int
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

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Link::class, orphanRemoval: true)]
private Collection $links;
    public function __construct()
    {
        $this->links = new ArrayCollection();
    }

