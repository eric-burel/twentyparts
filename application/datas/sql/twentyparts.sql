-- phpMyAdmin SQL Dump
-- version 4.0.9deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Dim 28 Septembre 2014 à 22:45
-- Version du serveur: 5.5.33-1
-- Version de PHP: 5.5.6-1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `twentyparts`
--

-- --------------------------------------------------------

--
-- Structure de la table `lang`
--

CREATE TABLE IF NOT EXISTS `lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fr_FR` longtext COLLATE utf8_unicode_ci,
  `en_EN` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Structure de la table `new`
--

CREATE TABLE IF NOT EXISTS `new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titleId` int(11) DEFAULT NULL,
  `descrId` int(11) DEFAULT NULL,
  `keywordsId` int(11) DEFAULT NULL,
  `contentId` int(11) DEFAULT NULL,
  `date` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `title` (`titleId`),
  KEY `content` (`contentId`),
  KEY `descrId` (`descrId`),
  KEY `keywordsId` (`keywordsId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `page`
--

CREATE TABLE IF NOT EXISTS `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titleId` int(11) DEFAULT NULL,
  `descrId` int(11) DEFAULT NULL,
  `keywordsId` int(11) DEFAULT NULL,
  `contentId` int(11) DEFAULT NULL,
  `isRequired` tinyint(1) NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `title` (`titleId`),
  KEY `content` (`contentId`),
  KEY `descrId` (`descrId`),
  KEY `keywordsId` (`keywordsId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `new`
--
ALTER TABLE `new`
  ADD CONSTRAINT `new_ibfk_1` FOREIGN KEY (`descrId`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `new_ibfk_2` FOREIGN KEY (`keywordsId`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `new_ibfk_3` FOREIGN KEY (`titleId`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `new_ibfk_4` FOREIGN KEY (`contentId`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Contraintes pour la table `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `page_ibfk_1` FOREIGN KEY (`descrId`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `page_ibfk_2` FOREIGN KEY (`keywordsId`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `page_ibfk_3` FOREIGN KEY (`titleId`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `page_ibfk_4` FOREIGN KEY (`contentId`) REFERENCES `lang` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;