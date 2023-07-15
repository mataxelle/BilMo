<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Entity\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('email')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    use BlameableEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(min: 2, max: 100)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide')]
    private ?string $firstname = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(min: 2, max: 100)]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide')]
    private ?string $lastname = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Length(min: 2, max: 180)]
    #[Assert\Email(message: 'Cet email est invalide')]
    #[Assert\NotBlank(message: 'Ce champ ne peut pas être vide')]
    private ?string $email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
