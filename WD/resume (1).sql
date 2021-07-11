-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Jul 07, 2021 at 07:59 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resume`
--

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `job_title`, `description`, `is_deleted`) VALUES
(1, 'YOUR CONTACT DETAILS', '	YOUR CONTACT DETAILS																									', 0),
(2, 'job title one', '										\r\n	Card titles are used by adding .card-title to a <h*> tag. In the same way, links are added and placed next to each other by adding .card-link to an <a> tag.\r\n\r\nSubtitles are used by adding a .card-subtitle to a <h*> tag. If the .card-title and the .card-subtitle items are placed in a .card-body item, the card title and subtitle are aligned nicely.																	', 0),
(3, 'sdf', '\r\n	sdfsdf								', 0),
(4, 'first tile', '\r\n	Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when ...								', 0),
(5, 'YOUR CONTACT DETAILS', '		sdfsdf																	', 0);

-- --------------------------------------------------------

--
-- Table structure for table `upload_cv`
--

CREATE TABLE `upload_cv` (
  `id` int(11) NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `skills` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `upload_cv`
--
ALTER TABLE `upload_cv`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `upload_cv`
--
ALTER TABLE `upload_cv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
