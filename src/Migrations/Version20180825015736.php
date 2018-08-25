<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180825015736 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE meeting (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, is_closed TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_F515E139296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_attendee (id INT AUTO_INCREMENT NOT NULL, meeting_id INT DEFAULT NULL, user_id INT DEFAULT NULL, hash CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', what_yesterday LONGTEXT NOT NULL, what_today LONGTEXT NOT NULL, what_problem LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_82C1F8B2D1B862B8 (hash), INDEX IDX_82C1F8B267433D9C (meeting_id), INDEX IDX_82C1F8B2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE meeting_attendee ADD CONSTRAINT FK_82C1F8B267433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id)');
        $this->addSql('ALTER TABLE meeting_attendee ADD CONSTRAINT FK_82C1F8B2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_attendee DROP FOREIGN KEY FK_82C1F8B267433D9C');
        $this->addSql('DROP TABLE meeting');
        $this->addSql('DROP TABLE meeting_attendee');
    }
}
