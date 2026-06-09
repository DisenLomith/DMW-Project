-- Database: `pet_adoption_db`
-- Full schema for Pet Adoption System

CREATE DATABASE IF NOT EXISTS `pet_adoption_db`;
USE `pet_adoption_db`;

-- Admin table
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(100) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin (username: admin, password: admin123)
INSERT INTO `admin` (`admin_name`, `admin_password`) VALUES
('admin', '$2y$12$oSu8zDlEC6DTJEFJGp8XMelohxLhWlDdkQoA868yJvsms1QiQBulu');

-- Adopters table
CREATE TABLE IF NOT EXISTS `adopters` (
  `adopter_id` int(11) NOT NULL AUTO_INCREMENT,
  `adopter_nic` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `occupation` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `adopter_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`adopter_id`),
  UNIQUE KEY `adopter_nic` (`adopter_nic`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pets table
CREATE TABLE IF NOT EXISTS `pets` (
  `pet_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `pet_name` varchar(100) NOT NULL,
  `breed` varchar(100) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `age` int(11) NOT NULL,
  `vaccination_status` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `arrival_date` date NOT NULL,
  `adoption_status` enum('Available','Pending','Adopted') NOT NULL DEFAULT 'Available',
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`pet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Adoption requests table
CREATE TABLE IF NOT EXISTS `adoption_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `adopter_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `admin_notes` text DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `adopter_id` (`adopter_id`),
  KEY `pet_id` (`pet_id`),
  CONSTRAINT `adoption_requests_ibfk_1` FOREIGN KEY (`adopter_id`) REFERENCES `adopters` (`adopter_id`) ON DELETE CASCADE,
  CONSTRAINT `adoption_requests_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`pet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
