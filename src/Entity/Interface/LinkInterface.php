<?php

namespace App\Entity\Interface;

use Doctrine\Common\Collections\Collection;
use App\Entity\User;
use App\Entity\Tag;

interface LinkInterface
{
    public function getId(): ?int;
    public function getUrl(): string;
    public function setUrl(string $url): self;
    public function getTitle(): string;
    public function setTitle(string $title): self;
    public function getDesc(): string;
    public function setDesc(string $desc): self;
    public function getUser(): User;
    public function setUser(User $user): self;
    /**
     * Collections de liens par tag
     * @return Collection
     */
    public function getTags(): Collection;
    public function addTag(Tag $tag): self;
    public function removeTag(Tag $tag): self;
}
