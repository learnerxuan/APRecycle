-- ============================================
-- APRecycle Database Upgrade & Sample Data
-- ============================================
-- Purpose: Fix database structure and populate with sample data
-- Run this AFTER importing aprecycle.sql
-- ============================================

USE aprecycle;

-- ============================================
-- STEP 1: DATABASE STRUCTURE IMPROVEMENTS
-- ============================================

-- Fix challenge.point_multiplier from INT to DECIMAL
ALTER TABLE `challenge`
    MODIFY COLUMN `point_multiplier` DECIMAL(3,1) NOT NULL DEFAULT 1.0;

-- Add target_material_id to challenge table for Smart Challenges
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'challenge' AND COLUMN_NAME = 'target_material_id');
SET @alter_sql = IF(@col_exists = 0, 'ALTER TABLE `challenge` ADD COLUMN `target_material_id` INT NULL DEFAULT NULL AFTER `reward_id`, ADD CONSTRAINT `fk_challenge_material` FOREIGN KEY (`target_material_id`) REFERENCES `material`(`material_id`) ON DELETE SET NULL ON UPDATE CASCADE;', 'SELECT "Column target_material_id already exists"');
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add created_at to challenge table
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'challenge' AND COLUMN_NAME = 'created_at');
SET @alter_sql = IF(@col_exists = 0, 'ALTER TABLE `challenge` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP', 'SELECT "Column created_at already exists"');
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add is_completed to user_challenge table
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_challenge' AND COLUMN_NAME = 'is_completed');
SET @alter_sql = IF(@col_exists = 0, 'ALTER TABLE `user_challenge` ADD COLUMN `is_completed` BOOLEAN DEFAULT FALSE', 'SELECT "Column is_completed already exists"');
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add challenge_quantity to user_challenge table (tracks items recycled during challenge)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_challenge' AND COLUMN_NAME = 'challenge_quantity');
SET @alter_sql = IF(@col_exists = 0, 'ALTER TABLE `user_challenge` ADD COLUMN `challenge_quantity` INT NOT NULL DEFAULT 0 COMMENT "Number of items recycled during this challenge" AFTER `challenge_point`', 'SELECT "Column challenge_quantity already exists"');
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add target_quantity to challenge table (for quantity-based challenges)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'challenge' AND COLUMN_NAME = 'target_quantity');
SET @alter_sql = IF(@col_exists = 0, 'ALTER TABLE `challenge` ADD COLUMN `target_quantity` INT NOT NULL DEFAULT 0 COMMENT "Number of items to recycle for completion"', 'SELECT "Column target_quantity already exists"');
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add target_points to challenge table (for points-based challenges)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'challenge' AND COLUMN_NAME = 'target_points');
SET @alter_sql = IF(@col_exists = 0, 'ALTER TABLE `challenge` ADD COLUMN `target_points` INT NOT NULL DEFAULT 0 COMMENT "Points needed during challenge period for completion"', 'SELECT "Column target_points already exists"');
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add completion_type to challenge table
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'challenge' AND COLUMN_NAME = 'completion_type');
SET @alter_sql = IF(@col_exists = 0, 'ALTER TABLE `challenge` ADD COLUMN `completion_type` ENUM("quantity", "points", "participation") NOT NULL DEFAULT "quantity" COMMENT "How to complete: quantity=recycle X items, points=earn X points, participation=join+submit 1 item"', 'SELECT "Column completion_type already exists"');
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Remove point_required from reward table (rewards only from challenges)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reward' AND COLUMN_NAME = 'point_required');
SET @alter_sql = IF(@col_exists = 1, 'ALTER TABLE `reward` DROP COLUMN `point_required`', 'SELECT "Column point_required already removed"');
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add badge_type to badge table
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'badge' AND COLUMN_NAME = 'badge_type');
SET @alter_sql = IF(@col_exists = 0, 'ALTER TABLE `badge` ADD COLUMN `badge_type` ENUM("milestone", "challenge") NOT NULL DEFAULT "challenge" COMMENT "milestone=auto-unlock at points, challenge=only from completing challenges" AFTER `point_required`', 'SELECT "Column badge_type already exists"');
PREPARE stmt FROM @alter_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- STEP 2: SAMPLE DATA - MATERIALS
-- ============================================

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

-- ============================================
-- STEP 3: SAMPLE DATA - RECYCLING BINS
-- ============================================

INSERT INTO `recycling_bin` (`bin_id`, `bin_name`, `bin_location`) VALUES
(1, 'Bin Alpha', 'Library Main Entrance'),
(2, 'Bin Beta', 'Cafeteria Level 1'),
(3, 'Bin Gamma', 'Block A Ground Floor'),
(4, 'Bin Delta', 'Block B Level 2'),
(5, 'Bin Epsilon', 'Sports Complex'),
(6, 'Bin Zeta', 'Student Center'),
(7, 'Bin Eta', 'Admin Building Lobby'),
(8, 'Bin Theta', 'Parking Lot A');

-- ============================================
-- STEP 4: SAMPLE DATA - BADGES
-- ============================================
-- NOTE: Two types of badges:
-- 1. MILESTONE (badge_type='milestone', point_required > 0): Auto-unlock when user reaches lifetime points
-- 2. CHALLENGE (badge_type='challenge', point_required = 0): Only earned by completing specific challenges

INSERT INTO `badge` (`badge_id`, `badge_name`, `point_required`, `badge_type`, `description`) VALUES
-- MILESTONE BADGES (Auto-unlock at lifetime points)
(1, 'Bronze Recycler', 100, 'milestone', 'Earned by reaching 100 lifetime points - your recycling journey begins!'),
(2, 'Silver Recycler', 500, 'milestone', 'Reached 500 lifetime points - a dedicated environmental champion!'),
(3, 'Gold Recycler', 1000, 'milestone', 'Elite status achieved at 1000 lifetime points - leading the green revolution!'),
(4, 'Platinum Recycler', 2500, 'milestone', 'Legendary status! 2500+ lifetime points - ultimate recycling master'),

-- CHALLENGE-SPECIFIC BADGES (Only from completing challenges)
(5, 'Plastic Free November Winner', 0, 'challenge', 'Completed the Plastic Free November challenge'),
(6, 'E-Waste Hero', 0, 'challenge', 'Successfully completed the E-Waste Drive challenge'),
(7, 'Earth Week Champion', 0, 'challenge', 'Participated and completed Earth Week Challenge'),
(8, 'Paper Saving Pioneer', 0, 'challenge', 'Completed the Paper Recycling Month challenge'),
(9, 'Aluminum Can Crusher', 0, 'challenge', 'Completed the Aluminum Can Drive challenge'),
(10, 'Welcome Badge', 0, 'challenge', 'Completed your first challenge - welcome to APRecycle!');

-- ============================================
-- STEP 5: SAMPLE DATA - REWARDS
-- ============================================
-- NOTE: Rewards are ONLY from challenges (no point_required)
-- They are physical items awarded when users complete specific challenges

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

-- ============================================
-- STEP 6: SAMPLE DATA - TEAMS
-- ============================================

INSERT INTO `team` (`team_id`, `team_name`, `description`, `date_created`, `points`) VALUES
(1, 'Green Warriors', 'United for a sustainable campus! We recycle, we compete, we win!', '2024-09-01 10:00:00', 850),
(2, 'Eco Legends', 'Legendary recyclers saving the planet one item at a time', '2024-09-05 14:30:00', 720),
(3, 'Planet Defenders', 'Defending our planet through aggressive recycling and teamwork', '2024-09-10 09:15:00', 650),
(4, 'Sustainability Squad', 'Squad goals: Zero waste campus!', '2024-09-15 16:45:00', 580),
(5, 'Recycle Rebels', 'Rebelling against waste! Join the revolution!', '2024-09-20 11:20:00', 490),
(6, 'Earth Guardians', 'Guarding Mother Earth with every recycle', '2024-10-01 13:00:00', 420),
(7, 'Zero Waste Heroes', 'Heroes don''t wear capes, they recycle!', '2024-10-10 10:30:00', 380),
(8, 'The Composters', 'Turning waste into worth, one day at a time', '2024-10-20 15:00:00', 310);

-- ============================================
-- STEP 7: SAMPLE DATA - USERS
-- ============================================
-- Password for all users: password123 (hashed with password_hash)

INSERT INTO `user` (`user_id`, `username`, `password`, `email`, `role`, `qr_code`, `lifetime_points`, `created_at`, `team_id`) VALUES
-- Administrators
(1, 'admin1', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'admin@aprecycle.com', 'administrator', 'QR_ADMIN001', 0, '2024-08-01 08:00:00', NULL),
(2, 'admin2', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'admin2@aprecycle.com', 'administrator', 'QR_ADMIN002', 0, '2024-08-01 08:30:00', NULL),

-- Eco-Moderators
(3, 'moderator1', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'moderator1@aprecycle.com', 'eco-moderator', 'QR_MOD001', 150, '2024-08-05 09:00:00', NULL),
(4, 'moderator2', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'moderator2@aprecycle.com', 'eco-moderator', 'QR_MOD002', 200, '2024-08-05 09:30:00', NULL),
(5, 'moderator3', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'moderator3@aprecycle.com', 'eco-moderator', 'QR_MOD003', 180, '2024-08-10 10:00:00', NULL),

-- Recyclers (Team 1 - Green Warriors)
(6, 'john_warrior', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'john@student.apu.edu.my', 'recycler', 'QR_USR006', 450, '2024-09-01 11:00:00', 1),
(7, 'sarah_green', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'sarah@student.apu.edu.my', 'recycler', 'QR_USR007', 420, '2024-09-02 12:00:00', 1),
(8, 'mike_eco', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'mike@student.apu.edu.my', 'recycler', 'QR_USR008', 380, '2024-09-03 13:00:00', 1),

-- Recyclers (Team 2 - Eco Legends)
(9, 'emma_legend', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'emma@student.apu.edu.my', 'recycler', 'QR_USR009', 390, '2024-09-05 15:00:00', 2),
(10, 'david_eco', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'david@student.apu.edu.my', 'recycler', 'QR_USR010', 360, '2024-09-06 16:00:00', 2),
(11, 'lisa_planet', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'lisa@student.apu.edu.my', 'recycler', 'QR_USR011', 340, '2024-09-07 14:00:00', 2),

-- Recyclers (Team 3 - Planet Defenders)
(12, 'alex_defender', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'alex@student.apu.edu.my', 'recycler', 'QR_USR012', 350, '2024-09-10 10:00:00', 3),
(13, 'maria_save', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'maria@student.apu.edu.my', 'recycler', 'QR_USR013', 320, '2024-09-11 11:00:00', 3),

-- Recyclers (Team 4 - Sustainability Squad)
(14, 'ryan_squad', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'ryan@student.apu.edu.my', 'recycler', 'QR_USR014', 310, '2024-09-15 17:00:00', 4),
(15, 'sophia_sustain', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'sophia@student.apu.edu.my', 'recycler', 'QR_USR015', 290, '2024-09-16 18:00:00', 4),

-- Recyclers (No Team)
(16, 'kevin_solo', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'kevin@student.apu.edu.my', 'recycler', 'QR_USR016', 250, '2024-09-20 12:00:00', NULL),
(17, 'jenny_new', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'jenny@student.apu.edu.my', 'recycler', 'QR_USR017', 180, '2024-10-01 13:00:00', NULL),
(18, 'tom_beginner', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'tom@student.apu.edu.my', 'recycler', 'QR_USR018', 120, '2024-10-15 14:00:00', NULL),
(19, 'anna_starter', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'anna@student.apu.edu.my', 'recycler', 'QR_USR019', 90, '2024-11-01 15:00:00', NULL),
(20, 'peter_fresh', '$2y$10$ZqO6N1//qDuUZeuslem8FOPdnS1wQ6s4lJ853EvqQ9Z/oZy2hqSQi', 'peter@student.apu.edu.my', 'recycler', 'QR_USR020', 60, '2024-11-15 16:00:00', NULL);

-- ============================================
-- STEP 8: FIX RECYCLER QR CODES
-- ============================================
-- Update all recycler QR codes to use the correct format: RECYCLER:user_id:hash
-- This ensures QR codes match the format expected by bin_camera system

UPDATE `user`
SET qr_code = CONCAT('RECYCLER:', user_id, ':', SUBSTRING(SHA2(CONCAT(user_id, username, 'APRecycle2024SecretKey'), 256), 1, 16))
WHERE role = 'recycler';

-- ============================================
-- STEP 9: SAMPLE DATA - CHALLENGES
-- ============================================
-- NOTE: Two types of badges/rewards in this system:
-- 1. MILESTONE BADGES/REWARDS - Auto-unlocked when user reaches point_required (e.g., badge.point_required = 500)
-- 2. CHALLENGE BADGES/REWARDS - Awarded when challenge is completed (based on target_quantity/target_points/completion_type)

INSERT INTO `challenge` (`challenge_id`, `title`, `description`, `start_date`, `end_date`, `badge_id`, `reward_id`, `point_multiplier`, `target_material_id`, `target_quantity`, `target_points`, `completion_type`) VALUES
-- Active Challenges (quantity-based)
(1, 'Plastic Free November', 'Join us in reducing plastic waste this November! Recycle at least 20 plastic items to complete this challenge and earn the Plastic Free November Winner badge plus Stainless Steel Water Bottle reward!', '2024-11-01', '2024-11-30', 5, 2, 2.0, 1, 20, 0, 'quantity'),
(2, 'E-Waste Drive December', 'Bring your old electronics! Recycle 5 e-waste items this month to win the E-Waste Hero badge and Premium Wireless Earbuds. Plus 1.5x points on all e-waste!', '2024-12-01', '2024-12-31', 6, 5, 1.5, 6, 5, 0, 'quantity'),

-- Upcoming Challenges (mixed types)
(3, 'Earth Week Challenge 2025', 'Celebrate Earth Week by earning 300 points! Complete this points-based challenge to earn the Earth Week Champion badge and Bamboo Cutlery Set. 2.5x multiplier active!', '2025-01-15', '2025-01-22', 7, 3, 2.5, NULL, 0, 300, 'points'),
(4, 'Paper Recycling Month', 'February is Paper Month! Recycle 40 paper items to save trees and earn the Paper Saving Pioneer badge plus APU Hoodie. Triple points for all paper!', '2025-02-01', '2025-02-28', 8, 4, 3.0, 5, 40, 0, 'quantity'),
(5, 'Aluminum Can Drive', 'Crush it! Recycle 25 aluminum cans to complete the challenge and earn the Aluminum Can Crusher badge plus APU Eco Tote Bag. 1.8x points boost!', '2025-03-01', '2025-03-15', 9, 1, 1.8, 2, 25, 0, 'quantity'),

-- Past Challenges
(6, 'October Kickoff Challenge', 'Start your recycling journey this October! Just participate by submitting any 1 item to earn the Welcome Badge and Eco Tote Bag. 1.5x points for everyone!', '2024-10-01', '2024-10-31', 10, 1, 1.5, NULL, 1, 0, 'participation'),
(7, 'Back to School Recycle', 'Welcome back! Simple participation challenge - recycle 1 item to get started with the Welcome Badge. Perfect for beginners!', '2024-09-01', '2024-09-30', 10, NULL, 1.2, NULL, 1, 0, 'participation');

-- ============================================
-- STEP 10: SAMPLE DATA - USER CHALLENGES
-- ============================================

INSERT INTO `user_challenge` (`user_id`, `challenge_id`, `challenge_point`, `date_joined`) VALUES
-- Challenge 1: Plastic Free November (Active)
(6, 1, 180, '2024-11-01 10:00:00'),
(7, 1, 160, '2024-11-01 11:00:00'),
(8, 1, 140, '2024-11-02 09:00:00'),
(9, 1, 170, '2024-11-01 14:00:00'),
(10, 1, 150, '2024-11-03 10:00:00'),
(11, 1, 130, '2024-11-04 11:00:00'),
(12, 1, 120, '2024-11-05 12:00:00'),

-- Challenge 2: E-Waste Drive December (Active)
(6, 2, 80, '2024-12-01 10:00:00'),
(9, 2, 75, '2024-12-01 11:00:00'),
(12, 2, 60, '2024-12-02 09:00:00'),

-- Challenge 6: October Kickoff (Past - Completed)
(6, 6, 250, '2024-10-01 09:00:00'),
(7, 6, 220, '2024-10-01 10:00:00'),
(8, 6, 210, '2024-10-02 11:00:00'),
(9, 6, 200, '2024-10-01 13:00:00'),
(10, 6, 180, '2024-10-03 14:00:00');

-- ============================================
-- STEP 11: SAMPLE DATA - USER BADGES
-- ============================================

INSERT INTO `user_badge` (`user_id`, `badge_id`, `date_awarded`) VALUES
-- High achievers with multiple badges
(6, 1, '2024-09-15 10:00:00'),  -- Eco Warrior
(6, 2, '2024-10-20 11:00:00'),  -- Green Champion
(6, 4, '2024-11-10 12:00:00'),  -- Plastic Crusher
(6, 7, '2024-11-05 13:00:00'),  -- Week Streak

(7, 1, '2024-09-18 10:00:00'),
(7, 2, '2024-10-25 11:00:00'),
(7, 9, '2024-10-15 12:00:00'),  -- Team Player

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

-- ============================================
-- STEP 12: SAMPLE DATA - USER REWARDS
-- ============================================

INSERT INTO `user_reward` (`user_id`, `reward_id`, `date_earned`) VALUES
(6, 1, '2024-09-20 10:00:00'),  -- APU Eco Tote Bag
(6, 2, '2024-10-15 11:00:00'),  -- Stainless Steel Water Bottle
(6, 3, '2024-11-01 12:00:00'),  -- Bamboo Cutlery Set

(7, 1, '2024-09-25 10:00:00'),
(7, 2, '2024-10-20 11:00:00'),

(9, 1, '2024-09-28 10:00:00'),
(9, 2, '2024-10-25 11:00:00'),

(10, 1, '2024-10-05 10:00:00'),

(12, 1, '2024-10-10 10:00:00'),
(12, 6, '2024-11-05 11:00:00');  -- Plant Starter Kit

-- ============================================
-- STEP 13: SAMPLE DATA - RECYCLING SUBMISSIONS
-- ============================================

INSERT INTO `recycling_submission` (`submission_id`, `user_id`, `bin_id`, `image_url`, `ai_confidence`, `status`, `moderator_feedback`) VALUES
-- Approved submissions
(1, 6, 1, '/uploads/submissions/sub_001.jpg', 95.50, 'approved', 'Great job! Clear plastic bottle, properly cleaned.'),
(2, 6, 2, '/uploads/submissions/sub_002.jpg', 92.30, 'approved', 'Perfect aluminum can recycling!'),
(3, 7, 1, '/uploads/submissions/sub_003.jpg', 88.70, 'approved', 'Good work on the cardboard box.'),
(4, 8, 3, '/uploads/submissions/sub_004.jpg', 96.20, 'approved', 'Excellent glass bottle condition.'),
(5, 9, 2, '/uploads/submissions/sub_005.jpg', 91.50, 'approved', 'Nice plastic container!'),
(6, 10, 4, '/uploads/submissions/sub_006.jpg', 94.80, 'approved', 'Great paper recycling.'),

-- Rejected submission
(10, 14, 1, '/uploads/submissions/sub_010.jpg', 45.20, 'rejected', 'Sorry, this item is not recyclable. Please check our educational content for proper waste identification.'),

-- More approved submissions for variety
(11, 6, 5, '/uploads/submissions/sub_011.jpg', 97.10, 'approved', 'Perfect e-waste submission!'),
(12, 7, 6, '/uploads/submissions/sub_012.jpg', 93.40, 'approved', 'Good aluminum recycling.'),
(13, 9, 7, '/uploads/submissions/sub_013.jpg', 89.80, 'approved', 'Great metal scrap!'),
(14, 12, 8, '/uploads/submissions/sub_014.jpg', 95.60, 'approved', 'Excellent cardboard condition.'),
(15, 15, 1, '/uploads/submissions/sub_015.jpg', 90.20, 'approved', 'Well done!');

-- ============================================
-- STEP 14: SAMPLE DATA - SUBMISSION MATERIALS
-- ============================================

INSERT INTO `submission_material` (`submission_id`, `material_id`, `quantity`) VALUES
(1, 1, 3),   -- 3 plastic bottles
(2, 2, 5),   -- 5 aluminum cans
(3, 4, 2),   -- 2 cardboard boxes
(4, 3, 1),   -- 1 glass bottle
(5, 9, 2),   -- 2 plastic containers
(6, 5, 10),  -- 10 papers
(10, 1, 1),  -- Rejected: 1 item
(11, 6, 1),  -- 1 small e-waste
(12, 2, 8),  -- 8 aluminum cans
(13, 8, 2),  -- 2 metal scraps
(14, 4, 4),  -- 4 cardboard boxes
(15, 5, 15); -- 15 papers

-- ============================================
-- STEP 15: SAMPLE DATA - EDUCATIONAL CONTENT
-- ============================================

INSERT INTO `educational_content` (`content_id`, `title`, `content_body`, `image`, `tags`, `created_at`, `author_id`) VALUES
(1, 'How to Identify Recyclable Plastics', 'Learn about the different types of plastic and which ones can be recycled. Look for the recycling symbol with numbers 1-7. PET (#1) and HDPE (#2) are the most commonly recycled plastics.\n\nSteps to recycle plastic properly:\n1. Check the recycling number\n2. Clean the plastic item\n3. Remove caps and labels if possible\n4. Place in the correct bin\n\nRemember: Clean and dry plastics recycle better!', '/images/content/plastic_guide.jpg', 'plastic,recycling,guide,beginner', '2024-09-10 10:00:00', 3),

(2, 'E-Waste Recycling Guide', 'Electronic waste contains valuable materials that can be recovered and reused. Never throw electronics in regular trash!\n\nWhat counts as e-waste:\n- Old phones and tablets\n- Broken laptops\n- Cables and chargers\n- Small appliances\n- Batteries\n\nImportant: Remove all personal data before recycling electronics. Our campus e-waste bins are located at the IT Department and Student Center.', '/images/content/ewaste_guide.jpg', 'e-waste,electronics,safety,guide', '2024-09-15 11:00:00', 4),

(3, 'Paper Recycling Best Practices', 'Paper is one of the easiest materials to recycle, but it needs to be clean and dry.\n\nDO recycle:\n✓ Newspapers and magazines\n✓ Office paper\n✓ Cardboard boxes\n✓ Paper bags\n\nDON\'T recycle:\n✗ Greasy pizza boxes\n✗ Wax-coated paper\n✗ Tissues or paper towels\n✗ Shredded paper (too small)\n\nTip: Flatten cardboard boxes to save space!', '/images/content/paper_guide.jpg', 'paper,cardboard,office,tips', '2024-09-20 12:00:00', 3),

(4, 'Aluminum Can Recycling Facts', 'Did you know? Recycling one aluminum can saves enough energy to power a laptop for 3 hours!\n\nQuick facts:\n- Aluminum can be recycled infinitely\n- Takes 60 days for a can to go from bin to store shelf\n- 95% less energy than making new cans\n\nHow to recycle cans:\n1. Rinse the can\n2. Crush it (optional but saves space)\n3. Place in aluminum recycling bin\n\nEvery can counts!', '/images/content/aluminum_facts.jpg', 'aluminum,metal,facts,energy', '2024-10-01 13:00:00', 4),

(5, 'Glass Recycling 101', 'Glass is 100% recyclable and can be recycled endlessly without loss of quality.\n\nTypes of glass we accept:\n- Clear bottles\n- Colored bottles (green, brown)\n- Glass jars\n\nNOT accepted:\n- Window glass\n- Light bulbs\n- Mirrors\n- Ceramics\n\nSafety first: Handle broken glass carefully and place in designated containers.', '/images/content/glass_guide.jpg', 'glass,bottles,safety,guide', '2024-10-10 14:00:00', 5),

(6, 'Why Recycling Matters', 'Every item you recycle makes a real difference!\n\nEnvironmental benefits:\n🌍 Reduces landfill waste\n🌳 Saves natural resources\n💨 Lowers carbon emissions\n💧 Conserves water and energy\n\nOn our campus alone, we\'ve saved:\n- 500kg of CO₂ this semester\n- Equivalent to planting 25 trees\n- 1000+ items diverted from landfills\n\nYour contribution matters - keep recycling!', '/images/content/why_recycle.jpg', 'motivation,impact,environment,general', '2024-10-15 15:00:00', 3),

(7, 'Common Recycling Mistakes to Avoid', 'Avoid these common mistakes for better recycling:\n\n❌ Wishcycling - Don\'t recycle items you\'re unsure about\n❌ Dirty containers - Always rinse before recycling\n❌ Plastic bags in bins - Use designated bag collection\n❌ Mixing materials - Sort properly\n\n✅ When in doubt, check with eco-moderators\n✅ Use our campus recycling guide app\n✅ Attend monthly recycling workshops\n\nBetter recycling = Better environment!', '/images/content/mistakes.jpg', 'tips,mistakes,education,general', '2024-10-20 16:00:00', 4),

(8, 'Tetra Pak Recycling Instructions', 'Tetra Pak cartons (juice boxes, milk cartons) are recyclable!\n\nHow to recycle:\n1. Rinse the carton\n2. Flatten it\n3. Place in designated bin\n\nTetra Paks are made of:\n- 75% paper\n- 20% plastic\n- 5% aluminum\n\nAll three materials can be separated and recycled. Find Tetra Pak bins at all cafeterias!', '/images/content/tetrapak.jpg', 'tetrapak,cartons,guide,tips', '2024-11-01 17:00:00', 5);

-- ============================================
-- COMPLETION MESSAGE
-- ============================================

SELECT '✅ Database upgrade and sample data insertion completed successfully!' AS Status;
SELECT '' AS '';
SELECT '📝 IMPORTANT: Badge & Reward System Explanation' AS Notice;
SELECT '' AS '';
SELECT '🎯 This database now supports TWO types of badge/reward systems:' AS '';
SELECT '' AS '';
SELECT '1️⃣  MILESTONE SYSTEM (Auto-unlock):' AS '';
SELECT '   - Badges/rewards have point_required field' AS '';
SELECT '   - Auto-awarded when user reaches lifetime points threshold' AS '';
SELECT '   - Example: "Green Champion" badge at 500 lifetime points' AS '';
SELECT '' AS '';
SELECT '2️⃣  CHALLENGE SYSTEM (Completion-based):' AS '';
SELECT '   - Challenges have completion criteria (quantity/points/participation)' AS '';
SELECT '   - Badge/reward awarded when challenge is completed' AS '';
SELECT '   - Example: "Recycle 20 plastic bottles → Get Plastic Crusher badge"' AS '';
SELECT '' AS '';
SELECT '📊 Challenge Completion Types:' AS '';
SELECT '   - QUANTITY: Recycle X items (target_quantity)' AS '';
SELECT '   - POINTS: Earn X points during challenge (target_points)' AS '';
SELECT '   - PARTICIPATION: Just join + submit 1 item (easiest)' AS '';
SELECT '' AS '';
SELECT CONCAT('Materials: ', COUNT(*), ' records') AS Summary FROM material
UNION ALL
SELECT CONCAT('Bins: ', COUNT(*), ' records') FROM recycling_bin
UNION ALL
SELECT CONCAT('Badges: ', COUNT(*), ' records') FROM badge
UNION ALL
SELECT CONCAT('Rewards: ', COUNT(*), ' records') FROM reward
UNION ALL
SELECT CONCAT('Teams: ', COUNT(*), ' records') FROM team
UNION ALL
SELECT CONCAT('Users: ', COUNT(*), ' records') FROM user
UNION ALL
SELECT CONCAT('Challenges: ', COUNT(*), ' records') FROM challenge
UNION ALL
SELECT CONCAT('Educational Content: ', COUNT(*), ' records') FROM educational_content
UNION ALL
SELECT CONCAT('Recycling Submissions: ', COUNT(*), ' records') FROM recycling_submission;

-- ============================================
-- END OF SCRIPT
-- ============================================
