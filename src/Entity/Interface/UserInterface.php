<?php

namespace App\Entity\Interface;

interface UserInterface
{
    public function getId(): string;
    public function getFirstname(): ?string;
    public function setFirstname(?string $firstname): self;
    public function getLastname(): ?string;
    public function setLastname(?string $lastname): self;
    public function getLogin(): string;
    public function setLogin(string $login): self;
    public function isAdministrator(): bool;
    public function setAdministrator(bool $administrator): self;
    public function getPassword(): string;
    public function setPassword(string $password): self;
}
