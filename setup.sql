-- MySQL Setup Script for Momentum App
-- Import this via phpMyAdmin

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) DEFAULT 'User',
  `is_banned` TINYINT(1) DEFAULT 0,
  `total_xp` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `training_plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `duration_weeks` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `user_plan_enrollments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `plan_id` INT NOT NULL,
  `status` VARCHAR(50) DEFAULT 'active',
  `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `training_plans`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `badges` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `badge_name` VARCHAR(255) NOT NULL,
  `earned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `workouts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `duration` INT NOT NULL, -- duration in seconds or minutes
  `date_logged` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `exercises` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `video_url` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `plan_workouts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `day_of_plan` INT NOT NULL,
  `workout_type` VARCHAR(255) NOT NULL,
  `suggested_duration` INT,
  FOREIGN KEY (`plan_id`) REFERENCES `training_plans`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `workout_exercises` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_workout_id` INT NOT NULL,
  `exercise_id` INT NOT NULL,
  `sets` INT NOT NULL,
  `reps` VARCHAR(50),
  `duration_seconds` INT,
  FOREIGN KEY (`plan_workout_id`) REFERENCES `plan_workouts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`exercise_id`) REFERENCES `exercises`(`id`) ON DELETE RESTRICT
);
