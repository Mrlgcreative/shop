-- Script de migration pour ajouter la colonne devise aux tables existantes
-- Exécutez ce script si votre base de données existe déjà sans la colonne devise

USE shop;

-- Ajouter la colonne devise à la table articles
ALTER TABLE `articles` ADD COLUMN `devise` enum('FC','USD') NOT NULL DEFAULT 'FC' AFTER `quantité`;

-- Ajouter la colonne devise à la table historique  
ALTER TABLE `historique` ADD COLUMN `devise` enum('FC','USD') NOT NULL DEFAULT 'FC' AFTER `réduction`;

-- Ajouter la colonne devise à la table ventes
ALTER TABLE `ventes` ADD COLUMN `devise` enum('FC','USD') NOT NULL DEFAULT 'FC' AFTER `remise`;

-- Mettre à jour les enregistrements existants avec la devise par défaut FC
UPDATE `articles` SET `devise` = 'FC' WHERE `devise` = '';
UPDATE `historique` SET `devise` = 'FC' WHERE `devise` = '';  
UPDATE `ventes` SET `devise` = 'FC' WHERE `devise` = '';

-- Vérifier les modifications
DESCRIBE `articles`;
DESCRIBE `historique`;
DESCRIBE `ventes`;

SELECT 'Migration terminée avec succès ! Colonne devise ajoutée aux tables articles, historique et ventes.' as message;
