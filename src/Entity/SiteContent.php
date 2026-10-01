<?php

namespace App\Entity;

use App\Repository\SiteContentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteContentRepository::class)]
class SiteContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $contentKey = '';

    #[ORM\Column(type: 'text')]
    private string $content = '';

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getContentKey(): string { return $this->contentKey; }
    public function setContentKey(string $contentKey): static { $this->contentKey = $contentKey; return $this; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; $this->updatedAt = new \DateTimeImmutable(); return $this; }

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
}
