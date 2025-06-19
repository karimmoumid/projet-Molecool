<?php

namespace App\Entity;

use App\Repository\SocialSecurityNumberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SocialSecurityNumberRepository::class)]
class SocialSecurityNumber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 13)]
    private ?string $number = null;

    #[ORM\OneToOne(mappedBy: 'social_security_number', cascade: ['persist', 'remove'])]
    private ?User $users = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): static
    {
        // unset the owning side of the relation if necessary
        if ($users === null && $this->users !== null) {
            $this->users->setSocialSecurityNumber(null);
        }

        // set the owning side of the relation if necessary
        if ($users !== null && $users->getSocialSecurityNumber() !== $this) {
            $users->setSocialSecurityNumber($this);
        }

        $this->users = $users;

        return $this;
    }
}
