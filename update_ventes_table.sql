-- Script pour ajouter la colonne remise à la table ventes existante
-- Exécutez ce script si la table ventes existe déjà dans votre base de données

USE shop;

-- Ajouter la colonne remise à la table ventes
ALTER TABLE `ventes` ADD COLUMN `remise` varchar(10) NULL AFTER `prix`;

-- Vérifier que la colonne a été ajoutée
DESCRIBE `ventes`;
