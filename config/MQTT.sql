-- phpMyAdmin SQL Dump
-- version 5.0.4deb2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : ven. 24 juin 2022 à 16:51
-- Version du serveur :  10.5.15-MariaDB-0+deb11u1
-- Version de PHP : 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `MQTT`
--

-- --------------------------------------------------------

--
-- Structure de la table `mqtt-value`
--
CREATE DATABASE IF NOT EXISTS MQTT;
CREATE USER IF NOT EXISTS 'mqtt'@'%' IDENTIFIED BY 'mqtt';
GRANT ALL PRIVILEGES ON *.* TO 'mqtt'@'%';

USE MQTT;
CREATE TABLE IF NOT EXISTS `mqtt-value` (
  `id` int(11) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `mqtt-value`
--

INSERT INTO `mqtt-value` (`id`, `value`) VALUES
(1, 'message 26');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `mqtt-value`
--
ALTER TABLE `mqtt-value`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
