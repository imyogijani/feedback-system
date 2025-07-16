-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2025 at 08:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `feedback_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `demo_requests`
--

CREATE TABLE `demo_requests` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved` tinyint(1) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT 4,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 1,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE `forms` (
  `id` int(11) NOT NULL,
  `created_for` int(11) DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `form_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `published` tinyint(1) DEFAULT 0,
  `user_fields` text DEFAULT NULL,
  `firstname` tinyint(1) DEFAULT 0,
  `lastname` tinyint(1) DEFAULT 0,
  `email` tinyint(1) DEFAULT 0,
  `number` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forms`
--

INSERT INTO `forms` (`id`, `created_for`, `title`, `description`, `form_type`, `created_at`, `published`, `user_fields`, `firstname`, `lastname`, `email`, `number`, `created_by`) VALUES
(1, 62, 'Test form 1', 'Testing purpose', '', '2025-07-10 05:44:30', 1, NULL, 1, 1, 0, 1, 1),
(2, 63, 'Form 2', 'fdfdf', '', '2025-07-10 07:18:37', 1, NULL, 1, 0, 0, 0, 1),
(3, 1, 'test form 3', 'test', '', '2025-07-11 07:01:11', 0, NULL, 1, 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `forms_combined`
--

CREATE TABLE `forms_combined` (
  `id` int(11) NOT NULL,
  `form_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `section_title` varchar(255) DEFAULT NULL,
  `form_type` varchar(100) DEFAULT NULL,
  `form_created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `published` tinyint(1) DEFAULT NULL,
  `created_for` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `user_fields` text DEFAULT NULL,
  `questions_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`questions_json`)),
  `firstname` tinyint(1) DEFAULT NULL,
  `lastname` tinyint(1) DEFAULT NULL,
  `email` tinyint(1) DEFAULT NULL,
  `number` tinyint(1) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `question_text` text DEFAULT NULL,
  `question_type` varchar(50) DEFAULT NULL,
  `question_order` int(11) DEFAULT NULL,
  `option_id` int(11) DEFAULT NULL,
  `option_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forms_combined`
--

INSERT INTO `forms_combined` (`id`, `form_id`, `title`, `description`, `section_title`, `form_type`, `form_created_at`, `published`, `created_for`, `created_by`, `user_fields`, `questions_json`, `firstname`, `lastname`, `email`, `number`, `question_id`, `question_text`, `question_type`, `question_order`, `option_id`, `option_text`, `created_at`) VALUES
(3, NULL, 'Form 1', 'Test Form', NULL, '', '2025-07-16 05:51:48', 1, 1, 1, NULL, '[{\"section_id\":1,\"section_title\":\"S1\",\"questions\":[{\"section_id\":1,\"text\":\"Q1\",\"type\":\"text\",\"options\":[]},{\"section_id\":1,\"text\":\"Q2\",\"type\":\"textarea\",\"options\":[]},{\"section_id\":1,\"text\":\"Q3\",\"type\":\"radio\",\"options\":[{\"label\":\"yes\"},{\"label\":\"no\"}]}]},{\"section_id\":2,\"section_title\":\"S2\",\"questions\":[{\"section_id\":2,\"text\":\"Q1\",\"type\":\"checkbox\",\"options\":[{\"label\":\"check 1\"},{\"label\":\"check 2\"}]},{\"section_id\":2,\"text\":\"Q2\",\"type\":\"dropdown\",\"options\":[{\"label\":\"Drop 1\"},{\"label\":\"Drop 2\"}]},{\"section_id\":2,\"text\":\"Q3\",\"type\":\"date\",\"options\":[]}]},{\"section_id\":3,\"section_title\":\"S3\",\"questions\":[{\"section_id\":3,\"text\":\"Q1\",\"type\":\"rating_star\",\"options\":[]},{\"section_id\":3,\"text\":\"Q2\",\"type\":\"rating_heart\",\"options\":[]},{\"section_id\":3,\"text\":\"Q3\",\"type\":\"rating_thumb\",\"options\":[]}]}]', 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-16 05:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `form_responses`
--

CREATE TABLE `form_responses` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `number` varchar(20) DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `response_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_responses_combined`
--

CREATE TABLE `form_responses_combined` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `number` varchar(20) DEFAULT NULL,
  `responses_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`responses_json`)),
  `answer` text NOT NULL,
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_responses_combined`
--

INSERT INTO `form_responses_combined` (`id`, `form_id`, `question_id`, `firstname`, `lastname`, `email`, `number`, `responses_json`, `answer`, `submitted_at`) VALUES
(2, 3, 0, 'Foram', 'parekh', 'foram@mail.com', '9595959595', '[{\"section_id\":1,\"section_title\":\"S1\",\"answers\":[{\"question_text\":\"Q1\",\"answer\":\"Response 1\"},{\"question_text\":\"Q2\",\"answer\":\"Test\"},{\"question_text\":\"Q3\",\"answer\":\"yes\"}]},{\"section_id\":2,\"section_title\":\"S2\",\"answers\":[{\"question_text\":\"Q1\",\"answer\":\"[\\\"check 1\\\",\\\"check 2\\\"]\"},{\"question_text\":\"Q2\",\"answer\":\"Drop 1\"},{\"question_text\":\"Q3\",\"answer\":\"2025-07-08\"}]},{\"section_id\":3,\"section_title\":\"S3\",\"answers\":[{\"question_text\":\"Q1\",\"answer\":\"3\"},{\"question_text\":\"Q2\",\"answer\":\"4\"},{\"question_text\":\"Q3\",\"answer\":\"5\"}]}]', '', '2025-07-16 11:22:59'),
(3, 3, 0, 'siya', 'parekh', 'siya@mail.com', '9854564546', '[{\"section_id\":1,\"section_title\":\"S1\",\"answers\":[{\"question_text\":\"Q1\",\"answer\":\"Response 2\"},{\"question_text\":\"Q2\",\"answer\":\"Test\"},{\"question_text\":\"Q3\",\"answer\":\"no\"}]},{\"section_id\":2,\"section_title\":\"S2\",\"answers\":[{\"question_text\":\"Q1\",\"answer\":\"[\\\"check 1\\\"]\"},{\"question_text\":\"Q2\",\"answer\":\"Drop 1\"},{\"question_text\":\"Q3\",\"answer\":\"2025-07-11\"}]},{\"section_id\":3,\"section_title\":\"S3\",\"answers\":[{\"question_text\":\"Q1\",\"answer\":\"4\"},{\"question_text\":\"Q2\",\"answer\":\"3\"},{\"question_text\":\"Q3\",\"answer\":\"2\"}]}]', '', '2025-07-16 11:40:16');

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `question_id`, `option_text`, `created_at`) VALUES
(1, 5, 'Check 1', '2025-07-10 05:44:30'),
(2, 5, 'Check 2', '2025-07-10 05:44:30'),
(3, 5, 'check 3', '2025-07-10 05:44:30'),
(4, 6, 'Drop 1', '2025-07-10 05:44:30'),
(5, 6, 'Drop 2', '2025-07-10 05:44:30'),
(6, 7, 'Radio 1', '2025-07-10 05:44:30'),
(7, 7, 'Radio 2', '2025-07-10 05:44:30');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` varchar(50) NOT NULL,
  `question_order` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `form_id`, `question_text`, `question_type`, `question_order`, `created_at`) VALUES
(1, 1, 'Q1', 'text', NULL, '2025-07-10 05:44:30'),
(2, 1, 'Q2', 'rating_star', NULL, '2025-07-10 05:44:30'),
(3, 1, 'Q3', 'rating_heart', NULL, '2025-07-10 05:44:30'),
(4, 1, 'Q4', 'rating_thumb', NULL, '2025-07-10 05:44:30'),
(5, 1, 'Q5', 'checkbox', NULL, '2025-07-10 05:44:30'),
(6, 1, 'Q6', 'dropdown', NULL, '2025-07-10 05:44:30'),
(7, 1, 'Q7', 'radio', NULL, '2025-07-10 05:44:30'),
(8, 2, 'Q1', 'text', NULL, '2025-07-10 07:18:37'),
(9, 2, 'Q2', 'textarea', NULL, '2025-07-10 07:18:37'),
(10, 2, 'Q3', 'date', NULL, '2025-07-10 07:18:37'),
(11, 3, 'test q1', 'text', NULL, '2025-07-11 07:01:11'),
(12, 3, 'test q2', 'textarea', NULL, '2025-07-11 07:01:11');

-- --------------------------------------------------------

--
-- Table structure for table `responses`
--

CREATE TABLE `responses` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `form_response_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'admin'),
(4, 'demo_user'),
(2, 'moderator'),
(3, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `firebase_uid` text DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `business_type` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `firebase_uid`, `username`, `email`, `mobile`, `amount`, `password`, `role_id`, `profile_image`, `business_name`, `business_type`, `start_date`, `end_date`, `created_at`, `created_by`) VALUES
(1, NULL, NULL, NULL, 'admin', 'admin@gmail.com', NULL, 0.00, 'admin@123', 1, 'admin_1.png', 'Akshrraj Infotech', NULL, NULL, NULL, '2025-05-08 06:29:58', NULL),
(62, NULL, NULL, NULL, 'Test', 'test@gmail.com', NULL, 0.00, 'Test', 3, '', '123456', NULL, NULL, NULL, '2025-07-10 05:44:30', NULL),
(63, NULL, NULL, NULL, 'Test 1', 'test_1@gmail.com', NULL, 0.00, 'Test 1', 3, '', '123456', NULL, NULL, NULL, '2025-07-10 07:18:37', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `demo_requests`
--
ALTER TABLE `demo_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forms_combined`
--
ALTER TABLE `forms_combined`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_responses`
--
ALTER TABLE `form_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`);

--
-- Indexes for table `form_responses_combined`
--
ALTER TABLE `form_responses_combined`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`);

--
-- Indexes for table `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`) USING BTREE,
  ADD KEY `ibfk_question_id` (`question_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `firebase_uid` (`firebase_uid`) USING HASH,
  ADD KEY `fk_users_roles` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `demo_requests`
--
ALTER TABLE `demo_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `forms_combined`
--
ALTER TABLE `forms_combined`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `form_responses`
--
ALTER TABLE `form_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `form_responses_combined`
--
ALTER TABLE `form_responses_combined`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `responses`
--
ALTER TABLE `responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `form_responses`
--
ALTER TABLE `form_responses`
  ADD CONSTRAINT `form_responses_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`);

--
-- Constraints for table `options`
--
ALTER TABLE `options`
  ADD CONSTRAINT `options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `ibfk_form_id` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ibfk_question_id` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
