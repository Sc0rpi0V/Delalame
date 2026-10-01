<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520072517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devis_request_prestation (devis_request_id INT NOT NULL, prestation_id INT NOT NULL, INDEX IDX_CD8F9D1FF2DFF34D (devis_request_id), INDEX IDX_CD8F9D1F9E45C554 (prestation_id), PRIMARY KEY (devis_request_id, prestation_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE prestation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description VARCHAR(255) DEFAULT NULL, category VARCHAR(20) NOT NULL, price_ht NUMERIC(10, 2) DEFAULT 0 NOT NULL, tva_rate NUMERIC(4, 1) DEFAULT 20 NOT NULL, position INT DEFAULT 0 NOT NULL, active TINYINT DEFAULT 1 NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE devis_request_prestation ADD CONSTRAINT FK_CD8F9D1FF2DFF34D FOREIGN KEY (devis_request_id) REFERENCES devis_request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devis_request_prestation ADD CONSTRAINT FK_CD8F9D1F9E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis_request_prestation DROP FOREIGN KEY FK_CD8F9D1FF2DFF34D');
        $this->addSql('ALTER TABLE devis_request_prestation DROP FOREIGN KEY FK_CD8F9D1F9E45C554');
        $this->addSql('DROP TABLE devis_request_prestation');
        $this->addSql('DROP TABLE prestation');
    }
}
