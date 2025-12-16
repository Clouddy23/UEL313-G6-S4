<?php

namespace App\Entity\Interface;

use Doctrine\Common\Collections\Collection;
use App\Entity\Link;

interface TagInterface
{
    public function getId(): ?int;
    public function getName(): string;
    public function setName(string $name): self;
    /**
     * @return Collection
     */
    public function getLinks(): Collection;
    public function addLink(Link $link): self;
    public function removeLink(Link $link): self;
}
