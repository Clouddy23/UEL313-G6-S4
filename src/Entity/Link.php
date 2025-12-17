<?php

namespace App\Entity;

use App\Entity\Interface\LinkInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "link")]
class Link implements LinkInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    private string $url;

    #[ORM\Column(type: "string")]
    private string $title;

    #[ORM\Column(type: "string")]
    private string $desc;

    // Chaque lien est associé à un utilisateur
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "links")]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    // Un lien peut avoir plusieurs tags
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: "links")]
    #[ORM\JoinTable(name: "link_tag")]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDesc(): string
    {
        return $this->desc;
    }

    public function setDesc(string $desc): self
    {
        $this->desc = $desc;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Gestion des tags associés au lien
     * Retourne une collection de Tags associés à ce Link
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Ajoute un Tag à la collection de Tags associés à ce Link
     * @return self
     */
    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            // Synchronisation côté inverse
            if (!$tag->getLinks()->contains($this)) {
                $tag->addLink($this);
            }
        }
        return $this;
    }

    /**
     * Retire un Tag de la collection de Tags associés à ce Link
     * @return self
     */
    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            // Synchronisation côté inverse
            if ($tag->getLinks()->contains($this)) {
                $tag->removeLink($this);
            }
        };
        return $this;
    }
}
