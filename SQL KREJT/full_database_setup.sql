-- Full Database Setup for GameHub
-- This file contains all database schema and initial data
-- Last updated: 2023-11-23

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `gamehub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `gamehub`;

-- Create migrations table to track which migrations have been run
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `run_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users table with all migrations applied
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `house` varchar(50) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `xp` int(11) DEFAULT 0,
  `level` int(11) DEFAULT 1,
  `games_played` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `house` (`house`),
  KEY `level` (`level`),
  KEY `xp` (`xp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Games table
CREATE TABLE IF NOT EXISTS `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Scores table
CREATE TABLE IF NOT EXISTS `scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `xp_earned` int(11) NOT NULL DEFAULT 0,
  `time_spent` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `game_id` (`game_id`),
  KEY `created_at` (`created_at`),
  KEY `score` (`score`),
  CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Points log table
CREATE TABLE IF NOT EXISTS `points_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `points` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `game_id` (`game_id`),
  CONSTRAINT `points_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `points_log_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default houses data
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `password`, `house`, `points`, `xp`, `level`, `games_played`) VALUES
(1, 'Hipsters', 'hipsters@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hipsters', 10000, 10000, 10, 50),
(2, 'Speeders', 'speeders@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Speeders', 9000, 9000, 9, 45),
(3, 'Engineers', 'engineers@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Engineers', 8000, 8000, 8, 40),
(4, 'Shadows', 'shadows@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shadows', 8500, 8500, 8, 42);

-- Insert default games
INSERT IGNORE INTO `games` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Trivia Game', 'trivia', 'Test your knowledge with our fun trivia game!'),
(2, 'Memory Game', 'memory', 'Match pairs of cards in this classic memory game!'),
(3, 'Word Scramble', 'word-scramble', 'Unscramble letters to form words!'),
(4, 'Land Mine', 'land-mine', 'Navigate through a minefield!'),
(5, 'Guess Number', 'guess-number', 'Can you guess the secret number?'),
(6, 'Reaction Time', 'reaction-time', 'Test your reflexes in this quick reaction game!');

-- Insert some sample scores
INSERT IGNORE INTO `scores` (`user_id`, `game_id`, `score`, `xp_earned`, `time_spent`) VALUES
(1, 1, 5000, 500, 300),
(1, 2, 4800, 480, 280),
(1, 3, 4600, 460, 320),
(1, 4, 5200, 520, 350),
(1, 5, 4900, 490, 290),
(1, 6, 5100, 510, 310),
(2, 1, 4000, 400, 250),
(2, 2, 3800, 380, 270),
(2, 3, 3600, 360, 300),
(2, 4, 4200, 420, 330),
(2, 5, 3900, 390, 270),
(2, 6, 4100, 410, 290),
(3, 1, 3500, 350, 320),
(3, 2, 3300, 330, 300),
(3, 3, 3100, 310, 280),
(3, 4, 3700, 370, 350),
(3, 5, 3400, 340, 290),
(3, 6, 3600, 360, 310),
(4, 1, 3000, 300, 290),
(4, 2, 2800, 280, 310),
(4, 3, 2600, 260, 270),
(4, 4, 3200, 320, 340),
(4, 5, 2900, 290, 280),
(4, 6, 3100, 310, 300);

-- Create house leaderboard view
CREATE OR REPLACE VIEW `house_leaderboard` AS
SELECT 
    u.house,
    COUNT(DISTINCT s.user_id) as total_players,
    COUNT(s.id) as total_games_played,
    SUM(s.score) as total_score,
    SUM(s.xp_earned) as total_xp,
    ROUND(AVG(s.score), 2) as avg_score_per_game,
    ROUND(SUM(s.xp_earned) / COUNT(DISTINCT s.user_id), 2) as avg_xp_per_player
FROM 
    users u
JOIN 
    scores s ON u.id = s.user_id
WHERE 
    u.house IS NOT NULL
    AND u.house != 'Beginner'
GROUP BY 
    u.house
ORDER BY 
    u.house = 'Hipsters' DESC,
    total_xp DESC;

-- Create user leaderboard view
CREATE OR REPLACE VIEW `user_leaderboard` AS
SELECT 
    u.id,
    u.username,
    u.house,
    u.avatar,
    u.level,
    u.xp,
    COUNT(s.id) as games_played,
    SUM(s.score) as total_score,
    SUM(s.xp_earned) as total_xp_earned,
    ROUND(AVG(s.score), 2) as avg_score,
    COUNT(DISTINCT s.game_id) as unique_games_played
FROM 
    users u
LEFT JOIN 
    scores s ON u.id = s.user_id
WHERE 
    u.house IS NOT NULL
    AND u.house != 'Beginner'
GROUP BY 
    u.id, u.username, u.house, u.avatar, u.level, u.xp
ORDER BY 
    u.xp DESC, u.level DESC, u.username;

-- Create game statistics view
CREATE OR REPLACE VIEW `game_statistics` AS
SELECT 
    g.id,
    g.name,
    g.slug,
    COUNT(DISTINCT s.user_id) as total_players,
    COUNT(s.id) as total_games_played,
    IFNULL(SUM(s.score), 0) as total_score,
    IFNULL(ROUND(AVG(s.score), 2), 0) as avg_score,
    IFNULL(MAX(s.score), 0) as high_score,
    IFNULL(SUM(s.xp_earned), 0) as total_xp_earned,
    IFNULL(ROUND(AVG(s.time_spent), 2), 0) as avg_time_spent
FROM 
    games g
LEFT JOIN 
    scores s ON g.id = s.game_id
GROUP BY 
    g.id, g.name, g.slug
ORDER BY 
    total_players DESC, total_games_played DESC;

-- Insert migration records to indicate these changes have been applied
INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
('000_create_migrations_table', 1),
('001_add_house_to_users', 1),
('002_add_xp_to_users', 1),
('003_add_house_leaderboard', 1),
('004_add_games_played_column', 1);

-- Add any additional indexes for performance
ALTER TABLE `scores` ADD INDEX `idx_score_xp` (`score`, `xp_earned`);
ALTER TABLE `users` ADD INDEX `idx_house_points` (`house`, `points`);
ALTER TABLE `users` ADD INDEX `idx_level_xp` (`level`, `xp`);
