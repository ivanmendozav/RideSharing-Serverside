-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2015 at 02:51 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ridesharingmining`
--

-- --------------------------------------------------------

--
-- Table structure for table `accelerometer_sensor_data`
--

CREATE TABLE IF NOT EXISTS `accelerometer_sensor_data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `x` double NOT NULL,
  `y` double NOT NULL,
  `z` double NOT NULL,
  `timestamp` bigint(14) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `battery_sensor_data`
--

CREATE TABLE IF NOT EXISTS `battery_sensor_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'unique id',
  `charging` tinyint(1) NOT NULL COMMENT 'is device charging',
  `usb` tinyint(1) NOT NULL COMMENT 'is connected to usb',
  `ac` tinyint(1) NOT NULL COMMENT 'is connected to ac',
  `level` tinyint(3) unsigned NOT NULL COMMENT 'battery level',
  `timestamp` bigint(14) NOT NULL COMMENT 'sensor timestamp',
  `user_id` int(11) NOT NULL COMMENT 'account unique id',
  `enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'for logical deletion',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'database timestamo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gps_sensor_data`
--

CREATE TABLE IF NOT EXISTS `gps_sensor_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `timestamp` bigint(14) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `altitude` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wifi_sensor_data`
--

CREATE TABLE IF NOT EXISTS `wifi_sensor_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `timestamp` bigint(14) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `ssid` varchar(50) COLLATE utf8_spanish2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
