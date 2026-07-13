SET foreign_key_checks = 0;

-- Add calagopus_user_id column to mythicaldash_users table
ALTER TABLE `mythicaldash_users`
ADD COLUMN `calagopus_user_id` INT(11) DEFAULT 0 AFTER `pterodactyl_user_id`;

-- Create index for faster lookups
ALTER TABLE `mythicaldash_users`
ADD INDEX `idx_calagopus_user_id` (`calagopus_user_id`);

SET foreign_key_checks = 1;
