-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 09, 2025 at 06:00 AM
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

--
-- Dumping data for table `demo_requests`
--

INSERT INTO `demo_requests` (`id`, `email`, `mobile`, `requested_at`, `approved`, `profile_image`, `role_id`, `created_at`, `created_by`, `first_name`, `last_name`, `business_name`, `comment`, `business_type`) VALUES
(1, 'foram@mail.com', '9595959595', '2025-06-20 07:37:40', 1, 'user_1.webp', 4, '2025-06-23 10:32:11', 1, NULL, NULL, NULL, NULL, NULL),
(2, 'user1@gmail.com', '9595959595', '2025-06-20 07:38:45', 0, NULL, 4, '2025-06-23 10:32:11', 1, 'User1', NULL, NULL, NULL, NULL),
(3, 'user2@gmail.com', '8585858585', '2025-06-24 07:24:17', 0, 'user_3.webp', 4, '2025-06-24 12:54:17', 1, NULL, NULL, NULL, NULL, NULL),
(4, 'user3@gmail.com', '8585858585', '2025-06-25 06:22:41', 1, NULL, 4, '2025-06-25 11:52:41', 1, NULL, NULL, NULL, NULL, NULL),
(5, 'foram12@mail.com', '9595959595', '2025-07-01 06:34:16', 0, 'user_5.webp', 4, '2025-07-01 12:04:16', 1, 'Foram', 'Parikh', 'Test', 'tst', 'IT Services'),
(6, 'test@mail.com', '9595959595', '2025-07-01 07:12:02', 0, 'user_6.webp', 4, '2025-07-01 12:42:02', 1, 'test', 'test', 'Test', 'testing', 'testing - other'),
(12, 'foram111@mail.com', '9595959595', '2025-07-08 07:34:40', 0, NULL, 4, '2025-07-08 13:04:40', 1, 'test', 'test', 'Test', 'test', 'Education');

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE `forms` (
  `id` int(11) NOT NULL,
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

INSERT INTO `forms` (`id`, `title`, `description`, `form_type`, `created_at`, `published`, `user_fields`, `firstname`, `lastname`, `email`, `number`, `created_by`) VALUES
(23, 'Test Form 1', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 'Feedback', '2025-07-02 06:37:28', 1, NULL, 1, 1, 1, 1, 1),
(24, 'Form 1', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'Complaints', '2025-07-02 06:46:19', 0, NULL, 1, 1, 1, 1, 38),
(25, 'Form 1', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'Feedback', '2025-07-02 06:51:33', 1, NULL, 1, 1, 1, 1, 39),
(26, 'Testing form', 'test', 'Feedback', '2025-07-04 04:54:37', 0, NULL, 1, 1, 1, 1, 52),
(29, 'Test Test Form', 'Testing', 'Feedback', '2025-07-08 06:48:42', 1, NULL, 1, 1, 1, 1, 1),
(30, 'Product Feedback Form', '', 'Feedback', '2025-07-08 12:56:11', 1, NULL, 1, 1, 1, 1, 1);

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
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_responses`
--

INSERT INTO `form_responses` (`id`, `form_id`, `firstname`, `lastname`, `email`, `number`, `submitted_at`) VALUES
(32, 23, 'Foram', 'Parekh', 'foram@gmail.com', '9595959595', '2025-07-02 12:09:28'),
(33, 23, 'Diya', 'parekh', 'diya@gmail.com', '9998994030', '2025-07-02 12:11:09'),
(34, 24, 'jiya', 'parekh', 'jiya@gmail.com', '9898989898', '2025-07-02 12:18:11'),
(35, 25, 'Reena', 'Parekh', 'reena@gmail.com', '9797979797', '2025-07-02 12:23:16'),
(36, 26, 'testuser', 'parekh', 'test1@mail.com', '9595959595', '2025-07-04 10:27:46'),
(37, 29, 'test foram', 'test foram', 'test2@gmail.com', '8585858585', '2025-07-08 12:22:44'),
(38, 29, 'siya test user', 'test user', 'test3@gmail.com', '9898989898', '2025-07-08 12:29:11'),
(39, 29, 'ttt', 'ttt', 'ttt@mail.com', '9889898989', '2025-07-08 12:33:07'),
(40, 30, 'Yogesh', 'Jani', 'a@gmail.com', '9998994030', '2025-07-08 19:46:43');

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
(146, 90, 'yes', '2025-07-02 06:46:19'),
(147, 90, 'no', '2025-07-02 06:46:19'),
(148, 91, 'Checkbox 1', '2025-07-02 06:46:19'),
(149, 91, 'Checkbox 2', '2025-07-02 06:46:19'),
(150, 91, 'Checkbox 3', '2025-07-02 06:46:19'),
(151, 95, 'yes', '2025-07-02 06:51:33'),
(152, 95, 'no', '2025-07-02 06:51:33'),
(153, 96, 'Checkbox 1', '2025-07-02 06:51:33'),
(154, 96, 'Checkbox 2', '2025-07-02 06:51:33'),
(155, 96, 'Checkbox 3', '2025-07-02 06:51:33'),
(156, 100, 'yes', '2025-07-04 04:54:37'),
(157, 100, 'no', '2025-07-04 04:54:37'),
(163, 85, 'yes', '2025-07-08 06:12:52'),
(164, 85, 'no', '2025-07-08 06:12:52'),
(165, 86, 'Checkbox 1', '2025-07-08 06:12:52'),
(166, 86, 'Checkbox 2', '2025-07-08 06:12:52'),
(167, 86, 'Checkbox 3', '2025-07-08 06:12:52'),
(172, 109, 'yes', '2025-07-08 06:49:42'),
(173, 109, 'no', '2025-07-08 06:49:42'),
(180, 111, 'Good', '2025-07-08 12:59:09'),
(181, 111, 'Bad', '2025-07-08 12:59:09'),
(182, 111, 'Best', '2025-07-08 12:59:09'),
(183, 111, 'Average', '2025-07-08 12:59:09'),
(184, 112, 'Yes', '2025-07-08 12:59:09'),
(185, 112, 'No', '2025-07-08 12:59:09');

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
(83, 23, 'Q1', 'text', NULL, '2025-07-02 06:37:28'),
(84, 23, 'Q2', 'textarea', NULL, '2025-07-02 06:37:28'),
(85, 23, 'Q3', 'radio', NULL, '2025-07-02 06:37:28'),
(86, 23, 'Q4', 'checkbox', NULL, '2025-07-02 06:37:28'),
(87, 23, 'Q5', 'rating_star', NULL, '2025-07-02 06:37:28'),
(88, 24, 'Q1', 'text', NULL, '2025-07-02 06:46:19'),
(89, 24, 'Q2', 'textarea', NULL, '2025-07-02 06:46:19'),
(90, 24, 'Q3', 'radio', NULL, '2025-07-02 06:46:19'),
(91, 24, 'Q4', 'checkbox', NULL, '2025-07-02 06:46:19'),
(92, 24, 'Q5', 'rating_heart', NULL, '2025-07-02 06:46:19'),
(93, 25, 'Q1', 'text', NULL, '2025-07-02 06:51:33'),
(94, 25, 'Q2', 'textarea', NULL, '2025-07-02 06:51:33'),
(95, 25, 'Q3', 'radio', NULL, '2025-07-02 06:51:33'),
(96, 25, 'Q4', 'checkbox', NULL, '2025-07-02 06:51:33'),
(97, 25, 'Q5', 'rating_thumb', NULL, '2025-07-02 06:51:33'),
(98, 26, 'Q1', 'text', NULL, '2025-07-04 04:54:37'),
(99, 26, 'Q2', 'textarea', NULL, '2025-07-04 04:54:37'),
(100, 26, 'Q3', 'radio', NULL, '2025-07-04 04:54:37'),
(101, 26, 'Q4', 'rating_star', NULL, '2025-07-04 04:54:37'),
(107, 29, 'Q1', 'text', NULL, '2025-07-08 06:48:42'),
(108, 29, 'Q2', 'textarea', NULL, '2025-07-08 06:48:42'),
(109, 29, 'Q3', 'radio', NULL, '2025-07-08 06:48:42'),
(110, 29, 'Q4', 'rating_star', NULL, '2025-07-08 06:48:42'),
(111, 30, 'How was our Service??', 'radio', NULL, '2025-07-08 12:56:11'),
(112, 30, 'Do You recommend it to Other?', 'radio', NULL, '2025-07-08 12:56:11'),
(113, 30, 'Any Suggestions for Us?', 'textarea', NULL, '2025-07-08 12:56:11');

-- --------------------------------------------------------

--
-- Table structure for table `responses`
--

CREATE TABLE `responses` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `responses`
--

INSERT INTO `responses` (`id`, `form_id`, `question_id`, `answer`, `created_at`) VALUES
(94, 23, 83, 'Test', '2025-07-02 06:39:28'),
(95, 23, 84, 'Testing', '2025-07-02 06:39:28'),
(96, 23, 85, 'yes', '2025-07-02 06:39:28'),
(97, 23, 86, '[\"Checkbox 1\",\"Checkbox 2\"]', '2025-07-02 06:39:28'),
(98, 23, 87, '4', '2025-07-02 06:39:28'),
(99, 23, 83, 'Test', '2025-07-02 06:41:09'),
(100, 23, 84, 'Testing form', '2025-07-02 06:41:09'),
(101, 23, 85, 'no', '2025-07-02 06:41:09'),
(102, 23, 86, '[\"Checkbox 1\"]', '2025-07-02 06:41:09'),
(103, 23, 87, '5', '2025-07-02 06:41:09'),
(104, 24, 88, 'Test', '2025-07-02 06:48:11'),
(105, 24, 89, 'Testing', '2025-07-02 06:48:11'),
(106, 24, 90, 'no', '2025-07-02 06:48:11'),
(107, 24, 91, '[\"Checkbox 2\"]', '2025-07-02 06:48:11'),
(108, 24, 92, '3', '2025-07-02 06:48:11'),
(109, 25, 93, 'test', '2025-07-02 06:53:16'),
(110, 25, 94, 'testing', '2025-07-02 06:53:16'),
(111, 25, 95, 'yes', '2025-07-02 06:53:16'),
(112, 25, 96, '[\"Checkbox 1\",\"Checkbox 2\",\"Checkbox 3\"]', '2025-07-02 06:53:16'),
(113, 25, 97, '5', '2025-07-02 06:53:16'),
(114, 26, 98, 'test1', '2025-07-04 04:57:46'),
(115, 26, 99, 'test1', '2025-07-04 04:57:46'),
(116, 26, 100, 'yes', '2025-07-04 04:57:46'),
(117, 26, 101, '4', '2025-07-04 04:57:46'),
(118, 29, 107, 'test', '2025-07-08 06:52:44'),
(119, 29, 108, 'test', '2025-07-08 06:52:44'),
(120, 29, 109, 'yes', '2025-07-08 06:52:44'),
(121, 29, 110, '4', '2025-07-08 06:52:44'),
(122, 29, 107, 'test', '2025-07-08 06:59:11'),
(123, 29, 108, 'test', '2025-07-08 06:59:11'),
(124, 29, 109, 'no', '2025-07-08 06:59:11'),
(125, 29, 110, '3', '2025-07-08 06:59:11'),
(126, 29, 107, 'test', '2025-07-08 07:03:07'),
(127, 29, 108, 'test', '2025-07-08 07:03:07'),
(128, 29, 109, 'no', '2025-07-08 07:03:07'),
(129, 29, 110, '4', '2025-07-08 07:03:07'),
(130, 30, 111, 'Good', '2025-07-08 14:16:43'),
(131, 30, 112, 'Yes', '2025-07-08 14:16:43'),
(132, 30, 113, 'No', '2025-07-08 14:16:43');

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
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `firebase_uid`, `username`, `email`, `mobile`, `amount`, `password`, `role_id`, `profile_image`, `start_date`, `end_date`, `created_at`, `created_by`) VALUES
(1, NULL, NULL, NULL, 'admin', 'admin@gmail.com', NULL, 0.00, 'admin@123', 1, 'admin_1.jpg', NULL, NULL, '2025-05-08 06:29:58', NULL),
(38, NULL, NULL, NULL, 'User1', 'user1@gmail.com', NULL, 0.00, '$2y$10$8DdyE/vONXBowxneCYjlKO4HzWIdvG1G7MghN2vM1Fz7EDij9FlnS', 3, 'user_38.webp', '2025-07-02', '2025-07-09', '2025-07-02 03:13:04', 1),
(39, NULL, NULL, 'Ivwh6kYuHUZmwxCm4gZ0qi7o5M72', 'Yogesh Jani', 'janiyogesh61@gmail.com', NULL, 0.00, '', 3, NULL, NULL, NULL, '2025-07-02 06:49:24', 1),
(40, NULL, NULL, NULL, 'user2', 'user2@gmail.com', NULL, 0.00, '$2y$10$0rfwCnTNztiBjLXNokAiu.yoRFGRd/0IoPH6r0DHhHtY3Si5fA.lS', 2, NULL, NULL, NULL, '2025-07-02 03:24:32', 1),
(43, NULL, NULL, NULL, 'emp1', 'emp1@mail.com', NULL, 0.00, '$2y$10$S56rs0ZJCf9Bfmy3pe//o.XYXPMEgX36oTbQmbbX6eQvYgjuBzXoq', NULL, NULL, NULL, NULL, '2025-07-03 06:07:43', NULL),
(44, 'Test', 'Test', NULL, 'Test', 'test@mail.com', '9595959595', 1.00, '$2y$10$o.kTYG5aSQ.2KtU.OrHmV.F0MJ7T3iVDKC9JPpYxKRNvkOv.qXlBe', 3, NULL, NULL, NULL, '2025-07-03 06:40:06', NULL),
(52, 'siya', 'siya', NULL, 'siya', 'siya@mail.com', '9595959595', 1.00, '$2y$10$mBduIu9e.RGTmHBer0.GE.81Gsj4MuqZdrsAfRWRGuwr.NANUaCPy', 3, 'user_52.webp', NULL, NULL, '2025-07-03 07:26:08', NULL);

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
-- Indexes for table `form_responses`
--
ALTER TABLE `form_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`);

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
  ADD KEY `form_id` (`form_id`) USING BTREE;

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
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `firebase_uid` (`firebase_uid`) USING HASH,
  ADD KEY `fk_users_roles` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `demo_requests`
--
ALTER TABLE `demo_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `form_responses`
--
ALTER TABLE `form_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `responses`
--
ALTER TABLE `responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

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
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
