-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 24, 2025 at 01:07 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aprecycle`
--

-- --------------------------------------------------------

--
-- Table structure for table `badge`
--

DROP TABLE IF EXISTS `badge`;
CREATE TABLE IF NOT EXISTS `badge` (
  `badge_id` int NOT NULL AUTO_INCREMENT,
  `badge_name` varchar(100) NOT NULL,
  `point_required` int NOT NULL DEFAULT '0' COMMENT '0 = challenge-only badge, >0 = milestone badge (auto-unlock at this lifetime points)',
  `badge_type` enum('milestone','challenge') NOT NULL DEFAULT 'challenge' COMMENT 'milestone = auto-unlock at points, challenge = only from completing challenges',
  `description` text NOT NULL,
  PRIMARY KEY (`badge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `challenge`
--

DROP TABLE IF EXISTS `challenge`;
CREATE TABLE IF NOT EXISTS `challenge` (
  `challenge_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `badge_id` int DEFAULT NULL COMMENT 'Challenge-specific badge (awarded on completion)',
  `reward_id` int DEFAULT NULL COMMENT 'Challenge-specific reward (awarded on completion)',
  `point_multiplier` decimal(3,1) NOT NULL DEFAULT '1.0',
  `target_material_id` int DEFAULT NULL COMMENT 'Specific material for smart challenges',
  `target_quantity` int NOT NULL DEFAULT '0' COMMENT 'Number of items to recycle (for quantity-based challenges)',
  `target_points` int NOT NULL DEFAULT '0' COMMENT 'Points to earn during challenge period (for points-based challenges)',
  `completion_type` enum('quantity','points','participation') NOT NULL DEFAULT 'quantity' COMMENT 'How to complete: quantity=recycle X items, points=earn X points, participation=join+submit 1 item',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`challenge_id`),
  KEY `badge_id` (`badge_id`),
  KEY `reward_id` (`reward_id`),
  KEY `target_material_id` (`target_material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `educational_content`
--

DROP TABLE IF EXISTS `educational_content`;
CREATE TABLE IF NOT EXISTS `educational_content` (
  `content_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `content_body` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author_id` int DEFAULT NULL,
  PRIMARY KEY (`content_id`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material`
--

DROP TABLE IF EXISTS `material`;
CREATE TABLE IF NOT EXISTS `material` (
  `material_id` int NOT NULL AUTO_INCREMENT,
  `material_name` varchar(100) NOT NULL,
  `points_per_item` int NOT NULL,
  PRIMARY KEY (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recycling_bin`
--

DROP TABLE IF EXISTS `recycling_bin`;
CREATE TABLE IF NOT EXISTS `recycling_bin` (
  `bin_id` int NOT NULL AUTO_INCREMENT,
  `bin_name` varchar(50) NOT NULL,
  `bin_location` varchar(50) NOT NULL,
  PRIMARY KEY (`bin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recycling_submission`
--

DROP TABLE IF EXISTS `recycling_submission`;
CREATE TABLE IF NOT EXISTS `recycling_submission` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `bin_id` int NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `ai_confidence` decimal(5,2) NOT NULL,
  `status` varchar(20) NOT NULL,
  `moderator_feedback` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`submission_id`),
  KEY `user_id` (`user_id`),
  KEY `bin_id` (`bin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reward`
--

DROP TABLE IF EXISTS `reward`;
CREATE TABLE IF NOT EXISTS `reward` (
  `reward_id` int NOT NULL AUTO_INCREMENT,
  `reward_name` varchar(100) NOT NULL,
  `description` text NOT NULL COMMENT 'Physical items awarded when completing challenges',
  PRIMARY KEY (`reward_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Rewards are ONLY from challenges (no point_required)';

-- --------------------------------------------------------

--
-- Table structure for table `submission_material`
--

DROP TABLE IF EXISTS `submission_material`;
CREATE TABLE IF NOT EXISTS `submission_material` (
  `submission_id` int NOT NULL,
  `material_id` int NOT NULL,
  `quantity` int NOT NULL,
  PRIMARY KEY (`submission_id`,`material_id`),
  KEY `material_id` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

DROP TABLE IF EXISTS `team`;
CREATE TABLE IF NOT EXISTS `team` (
  `team_id` int NOT NULL AUTO_INCREMENT,
  `team_name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `points` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`team_id`),
  UNIQUE KEY `team_name_unique` (`team_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `lifetime_points` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `team_id` int DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `team_id` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_badge`
--

DROP TABLE IF EXISTS `user_badge`;
CREATE TABLE IF NOT EXISTS `user_badge` (
  `user_id` int NOT NULL,
  `badge_id` int NOT NULL,
  `date_awarded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`badge_id`),
  KEY `badge_id` (`badge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_challenge`
--

DROP TABLE IF EXISTS `user_challenge`;
CREATE TABLE IF NOT EXISTS `user_challenge` (
  `user_id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `challenge_point` int NOT NULL DEFAULT '0' COMMENT 'Points earned during this challenge period',
  `challenge_quantity` int NOT NULL DEFAULT '0' COMMENT 'Number of items recycled during this challenge',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `date_joined` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`challenge_id`),
  KEY `challenge_id` (`challenge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_reward`
--

DROP TABLE IF EXISTS `user_reward`;
CREATE TABLE IF NOT EXISTS `user_reward` (
  `user_id` int NOT NULL,
  `reward_id` int NOT NULL,
  `date_earned` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`reward_id`),
  KEY `reward_id` (`reward_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `challenge`
--
ALTER TABLE `challenge`
  ADD CONSTRAINT `challenge_ibfk_1` FOREIGN KEY (`badge_id`) REFERENCES `badge` (`badge_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `challenge_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `reward` (`reward_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `challenge_ibfk_3` FOREIGN KEY (`target_material_id`) REFERENCES `material` (`material_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `educational_content`
--
ALTER TABLE `educational_content`
  ADD CONSTRAINT `educational_content_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `recycling_submission`
--
ALTER TABLE `recycling_submission`
  ADD CONSTRAINT `recycling_submission_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recycling_submission_ibfk_2` FOREIGN KEY (`bin_id`) REFERENCES `recycling_bin` (`bin_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `submission_material`
--
ALTER TABLE `submission_material`
  ADD CONSTRAINT `submission_material_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `recycling_submission` (`submission_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `submission_material_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `material` (`material_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `team` (`team_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `user_badge`
--
ALTER TABLE `user_badge`
  ADD CONSTRAINT `user_badge_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_badge_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badge` (`badge_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_challenge`
--
ALTER TABLE `user_challenge`
  ADD CONSTRAINT `user_challenge_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_challenge_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenge` (`challenge_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_reward`
--
ALTER TABLE `user_reward`
  ADD CONSTRAINT `user_reward_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_reward_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `reward` (`reward_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
