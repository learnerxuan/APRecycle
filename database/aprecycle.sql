-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 09, 2026 at 04:59 AM
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `badge`
--

INSERT INTO `badge` (`badge_id`, `badge_name`, `point_required`, `badge_type`, `description`) VALUES
(1, 'Bronze Recycler', 100, 'milestone', 'Earned by reaching 100 lifetime points - your recycling journey begins!'),
(2, 'Silver Recycler', 500, 'milestone', 'Reached 500 lifetime points - a dedicated environmental champion!'),
(3, 'Gold Recycler', 1000, 'milestone', 'Elite status achieved at 1000 lifetime points - leading the green revolution!'),
(4, 'Platinum Recycler', 2500, 'milestone', 'Legendary status! 2500+ lifetime points - ultimate recycling master'),
(5, 'Plastic Free November Winner', 0, 'challenge', 'Completed the Plastic Free November challenge'),
(6, 'E-Waste Hero', 0, 'challenge', 'Successfully completed the E-Waste Drive challenge'),
(7, 'Earth Week Champion', 0, 'challenge', 'Participated and completed Earth Week Challenge'),
(8, 'Paper Saving Pioneer', 0, 'challenge', 'Completed the Paper Recycling Month challenge'),
(9, 'Aluminum Can Crusher', 0, 'challenge', 'Completed the Aluminum Can Drive challenge'),
(10, 'Welcome Badge', 0, 'challenge', 'Completed your first challenge - welcome to APRecycle!');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `challenge`
--

INSERT INTO `challenge` (`challenge_id`, `title`, `description`, `start_date`, `end_date`, `badge_id`, `reward_id`, `point_multiplier`, `target_material_id`, `target_quantity`, `target_points`, `completion_type`, `created_at`) VALUES
(1, 'Plastic Free November', 'Join us in reducing plastic waste this November! Recycle at least 20 plastic items to complete this challenge and earn the Plastic Free November Winner badge plus Stainless Steel Water Bottle reward!', '2024-11-01', '2024-11-30', 5, 2, 2.0, 1, 20, 0, 'quantity', '2026-01-09 12:58:53'),
(2, 'E-Waste Drive December', 'Bring your old electronics! Recycle 5 e-waste items this month to win the E-Waste Hero badge and Premium Wireless Earbuds. Plus 1.5x points on all e-waste!', '2024-12-01', '2024-12-31', 6, 5, 1.5, 6, 5, 0, 'quantity', '2026-01-09 12:58:53'),
(3, 'Earth Week Challenge 2025', 'Celebrate Earth Week by earning 300 points! Complete this points-based challenge to earn the Earth Week Champion badge and Bamboo Cutlery Set. 2.5x multiplier active!', '2025-01-15', '2025-01-22', 7, 3, 2.5, NULL, 0, 300, 'points', '2026-01-09 12:58:53'),
(4, 'Paper Recycling Month', 'February is Paper Month! Recycle 40 paper items to save trees and earn the Paper Saving Pioneer badge plus APU Hoodie. Triple points for all paper!', '2025-02-01', '2025-02-28', 8, 4, 3.0, 5, 40, 0, 'quantity', '2026-01-09 12:58:53'),
(5, 'Aluminum Can Drive', 'Crush it! Recycle 25 aluminum cans to complete the challenge and earn the Aluminum Can Crusher badge plus APU Eco Tote Bag. 1.8x points boost!', '2025-03-01', '2025-03-15', 9, 1, 1.8, 2, 25, 0, 'quantity', '2026-01-09 12:58:53'),
(6, 'October Kickoff Challenge', 'Start your recycling journey this October! Just participate by submitting any 1 item to earn the Welcome Badge and Eco Tote Bag. 1.5x points for everyone!', '2024-10-01', '2024-10-31', 10, 1, 1.5, NULL, 1, 0, 'participation', '2026-01-09 12:58:53'),
(7, 'Back to School Recycle', 'Welcome back! Simple participation challenge - recycle 1 item to get started with the Welcome Badge. Perfect for beginners!', '2024-09-01', '2024-09-30', 10, NULL, 1.2, NULL, 1, 0, 'participation', '2026-01-09 12:58:53');

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
  `status` enum('published','draft') NOT NULL DEFAULT 'published',
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `material`
--

INSERT INTO `material` (`material_id`, `material_name`, `points_per_item`) VALUES
(1, 'Plastic Bottle (PET)', 10),
(2, 'Aluminum Can', 15),
(3, 'Glass Bottle', 12),
(4, 'Cardboard Box', 8),
(5, 'Paper', 5),
(6, 'E-Waste (Small)', 25),
(7, 'E-Waste (Large)', 50),
(8, 'Metal Scrap', 20),
(9, 'Plastic Container (HDPE)', 10),
(10, 'Tetra Pak', 8);

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `recycling_bin`
--

INSERT INTO `recycling_bin` (`bin_id`, `bin_name`, `bin_location`) VALUES
(1, 'Bin Alpha', 'Library Main Entrance'),
(2, 'Bin Beta', 'Cafeteria Level 1'),
(3, 'Bin Gamma', 'Block A Ground Floor'),
(4, 'Bin Delta', 'Block B Level 2'),
(5, 'Bin Epsilon', 'Sports Complex'),
(6, 'Bin Zeta', 'Student Center'),
(7, 'Bin Eta', 'Admin Building Lobby'),
(8, 'Bin Theta', 'Parking Lot A');

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `recycling_submission`
--

INSERT INTO `recycling_submission` (`submission_id`, `user_id`, `bin_id`, `image_url`, `ai_confidence`, `status`, `moderator_feedback`, `created_at`) VALUES
(1, 6, 1, '/uploads/waste_001.jpg', 0.96, 'approved', 'Great job! Clear plastic bottle, properly cleaned.', '2026-01-09 12:58:53'),
(2, 6, 2, '/uploads/waste_002.jpg', 0.92, 'approved', 'Perfect aluminum can recycling!', '2026-01-09 12:58:53'),
(3, 7, 1, '/uploads/waste_003.jpg', 0.89, 'approved', 'Good work on the cardboard box.', '2026-01-09 12:58:53'),
(4, 8, 3, '/uploads/waste_004.jpg', 0.96, 'approved', 'Excellent glass bottle condition.', '2026-01-09 12:58:53'),
(5, 9, 2, '/uploads/waste_005.jpg', 0.92, 'approved', 'Nice plastic container!', '2026-01-09 12:58:53'),
(6, 10, 4, '/uploads/waste_006.jpg', 0.95, 'approved', 'Great paper recycling.', '2026-01-09 12:58:53'),
(10, 14, 1, '/uploads/waste_010.jpg', 0.45, 'rejected', 'Sorry, this item is not recyclable. Please check our educational content for proper waste identification.', '2026-01-09 12:58:53'),
(11, 6, 5, '/uploads/waste_011.jpg', 0.97, 'approved', 'Perfect e-waste submission!', '2026-01-09 12:58:53'),
(12, 7, 6, '/uploads/waste_012.jpg', 0.93, 'approved', 'Good aluminum recycling.', '2026-01-09 12:58:53'),
(13, 9, 7, '/uploads/waste_013.jpg', 0.90, 'approved', 'Great metal scrap!', '2026-01-09 12:58:53'),
(14, 12, 8, '/uploads/waste_014.jpg', 0.96, 'approved', 'Excellent cardboard condition.', '2026-01-09 12:58:53'),
(15, 15, 1, '/uploads/waste_015.jpg', 0.90, 'approved', 'Well done!', '2026-01-09 12:58:53');

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Rewards are ONLY from challenges (no point_required)';

--
-- Dumping data for table `reward`
--

INSERT INTO `reward` (`reward_id`, `reward_name`, `description`) VALUES
(1, 'APU Eco Tote Bag', 'Reusable canvas tote bag with APU Recycle logo'),
(2, 'Stainless Steel Water Bottle', 'Premium 500ml stainless steel water bottle'),
(3, 'Bamboo Cutlery Set', 'Eco-friendly bamboo cutlery with carrying case'),
(4, 'APU Hoodie (Recycled Material)', 'Limited edition hoodie made from recycled plastic bottles'),
(5, 'Wireless Earbuds', 'Premium wireless earbuds - top tier reward!'),
(6, 'Plant Starter Kit', 'Mini indoor plant kit with pot and seeds'),
(7, 'Eco Journal & Pen', 'Recycled paper journal with bamboo pen'),
(8, 'Campus Cafeteria Voucher (RM20)', 'RM20 credit for campus cafeteria'),
(9, 'Portable Solar Charger', 'Compact solar-powered phone charger'),
(10, 'APU Laptop Backpack', 'Durable laptop backpack with recycled materials');

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

--
-- Dumping data for table `submission_material`
--

INSERT INTO `submission_material` (`submission_id`, `material_id`, `quantity`) VALUES
(1, 1, 3),
(2, 2, 5),
(3, 4, 2),
(4, 3, 1),
(5, 9, 2),
(6, 5, 10),
(10, 1, 1),
(11, 6, 1),
(12, 2, 8),
(13, 8, 2),
(14, 4, 4),
(15, 5, 15);

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`team_id`, `team_name`, `description`, `date_created`, `points`) VALUES
(1, 'Green Warriors', 'United for a sustainable campus! We recycle, we compete, we win!', '2024-09-01 10:00:00', 850),
(2, 'Eco Legends', 'Legendary recyclers saving the planet one item at a time', '2024-09-05 14:30:00', 720),
(3, 'Planet Defenders', 'Defending our planet through aggressive recycling and teamwork', '2024-09-10 09:15:00', 650),
(4, 'Sustainability Squad', 'Squad goals: Zero waste campus!', '2024-09-15 16:45:00', 580),
(5, 'Recycle Rebels', 'Rebelling against waste! Join the revolution!', '2024-09-20 11:20:00', 490),
(6, 'Earth Guardians', 'Guarding Mother Earth with every recycle', '2024-10-01 13:00:00', 420),
(7, 'Zero Waste Heroes', 'Heroes don\'t wear capes, they recycle!', '2024-10-10 10:30:00', 380),
(8, 'The Composters', 'Turning waste into worth, one day at a time', '2024-10-20 15:00:00', 310);

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `email`, `role`, `qr_code`, `lifetime_points`, `created_at`, `team_id`) VALUES
(1, 'admin1', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'admin@aprecycle.com', 'administrator', 'QR_ADMIN001', 0, '2024-08-01 08:00:00', NULL),
(2, 'admin2', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'admin2@aprecycle.com', 'administrator', 'QR_ADMIN002', 0, '2024-08-01 08:30:00', NULL),
(3, 'moderator1', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'moderator1@aprecycle.com', 'eco-moderator', 'QR_MOD001', 150, '2024-08-05 09:00:00', NULL),
(4, 'moderator2', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'moderator2@aprecycle.com', 'eco-moderator', 'QR_MOD002', 200, '2024-08-05 09:30:00', NULL),
(5, 'moderator3', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'moderator3@aprecycle.com', 'eco-moderator', 'QR_MOD003', 180, '2024-08-10 10:00:00', NULL),
(6, 'john_warrior', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'john@student.apu.edu.my', 'recycler', 'RECYCLER:6:7b8c22dd34024dcd', 450, '2024-09-01 11:00:00', 1),
(7, 'sarah_green', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'sarah@student.apu.edu.my', 'recycler', 'RECYCLER:7:466228a69069a426', 420, '2024-09-02 12:00:00', 1),
(8, 'mike_eco', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'mike@student.apu.edu.my', 'recycler', 'RECYCLER:8:fd86ab4d6ddb0918', 380, '2024-09-03 13:00:00', 1),
(9, 'emma_legend', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'emma@student.apu.edu.my', 'recycler', 'RECYCLER:9:982b382684cc1249', 390, '2024-09-05 15:00:00', 2),
(10, 'david_eco', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'david@student.apu.edu.my', 'recycler', 'RECYCLER:10:0173b2e08d1c1c2e', 360, '2024-09-06 16:00:00', 2),
(11, 'lisa_planet', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'lisa@student.apu.edu.my', 'recycler', 'RECYCLER:11:195aca954cd4cdc4', 340, '2024-09-07 14:00:00', 2),
(12, 'alex_defender', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'alex@student.apu.edu.my', 'recycler', 'RECYCLER:12:936627b1953e93e1', 350, '2024-09-10 10:00:00', 3),
(13, 'maria_save', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'maria@student.apu.edu.my', 'recycler', 'RECYCLER:13:22ddc7be0d2c97bd', 320, '2024-09-11 11:00:00', 3),
(14, 'ryan_squad', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'ryan@student.apu.edu.my', 'recycler', 'RECYCLER:14:973ea025ec7f3aee', 310, '2024-09-15 17:00:00', 4),
(15, 'sophia_sustain', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'sophia@student.apu.edu.my', 'recycler', 'RECYCLER:15:c6435c0e2294f79f', 290, '2024-09-16 18:00:00', 4),
(16, 'kevin_solo', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'kevin@student.apu.edu.my', 'recycler', 'RECYCLER:16:cc1bad7b54538d68', 250, '2024-09-20 12:00:00', NULL),
(17, 'jenny_new', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'jenny@student.apu.edu.my', 'recycler', 'RECYCLER:17:303f7e2ae44bf6e0', 180, '2024-10-01 13:00:00', NULL),
(18, 'tom_beginner', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'tom@student.apu.edu.my', 'recycler', 'RECYCLER:18:8e3d18a4e829e900', 120, '2024-10-15 14:00:00', NULL),
(19, 'anna_starter', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'anna@student.apu.edu.my', 'recycler', 'RECYCLER:19:63779edf5e7e22cc', 90, '2024-11-01 15:00:00', NULL),
(20, 'peter_fresh', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'peter@student.apu.edu.my', 'recycler', 'RECYCLER:20:c508bc738b997d3f', 60, '2024-11-15 16:00:00', NULL);

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

--
-- Dumping data for table `user_badge`
--

INSERT INTO `user_badge` (`user_id`, `badge_id`, `date_awarded`) VALUES
(6, 1, '2024-09-15 10:00:00'),
(6, 2, '2024-10-20 11:00:00'),
(6, 4, '2024-11-10 12:00:00'),
(6, 7, '2024-11-05 13:00:00'),
(7, 1, '2024-09-18 10:00:00'),
(7, 2, '2024-10-25 11:00:00'),
(7, 9, '2024-10-15 12:00:00'),
(8, 1, '2024-09-20 10:00:00'),
(8, 9, '2024-10-10 11:00:00'),
(9, 1, '2024-09-22 10:00:00'),
(9, 2, '2024-10-28 11:00:00'),
(9, 4, '2024-11-12 12:00:00'),
(10, 1, '2024-09-25 10:00:00'),
(10, 9, '2024-10-20 11:00:00'),
(11, 1, '2024-09-28 10:00:00'),
(12, 1, '2024-10-01 10:00:00'),
(12, 9, '2024-10-25 11:00:00'),
(13, 1, '2024-10-05 10:00:00');

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

--
-- Dumping data for table `user_challenge`
--

INSERT INTO `user_challenge` (`user_id`, `challenge_id`, `challenge_point`, `challenge_quantity`, `is_completed`, `date_joined`) VALUES
(6, 1, 180, 0, 0, '2024-11-01 10:00:00'),
(6, 2, 80, 0, 0, '2024-12-01 10:00:00'),
(6, 6, 250, 0, 0, '2024-10-01 09:00:00'),
(7, 1, 160, 0, 0, '2024-11-01 11:00:00'),
(7, 6, 220, 0, 0, '2024-10-01 10:00:00'),
(8, 1, 140, 0, 0, '2024-11-02 09:00:00'),
(8, 6, 210, 0, 0, '2024-10-02 11:00:00'),
(9, 1, 170, 0, 0, '2024-11-01 14:00:00'),
(9, 2, 75, 0, 0, '2024-12-01 11:00:00'),
(9, 6, 200, 0, 0, '2024-10-01 13:00:00'),
(10, 1, 150, 0, 0, '2024-11-03 10:00:00'),
(10, 6, 180, 0, 0, '2024-10-03 14:00:00'),
(11, 1, 130, 0, 0, '2024-11-04 11:00:00'),
(12, 1, 120, 0, 0, '2024-11-05 12:00:00'),
(12, 2, 60, 0, 0, '2024-12-02 09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `user_reward`
--

DROP TABLE IF EXISTS `user_reward`;
CREATE TABLE IF NOT EXISTS `user_reward` (
  `user_id` int NOT NULL,
  `reward_id` int NOT NULL,
  `date_earned` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_claimed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=earned but not claimed, 1=claimed',
  PRIMARY KEY (`user_id`,`reward_id`),
  KEY `reward_id` (`reward_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_reward`
--

INSERT INTO `user_reward` (`user_id`, `reward_id`, `date_earned`, `is_claimed`) VALUES
(6, 1, '2024-09-20 10:00:00', 0),
(6, 2, '2024-10-15 11:00:00', 0),
(6, 3, '2024-11-01 12:00:00', 0),
(7, 1, '2024-09-25 10:00:00', 0),
(7, 2, '2024-10-20 11:00:00', 0),
(9, 1, '2024-09-28 10:00:00', 0),
(9, 2, '2024-10-25 11:00:00', 0),
(10, 1, '2024-10-05 10:00:00', 0),
(12, 1, '2024-10-10 10:00:00', 0),
(12, 6, '2024-11-05 11:00:00', 0);

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
