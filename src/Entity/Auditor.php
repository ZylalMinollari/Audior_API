<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\AuditorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: AuditorRepository::class)]
#[ApiResource]
class Auditor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Length(max: 70)]
    private ?string $name;

    #[ORM\Column(type: "string", unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 70)]
    private ?string $username;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    private ?string $password;


    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $timezone;

    /**
     * @ORM\OneToMany(targetEntity=Schedule::class, mappedBy="auditor")
     */
    private $schedules;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }


    public function getTimezone(): ?\DateTimeInterface
    {
        return $this->timezone;
    }

    
    public function setTimezone(?\DateTimeInterface $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }
}
