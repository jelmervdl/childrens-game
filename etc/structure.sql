# ************************************************************
# Sequel Pro SQL dump
# Version 4097
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.29-0ubuntu0.12.04.2)
# Database: franziska
# Generation Time: 2013-05-11 19:56:59 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table measurements
# ------------------------------------------------------------

CREATE TABLE `measurements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` char(4) NOT NULL DEFAULT '',
  `act_id` varchar(255) NOT NULL,
  `choice` varchar(255) NOT NULL,
  `start_time` bigint(20) NOT NULL,
  `stop_time` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table native_tongue
# ------------------------------------------------------------

CREATE TABLE `native_tongue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` char(4) NOT NULL DEFAULT '',
  `language` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table personal_details
# ------------------------------------------------------------

CREATE TABLE `personal_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` char(4) NOT NULL DEFAULT '',
  `age` int(11) NOT NULL,
  `sex` enum('mannelijk','vrouwelijk') DEFAULT NULL,
  `browser` text,
  `platform` text,
  `submitted` datetime DEFAULT NULL,
  `branch` varchar(255) NOT NULL DEFAULT 'master',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
