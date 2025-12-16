<?php

namespace App\Entity;

use App\Entity\Interface\TagInterface;
use App\Entity\Link;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "tag")]
class Tag implements TagInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    private string $name;

    // Un tag peut être associé à plusieurs liens
    #[ORM\ManyToMany(targetEntity: Link::class, mappedBy: "tags")]
    private Collection $links;

    public function __construct()
    {
        $this->links = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gestion des liens associés au tag
     * Retourne une collection de Link associés à ce Tag
     * @return Collection|Link[]
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    /**
     * Ajoute un lien à ce tag
     * @return self
     */
    public function addLink(Link $link): self
    {
        if (!$this->links->contains($link)) {
            $this->links[] = $link;
            $link->addTag($this);
        }
        return $this;
    }

    /**
     * Retire un lien de ce tag
     * @return self
     */
    public function removeLink(Link $link): self
    {
        if ($this->links->removeElement($link)) {
            $link->removeTag($this);
        }
        return $this;
    }
}
