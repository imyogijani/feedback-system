<?php
/**
 * Template Management System for Feedback Forms
 * Handles CRUD operations for form templates
 */

session_start();
include('config/config.php');

header('Content-Type: application/json');

// Check if user is authenticated
$isGoogleLogin = isset($_SESSION['auth_method']) && $_SESSION['auth_method'] === 'google';
$isTraditional = isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 2, 3]);

if (!($isGoogleLogin || $isTraditional)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_templates':
            getTemplates();
            break;
        case 'get_template':
            getTemplate();
            break;
        case 'create_template':
            createTemplate();
            break;
        case 'update_template':
            updateTemplate();
            break;
        case 'delete_template':
            deleteTemplate();
            break;
        case 'duplicate_template':
            duplicateTemplate();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

/**
 * Get all templates from database
 */
function getTemplates() {
    global $conn;

    $stmt = $conn->prepare("
        SELECT ft.*,
               COUNT(ts.id) as section_count,
               COUNT(tq.id) as question_count
        FROM form_templates ft
        LEFT JOIN template_sections ts ON ft.id = ts.template_id
        LEFT JOIN template_questions tq ON ts.id = tq.section_id
        WHERE ft.is_active = 1
        GROUP BY ft.id
        ORDER BY ft.created_at DESC
    ");

    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format user_fields as array
    foreach ($templates as &$template) {
        $template['user_fields'] = json_decode($template['user_fields'], true) ?: [];
    }

    echo json_encode([
        'success' => true,
        'templates' => $templates
    ]);
}

/**
 * Get a specific template with all its data
 */
function getTemplate() {
    global $conn;

    $templateId = $_GET['id'] ?? '';
    $templateKey = $_GET['key'] ?? '';

    if (!$templateId && !$templateKey) {
        http_response_code(400);
        echo json_encode(['error' => 'Template ID or key required']);
        return;
    }

    // Get template basic info
    if ($templateId) {
        $stmt = $conn->prepare("SELECT * FROM form_templates WHERE id = ? AND is_active = 1");
        $stmt->execute([$templateId]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM form_templates WHERE template_key = ? AND is_active = 1");
        $stmt->execute([$templateKey]);
    }

    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$template) {
        http_response_code(404);
        echo json_encode(['error' => 'Template not found']);
        return;
    }

    // Get sections with questions
    $stmt = $conn->prepare("
        SELECT ts.*,
               JSON_ARRAYAGG(
                   JSON_OBJECT(
                       'id', tq.id,
                       'text', tq.text,
                       'type', tq.type,
                       'options', tq.options,
                       'question_order', tq.question_order,
                       'is_required', tq.is_required
                   )
               ) as questions
        FROM template_sections ts
        LEFT JOIN template_questions tq ON ts.id = tq.section_id
        WHERE ts.template_id = ?
        GROUP BY ts.id
        ORDER BY ts.section_order
    ");

    $stmt->execute([$template['id']]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $template['user_fields'] = json_decode($template['user_fields'], true) ?: [];
    $template['sections'] = [];

    foreach ($sections as $section) {
        $questions = json_decode($section['questions'], true) ?: [];

        // Filter out null questions and format options
        $validQuestions = [];
        foreach ($questions as $question) {
            if ($question['id'] !== null) {
                $question['options'] = $question['options'] ? json_decode($question['options'], true) : null;
                $validQuestions[] = $question;
            }
        }

        $template['sections'][] = [
            'id' => $section['id'],
            'title' => $section['title'],
            'section_order' => $section['section_order'],
            'questions' => $validQuestions
        ];
    }

    echo json_encode([
        'success' => true,
        'template' => $template
    ]);
}

/**
 * Create a new template
 */
function createTemplate() {
    global $conn;

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['template_key'], $input['title'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }

    $conn->beginTransaction();

    try {
        // Check if template key already exists
        $stmt = $conn->prepare("SELECT id FROM form_templates WHERE template_key = ?");
        $stmt->execute([$input['template_key']]);
        if ($stmt->fetch()) {
            throw new Exception('Template key already exists');
        }

        // Insert template
        $stmt = $conn->prepare("
            INSERT INTO form_templates (template_key, title, description, form_type, user_fields, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
        $userFields = json_encode($input['user_fields'] ?? ['firstname', 'email']);

        $stmt->execute([
            $input['template_key'],
            $input['title'],
            $input['description'] ?? '',
            $input['form_type'] ?? 'Feedback',
            $userFields,
            $userId
        ]);

        $templateId = $conn->lastInsertId();

        // Insert sections and questions
        if (isset($input['sections']) && is_array($input['sections'])) {
            foreach ($input['sections'] as $sectionIndex => $section) {
                $stmt = $conn->prepare("
                    INSERT INTO template_sections (template_id, title, section_order)
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
                    $templateId,
                    $section['title'] ?? 'Section ' . ($sectionIndex + 1),
                    $sectionIndex + 1
                ]);

                $sectionId = $conn->lastInsertId();

                // Insert questions
                if (isset($section['questions']) && is_array($section['questions'])) {
                    foreach ($section['questions'] as $questionIndex => $question) {
                        $stmt = $conn->prepare("
                            INSERT INTO template_questions (section_id, text, type, options, question_order, is_required)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");

                        $options = null;
                        if (isset($question['options']) && is_array($question['options'])) {
                            $options = json_encode($question['options']);
                        }

                        $stmt->execute([
                            $sectionId,
                            $question['text'],
                            $question['type'],
                            $options,
                            $questionIndex + 1,
                            $question['is_required'] ?? 0
                        ]);
                    }
                }
            }
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Template created successfully',
            'template_id' => $templateId
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Update an existing template
 */
function updateTemplate() {
    global $conn;

    $input = json_decode(file_get_contents('php://input'), true);
    $templateId = $_GET['id'] ?? $input['id'] ?? '';

    if (!$templateId || !$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Template ID and data required']);
        return;
    }

    $conn->beginTransaction();

    try {
        // Update template basic info
        $stmt = $conn->prepare("
            UPDATE form_templates
            SET title = ?, description = ?, form_type = ?, user_fields = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $userFields = json_encode($input['user_fields'] ?? ['firstname', 'email']);

        $stmt->execute([
            $input['title'],
            $input['description'] ?? '',
            $input['form_type'] ?? 'Feedback',
            $userFields,
            $templateId
        ]);

        // Delete existing sections and questions (cascade will handle questions)
        $stmt = $conn->prepare("DELETE FROM template_sections WHERE template_id = ?");
        $stmt->execute([$templateId]);

        // Insert new sections and questions
        if (isset($input['sections']) && is_array($input['sections'])) {
            foreach ($input['sections'] as $sectionIndex => $section) {
                $stmt = $conn->prepare("
                    INSERT INTO template_sections (template_id, title, section_order)
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
                    $templateId,
                    $section['title'] ?? 'Section ' . ($sectionIndex + 1),
                    $sectionIndex + 1
                ]);

                $sectionId = $conn->lastInsertId();

                // Insert questions
                if (isset($section['questions']) && is_array($section['questions'])) {
                    foreach ($section['questions'] as $questionIndex => $question) {
                        $stmt = $conn->prepare("
                            INSERT INTO template_questions (section_id, text, type, options, question_order, is_required)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");

                        $options = null;
                        if (isset($question['options']) && is_array($question['options'])) {
                            $options = json_encode($question['options']);
                        }

                        $stmt->execute([
                            $sectionId,
                            $question['text'],
                            $question['type'],
                            $options,
                            $questionIndex + 1,
                            $question['is_required'] ?? 0
                        ]);
                    }
                }
            }
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Template updated successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Delete a template (soft delete)
 */
function deleteTemplate() {
    global $conn;

    $templateId = $_GET['id'] ?? $_POST['id'] ?? '';

    if (!$templateId) {
        http_response_code(400);
        echo json_encode(['error' => 'Template ID required']);
        return;
    }

    $stmt = $conn->prepare("UPDATE form_templates SET is_active = 0 WHERE id = ?");
    $stmt->execute([$templateId]);

    echo json_encode([
        'success' => true,
        'message' => 'Template deleted successfully'
    ]);
}

/**
 * Duplicate an existing template
 */
function duplicateTemplate() {
    global $conn;

    $templateId = $_GET['id'] ?? $_POST['id'] ?? '';

    if (!$templateId) {
        http_response_code(400);
        echo json_encode(['error' => 'Template ID required']);
        return;
    }

    $conn->beginTransaction();

    try {
        // Get original template
        $stmt = $conn->prepare("SELECT * FROM form_templates WHERE id = ? AND is_active = 1");
        $stmt->execute([$templateId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$template) {
            throw new Exception('Template not found');
        }

        // Create new template key
        $newKey = $template['template_key'] . '_copy_' . time();
        $newTitle = $template['title'] . ' (Copy)';

        // Insert new template
        $stmt = $conn->prepare("
            INSERT INTO form_templates (template_key, title, description, form_type, user_fields, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

        $stmt->execute([
            $newKey,
            $newTitle,
            $template['description'],
            $template['form_type'],
            $template['user_fields'],
            $userId
        ]);

        $newTemplateId = $conn->lastInsertId();

        // Copy sections
        $stmt = $conn->prepare("
            SELECT * FROM template_sections
            WHERE template_id = ?
            ORDER BY section_order
        ");
        $stmt->execute([$templateId]);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($sections as $section) {
            $stmt = $conn->prepare("
                INSERT INTO template_sections (template_id, title, section_order)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $newTemplateId,
                $section['title'],
                $section['section_order']
            ]);

            $newSectionId = $conn->lastInsertId();

            // Copy questions
            $stmt = $conn->prepare("
                SELECT * FROM template_questions
                WHERE section_id = ?
                ORDER BY question_order
            ");
            $stmt->execute([$section['id']]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($questions as $question) {
                $stmt = $conn->prepare("
                    INSERT INTO template_questions (section_id, text, type, options, question_order, is_required)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $newSectionId,
                    $question['text'],
                    $question['type'],
                    $question['options'],
                    $question['question_order'],
                    $question['is_required']
                ]);
            }
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Template duplicated successfully',
            'template_id' => $newTemplateId
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
?>
