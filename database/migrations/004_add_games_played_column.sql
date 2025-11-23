-- Migration 004: Add games_played column to users table
-- Date: 2025-11-23
-- Description: Adds a counter to track total games played by each user

-- Add the new column
ALTER TABLE `users` ADD COLUMN `games_played` INT NOT NULL DEFAULT 0 AFTER `experience`;

-- Update existing users to have their current games played count
-- This is a simplified version - you might want to update this based on actual game plays
UPDATE `users` u 
SET `games_played` = (
    SELECT COUNT(DISTINCT game_id) 
    FROM scores 
    WHERE user_id = u.id
);
