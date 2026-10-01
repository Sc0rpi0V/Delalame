<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260717145904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champ altText (texte alternatif) sur GalleryImage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery_image ADD alt_text VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery_image DROP alt_text');
    }
}
