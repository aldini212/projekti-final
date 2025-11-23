-- Add XP column to scores table if it doesn't exist
ALTER TABLE `scores` 
ADD COLUMN IF NOT EXISTS `xp_earned` INT NOT NULL DEFAULT 0 AFTER `score`;

-- Create house_leaderboard view
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
    total_xp DESC;
