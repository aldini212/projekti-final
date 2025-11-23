-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `gamehub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `gamehub`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `house` varchar(50) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `level` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
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
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `password`, `house`, `points`, `level`) VALUES
(1, 'Hipsters', 'hipsters@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hipsters', 10000, 10),
(2, 'Speeders', 'speeders@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Speeders', 9000, 9),
(3, 'Engineers', 'engineers@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Engineers', 8000, 8),
(4, 'Shadows', 'shadows@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shadows', 8500, 8);

-- Insert default game
INSERT IGNORE INTO `games` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Trivia Game', 'trivia', 'Test your knowledge with our fun trivia game!');

-- Insert some sample scores
INSERT IGNORE INTO `scores` (`user_id`, `game_id`, `score`, `xp_earned`, `time_spent`) VALUES
(1, 1, 5000, 500, 300),
(1, 1, 4500, 450, 280),
(2, 1, 4000, 400, 250),
(3, 1, 3500, 350, 320),
(4, 1, 3000, 300, 290);

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
