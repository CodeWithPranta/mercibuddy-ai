-- --------------------------------------------------------
-- Fixed import for the MerciBuddy database.
-- Parents are created before child tables, and foreign key
-- checks stay disabled until the whole import finishes.
--
-- Usage: mysql -u root -p < database/merci_buddy_fixed.sql
-- Or import it with HeidiSQL / phpMyAdmin (it creates and uses
-- the `merci_buddy` database, matching the app's .env).
-- --------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `mercibuddy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mercibuddy`;

-- --------------------------------------------------------
-- 1. users (parent of artisan_user, comments, filter_values)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` tinyint NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `social_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `user_type`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `social_id`, `social_type`) VALUES
	(22, 'Admin', 'admin@mercibuddy.com', 1, '2026-09-11 10:46:22', '$2y$12$YnMau8GS8tM3X.wgO06rK.BlwBxaumDS7J2jeNjGbrbrDk.3Y52AK', NULL, '2026-09-11 10:46:22', '2026-09-11 10:46:22', NULL, NULL),
	(23, 'Rakib Ahmed', 'rakib@mercibuddy.com', 2, '2026-09-11 10:46:22', '$2y$12$Hdgyq4nfSCJMASsrg8AdxuNBhFjnWo3hldCNMhTm6gQpj4b6Bq5D2', NULL, '2026-09-11 10:46:22', '2026-09-11 10:46:22', NULL, NULL),
	(24, 'Nusrat Jahan', 'nusrat@mercibuddy.com', 2, '2026-09-11 10:46:22', '$2y$12$r9e2lj4fVwBBRvG6tMhag.mJB6CTQJNE.sxzc6952HkEr/39XhFS2', NULL, '2026-09-11 10:46:22', '2026-09-11 10:46:22', NULL, NULL),
	(25, 'Tanvir Both', 'both@mercibuddy.com', 3, '2026-09-11 10:46:23', '$2y$12$DeE8pgTQ6dWefBNnWVnVoOxo1pEx8K0sw/exLbpdkzSBEy.l7qNo2', NULL, '2026-09-11 10:46:23', '2026-09-11 10:46:23', NULL, NULL),
	(26, 'General User 1', 'general1@mercibuddy.com', 0, '2026-09-11 10:46:23', '$2y$12$O4z2IgLGOkppJCyVyxy7juJ0S8Nygz71HARNq1f..UZRn9z801o/G', NULL, '2026-09-11 10:46:23', '2026-09-11 10:46:23', NULL, NULL),
	(27, 'General User 2', 'general2@mercibuddy.com', 0, '2026-09-11 10:46:23', '$2y$12$iogowR51O8wY/oZuAuGJbeppHSWXUilM6h9SqAel/3ZeV93lAbhUC', NULL, '2026-09-11 10:46:23', '2026-09-11 10:46:23', NULL, NULL),
	(28, 'General User 3', 'general3@mercibuddy.com', 0, '2026-09-11 10:46:23', '$2y$12$9CQ67CUcVTuhBlKubFNIYOTRH5U5IAzDnfNAcVBfMUaXQFNHmfEr2', NULL, '2026-09-11 10:46:23', '2026-09-11 10:46:23', NULL, NULL),
	(29, 'General User 4', 'general4@mercibuddy.com', 0, '2026-09-11 10:46:23', '$2y$10$mJSWOiCsRryiZvA9lj3usuXa7DmNpYHcoMpcgDJRQbWu./xAbVYzS', NULL, '2026-09-11 10:46:23', '2026-09-11 10:46:23', NULL, NULL),
	(30, 'General User 5', 'general5@mercibuddy.com', 0, '2026-09-11 10:46:23', '$2y$12$hi/lE7BG4LGP0t61FGphJ.JCOwiqbG1DqKy3EmrX5IljK.yrfro/q', NULL, '2026-09-11 10:46:23', '2026-09-11 10:46:23', NULL, NULL);

-- --------------------------------------------------------
-- 2. categories
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `created_at`, `updated_at`) VALUES
	(1, 'Beauty and Wellness', 'beauty-and-wellness', '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-6-6h12"/></svg>', '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(2, 'Childcare', 'childcare', '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-6-6h12"/></svg>', '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(3, 'Cleaning', 'cleaning', '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-6-6h12"/></svg>', '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(4, 'Elderly and Disability Care', 'elderly-and-disability-care', '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-6-6h12"/></svg>', '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(5, 'Fitness and Yoga', 'fitness-and-yoga', '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-6-6h12"/></svg>', '2026-09-11 10:46:22', '2026-09-11 10:46:22');

-- --------------------------------------------------------
-- 3. countries
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `countries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shortname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phonecode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `countries` (`id`, `shortname`, `name`, `phonecode`, `created_at`, `updated_at`) VALUES
	(1, 'BD', 'Bangladesh', '+880', '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(2, 'IN', 'India', '+91', '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(3, 'GB', 'United Kingdom', '+44', '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(4, 'US', 'United States', '+1', '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(5, 'CA', 'Canada', '+1', '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(6, 'AU', 'Australia', '+61', '2026-09-11 10:46:22', '2026-09-11 10:46:22');

-- --------------------------------------------------------
-- 4. states
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `states` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `states` (`id`, `name`, `country_id`, `created_at`, `updated_at`) VALUES
	(1, 'Dhaka', 1, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(2, 'West Bengal', 2, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(3, 'England', 3, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(4, 'New York', 4, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(5, 'Ontario', 5, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(6, 'New South Wales', 6, '2026-09-11 10:46:22', '2026-09-11 10:46:22');

-- --------------------------------------------------------
-- 5. cities
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cities` (`id`, `name`, `state_id`, `created_at`, `updated_at`) VALUES
	(1, 'Dhaka', 1, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(2, 'Kolkata', 2, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(3, 'London', 3, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(4, 'New York City', 4, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(5, 'Toronto', 5, '2026-09-11 10:46:22', '2026-09-11 10:46:22'),
	(6, 'Sydney', 6, '2026-09-11 10:46:22', '2026-09-11 10:46:22');

-- --------------------------------------------------------
-- 6. settings
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `favicon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `og_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hero_title` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_text` text COLLATE utf8mb4_unicode_ci,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `keywords` text COLLATE utf8mb4_unicode_ci,
  `copyright_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`id`, `favicon`, `logo`, `og_image`, `hero_title`, `title_text`, `meta_description`, `keywords`, `copyright_text`, `created_at`, `updated_at`) VALUES
	(1, 'favicon.ico', 'logo.png', 'og.png', 'Find skilled artisans near you', 'MerciBuddy - Connecting you with trusted local professionals', 'MerciBuddy helps you discover and connect with skilled artisans and service providers in your area.', 'artisan, services, handyman, freelancer, mercibuddy, professionals', '© 2026 MerciBuddy. All rights reserved.', '2026-09-11 10:46:23', '2026-09-11 10:46:23');

-- --------------------------------------------------------
-- 7. pages
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_title_unique` (`title`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `created_at`, `updated_at`) VALUES
	(1, 'About Us', 'about-us', '<p>This is the About Us page content. Edit this from the admin panel.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(2, 'Privacy Policy', 'privacy-policy', '<p>This is the Privacy Policy page content. Edit this from the admin panel.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(3, 'Terms & Conditions', 'terms-conditions', '<p>This is the Terms & Conditions page content. Edit this from the admin panel.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23');

-- --------------------------------------------------------
-- 8. artisans (parent of artisan_user, comments)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `artisans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `profile_photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cover_photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `experience_in_year` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_education` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profession` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profession_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` bigint unsigned NOT NULL,
  `state_id` bigint unsigned NOT NULL,
  `city_id` bigint unsigned NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `biography` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `video_cv` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `artisans` (`id`, `full_name`, `category_id`, `profile_photo`, `cover_photo`, `user_id`, `experience_in_year`, `website`, `last_education`, `date_of_birth`, `profession`, `profession_type`, `country_id`, `state_id`, `city_id`, `address`, `biography`, `created_at`, `updated_at`, `video_cv`) VALUES
	(1, 'Rakib Ahmed', 1, 'https://i.pravatar.cc/300?u=23', 'https://picsum.photos/seed/cover23/1280/720', 23, '15', 'https://example.com', 'BSc in Computer Science', '1994-09-11', 'Beauty and Wellness', 'Full-time', 1, 1, 1, '123 Sample Street, Dhaka', '<p>Experienced Beauty and Wellness with a passion for delivering quality work and helping clients achieve their goals.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23', 'https://youtu.be/dQw4w9WgXcQ'),
	(2, 'Nusrat Jahan', 2, 'https://i.pravatar.cc/300?u=24', 'https://picsum.photos/seed/cover24/1280/720', 24, '8', 'https://example.com', 'BSc in Computer Science', '1986-09-11', 'Childcare', 'Part-time', 1, 2, 2, '123 Sample Street, Kolkata', '<p>Experienced Childcare with a passion for delivering quality work and helping clients achieve their goals.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23', 'https://youtu.be/dQw4w9WgXcQ'),
	(3, 'Tanvir Both', 3, 'https://i.pravatar.cc/300?u=25', 'https://picsum.photos/seed/cover25/1280/720', 25, '8', 'https://example.com', 'BSc in Computer Science', '1997-09-11', 'Cleaning', 'Contract', 1, 1, 1, '123 Sample Street, Dhaka', '<p>Experienced Cleaning with a passion for delivering quality work and helping clients achieve their goals.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23', 'https://youtu.be/dQw4w9WgXcQ');

-- --------------------------------------------------------
-- 9. contact_details
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `contact_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contact_details` (`id`, `phone`, `email`, `facebook`, `linkedin`, `whatsapp`, `created_at`, `updated_at`, `user_id`) VALUES
	(1, '+88017000000023', 'rakib@mercibuddy.com', 'https://facebook.com/rakib-ahmed', 'https://linkedin.com/in/rakib-ahmed', '+88017000000023', '2026-09-11 10:46:23', '2026-09-11 10:46:23', 23),
	(2, '+88017000000024', 'nusrat@mercibuddy.com', 'https://facebook.com/nusrat-jahan', 'https://linkedin.com/in/nusrat-jahan', '+88017000000024', '2026-09-11 10:46:23', '2026-09-11 10:46:23', 24),
	(3, '+88017000000025', 'both@mercibuddy.com', 'https://facebook.com/tanvir-both', 'https://linkedin.com/in/tanvir-both', '+88017000000025', '2026-09-11 10:46:23', '2026-09-11 10:46:23', 25);

-- --------------------------------------------------------
-- 10. services
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`id`, `title`, `user_id`, `slug`, `featured_image`, `content`, `created_at`, `updated_at`) VALUES
	(1, 'Service by Rakib Ahmed', 23, 'service-by-rakib-ahmed', 'https://picsum.photos/seed/svc23/600/400', '<p>This is a sample service offered by Rakib Ahmed. Delivering high-quality results on time.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(2, 'Premium service - Rakib Ahmed', 23, 'premium-service-rakib-ahmed-23', 'https://picsum.photos/seed/prem23/600/400', '<p>Premium offering by Rakib Ahmed. Includes priority support and extended revisions.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(3, 'Service by Nusrat Jahan', 24, 'service-by-nusrat-jahan', 'https://picsum.photos/seed/svc24/600/400', '<p>This is a sample service offered by Nusrat Jahan. Delivering high-quality results on time.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(4, 'Premium service - Nusrat Jahan', 24, 'premium-service-rakib-ahmed-23', 'https://picsum.photos/seed/prem24/600/400', '<p>Premium offering by Nusrat Jahan. Includes priority support and extended revisions.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(5, 'Service by Tanvir Both', 25, 'service-by-tanvir-both', 'https://picsum.photos/seed/svc25/600/400', '<p>This is a sample service offered by Tanvir Both. Delivering high-quality results on time.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(6, 'Premium service - Tanvir Both', 25, 'premium-service-tanvir-both-25', 'https://picsum.photos/seed/prem25/600/400', '<p>Premium offering by Tanvir Both. Includes priority support and extended revisions.</p>', '2026-09-11 10:46:23', '2026-09-11 10:46:23');

-- --------------------------------------------------------
-- 11. comments
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `artisan_id` bigint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` (`id`, `user_id`, `artisan_id`, `comment`, `created_at`, `updated_at`) VALUES
	(1, 26, 1, 'Great work by Rakib Ahmed! Highly recommended.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(2, 26, 2, 'Very professional and delivered on time.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(3, 26, 3, 'Excellent communication throughout the project.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(4, 27, 1, 'Very professional and delivered on time.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(5, 27, 2, 'Excellent communication throughout the project.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(6, 27, 3, 'Would definitely hire Tanvir Both again.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(7, 28, 1, 'Excellent communication throughout the project.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(8, 28, 2, 'Would definitely hire Nusrat Jahan again.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(9, 28, 3, 'Good quality work, fair pricing.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(10, 29, 1, 'Would definitely hire Rakib Ahmed again.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(11, 29, 2, 'Good quality work, fair pricing.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(12, 29, 3, 'Great work by Tanvir Both! Highly recommended.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(13, 30, 1, 'Good quality work, fair pricing.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(14, 30, 2, 'Great work by Nusrat Jahan! Highly recommended.', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(15, 30, 3, 'Very professional and delivered on time.', '2026-09-11 10:46:23', '2026-09-11 10:46:23');

-- --------------------------------------------------------
-- 12. filter_values
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `filter_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `country_id` bigint unsigned NOT NULL,
  `state_id` bigint unsigned DEFAULT NULL,
  `city_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `filter_values` (`id`, `user_id`, `country_id`, `state_id`, `city_id`, `category_id`, `created_at`, `updated_at`) VALUES
	(1, 26, 1, 1, 1, 1, '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(2, 27, 1, 2, 2, 2, '2026-09-11 10:46:23', '2026-09-11 10:46:23');

-- --------------------------------------------------------
-- 13. artisan_user (child table: parents users + artisans exist now)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `artisan_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `artisan_id` bigint unsigned NOT NULL,
  `reaction` enum('like','dislike') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artisan_user_user_id_foreign` (`user_id`),
  KEY `artisan_user_artisan_id_foreign` (`artisan_id`),
  CONSTRAINT `artisan_user_artisan_id_foreign` FOREIGN KEY (`artisan_id`) REFERENCES `artisans` (`id`),
  CONSTRAINT `artisan_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `artisan_user` (`id`, `user_id`, `artisan_id`, `reaction`, `created_at`, `updated_at`) VALUES
	(1, 26, 1, 'dislike', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(2, 26, 2, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(3, 26, 3, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(4, 27, 1, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(5, 27, 2, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(6, 27, 3, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(7, 28, 1, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(8, 28, 2, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(9, 28, 3, 'dislike', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(10, 29, 1, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(11, 29, 2, 'dislike', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(12, 29, 3, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(13, 30, 1, 'dislike', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(14, 30, 2, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23'),
	(15, 30, 3, 'like', '2026-09-11 10:46:23', '2026-09-11 10:46:23');

-- --------------------------------------------------------
-- 14. framework tables (no data)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
	(3, '2019_08_19_000000_create_failed_jobs_table', 1),
	(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(5, '2023_11_28_065120_add_is_admin_column_to_users_table', 1),
	(6, '2023_11_28_105521_add_social_login_to_users_table', 1),
	(7, '2023_11_28_155448_create_categories_table', 1),
	(8, '2023_11_29_033101_create_countries_table', 1),
	(9, '2023_11_29_033122_create_states_table', 1),
	(10, '2023_11_29_033150_create_cities_table', 1),
	(11, '2023_11_29_094603_create_settings_table', 1),
	(12, '2023_11_29_114931_create_pages_table', 1),
	(13, '2023_11_29_121641_create_artisans_table', 1),
	(14, '2023_12_03_100636_add_job_type_column_to_artisans_table', 1),
	(15, '2023_12_04_190441_create_contact_details_table', 1),
	(16, '2023_12_04_194630_create_services_table', 1),
	(17, '2023_12_04_202237_create_comments_table', 1),
	(18, '2023_12_04_203701_add_category_id_to_artisans_table', 1),
	(19, '2023_12_05_130246_create_filter_values_table', 1),
	(20, '2023_12_08_132525_create_artisan_user_table', 1),
	(21, '2023_12_08_175717_add_user_id_to_contact_details_table', 1),
	(22, '2023_12_12_024921_add_video_cv_to_artisans_table', 1),
	(23, '2026_01_11_000001_create_agent_conversations_table', 1);

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `agent_conversations` (
  `id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `participant_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participant_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `participant_updated_at_index` (`participant_type`,`participant_id`,`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `agent_conversation_messages` (
  `id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversation_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `participant_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participant_id` bigint unsigned DEFAULT NULL,
  `agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tool_calls` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tool_results` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `usage` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `approval_state` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversation_index` (`conversation_id`,`participant_type`,`participant_id`,`updated_at`),
  KEY `participant_index` (`participant_type`,`participant_id`),
  KEY `agent_conversation_messages_conversation_id_index` (`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
