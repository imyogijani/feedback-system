<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$isGoogleLogin = isset($_SESSION['auth_method']) && $_SESSION['auth_method'] === 'google';
$isTraditional = isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [1, 2, 3]);

if (!($isGoogleLogin || $isTraditional)) {
    header("Location: login.php");
    exit();
}

include('config/config.php');
include('assets/inc/incHeader.php');
?>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include('assets/inc/incSidebar.php'); ?>

            <!-- Layout container -->
            <div class="layout-page">
                <?php include('assets/inc/incNavbar.php'); ?>

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2 class="fw-bold text-primary mb-1">
                                    <i class="fas fa-layer-group me-2"></i>Template Manager
                                </h2>
                                <p class="text-muted mb-0">Create, edit, and manage form templates for quick form generation</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" onclick="openCreateTemplateModal()">
                                    <i class="fas fa-plus me-1"></i>Create New Template
                                </button>
                                <a href="form_generator.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Form Builder
                                </a>
                            </div>
                        </div>

                        <!-- Templates Grid -->
                        <div class="row" id="templatesGrid">
                            <!-- Templates will be loaded here -->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Template Modal -->
    <div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templateModalTitle">Create New Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="templateForm">
                        <input type="hidden" id="templateId" name="id">

                        <!-- Basic Template Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Template Key <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="templateKey" name="template_key" required placeholder="e.g., customer_feedback">
                                <div class="form-text">Unique identifier for this template (lowercase, underscores only)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Template Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="templateTitle" name="title" required placeholder="e.g., Customer Feedback Survey">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Form Type</label>
                                <select class="form-select" id="formType" name="form_type">
                                    <option value="Feedback">Feedback</option>
                                    <option value="Survey">Survey</option>
                                    <option value="Suggestion">Suggestion</option>
                                    <option value="Review">Review</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">User Fields</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="firstname" id="fieldFirstname" checked>
                                            <label class="form-check-label" for="fieldFirstname">First Name</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="lastname" id="fieldLastname">
                                            <label class="form-check-label" for="fieldLastname">Last Name</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="email" id="fieldEmail" checked>
                                            <label class="form-check-label" for="fieldEmail">Email</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="number" id="fieldNumber">
                                            <label class="form-check-label" for="fieldNumber">Phone Number</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="templateDescription" name="description" rows="3" placeholder="Brief description of this template's purpose"></textarea>
                        </div>

                        <!-- Sections and Questions -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-semibold mb-0">Sections & Questions</h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addTemplateSection()">
                                    <i class="fas fa-plus me-1"></i>Add Section
                                </button>
                            </div>
                            <div id="templateSections">
                                <!-- Sections will be added here -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveTemplate()">Save Template</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this template? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .template-card {
            transition: all 0.3s ease;
            border: 1px solid #e3e6f0;
        }

        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .template-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .template-card:hover .template-actions {
            opacity: 1;
        }

        .section-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .question-item {
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let templates = [];
        let currentTemplate = null;
        let deleteTemplateId = null;
        let sectionCounter = 0;

        // Load templates on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTemplates();
        });

        // Load templates from database
        async function loadTemplates() {
            const grid = document.getElementById('templatesGrid');
            grid.innerHTML = '<div class="col-12"><div class="loading-spinner"><div class="spinner-border text-primary"></div></div></div>';

            try {
                const response = await fetch('template_manager.php?action=get_templates');
                const data = await response.json();

                if (data.success) {
                    templates = data.templates;
                    renderTemplates();
                } else {
                    throw new Error(data.error || 'Failed to load templates');
                }
            } catch (error) {
                console.error('Error loading templates:', error);
                grid.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Failed to load templates: ${error.message}
                        </div>
                    </div>
                `;
            }
        }

        // Render templates grid
        function renderTemplates() {
            const grid = document.getElementById('templatesGrid');

            if (templates.length === 0) {
                grid.innerHTML = `
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-layer-group fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No templates found</h5>
                            <p class="text-muted">Create your first template to get started</p>
                            <button class="btn btn-primary" onclick="openCreateTemplateModal()">
                                <i class="fas fa-plus me-1"></i>Create Template
                            </button>
                        </div>
                    </div>
                `;
                return;
            }

            grid.innerHTML = templates.map(template => `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card template-card h-100 position-relative">
                        <div class="template-actions">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="editTemplate('${template.id}')">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="duplicateTemplate('${template.id}')">
                                        <i class="fas fa-copy me-2"></i>Duplicate
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteTemplate('${template.id}')">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary me-2">${template.form_type}</span>
                                <small class="text-muted">${template.template_key}</small>
                            </div>

                            <h6 class="card-title">${template.title}</h6>
                            <p class="card-text text-muted small">${template.description || 'No description provided'}</p>

                            <div class="row text-center mt-3">
                                <div class="col-4">
                                    <small class="text-muted d-block">Sections</small>
                                    <strong>${template.section_count || 0}</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Questions</small>
                                    <strong>${template.question_count || 0}</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Fields</small>
                                    <strong>${template.user_fields ? template.user_fields.length : 0}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Created ${new Date(template.created_at).toLocaleDateString()}
                            </small>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Open create template modal
        function openCreateTemplateModal() {
            currentTemplate = null;
            document.getElementById('templateModalTitle').textContent = 'Create New Template';
            document.getElementById('templateForm').reset();
            document.getElementById('templateId').value = '';
            document.getElementById('templateSections').innerHTML = '';
            sectionCounter = 0;

            // Add default section
            addTemplateSection();

            new bootstrap.Modal(document.getElementById('templateModal')).show();
        }

        // Add a new section to the template
        function addTemplateSection() {
            sectionCounter++;
            const sectionsContainer = document.getElementById('templateSections');

            const sectionHtml = `
                <div class="section-card p-3" data-section-id="${sectionCounter}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <input type="text" class="form-control fw-semibold" placeholder="Section Title" value="Section ${sectionCounter}" style="max-width: 300px;">
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="addQuestion(${sectionCounter})">
                                <i class="fas fa-plus me-1"></i>Add Question
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSection(${sectionCounter})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="questions-container" id="questions-${sectionCounter}">
                        <!-- Questions will be added here -->
                    </div>
                </div>
            `;

            sectionsContainer.insertAdjacentHTML('beforeend', sectionHtml);
            addQuestion(sectionCounter); // Add default question
        }

        // Add question to section
        function addQuestion(sectionId) {
            const questionsContainer = document.getElementById(`questions-${sectionId}`);
            const questionCount = questionsContainer.children.length + 1;

            const questionHtml = `
                <div class="question-item p-3 mb-2">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" class="form-control mb-2" placeholder="Question text" value="Question ${questionCount}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select mb-2" onchange="handleQuestionTypeChange(this)">
                                <option value="text">Short Answer</option>
                                <option value="textarea">Paragraph</option>
                                <option value="radio">Multiple Choice</option>
                                <option value="checkbox">Checkboxes</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="rating_star">Rating (Stars)</option>
                                <option value="rating_heart">Rating (Hearts)</option>
                                <option value="rating_thumb">Rating (Thumbs)</option>
                                <option value="date">Date</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeQuestion(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="options-container" style="display: none;">
                        <label class="form-label small">Options (one per line):</label>
                        <textarea class="form-control" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                    </div>
                </div>
            `;

            questionsContainer.insertAdjacentHTML('beforeend', questionHtml);
        }

        // Handle question type change
        function handleQuestionTypeChange(select) {
            const questionItem = select.closest('.question-item');
            const optionsContainer = questionItem.querySelector('.options-container');
            const needsOptions = ['radio', 'checkbox', 'dropdown'].includes(select.value);

            optionsContainer.style.display = needsOptions ? 'block' : 'none';
        }

        // Remove section
        function removeSection(sectionId) {
            const section = document.querySelector(`[data-section-id="${sectionId}"]`);
            if (section) {
                section.remove();
            }
        }

        // Remove question
        function removeQuestion(button) {
            const questionItem = button.closest('.question-item');
            if (questionItem) {
                questionItem.remove();
            }
        }

        // Save template
        async function saveTemplate() {
            const formData = new FormData(document.getElementById('templateForm'));
            const templateData = {};

            // Get basic template data
            templateData.template_key = formData.get('template_key');
            templateData.title = formData.get('title');
            templateData.description = formData.get('description');
            templateData.form_type = formData.get('form_type');

            // Get user fields
            templateData.user_fields = [];
            document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                if (checkbox.value && ['firstname', 'lastname', 'email', 'number'].includes(checkbox.value)) {
                    templateData.user_fields.push(checkbox.value);
                }
            });

            // Get sections and questions
            templateData.sections = [];
            document.querySelectorAll('.section-card').forEach(sectionCard => {
                const sectionTitle = sectionCard.querySelector('input[type="text"]').value;
                const section = {
                    title: sectionTitle,
                    questions: []
                };

                sectionCard.querySelectorAll('.question-item').forEach(questionItem => {
                    const questionText = questionItem.querySelector('input[type="text"]').value;
                    const questionType = questionItem.querySelector('select').value;
                    const optionsTextarea = questionItem.querySelector('textarea');

                    const question = {
                        text: questionText,
                        type: questionType
                    };

                    if (optionsTextarea && optionsTextarea.value.trim()) {
                        question.options = optionsTextarea.value.trim().split('\n').map(opt => opt.trim()).filter(opt => opt);
                    }

                    section.questions.push(question);
                });

                templateData.sections.push(section);
            });

            try {
                const templateId = document.getElementById('templateId').value;
                const url = templateId ?
                    `template_manager.php?action=update_template&id=${templateId}` :
                    'template_manager.php?action=create_template';

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(templateData)
                });

                const result = await response.json();

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
                    showAlert('success', result.message);
                    loadTemplates(); // Reload templates
                } else {
                    throw new Error(result.error || 'Failed to save template');
                }
            } catch (error) {
                console.error('Error saving template:', error);
                showAlert('error', 'Failed to save template: ' + error.message);
            }
        }

        // Edit template
        async function editTemplate(templateId) {
            try {
                const response = await fetch(`template_manager.php?action=get_template&id=${templateId}`);
                const data = await response.json();

                if (data.success) {
                    currentTemplate = data.template;
                    populateTemplateForm(currentTemplate);
                    document.getElementById('templateModalTitle').textContent = 'Edit Template';
                    new bootstrap.Modal(document.getElementById('templateModal')).show();
                } else {
                    throw new Error(data.error || 'Failed to load template');
                }
            } catch (error) {
                console.error('Error loading template:', error);
                showAlert('error', 'Failed to load template: ' + error.message);
            }
        }

        // Populate template form with existing data
        function populateTemplateForm(template) {
            document.getElementById('templateId').value = template.id;
            document.getElementById('templateKey').value = template.template_key;
            document.getElementById('templateTitle').value = template.title;
            document.getElementById('templateDescription').value = template.description || '';
            document.getElementById('formType').value = template.form_type;

            // Set user fields
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = template.user_fields.includes(checkbox.value);
            });

            // Clear and populate sections
            document.getElementById('templateSections').innerHTML = '';
            sectionCounter = 0;

            template.sections.forEach(section => {
                addTemplateSection();
                const currentSectionCard = document.querySelector(`[data-section-id="${sectionCounter}"]`);
                currentSectionCard.querySelector('input[type="text"]').value = section.title;

                // Clear default question and add actual questions
                const questionsContainer = currentSectionCard.querySelector('.questions-container');
                questionsContainer.innerHTML = '';

                section.questions.forEach(question => {
                    addQuestion(sectionCounter);
                    const questionItems = questionsContainer.querySelectorAll('.question-item');
                    const lastQuestion = questionItems[questionItems.length - 1];

                    lastQuestion.querySelector('input[type="text"]').value = question.text;
                    lastQuestion.querySelector('select').value = question.type;

                    // Handle options
                    if (question.options && question.options.length > 0) {
                        const optionsTextarea = lastQuestion.querySelector('textarea');
                        if (optionsTextarea) {
                            optionsTextarea.value = question.options.join('\n');
                            lastQuestion.querySelector('.options-container').style.display = 'block';
                        }
                    }
                });
            });
        }

        // Duplicate template
        async function duplicateTemplate(templateId) {
            try {
                const response = await fetch(`template_manager.php?action=duplicate_template&id=${templateId}`, {
                    method: 'POST'
                });
                const result = await response.json();

                if (result.success) {
                    showAlert('success', result.message);
                    loadTemplates(); // Reload templates
                } else {
                    throw new Error(result.error || 'Failed to duplicate template');
                }
            } catch (error) {
                console.error('Error duplicating template:', error);
                showAlert('error', 'Failed to duplicate template: ' + error.message);
            }
        }

        // Delete template
        function deleteTemplate(templateId) {
            deleteTemplateId = templateId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Confirm delete
        async function confirmDelete() {
            if (!deleteTemplateId) return;

            try {
                const response = await fetch(`template_manager.php?action=delete_template&id=${deleteTemplateId}`, {
                    method: 'POST'
                });
                const result = await response.json();

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    showAlert('success', result.message);
                    loadTemplates(); // Reload templates
                } else {
                    throw new Error(result.error || 'Failed to delete template');
                }
            } catch (error) {
                console.error('Error deleting template:', error);
                showAlert('error', 'Failed to delete template: ' + error.message);
            } finally {
                deleteTemplateId = null;
            }
        }

        // Show alert
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertIcon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${alertIcon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            const container = document.querySelector('.container-xxl');
            container.insertAdjacentHTML('afterbegin', alertHtml);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
    </script>
</body>
</html>
