-- Templates Management Tables for Feedback System
-- This script creates the necessary tables for managing form templates

-- 1. Form Templates Table
CREATE TABLE IF NOT EXISTS `form_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_key` varchar(100) NOT NULL UNIQUE,
  `title` varchar(255) NOT NULL,
  `description` text,
  `form_type` varchar(50) DEFAULT 'Feedback',
  `user_fields` JSON,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_template_key` (`template_key`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Template Sections Table
CREATE TABLE IF NOT EXISTS `template_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `section_order` int(11) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_template_id` (`template_id`),
  KEY `idx_section_order` (`section_order`),
  FOREIGN KEY (`template_id`) REFERENCES `form_templates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Template Questions Table
CREATE TABLE IF NOT EXISTS `template_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `options` JSON NULL,
  `question_order` int(11) DEFAULT 1,
  `is_required` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_section_id` (`section_id`),
  KEY `idx_question_order` (`question_order`),
  KEY `idx_type` (`type`),
  FOREIGN KEY (`section_id`) REFERENCES `template_sections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert the existing templates into the database
-- 1. Customer Satisfaction Survey
INSERT INTO `form_templates` (`template_key`, `title`, `description`, `form_type`, `user_fields`) VALUES
('customer_satisfaction', 'Customer Satisfaction Survey', 'Help us improve our service by sharing your experience with us.', 'Feedback', '["firstname", "lastname", "email"]');

SET @template_id = LAST_INSERT_ID();

INSERT INTO `template_sections` (`template_id`, `title`, `section_order`) VALUES
(@template_id, 'Your Experience', 1);

SET @section_id = LAST_INSERT_ID();

INSERT INTO `template_questions` (`section_id`, `text`, `type`, `question_order`) VALUES
(@section_id, 'How would you rate your overall experience with our service?', 'rating_star', 1),
(@section_id, 'How likely are you to recommend us to a friend?', 'rating_heart', 2),
(@section_id, 'What did you like most about our service?', 'textarea', 3);

-- 2. Product Feedback Form
INSERT INTO `form_templates` (`template_key`, `title`, `description`, `form_type`, `user_fields`) VALUES
('product_feedback', 'Product Feedback Form', 'Share your thoughts about our product to help us make it better.', 'Feedback', '["firstname", "email"]');

SET @template_id = LAST_INSERT_ID();

INSERT INTO `template_sections` (`template_id`, `title`, `section_order`) VALUES
(@template_id, 'Product Evaluation', 1);

SET @section_id = LAST_INSERT_ID();

INSERT INTO `template_questions` (`section_id`, `text`, `type`, `options`, `question_order`) VALUES
(@section_id, 'Which product are you reviewing?', 'dropdown', '["Product A", "Product B", "Product C", "Other"]', 1),
(@section_id, 'How would you rate this product overall?', 'rating_star', NULL, 2),
(@section_id, 'How satisfied are you with the product quality?', 'radio', '["Very Satisfied", "Satisfied", "Neutral", "Dissatisfied", "Very Dissatisfied"]', 3),
(@section_id, 'What improvements would you suggest?', 'textarea', NULL, 4);

-- 3. Event Feedback Survey
INSERT INTO `form_templates` (`template_key`, `title`, `description`, `form_type`, `user_fields`) VALUES
('event_feedback', 'Event Feedback Survey', 'Thank you for attending our event. Your feedback is valuable to us.', 'Feedback', '["firstname", "lastname", "email"]');

SET @template_id = LAST_INSERT_ID();

INSERT INTO `template_sections` (`template_id`, `title`, `section_order`) VALUES
(@template_id, 'Event Experience', 1);

SET @section_id = LAST_INSERT_ID();

INSERT INTO `template_questions` (`section_id`, `text`, `type`, `options`, `question_order`) VALUES
(@section_id, 'When did you attend the event?', 'date', NULL, 1),
(@section_id, 'How would you rate the overall event?', 'rating_star', NULL, 2),
(@section_id, 'Which aspects of the event did you enjoy most?', 'checkbox', '["Content/Presentations", "Networking", "Venue", "Food & Beverages", "Organization"]', 3),
(@section_id, 'Any suggestions for future events?', 'textarea', NULL, 4);

-- 4. Employee Feedback Survey
INSERT INTO `form_templates` (`template_key`, `title`, `description`, `form_type`, `user_fields`) VALUES
('employee_feedback', 'Employee Feedback Survey', 'Your opinion matters! Help us create a better workplace for everyone.', 'Feedback', '["firstname", "email"]');

SET @template_id = LAST_INSERT_ID();

INSERT INTO `template_sections` (`template_id`, `title`, `section_order`) VALUES
(@template_id, 'Workplace Environment', 1);

SET @section_id = LAST_INSERT_ID();

INSERT INTO `template_questions` (`section_id`, `text`, `type`, `options`, `question_order`) VALUES
(@section_id, 'How satisfied are you with your work environment?', 'radio', '["Very Satisfied", "Satisfied", "Neutral", "Dissatisfied", "Very Dissatisfied"]', 1),
(@section_id, 'Rate your manager\'s support and communication', 'rating_star', NULL, 2),
(@section_id, 'What suggestions do you have for improvement?', 'textarea', NULL, 3);

-- 5. Service Quality Assessment
INSERT INTO `form_templates` (`template_key`, `title`, `description`, `form_type`, `user_fields`) VALUES
('service_quality', 'Service Quality Assessment', 'Help us evaluate and improve our service quality standards.', 'Feedback', '["firstname", "lastname", "email", "number"]');

SET @template_id = LAST_INSERT_ID();

INSERT INTO `template_sections` (`template_id`, `title`, `section_order`) VALUES
(@template_id, 'Service Evaluation', 1);

SET @section_id = LAST_INSERT_ID();

INSERT INTO `template_questions` (`section_id`, `text`, `type`, `options`, `question_order`) VALUES
(@section_id, 'How quickly were you served?', 'radio', '["Very Quickly", "Quickly", "Average", "Slowly", "Very Slowly"]', 1),
(@section_id, 'Rate the quality of service you received', 'rating_star', NULL, 2),
(@section_id, 'Would you use our service again?', 'radio', '["Definitely", "Probably", "Maybe", "Probably Not", "Definitely Not"]', 3),
(@section_id, 'Additional comments or suggestions', 'textarea', NULL, 4);

-- 6. General Survey Form
INSERT INTO `form_templates` (`template_key`, `title`, `description`, `form_type`, `user_fields`) VALUES
('general_survey', 'General Survey Form', 'A flexible survey form that can be customized for various purposes.', 'Suggestion', '["firstname", "email"]');

SET @template_id = LAST_INSERT_ID();

INSERT INTO `template_sections` (`template_id`, `title`, `section_order`) VALUES
(@template_id, 'General Questions', 1);

SET @section_id = LAST_INSERT_ID();

INSERT INTO `template_questions` (`section_id`, `text`, `type`, `options`, `question_order`) VALUES
(@section_id, 'Please select your age group', 'dropdown', '["18-25", "26-35", "36-45", "46-55", "55+"]', 1),
(@section_id, 'How did you hear about us?', 'radio', '["Social Media", "Website", "Friend/Family", "Advertisement", "Other"]', 2),
(@section_id, 'Rate your overall satisfaction', 'rating_star', NULL, 3),
(@section_id, 'Any additional comments?', 'textarea', NULL, 4);
