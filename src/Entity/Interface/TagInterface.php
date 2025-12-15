<?php

namespace App\Entity\Interface;

use Doctrine\Common\Collections\Collection;

interface TagInterface
{
    public function getId(): ?int;
    public function getName(): string;
    public function setName(string $name): self;
    /**
     * @return Collection
     */
    public function getLinks(): Collection;
    public function addLink(\App\Entity\Link $link): self;
    public function removeLink(\App\Entity\Link $link): self;
}
