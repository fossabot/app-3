<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180403123031 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE buildings_app_users (building_id CHAR(36) NOT NULL --(DC2Type:guid)
        , app_user_id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(building_id, app_user_id))');
        $this->addSql('CREATE INDEX IDX_2D02FBAC4D2A7E12 ON buildings_app_users (building_id)');
        $this->addSql('CREATE INDEX IDX_2D02FBAC4A3353D8 ON buildings_app_users (app_user_id)');
        $this->addSql('DROP TABLE app_user_buildings');
        $this->addSql('DROP INDEX IDX_B8D991E4D2A7E12');
        $this->addSql('CREATE TEMPORARY TABLE __temp__building_map AS SELECT id, building_id, name, description, file_name, public_identifier, public_identifier_valid_till FROM building_map');
        $this->addSql('DROP TABLE building_map');
        $this->addSql('CREATE TABLE building_map (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , building_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , name CLOB NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, file_name CLOB DEFAULT NULL COLLATE BINARY, public_identifier CLOB DEFAULT NULL COLLATE BINARY, public_identifier_valid_till DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_B8D991E4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO building_map (id, building_id, name, description, file_name, public_identifier, public_identifier_valid_till) SELECT id, building_id, name, description, file_name, public_identifier, public_identifier_valid_till FROM __temp__building_map');
        $this->addSql('DROP TABLE __temp__building_map');
        $this->addSql('CREATE INDEX IDX_B8D991E4D2A7E12 ON building_map (building_id)');
        $this->addSql('DROP INDEX IDX_9F74B8987887A021');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setting AS SELECT id, frontend_user_id, "key", content FROM setting');
        $this->addSql('DROP TABLE setting');
        $this->addSql('CREATE TABLE setting (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , frontend_user_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , "key" CLOB NOT NULL COLLATE BINARY, content CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_9F74B8987887A021 FOREIGN KEY (frontend_user_id) REFERENCES frontend_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO setting (id, frontend_user_id, "key", content) SELECT id, frontend_user_id, "key", content FROM __temp__setting');
        $this->addSql('DROP TABLE __temp__setting');
        $this->addSql('CREATE INDEX IDX_9F74B8987887A021 ON setting (frontend_user_id)');
        $this->addSql('DROP INDEX IDX_82CF20FEB03A8386');
        $this->addSql('DROP INDEX IDX_82CF20FE4373858F');
        $this->addSql('DROP INDEX IDX_82CF20FE34508F72');
        $this->addSql('CREATE TEMPORARY TABLE __temp__marker AS SELECT id, craftsman_id, building_map_id, created_by_id, approved, mark_xpercentage, mark_ypercentage, frame_xpercentage, frame_ypercentage, frame_xheight, frame_ylength, content, image_file_name, created_at, last_changed_at, viewed_online FROM marker');
        $this->addSql('DROP TABLE marker');
        $this->addSql('CREATE TABLE marker (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , craftsman_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , building_map_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , created_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , approved DATETIME DEFAULT NULL, mark_xpercentage DOUBLE PRECISION NOT NULL, mark_ypercentage DOUBLE PRECISION NOT NULL, frame_xpercentage DOUBLE PRECISION NOT NULL, frame_ypercentage DOUBLE PRECISION NOT NULL, frame_xheight DOUBLE PRECISION NOT NULL, frame_ylength DOUBLE PRECISION NOT NULL, content CLOB NOT NULL COLLATE BINARY, image_file_name CLOB NOT NULL COLLATE BINARY, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, viewed_online BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_82CF20FE34508F72 FOREIGN KEY (craftsman_id) REFERENCES craftsman (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_82CF20FE4373858F FOREIGN KEY (building_map_id) REFERENCES building_map (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_82CF20FEB03A8386 FOREIGN KEY (created_by_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO marker (id, craftsman_id, building_map_id, created_by_id, approved, mark_xpercentage, mark_ypercentage, frame_xpercentage, frame_ypercentage, frame_xheight, frame_ylength, content, image_file_name, created_at, last_changed_at, viewed_online) SELECT id, craftsman_id, building_map_id, created_by_id, approved, mark_xpercentage, mark_ypercentage, frame_xpercentage, frame_ypercentage, frame_xheight, frame_ylength, content, image_file_name, created_at, last_changed_at, viewed_online FROM __temp__marker');
        $this->addSql('DROP TABLE __temp__marker');
        $this->addSql('CREATE INDEX IDX_82CF20FEB03A8386 ON marker (created_by_id)');
        $this->addSql('CREATE INDEX IDX_82CF20FE4373858F ON marker (building_map_id)');
        $this->addSql('CREATE INDEX IDX_82CF20FE34508F72 ON marker (craftsman_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE app_user_buildings (app_user_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , building_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(app_user_id, building_id))');
        $this->addSql('CREATE INDEX IDX_454BA9BD4D2A7E12 ON app_user_buildings (building_id)');
        $this->addSql('CREATE INDEX IDX_454BA9BD4A3353D8 ON app_user_buildings (app_user_id)');
        $this->addSql('DROP TABLE buildings_app_users');
        $this->addSql('DROP INDEX IDX_B8D991E4D2A7E12');
        $this->addSql('CREATE TEMPORARY TABLE __temp__building_map AS SELECT id, building_id, file_name, name, description, public_identifier, public_identifier_valid_till FROM building_map');
        $this->addSql('DROP TABLE building_map');
        $this->addSql('CREATE TABLE building_map (id CHAR(36) NOT NULL --(DC2Type:guid)
        , file_name CLOB DEFAULT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, public_identifier CLOB DEFAULT NULL, public_identifier_valid_till DATETIME DEFAULT NULL, building_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO building_map (id, building_id, file_name, name, description, public_identifier, public_identifier_valid_till) SELECT id, building_id, file_name, name, description, public_identifier, public_identifier_valid_till FROM __temp__building_map');
        $this->addSql('DROP TABLE __temp__building_map');
        $this->addSql('CREATE INDEX IDX_B8D991E4D2A7E12 ON building_map (building_id)');
        $this->addSql('DROP INDEX IDX_82CF20FE34508F72');
        $this->addSql('DROP INDEX IDX_82CF20FE4373858F');
        $this->addSql('DROP INDEX IDX_82CF20FEB03A8386');
        $this->addSql('CREATE TEMPORARY TABLE __temp__marker AS SELECT id, craftsman_id, building_map_id, created_by_id, approved, mark_xpercentage, mark_ypercentage, frame_xpercentage, frame_ypercentage, frame_xheight, frame_ylength, content, viewed_online, image_file_name, created_at, last_changed_at FROM marker');
        $this->addSql('DROP TABLE marker');
        $this->addSql('CREATE TABLE marker (id CHAR(36) NOT NULL --(DC2Type:guid)
        , approved DATETIME DEFAULT NULL, mark_xpercentage DOUBLE PRECISION NOT NULL, mark_ypercentage DOUBLE PRECISION NOT NULL, frame_xpercentage DOUBLE PRECISION NOT NULL, frame_ypercentage DOUBLE PRECISION NOT NULL, frame_xheight DOUBLE PRECISION NOT NULL, frame_ylength DOUBLE PRECISION NOT NULL, content CLOB NOT NULL, viewed_online BOOLEAN DEFAULT \'0\' NOT NULL, image_file_name CLOB NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, craftsman_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , building_map_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , created_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO marker (id, craftsman_id, building_map_id, created_by_id, approved, mark_xpercentage, mark_ypercentage, frame_xpercentage, frame_ypercentage, frame_xheight, frame_ylength, content, viewed_online, image_file_name, created_at, last_changed_at) SELECT id, craftsman_id, building_map_id, created_by_id, approved, mark_xpercentage, mark_ypercentage, frame_xpercentage, frame_ypercentage, frame_xheight, frame_ylength, content, viewed_online, image_file_name, created_at, last_changed_at FROM __temp__marker');
        $this->addSql('DROP TABLE __temp__marker');
        $this->addSql('CREATE INDEX IDX_82CF20FE34508F72 ON marker (craftsman_id)');
        $this->addSql('CREATE INDEX IDX_82CF20FE4373858F ON marker (building_map_id)');
        $this->addSql('CREATE INDEX IDX_82CF20FEB03A8386 ON marker (created_by_id)');
        $this->addSql('DROP INDEX IDX_9F74B8987887A021');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setting AS SELECT id, frontend_user_id, "key", content FROM setting');
        $this->addSql('DROP TABLE setting');
        $this->addSql('CREATE TABLE setting (id CHAR(36) NOT NULL --(DC2Type:guid)
        , "key" CLOB NOT NULL, content CLOB NOT NULL, frontend_user_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO setting (id, frontend_user_id, "key", content) SELECT id, frontend_user_id, "key", content FROM __temp__setting');
        $this->addSql('DROP TABLE __temp__setting');
        $this->addSql('CREATE INDEX IDX_9F74B8987887A021 ON setting (frontend_user_id)');
    }
}