-- =====================================================================
-- Hostel Complaint Management System
-- Enhanced Database Schema & Sample Data
-- Database Name: hostel_complaint_db
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `hostel_complaint_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `hostel_complaint_db`;

-- ---------------------------------------------------------------------
-- Table structure for table `users`
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'warden') NOT NULL,
    `roll_number` VARCHAR(30) DEFAULT NULL,
    `hostel_block` VARCHAR(20) DEFAULT 'Block A',
    `room_number` VARCHAR(20) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table structure for table `complaints`
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `complaints` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `priority` ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
    `description` TEXT NOT NULL,
    `status` ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    `warden_remark` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table structure for table `notices` (Hostel Announcement Board)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `content` TEXT NOT NULL,
    `posted_by` VARCHAR(100) DEFAULT 'Chief Warden',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
