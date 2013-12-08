-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 08, 2013 at 09:50 AM
-- Server version: 5.5.24
-- PHP Version: 5.3.10-1ubuntu3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `transcripts`
--

-- --------------------------------------------------------

--
-- Table structure for table `authority`
--

CREATE TABLE IF NOT EXISTS `authority` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `uri` text NOT NULL,
  `image` tinytext NOT NULL,
  `coinUri` text NOT NULL,
  `coinInfo` longtext NOT NULL,
  `author` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1856 ;

-- --------------------------------------------------------

--
-- Table structure for table `cs_comments`
--

CREATE TABLE IF NOT EXISTS `cs_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `transcript` varchar(32) NOT NULL,
  `interviewee` text NOT NULL,
  `mention` text NOT NULL,
  `comment` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pairs` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `cs_results`
--

CREATE TABLE IF NOT EXISTS `cs_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` text NOT NULL,
  `target` text NOT NULL,
  `transcript` varchar(32) NOT NULL,
  `user` int(11) NOT NULL,
  `value` text NOT NULL,
  `idLocals` tinytext NOT NULL,
  `points` tinyint(4) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3920 ;

-- --------------------------------------------------------

--
-- Table structure for table `cs_transcripts`
--

CREATE TABLE IF NOT EXISTS `cs_transcripts` (
  `transcript` varchar(32) NOT NULL,
  `totalPairs` smallint(6) NOT NULL DEFAULT '0',
  `totalResponse` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`transcript`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cs_users`
--

CREATE TABLE IF NOT EXISTS `cs_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `type` tinytext NOT NULL,
  `email` text NOT NULL,
  `pass` text NOT NULL,
  `oauth_token` text NOT NULL,
  `oauth_verifier` text NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `reset` varchar(40) NOT NULL,
  `cookie` varchar(40) NOT NULL,
  `screenName` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=248 ;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE IF NOT EXISTS `matches` (
  `id` int(11) DEFAULT NULL,
  `transcript` varchar(32) NOT NULL,
  `idLocal` int(11) NOT NULL,
  `personURI` text NOT NULL,
  `type` char(1) NOT NULL,
  `speaker` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `other`
--

CREATE TABLE IF NOT EXISTS `other` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `personURI` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9092 ;

-- --------------------------------------------------------

--
-- Table structure for table `text`
--

CREATE TABLE IF NOT EXISTS `text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transcript` varchar(32) NOT NULL,
  `text` mediumtext NOT NULL,
  `idLocal` int(11) NOT NULL,
  `type` char(1) NOT NULL,
  `speaker` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=40766 ;

-- --------------------------------------------------------

--
-- Table structure for table `transcripts`
--

CREATE TABLE IF NOT EXISTS `transcripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `md5` varchar(32) NOT NULL,
  `sourceName` text NOT NULL,
  `sourceURL` text NOT NULL,
  `interviewee` text NOT NULL,
  `intervieweeURI` text NOT NULL,
  `interviewers` text NOT NULL,
  `interviewees` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=174 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
