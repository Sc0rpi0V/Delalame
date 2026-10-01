<?php

namespace App\Entity;

use App\Repository\TeamMemberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamMemberRepository::class)]
#[ORM\HasLifecycleCallbacks]
class TeamMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $role = '';

    #[ORM\Column]
    private int $yearsExperience = 0;

    #[ORM\Column(length: 255)]
    private string $specialty = '';

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private bool $published = true;

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getRole(): string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }

    public function getYearsExperience(): int { return $this->yearsExperience; }
    public function setYearsExperience(int $yearsExperience): static { $this->yearsExperience = $yearsExperience; return $this; }

    public function getSpecialty(): string { return $this->specialty; }
    public function setSpecialty(string $specialty): static { $this->specialty = $specialty; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $published): static { $this->published = $published; return $this; }

    public function getInitials(): string
    {
        $parts = explode(' ', $this->name, 2);
        return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    }

    public function __toString(): string { return $this->name; }
}
