1-- phpMyAdmin SQL Dump
-- version 
-- http://www.phpmyadmin.net
--
-- Хост: u380919.mysql.masterhost.ru
-- Время создания: Фев 25 2014 г., 15:45
-- Версия сервера: 5.5.28
-- Версия PHP: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `u380919_vls`
--

-- --------------------------------------------------------

--
-- Структура таблицы `history_status`
--

CREATE TABLE IF NOT EXISTS `history_status` (
  `id_anketa` int(11) NOT NULL,
  `status_name` tinyint(2) DEFAULT NULL,
  `status_date` date DEFAULT NULL,
  `object` varchar(50) DEFAULT NULL,
  `status_subdivision` varchar(50) DEFAULT NULL,
  `domitory` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `grade` int(5) DEFAULT NULL,
  `rank` varchar(10) DEFAULT NULL,
  `rate` varchar(10) DEFAULT NULL,
  `bonus` varchar(10) DEFAULT NULL,
  `comment` text,
  `id_user` int(11) NOT NULL,
  `date_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `id_anketa` (`id_anketa`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
