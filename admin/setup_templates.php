<?php
/**
 * Database Setup Script for Template Management
 * This script creates the necessary tables for managing form templates
 */

include('config/config.php');

try {
    echo "Starting database setup for template management...\n";

    // Create form_templates table
    $sql = "
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
    ";

    $conn->exec($sql);
    echo "✓ Created form_templates table\n";

    // Create template_sections table
    $sql = "
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
    ";

    $conn->exec($sql);
    echo "✓ Created template_sections table\n";

    // Create template_questions table
    $sql = "
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
    ";

    $conn->exec($sql);
    echo "✓ Created template_questions table\n";

    // Check if templates already exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM form_templates");
    $stmt->execute();
    $templateCount = $stmt->fetchColumn();

    if ($templateCount == 0) {
        echo "No templates found, inserting default templates...\n";

        // Insert default templates
        $templates = [
            [
                'key' => 'customer_satisfaction',
                'title' => 'Customer Satisfaction Survey',
                'description' => 'Help us improve our service by sharing your experience with us.',
                'type' => 'Feedback',
                'fields' => '["firstname", "lastname", "email"]',
                'sections' => [
                    [
                        'title' => 'Your Experience',
                        'questions' => [
                            ['text' => 'How would you rate your overall experience with our service?', 'type' => 'rating_star'],
                            ['text' => 'How likely are you to recommend us to a friend?', 'type' => 'rating_heart'],
                            ['text' => 'What did you like most about our service?', 'type' => 'textarea']
                        ]
                    ]
                ]
            ],
            [
                'key' => 'product_feedback',
                'title' => 'Product Feedback Form',
                'description' => 'Share your thoughts about our product to help us make it better.',
                'type' => 'Feedback',
                'fields' => '["firstname", "email"]',
                'sections' => [
                    [
                        'title' => 'Product Evaluation',
                        'questions' => [
                            ['text' => 'Which product are you reviewing?', 'type' => 'dropdown', 'options' => '["Product A", "Product B", "Product C", "Other"]'],
                            ['text' => 'How would you rate this product overall?', 'type' => 'rating_star'],
                            ['text' => 'How satisfied are you with the product quality?', 'type' => 'radio', 'options' => '["Very Satisfied", "Satisfied", "Neutral", "Dissatisfied", "Very Dissatisfied"]'],
                            ['text' => 'What improvements would you suggest?', 'type' => 'textarea']
                        ]
                    ]
                ]
            ],
            [
                'key' => 'event_feedback',
                'title' => 'Event Feedback Survey',
                'description' => 'Thank you for attending our event. Your feedback is valuable to us.',
                'type' => 'Feedback',
                'fields' => '["firstname", "lastname", "email"]',
                'sections' => [
                    [
                        'title' => 'Event Experience',
                        'questions' => [
                            ['text' => 'When did you attend the event?', 'type' => 'date'],
                            ['text' => 'How would you rate the overall event?', 'type' => 'rating_star'],
                            ['text' => 'Which aspects of the event did you enjoy most?', 'type' => 'checkbox', 'options' => '["Content/Presentations", "Networking", "Venue", "Food & Beverages", "Organization"]'],
                            ['text' => 'Any suggestions for future events?', 'type' => 'textarea']
                        ]
                    ]
                ]
            ]
        ];

        foreach ($templates as $template) {
            // Insert template
            $stmt = $conn->prepare("
                INSERT INTO form_templates (template_key, title, description, form_type, user_fields)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $template['key'],
                $template['title'],
                $template['description'],
                $template['type'],
                $template['fields']
            ]);

            $templateId = $conn->lastInsertId();
            echo "✓ Inserted template: {$template['title']}\n";

            // Insert sections and questions
            foreach ($template['sections'] as $sectionIndex => $section) {
                $stmt = $conn->prepare("
                    INSERT INTO template_sections (template_id, title, section_order)
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
                    $templateId,
                    $section['title'],
                    $sectionIndex + 1
                ]);

                $sectionId = $conn->lastInsertId();

                // Insert questions
                foreach ($section['questions'] as $questionIndex => $question) {
                    $stmt = $conn->prepare("
                        INSERT INTO template_questions (section_id, text, type, options, question_order)
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    $options = isset($question['options']) ? $question['options'] : null;

                    $stmt->execute([
                        $sectionId,
                        $question['text'],
                        $question['type'],
                        $options,
                        $questionIndex + 1
                    ]);
                }
            }
        }

        echo "✓ Default templates inserted successfully\n";
    } else {
        echo "✓ Templates already exist ($templateCount found)\n";
    }

    echo "\n🎉 Database setup completed successfully!\n";
    echo "You can now:\n";
    echo "1. Access Template Manager at: template_management.php\n";
    echo "2. Use templates in Form Generator\n";
    echo "3. Create new templates through the interface\n";

} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
