-- Add house column to users table
ALTER TABLE `users` ADD COLUMN `house` VARCHAR(50) NULL DEFAULT NULL AFTER `level`;

-- Update existing users with random houses
UPDATE `users` SET `house` = 
  ELT(1 + FLOOR(RAND() * 3), 
    'Hipster', 
    'Speedster', 
    'Shadow',
    'Beginner'
  ) 
WHERE `house` IS NULL;
