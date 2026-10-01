<?php

namespace App\Entity;

use App\Repository\LegalPageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LegalPageRepository::class)]
class LegalPage
{
    public const TYPES = [
        'mentions_legales'          => 'Mentions légales',
        'cgv'                       => 'Conditions générales de vente',
        'cgu'                       => 'Conditions générales d\'utilisation',
        'politique_confidentialite' => 'Politique de confidentialité',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static
    {
        $this->content = $content;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function getLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function __toString(): string { return $this->getLabel(); }
}
