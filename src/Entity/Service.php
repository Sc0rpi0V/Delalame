<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[UniqueEntity('reference', message: 'Cette référence est déjà utilisée par un autre service.')]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private string $name = '';

    /** Code repris dans l'objet du mail de demande (ex. FEN-01). */
    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank]
    private string $reference = '';

    #[ORM\Column(length: 16)]
    private string $icon = '';

    #[ORM\Column(type: 'text')]
    private string $intro = '';

    /** @var string[] */
    #[ORM\Column(type: 'json')]
    private array $points = [];

    /** Corps pré-rédigé du mail ouvert par le visiteur (texte brut). */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $mailBody = '';

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private bool $published = true;

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): static { $this->reference = strtoupper(trim($reference)); return $this; }

    public function getIcon(): string { return $this->icon; }
    public function setIcon(?string $icon): static { $this->icon = (string) $icon; return $this; }

    public function getIntro(): string { return $this->intro; }
    public function setIntro(?string $intro): static { $this->intro = (string) $intro; return $this; }

    /** @return string[] */
    public function getPoints(): array { return $this->points; }
    /** @param string[] $points */
    public function setPoints(array $points): static { $this->points = array_values(array_filter(array_map('trim', $points))); return $this; }

    public function getMailBody(): string { return $this->mailBody; }
    public function setMailBody(string $mailBody): static { $this->mailBody = $mailBody; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $published): static { $this->published = $published; return $this; }

    public function getMailSubject(): string
    {
        return sprintf('Demande de devis — %s · %s', $this->reference, $this->name);
    }

    public function __toString(): string { return $this->name; }
}
