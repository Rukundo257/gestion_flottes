-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 23 sep. 2025 à 12:08
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Base de données : `gestion_flottes_automobiles`
DROP DATABASE IF EXISTS `gestion_flottes_automobiles`;
CREATE DATABASE `gestion_flottes_automobiles` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gestion_flottes_automobiles`;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur` (pour authentification basique)
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` INT(11) NOT NULL AUTO_INCREMENT,
  `nom_utilisateur` VARCHAR(50) NOT NULL UNIQUE,
  `mot_de_passe` VARCHAR(255) NOT NULL, -- Hashé avec password_hash()
  `role` ENUM('admin', 'utilisateur') NOT NULL DEFAULT 'utilisateur',
  `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `vehicule`
--

CREATE TABLE `vehicule` (
  `id_vehicule` INT(11) NOT NULL AUTO_INCREMENT,
  `immatriculation` VARCHAR(20) NOT NULL UNIQUE,
  `marque` VARCHAR(50) NOT NULL,
  `modele` VARCHAR(50) NOT NULL,
  `type` VARCHAR(30) NOT NULL,
  `kilometrage` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `statut` ENUM('disponible', 'en_maintenance', 'affecte', 'hors_service') NOT NULL DEFAULT 'disponible',
  PRIMARY KEY (`id_vehicule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Données exemple
--
INSERT INTO `vehicule` (`id_vehicule`, `immatriculation`, `marque`, `modele`, `type`, `kilometrage`, `statut`) VALUES
(1, 'I6PPO0P', 'TI', 'taxi', 'voiture', 60.00, 'disponible');

-- --------------------------------------------------------

--
-- Structure de la table `conducteur`
--

CREATE TABLE `conducteur` (
  `id_conducteur` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(50) NOT NULL,
  `prenom` VARCHAR(50) NOT NULL,
  `date_naissance` DATE NOT NULL,
  `numero_permis` VARCHAR(30) NOT NULL UNIQUE,
  `date_obtention_permis` DATE NOT NULL,
  `contact` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id_conducteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `affectation`
--

CREATE TABLE `affectation` (
  `id_affectation` INT(11) NOT NULL AUTO_INCREMENT,
  `id_vehicule` INT(11) NOT NULL,
  `id_conducteur` INT(11) NOT NULL,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE DEFAULT NULL,
  `role` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`id_affectation`),
  FOREIGN KEY (`id_vehicule`) REFERENCES `vehicule` (`id_vehicule`) ON DELETE CASCADE,
  FOREIGN KEY (`id_conducteur`) REFERENCES `conducteur` (`id_conducteur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `assurance`
--

CREATE TABLE `assurance` (
  `id_assurance` INT(11) NOT NULL AUTO_INCREMENT,
  `id_vehicule` INT(11) NOT NULL,
  `compagnie` VARCHAR(100) NOT NULL,
  `numero_police` VARCHAR(50) NOT NULL UNIQUE,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `cout` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id_assurance`),
  FOREIGN KEY (`id_vehicule`) REFERENCES `vehicule` (`id_vehicule`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `carburant`
--

CREATE TABLE `carburant` (
  `id_carburant` INT(11) NOT NULL AUTO_INCREMENT,
  `id_vehicule` INT(11) NOT NULL,
  `date_plein` DATE NOT NULL,
  `quantite` DECIMAL(10,2) NOT NULL,
  `cout_total` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id_carburant`),
  FOREIGN KEY (`id_vehicule`) REFERENCES `vehicule` (`id_vehicule`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `maintenance`
--

CREATE TABLE `maintenance` (
  `id_maintenance` INT(11) NOT NULL AUTO_INCREMENT,
  `id_vehicule` INT(11) NOT NULL,
  `date_maintenance` DATE NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `cout` DECIMAL(10,2) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`id_maintenance`),
  FOREIGN KEY (`id_vehicule`) REFERENCES `vehicule` (`id_vehicule`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `trajet`
--

CREATE TABLE `trajet` (
  `id_trajet` INT(11) NOT NULL AUTO_INCREMENT,
  `id_vehicule` INT(11) NOT NULL,
  `id_conducteur` INT(11) NOT NULL,
  `date_heure_debut` DATETIME NOT NULL,
  `date_heure_fin` DATETIME DEFAULT NULL,
  `point_depart` VARCHAR(255) NOT NULL,
  `point_arrivee` VARCHAR(255) NOT NULL,
  `distance_km` DECIMAL(10,2) NOT NULL,
  `description` TEXT,
  PRIMARY KEY (`id_trajet`),
  FOREIGN KEY (`id_vehicule`) REFERENCES `vehicule` (`id_vehicule`) ON DELETE CASCADE,
  FOREIGN KEY (`id_conducteur`) REFERENCES `conducteur` (`id_conducteur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `geolocalisation` (liée à trajet pour traçabilité)
--

CREATE TABLE `geolocalisation` (
  `id_geo` INT(11) NOT NULL AUTO_INCREMENT,
  `id_trajet` INT(11) NOT NULL,
  `latitude` DECIMAL(10,6) NOT NULL,
  `longitude` DECIMAL(10,6) NOT NULL,
  `vitesse` DECIMAL(6,2) NOT NULL,
  `direction` VARCHAR(20) NOT NULL,
  `date_heure` DATETIME NOT NULL,
  PRIMARY KEY (`id_geo`),
  FOREIGN KEY (`id_trajet`) REFERENCES `trajet` (`id_trajet`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;