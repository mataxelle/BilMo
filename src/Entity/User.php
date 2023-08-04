<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use App\Entity\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity('email')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    use BlameableEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'client:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]#[Assert\NotBlank(message: "Un prénom est obligatoire")]
    #[Assert\Length(min: 1, max: 100, minMessage: "Le prénom doit faire au moins {{ limit }} caractères", maxMessage: "Le prénom ne peut pas faire plus de {{ limit }} caractères")]
    #[Groups(['user:read', 'client:read'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 100)]#[Assert\NotBlank(message: "Un nom de catégorie est obligatoire")]
    #[Assert\Length(min: 1, max: 100, minMessage: "Le nom doit faire au moins {{ limit }} caractères", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    #[Groups(['user:read', 'client:read'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Length(min: 2, max: 180)]
    #[Assert\Email(message: 'Cet email est invalide')]
    #[Assert\NotBlank(message: "Un email est obligatoire")]
    #[Groups(['user:read', 'client:read'])]
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
