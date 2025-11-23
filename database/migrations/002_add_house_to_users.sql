-- Add house column to users table if it doesn't exist
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `house` VARCHAR(50) NULL DEFAULT NULL AFTER `level`;

-- Update existing users with random houses if house is NULL
-- Using a more reliable method for even distribution
UPDATE `users` 
SET `house` = CASE 
  WHEN RAND() < 0.25 THEN 'Hipster'
  WHEN RAND() < 0.5 THEN 'Speedster'
  WHEN RAND() < 0.75 THEN 'Shadow'
  ELSE 'Beginner'
END
WHERE `house` IS NULL;
