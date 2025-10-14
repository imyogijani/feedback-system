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
                            <!-- Your page content goes here -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h2 class="fw-bold text-primary mb-1">
                                        <i class="fas fa-clipboard-list me-2"></i>Create Feedback Form
                                    </h2>
                                    <p class="text-muted mb-0">Design and customize your feedback form with multiple question types</p>
                                </div>
                                <div class="text-end d-flex align-items-center gap-3 flex-wrap">
                                    <button type="button" class="btn btn-outline-success btn-lg px-4 py-2 shadow-sm" onclick="openTemplateModal()" title="Build form using pre-designed templates">
                                        <i class="fas fa-layer-group me-2"></i>
                                        <span class="d-none d-md-inline">Form Build from Template</span>
                                        <span class="d-md-none">Templates</span>
                                    </button>
                                    <span class="badge bg-light text-primary px-3 py-2">
                                        <i class="fas fa-magic me-1"></i>Form Builder
                                    </span>
                                </div>
                            </div>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" id="form-limit-alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body p-4">
                                    <form method="post" action="crud/save_form.php" id="feedbackForm" enctype="multipart/form-data">
                                    <?php if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2): ?>
                                    <!-- Admin/Moderator Section -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary rounded-circle p-2 me-3">
                                                    <i class="fas fa-user-tie text-white"></i>
                                                </div>
                                                <div>
                                                    <h5 class="mb-0 text-primary">Assignment Settings</h5>
                                                    <small class="text-muted">Configure form ownership and branding</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-building me-2 text-primary"></i>Created for
                                                </label>
                                                <select class="form-select shadow-sm" name="created_for" id="businessNameSelect" onchange="handleBusinessSelection()">
                                            <option value="">Select business name</option>
                                            <?php
                                            // Fetch users for dropdown (with profile image)
                                            $userStmt = $conn->prepare("SELECT id, business_name, profile_image FROM users WHERE business_name IS NOT NULL AND business_name != ''");
                                            $userStmt->execute();
                                            $hasUser = false;
                                            while ($user = $userStmt->fetch(PDO::FETCH_ASSOC)) {
                                                $label = htmlspecialchars($user['business_name']);
                                                $img = $user['profile_image'];
                                                // If image path is not empty, prepend relative path if needed
                                                if ($img && strpos($img, 'http') !== 0 && strpos($img, '/') !== 0) {
                                                    $img = 'assets/images/' . $img;
                                                }
                                                // If image is empty or file does not exist, use a default placeholder
                                                $imgPath = $img && file_exists(__DIR__ . '/' . $img) ? $img : 'https://ui-avatars.com/api/?name=' . urlencode($label) . '&background=cccccc&color=222222&size=100';
                                                $imgAttr = htmlspecialchars($imgPath);
                                                echo '<option value="' . htmlspecialchars($user['id']) . '" data-img="' . $imgAttr . '">' . $label . '</option>';
                                                $hasUser = true;
                                            }
                                            ?>
                                            <option value="custom" data-img="">
                                                <i class="fas fa-plus me-2"></i>🏢 Not from this list (Enter manually)
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div id="profileImagePreview" class="text-center" style="margin-top:35px; display:none;">
                                            <div class="profile-preview-container">
                                                <img id="profileImgTag" src="" alt="Profile Image" class="rounded-circle border border-3 border-primary shadow-sm" style="width:80px; height:80px; object-fit:cover;" />
                                                <div class="mt-2">
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Selected
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <script>
                                        function handleBusinessSelection() {
                                            var select = document.getElementById('businessNameSelect');
                                            var selected = select.options[select.selectedIndex];
                                            var selectedValue = selected.value;
                                            var img = selected.getAttribute('data-img');
                                            var previewDiv = document.getElementById('profileImagePreview');
                                            var imgTag = document.getElementById('profileImgTag');
                                            var companyNameInput = document.getElementById('companyNameInput');
                                            var companyLogoInput = document.getElementById('companyLogoInput');
                                            var manualEntrySection = document.getElementById('manualEntrySection');
                                            var companyNameLabel = document.getElementById('companyNameLabel');
                                            var companyLogoLabel = document.getElementById('companyLogoLabel');

                                            if (selectedValue === 'custom') {
                                                // User selected "Not from this list"
                                                previewDiv.style.display = 'none';
                                                companyNameInput.disabled = false;
                                                companyLogoInput.disabled = false;
                                                companyNameInput.required = true;
                                                companyNameInput.focus();

                                                // Update labels to indicate manual entry
                                                companyNameLabel.innerHTML = '<i class="fas fa-edit me-2 text-primary"></i>Company Name <span class="text-danger">*</span>';
                                                companyLogoLabel.innerHTML = '<i class="fas fa-upload me-2 text-primary"></i>Company Logo <span class="text-danger">*</span>';

                                                // Show helper text
                                                if (!document.getElementById('customEntryHelper')) {
                                                    var helperDiv = document.createElement('div');
                                                    helperDiv.id = 'customEntryHelper';
                                                    helperDiv.className = 'alert alert-info mt-2';
                                                    helperDiv.innerHTML = '<i class="fas fa-info-circle me-2"></i><strong>Custom Entry Mode:</strong> Please enter the company name and upload a logo manually.';
                                                    manualEntrySection.insertBefore(helperDiv, manualEntrySection.firstChild);
                                                }

                                            } else if (selectedValue && selectedValue !== '') {
                                                // User selected an existing business
                                                if (img && img.trim() !== '' && img !== 'null') {
                                                    imgTag.src = img;
                                                    previewDiv.style.display = '';
                                                    companyNameInput.disabled = true;
                                                    companyLogoInput.disabled = true;
                                                    companyNameInput.required = false;
                                                } else {
                                                    previewDiv.style.display = 'none';
                                                    companyNameInput.disabled = false;
                                                    companyLogoInput.disabled = false;
                                                    companyNameInput.required = false;
                                                }

                                                // Reset labels
                                                companyNameLabel.innerHTML = '<i class="fas fa-building me-2 text-primary"></i>Company Name';
                                                companyLogoLabel.innerHTML = '<i class="fas fa-image me-2 text-primary"></i>Company Logo';

                                                // Remove helper text
                                                var helperDiv = document.getElementById('customEntryHelper');
                                                if (helperDiv) {
                                                    helperDiv.remove();
                                                }

                                            } else {
                                                // No selection made
                                                imgTag.src = '';
                                                previewDiv.style.display = 'none';
                                                companyNameInput.disabled = false;
                                                companyLogoInput.disabled = false;
                                                companyNameInput.required = false;

                                                // Reset labels
                                                companyNameLabel.innerHTML = '<i class="fas fa-building me-2 text-primary"></i>Company Name';
                                                companyLogoLabel.innerHTML = '<i class="fas fa-image me-2 text-primary"></i>Company Logo';

                                                // Remove helper text
                                                var helperDiv = document.getElementById('customEntryHelper');
                                                if (helperDiv) {
                                                    helperDiv.remove();
                                                }
                                            }
                                        }

                                        // Handle manual logo upload preview
                                        function handleLogoUpload(input) {
                                            var previewDiv = document.getElementById('manualLogoPreview');
                                            var previewImg = document.getElementById('manualLogoImg');

                                            if (input.files && input.files[0]) {
                                                var reader = new FileReader();
                                                reader.onload = function(e) {
                                                    previewImg.src = e.target.result;
                                                    previewDiv.style.display = 'block';
                                                };
                                                reader.readAsDataURL(input.files[0]);
                                            } else {
                                                previewDiv.style.display = 'none';
                                            }
                                        }

                                        // Add event listener for logo upload
                                        document.addEventListener('DOMContentLoaded', function() {
                                            handleBusinessSelection();

                                            var logoInput = document.getElementById('companyLogoInput');
                                            if (logoInput) {
                                                logoInput.addEventListener('change', function() {
                                                    handleLogoUpload(this);
                                                });
                                            }

                                            // Form validation for custom entry
                                            var form = document.getElementById('feedbackForm');
                                            if (form) {
                                                form.addEventListener('submit', function(e) {
                                                    var businessSelect = document.getElementById('businessNameSelect');
                                                    var companyNameInput = document.getElementById('companyNameInput');
                                                    var companyLogoInput = document.getElementById('companyLogoInput');

                                                    if (businessSelect.value === 'custom') {
                                                        if (!companyNameInput.value.trim()) {
                                                            e.preventDefault();
                                                            companyNameInput.focus();
                                                            companyNameInput.style.borderColor = '#dc3545';

                                                            // Show error message
                                                            var existingError = document.getElementById('companyNameError');
                                                            if (existingError) existingError.remove();

                                                            var errorDiv = document.createElement('div');
                                                            errorDiv.id = 'companyNameError';
                                                            errorDiv.className = 'text-danger mt-1 small';
                                                            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Company name is required when selecting "Not from this list"';
                                                            companyNameInput.parentNode.appendChild(errorDiv);

                                                            setTimeout(function() {
                                                                companyNameInput.style.borderColor = '';
                                                                if (errorDiv) errorDiv.remove();
                                                            }, 3000);

                                                            return false;
                                                        }

                                                        if (!companyLogoInput.files || !companyLogoInput.files[0]) {
                                                            e.preventDefault();
                                                            companyLogoInput.focus();
                                                            companyLogoInput.style.borderColor = '#dc3545';

                                                            // Show error message
                                                            var existingError = document.getElementById('companyLogoError');
                                                            if (existingError) existingError.remove();

                                                            var errorDiv = document.createElement('div');
                                                            errorDiv.id = 'companyLogoError';
                                                            errorDiv.className = 'text-danger mt-1 small';
                                                            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Company logo is required when selecting "Not from this list"';
                                                            companyLogoInput.parentNode.appendChild(errorDiv);

                                                            setTimeout(function() {
                                                                companyLogoInput.style.borderColor = '';
                                                                if (errorDiv) errorDiv.remove();
                                                            }, 3000);

                                                            return false;
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                        </script>
                                        </div>
                                </div>
                                <div class="row" id="manualEntrySection">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold" id="companyNameLabel">
                                            <i class="fas fa-building me-2 text-primary"></i>Company Name
                                        </label>
                                        <input type="text" class="form-control shadow-sm" name="company_name" id="companyNameInput" placeholder="Enter company name">
                                        <small class="text-muted">This will be used if no business is selected from the dropdown</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold" id="companyLogoLabel">
                                            <i class="fas fa-image me-2 text-primary"></i>Company Logo
                                        </label>
                                        <input type="file" class="form-control shadow-sm" name="company_logo" id="companyLogoInput" accept="image/*">
                                        <small class="text-muted">Supported formats: JPG, PNG, GIF (Max: 2MB)</small>

                                        <!-- Logo Preview for Manual Upload -->
                                        <div id="manualLogoPreview" class="mt-2" style="display:none;">
                                            <div class="text-center">
                                                <img id="manualLogoImg" src="" alt="Logo Preview" class="rounded border border-2 border-primary shadow-sm" style="max-width:100px; max-height:100px; object-fit:cover;" />
                                                <div class="mt-1">
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-image me-1"></i>Logo Preview
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <!-- Form Configuration Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success rounded-circle p-2 me-3">
                                                <i class="fas fa-cog text-white"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 text-success">Form Configuration</h5>
                                                <small class="text-muted">Define your form's basic properties</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-tag me-2 text-success"></i>Form Type
                                        </label>
                                        <select name="form_type" id="form_type" class="form-select shadow-sm">
                                            <option>Select Option</option>
                                            <option value="Suggestion">📝 Suggestion</option>
                                            <option value="Complaints">⚠️ Complaints</option>
                                            <option value="Feedback">💬 Feedback</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-heading me-2 text-success"></i>Form Title
                                        </label>
                                        <input type="text" class="form-control shadow-sm" name="title" required placeholder="Enter form title">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-align-left me-2 text-success"></i>Description
                                    </label>
                                    <textarea class="form-control shadow-sm" name="description" rows="3" placeholder="Describe the purpose of this form"></textarea>
                                </div>
                                <!-- User Information Section -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-info rounded-circle p-2 me-3">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 text-info">User Information Fields</h5>
                                            <small class="text-muted">Select which user details to collect</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="form-check form-check-card">
                                                <input class="form-check-input" type="checkbox" name="firstname" value="1" id="firstname">
                                                <label class="form-check-label fw-semibold" for="firstname">
                                                    <i class="fas fa-user me-2 text-info"></i>First Name
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="form-check form-check-card">
                                                <input class="form-check-input" type="checkbox" name="lastname" value="1" id="lastname">
                                                <label class="form-check-label fw-semibold" for="lastname">
                                                    <i class="fas fa-user me-2 text-info"></i>Last Name
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="form-check form-check-card">
                                                <input class="form-check-input" type="checkbox" name="email" value="1" id="email">
                                                <label class="form-check-label fw-semibold" for="email">
                                                    <i class="fas fa-envelope me-2 text-info"></i>Email
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="form-check form-check-card">
                                                <input class="form-check-input" type="checkbox" name="number" value="1" id="number">
                                                <label class="form-check-label fw-semibold" for="number">
                                                    <i class="fas fa-phone me-2 text-info"></i>Phone Number
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Questions Section -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning rounded-circle p-2 me-3">
                                                <i class="fas fa-question text-white"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 text-warning">Form Questions</h5>
                                                <small class="text-muted">Design your form with multiple question types</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-light text-warning px-3 py-2">
                                                <i class="fas fa-grip-vertical me-1"></i>Drag to Reorder
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div id="questions">
            <!-- Default Question Section with Add Question button for sub-questions -->
            <section class="question-section mb-4" draggable="true" ondragstart="dragSection(event)" ondragover="allowSectionDrop(event)" ondrop="dropSection(event)">
                <div class="section-header custom-section-header d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <span class="section-badge me-2">1</span>
                        <input type="text" class="form-control fw-bold section-title-input" name="section_titles[]" value="" placeholder="Add Section Name" style="width: 200px;" />
                    </div>
                    <span class="drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
                </div>
                <div class="question-block">
                    <div class="row align-items-center mb-2">
                        <div class="col-10 col-md-10">
                            <label class="question-label">Question 1</label>
                        </div>
                        <div class="col-2 col-md-2 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-btn" onclick="removeQuestion(this)" title="Remove section"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
            <input type="text" name="questions[]" class="form-control mt-2" placeholder="Question text" required data-section="1">
                        </div>
                        <div class="col-md-6">
                            <select id="questionType1" name="types[]" class="form-select mt-2" onchange="updateQuestionFields(1)" data-section="1">
                                <option>Select Option</option>
                                <option value="text">Short Answer</option>
                                <option value="textarea">Paragraph</option>
                                <option value="radio">Radio Button</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="date">Date Picker</option>
                                <option value="rating_star">Rating (Stars)</option>
                                <option value="rating_heart">Rating (Hearts)</option>
                                <option value="rating_thumb">Rating (Thumbs)</option>
                            </select>
                        </div>
                    </div>
                    <div id="questionFields1">
                        <!-- Question-specific fields will be added here -->
                    </div>
                    <div class="text-end mt-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSubQuestion(this, 1, this.closest('.question-section'))"><i class="fas fa-plus"></i> Add Question</button>
                    </div>
                </div>
            </section>
            <script>
            // Section drag and drop logic (fixed)
            let draggedSection = null;
            let dragOverSection = null;
            function dragSection(ev) {
                draggedSection = ev.currentTarget;
                ev.dataTransfer.effectAllowed = 'move';
                ev.dataTransfer.setData('text/plain', '');
            }
            function allowSectionDrop(ev) {
                ev.preventDefault();
                dragOverSection = ev.currentTarget;
                dragOverSection.classList.add('drag-over');
            }
            function dropSection(ev) {
                ev.preventDefault();
                if (draggedSection && dragOverSection && draggedSection !== dragOverSection) {
                    const parent = dragOverSection.parentNode;
                    parent.insertBefore(draggedSection, dragOverSection);
                }
                if (dragOverSection) dragOverSection.classList.remove('drag-over');
                draggedSection = null;
                dragOverSection = null;
            }
            // Remove drag-over class on dragleave
            document.addEventListener('dragleave', function(ev) {
                if (ev.target.classList && ev.target.classList.contains('question-section')) {
                    ev.target.classList.remove('drag-over');
                }
            });
            </script>
                                </div>
                </div>

                <!-- Add Section Button -->
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-outline-primary btn-lg px-4 py-2" onclick="addSection()">
                        <i class="fas fa-plus-circle me-2"></i>Add New Section
                    </button>
                </div>

                <!-- Completion Settings -->
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-purple rounded-circle p-2 me-3" style="background: #6f42c1;">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" style="color: #6f42c1;">Completion Settings</h5>
                            <small class="text-muted">Configure post-submission behavior</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="enableThankYou" name="enable_thankyou" value="1" onchange="toggleThankYouMessage()">
                                <label class="form-check-label fw-semibold" for="enableThankYou">
                                    <i class="fas fa-heart me-2" style="color: #6f42c1;"></i>Enable Custom Thank You Message
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="allow_another_response" id="allowAnotherResponse" value="1">
                                <label class="form-check-label fw-semibold" for="allowAnotherResponse">
                                    <i class="fas fa-redo me-2" style="color: #6f42c1;"></i>Allow Multiple Responses
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4" id="thankYouMessageContainer" style="display: none;">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-comment me-2" style="color: #6f42c1;"></i>Custom Thank You Message
                        </label>
                        <textarea class="form-control shadow-sm" name="thankyou_message" id="thankyouMessageField" rows="3" placeholder="Enter your custom thank you message here..."></textarea>
                        <small class="text-muted">This message will be displayed after form submission</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                    <div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Remember to preview your form before publishing
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-eye me-2"></i>Preview
                        </button>
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <i class="fas fa-save me-2"></i>Create Form
                        </button>
                    </div>
                </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Template Modal -->
                            <div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-gradient-success text-white">
                                            <h5 class="modal-title" id="templateModalLabel">
                                                <i class="fas fa-layer-group me-2"></i>Build Form from Template
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row mb-4">
                                                <div class="col-12">
                                                    <h6 class="text-success mb-3">
                                                        <i class="fas fa-magic me-2"></i>Choose a template to get started quickly
                                                    </h6>
                                                    <p class="text-muted">Select from our pre-designed templates to create professional feedback forms in seconds.</p>
                                                </div>
                                            </div>

                                            <div class="row g-4" id="templateContainer">
                                                <!-- Customer Satisfaction Template -->
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="template-card card h-100 border-0 shadow-sm" onclick="selectTemplate('customer_satisfaction')">
                                                        <div class="card-header bg-primary text-white text-center py-3">
                                                            <i class="fas fa-smile fa-2x mb-2"></i>
                                                            <h6 class="mb-0">Customer Satisfaction</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="text-muted small mb-3">Perfect for measuring customer experience and satisfaction levels.</p>
                                                            <div class="template-features">
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-star"></i> Rating Questions</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-comment"></i> Feedback</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-user"></i> Contact Info</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Product Feedback Template -->
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="template-card card h-100 border-0 shadow-sm" onclick="selectTemplate('product_feedback')">
                                                        <div class="card-header bg-info text-white text-center py-3">
                                                            <i class="fas fa-box fa-2x mb-2"></i>
                                                            <h6 class="mb-0">Product Feedback</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="text-muted small mb-3">Collect detailed feedback about your products and services.</p>
                                                            <div class="template-features">
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-list"></i> Multiple Choice</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-thumbs-up"></i> Ratings</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-edit"></i> Text Areas</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Event Feedback Template -->
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="template-card card h-100 border-0 shadow-sm" onclick="selectTemplate('event_feedback')">
                                                        <div class="card-header bg-warning text-white text-center py-3">
                                                            <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                                            <h6 class="mb-0">Event Feedback</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="text-muted small mb-3">Get insights about events, workshops, and conferences.</p>
                                                            <div class="template-features">
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-calendar"></i> Date Fields</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-star"></i> Experience Rating</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-users"></i> Attendee Info</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Employee Feedback Template -->
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="template-card card h-100 border-0 shadow-sm" onclick="selectTemplate('employee_feedback')">
                                                        <div class="card-header bg-secondary text-white text-center py-3">
                                                            <i class="fas fa-users fa-2x mb-2"></i>
                                                            <h6 class="mb-0">Employee Feedback</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="text-muted small mb-3">Internal feedback for workplace improvement and culture.</p>
                                                            <div class="template-features">
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-briefcase"></i> Work Environment</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-chart-line"></i> Performance</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-lightbulb"></i> Suggestions</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Service Quality Template -->
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="template-card card h-100 border-0 shadow-sm" onclick="selectTemplate('service_quality')">
                                                        <div class="card-header bg-danger text-white text-center py-3">
                                                            <i class="fas fa-headset fa-2x mb-2"></i>
                                                            <h6 class="mb-0">Service Quality</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="text-muted small mb-3">Evaluate service quality and customer support effectiveness.</p>
                                                            <div class="template-features">
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-clock"></i> Response Time</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-medal"></i> Quality Rating</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-redo"></i> Follow-up</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- General Survey Template -->
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="template-card card h-100 border-0 shadow-sm" onclick="selectTemplate('general_survey')">
                                                        <div class="card-header bg-dark text-white text-center py-3">
                                                            <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                                                            <h6 class="mb-0">General Survey</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="text-muted small mb-3">A versatile template for any type of survey or feedback collection.</p>
                                                            <div class="template-features">
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-question-circle"></i> Mixed Questions</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-list-ul"></i> Flexible Options</span>
                                                                <span class="badge bg-light text-dark me-1 mb-1"><i class="fas fa-cogs"></i> Customizable</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-2"></i>Cancel
                                            </button>
                                            <button type="button" class="btn btn-success" onclick="applySelectedTemplate()" id="applyTemplateBtn" disabled>
                                                <i class="fas fa-magic me-2"></i>Apply Selected Template
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- / Content -->
 <?php require_once 'assets/inc/incFooter.php'; ?>

                    </div>
                    <!-- / Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <!-- / Layout wrapper -->

        <!-- Core JS -->
        <!-- build:js assets/vendor/js/core.js -->
        <script src="../assets/vendor/libs/jquery/jquery.js"></script>
        <script src="../assets/vendor/libs/popper/popper.js"></script>
        <script src="../assets/vendor/js/bootstrap.js"></script>
        <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

        <script src="../assets/vendor/js/menu.js"></script>
        <!-- endbuild -->

        <!-- Vendors JS -->
        <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>

        <!-- Main JS -->
        <script src="../assets/js/main.js"></script>

        <!-- Page JS -->
        <script src="../assets/js/dashboards-analytics.js"></script>

        <!-- Place this tag in your head or just before your close body tag. -->
        <script async defer src="https://buttons.github.io/buttons.js"></script>

        <script>
            let selectedTemplate = null;
            let questionCounter = 1; // Initialize to 1 to match the default question

            // Template functionality
            async function openTemplateModal() {
                const modal = new bootstrap.Modal(document.getElementById('templateModal'));

                // Show loading state
                const templateGrid = document.getElementById('templateGrid');
                templateGrid.innerHTML = `
                    <div class="col-12 text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading templates...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading templates...</p>
                    </div>
                `;

                modal.show();

                // Load templates from database
                await loadTemplatesForModal();
            }

            async function loadTemplatesForModal() {
                try {
                    const response = await fetch('template_manager.php?action=get_templates');
                    const data = await response.json();

                    if (data.success && data.templates.length > 0) {
                        renderTemplateGrid(data.templates);
                    } else {
                        showNoTemplatesMessage();
                    }
                } catch (error) {
                    console.error('Error loading templates:', error);
                    showTemplateLoadError();
                }
            }

            function renderTemplateGrid(templates) {
                const templateGrid = document.getElementById('templateGrid');

                templateGrid.innerHTML = templates.map(template => `
                    <div class="col-md-6 mb-3">
                        <div class="card template-card h-100" onclick="selectTemplate('${template.template_key}', this)" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary">${template.form_type}</span>
                                    <small class="text-muted">${template.section_count || 0} sections, ${template.question_count || 0} questions</small>
                                </div>
                                <h6 class="card-title">${template.title}</h6>
                                <p class="card-text text-muted small">${template.description || 'No description provided'}</p>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        Fields: ${template.user_fields ? template.user_fields.join(', ') : 'None'}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            function showNoTemplatesMessage() {
                const templateGrid = document.getElementById('templateGrid');
                templateGrid.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Templates Available</h5>
                        <p class="text-muted">Create templates in the Template Manager to use here.</p>
                        <a href="template_management.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Manage Templates
                        </a>
                    </div>
                `;

                // Disable apply button
                document.getElementById('applyTemplateBtn').disabled = true;
            }

            function showTemplateLoadError() {
                const templateGrid = document.getElementById('templateGrid');
                templateGrid.innerHTML = `
                    <div class="col-12 text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                        <h6 class="text-muted">Failed to Load Templates</h6>
                        <p class="text-muted small">Please try again or contact support if the problem persists.</p>
                        <button class="btn btn-outline-primary btn-sm" onclick="loadTemplatesForModal()">
                            <i class="fas fa-redo me-1"></i>Retry
                        </button>
                    </div>
                `;
            }

            function selectTemplate(templateKey, cardElement) {
                // Remove selection from all cards
                document.querySelectorAll('.template-card').forEach(card => {
                    card.classList.remove('selected-template');
                });

                // Add selection to clicked card
                cardElement.classList.add('selected-template');
                selectedTemplate = templateKey;

                // Enable apply button
                document.getElementById('applyTemplateBtn').disabled = false;
            }

            async function applySelectedTemplate() {
                if (!selectedTemplate) return;

                // Clear existing form
                clearForm();

                // Load complete template data from database
                try {
                    const response = await fetch(`template_manager.php?action=get_template&key=${selectedTemplate}`);
                    const data = await response.json();

                    if (data.success && data.template) {
                        applyTemplateToForm(data.template);

                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('templateModal'));
                        modal.hide();

                        // Show success message
                        showAlertMessage('success', `Template "${data.template.title}" applied successfully!`);
                    } else {
                        throw new Error(data.error || 'Template not found');
                    }
                } catch (error) {
                    console.error('Error applying template:', error);
                    showAlertMessage('error', 'Failed to apply template: ' + error.message);
                }
            }

            function applyTemplateToForm(template) {

                if (template) {
                    // Set form basic info
                    document.getElementById('form_type').value = template.form_type;
                    document.querySelector('input[name="title"]').value = template.title;
                    document.querySelector('textarea[name="description"]').value = template.description;

                    // Set user info checkboxes
                    template.user_fields.forEach(field => {
                        const checkbox = document.getElementById(field);
                        if (checkbox) checkbox.checked = true;
                    });

                    // Clear existing questions
                    const questionsContainer = document.getElementById('questions');
                    questionsContainer.innerHTML = '';

                    // Add template questions
                    questionCounter = 1;
                    template.sections.forEach((section, sectionIndex) => {
                        addTemplateSection(section, sectionIndex + 1);
                    });

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('templateModal'));
                    modal.hide();

                    // Show success message
                    showTemplateSuccessMessage(template.title);
                }
            }

            function clearForm() {
                // Reset form fields
                document.getElementById('form_type').selectedIndex = 0;
                document.querySelector('input[name="title"]').value = '';
                document.querySelector('textarea[name="description"]').value = '';

                // Uncheck all user info checkboxes
                document.querySelectorAll('input[type="checkbox"][name$="name"], input[type="checkbox"][name="email"], input[type="checkbox"][name="number"]').forEach(cb => {
                    cb.checked = false;
                });
            }

            function addTemplateSection(sectionData, sectionNumber) {
                const section = document.createElement('section');
                section.classList.add('question-section', 'mb-4');
                section.setAttribute('draggable', 'true');
                section.ondragstart = dragSection;
                section.ondragover = allowSectionDrop;
                section.ondrop = dropSection;

                let questionsHtml = '';
                const currentSectionStartIndex = questionCounter;

                // Generate all questions in the section
                sectionData.questions.forEach((question, qIndex) => {
                    const questionNumber = qIndex + 1;
                    const globalQuestionIndex = currentSectionStartIndex + qIndex;
                    let questionFieldsHtml = '';

                    // Generate question-specific fields based on type
                    if (['radio', 'checkbox', 'dropdown'].includes(question.type) && question.options) {
                        let optionsHtml = '';
                        question.options.forEach(option => {
                            let iconHtml = '';
                            if (question.type === 'radio') {
                                iconHtml = `<span class="input-group-text"><input type="radio" disabled></span>`;
                            } else if (question.type === 'checkbox') {
                                iconHtml = `<span class="input-group-text"><input type="checkbox" disabled></span>`;
                            } else if (question.type === 'dropdown') {
                                iconHtml = `<span class="input-group-text"><i class="fas fa-caret-down"></i></span>`;
                            }

                            optionsHtml += `
                                <div class="input-group mb-2">
                                    ${iconHtml}
                                    <input type="text" class="form-control" name="options[${globalQuestionIndex}][]" placeholder="Option text" value="${option}">
                                    <button class="btn btn-outline-secondary" type="button" onclick="removeOption(this)">Remove</button>
                                </div>
                            `;
                        });

                        questionFieldsHtml = `
                            <div class="mt-3">
                                <label class="form-label">Options</label>
                                <div id="optionsContainer${globalQuestionIndex}">
                                    ${optionsHtml}
                                </div>
                                <button type="button" class="btn btn-outline-primary" onclick="addOptionGlobal(${sectionNumber}, ${globalQuestionIndex})">Add Option</button>
                            </div>
                        `;
                    } else if (question.type === 'date') {
                        questionFieldsHtml = `
                            <div class="mt-3">
                                <label class="form-label">Date Picker</label>
                                <input type="date" class="form-control" name="date_answer[${globalQuestionIndex}]" placeholder="Select date">
                            </div>
                        `;
                    } else if (question.type === 'text') {
                        questionFieldsHtml = `
                            <div class="mt-3">
                                <label class="form-label">Short Answer</label>
                                <input type="text" class="form-control" name="text_answer[${globalQuestionIndex}]" placeholder="Short answer text">
                            </div>
                        `;
                    } else if (question.type === 'textarea') {
                        questionFieldsHtml = `
                            <div class="mt-3">
                                <label class="form-label">Paragraph</label>
                                <textarea class="form-control" name="paragraph_answer[${globalQuestionIndex}]" rows="3" placeholder="Paragraph text"></textarea>
                            </div>
                        `;
                    } else if (question.type === 'rating_star') {
                        questionFieldsHtml = `
                            <div class="mt-3">
                                <label class="form-label">Rating (Stars)</label>
                                <div class="rating" id="rating-container-${globalQuestionIndex}">
                                    <div class="star-wrapper" data-rating="1">
                                        <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${globalQuestionIndex}, 0.5)" data-value="0.5"></i>
                                        <i class="fas fa-star fa-2x full-star" onclick="setRating(${globalQuestionIndex}, 1)" data-value="1"></i>
                                    </div>
                                    <div class="star-wrapper" data-rating="2">
                                        <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${globalQuestionIndex}, 1.5)" data-value="1.5"></i>
                                        <i class="fas fa-star fa-2x full-star" onclick="setRating(${globalQuestionIndex}, 2)" data-value="2"></i>
                                    </div>
                                    <div class="star-wrapper" data-rating="3">
                                        <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${globalQuestionIndex}, 2.5)" data-value="2.5"></i>
                                        <i class="fas fa-star fa-2x full-star" onclick="setRating(${globalQuestionIndex}, 3)" data-value="3"></i>
                                    </div>
                                    <div class="star-wrapper" data-rating="4">
                                        <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${globalQuestionIndex}, 3.5)" data-value="3.5"></i>
                                        <i class="fas fa-star fa-2x full-star" onclick="setRating(${globalQuestionIndex}, 4)" data-value="4"></i>
                                    </div>
                                    <div class="star-wrapper" data-rating="5">
                                        <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${globalQuestionIndex}, 4.5)" data-value="4.5"></i>
                                        <i class="fas fa-star fa-2x full-star" onclick="setRating(${globalQuestionIndex}, 5)" data-value="5"></i>
                                    </div>
                                    <input type="hidden" name="rating[${globalQuestionIndex}]" id="rating${globalQuestionIndex}" value="0">
                                </div>
                            </div>
                        `;
                    } else if (question.type === 'rating_heart') {
                        questionFieldsHtml = `
                            <div class="mt-3">
                                <label class="form-label">Rating (Hearts)</label>
                                <div class="rating" id="rating-container-${globalQuestionIndex}">
                                    <div class="heart-wrapper" data-rating="1">
                                        <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${globalQuestionIndex}, 0.5)" data-value="0.5"></i>
                                        <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${globalQuestionIndex}, 1)" data-value="1"></i>
                                    </div>
                                    <div class="heart-wrapper" data-rating="2">
                                        <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${globalQuestionIndex}, 1.5)" data-value="1.5"></i>
                                        <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${globalQuestionIndex}, 2)" data-value="2"></i>
                                    </div>
                                    <div class="heart-wrapper" data-rating="3">
                                        <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${globalQuestionIndex}, 2.5)" data-value="2.5"></i>
                                        <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${globalQuestionIndex}, 3)" data-value="3"></i>
                                    </div>
                                    <div class="heart-wrapper" data-rating="4">
                                        <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${globalQuestionIndex}, 3.5)" data-value="3.5"></i>
                                        <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${globalQuestionIndex}, 4)" data-value="4"></i>
                                    </div>
                                    <div class="heart-wrapper" data-rating="5">
                                        <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${globalQuestionIndex}, 4.5)" data-value="4.5"></i>
                                        <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${globalQuestionIndex}, 5)" data-value="5"></i>
                                    </div>
                                    <input type="hidden" name="rating[${globalQuestionIndex}]" id="rating${globalQuestionIndex}" value="0">
                                </div>
                            </div>
                        `;
                    } else if (question.type === 'rating_thumb') {
                        questionFieldsHtml = `
                            <div class="mt-3">
                                <label class="form-label">Rating (Thumbs)</label>
                                <div class="rating">
                                    <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${globalQuestionIndex}, 1)" data-value="1"></i>
                                    <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${globalQuestionIndex}, 2)" data-value="2"></i>
                                    <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${globalQuestionIndex}, 3)" data-value="3"></i>
                                    <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${globalQuestionIndex}, 4)" data-value="4"></i>
                                    <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${globalQuestionIndex}, 5)" data-value="5"></i>
                                    <input type="hidden" name="rating[${globalQuestionIndex}]" id="rating${globalQuestionIndex}" value="0">
                                </div>
                            </div>
                        `;
                    }

                    if (qIndex === 0) {
                        // Main question
                        questionsHtml += `
                            <div class="question-block">
                                <div class="row align-items-center mb-2">
                                    <div class="col-10 col-md-10">
                                        <label class="question-label">Question ${questionNumber}</label>
                                    </div>
                                    <div class="col-2 col-md-2 text-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-btn" onclick="removeQuestion(this)" title="Remove section"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="questions[]" class="form-control mt-2" placeholder="Question text" required data-section="${sectionNumber}" value="${question.text}">
                                    </div>
                                    <div class="col-md-6">
                                        <select id="questionType${globalQuestionIndex}" name="types[]" class="form-select mt-2" onchange="updateQuestionFields(${globalQuestionIndex})" data-section="${sectionNumber}">
                                            <option>Select Option</option>
                                            <option value="text" ${question.type === 'text' ? 'selected' : ''}>Short Answer</option>
                                            <option value="textarea" ${question.type === 'textarea' ? 'selected' : ''}>Paragraph</option>
                                            <option value="radio" ${question.type === 'radio' ? 'selected' : ''}>Radio Button</option>
                                            <option value="checkbox" ${question.type === 'checkbox' ? 'selected' : ''}>Checkbox</option>
                                            <option value="dropdown" ${question.type === 'dropdown' ? 'selected' : ''}>Dropdown</option>
                                            <option value="date" ${question.type === 'date' ? 'selected' : ''}>Date Picker</option>
                                            <option value="rating_star" ${question.type === 'rating_star' ? 'selected' : ''}>Rating (Stars)</option>
                                            <option value="rating_heart" ${question.type === 'rating_heart' ? 'selected' : ''}>Rating (Hearts)</option>
                                            <option value="rating_thumb" ${question.type === 'rating_thumb' ? 'selected' : ''}>Rating (Thumbs)</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="questionFields${globalQuestionIndex}">
                                    ${questionFieldsHtml}
                                </div>
                                <div class="text-end mt-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSubQuestion(this, ${sectionNumber}, this.closest('.question-section'))"><i class="fas fa-plus"></i> Add Question</button>
                                </div>
                            </div>
                        `;
                    } else {
                        // Sub question
                        questionsHtml += `
                            <div class="row align-items-center mb-2 sub-question-block">
                                <div class="col-10 col-md-10">
                                    <label class="question-label">Question ${questionNumber}</label>
                                </div>
                                <div class="col-2 col-md-2 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-btn" onclick="removeSubQuestion(this)" title="Remove question"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="questions[]" class="form-control mt-2" placeholder="Question text" required data-section="${sectionNumber}" value="${question.text}">
                                </div>
                                <div class="col-md-6">
                                    <select name="types[]" class="form-select mt-2" onchange="updateQuestionFieldsForSub(this, ${globalQuestionIndex})" data-section="${sectionNumber}">
                                        <option>Select Option</option>
                                        <option value="text" ${question.type === 'text' ? 'selected' : ''}>Short Answer</option>
                                        <option value="textarea" ${question.type === 'textarea' ? 'selected' : ''}>Paragraph</option>
                                        <option value="radio" ${question.type === 'radio' ? 'selected' : ''}>Radio Button</option>
                                        <option value="checkbox" ${question.type === 'checkbox' ? 'selected' : ''}>Checkbox</option>
                                        <option value="dropdown" ${question.type === 'dropdown' ? 'selected' : ''}>Dropdown</option>
                                        <option value="date" ${question.type === 'date' ? 'selected' : ''}>Date Picker</option>
                                        <option value="rating_star" ${question.type === 'rating_star' ? 'selected' : ''}>Rating (Stars)</option>
                                        <option value="rating_heart" ${question.type === 'rating_heart' ? 'selected' : ''}>Rating (Hearts)</option>
                                        <option value="rating_thumb" ${question.type === 'rating_thumb' ? 'selected' : ''}>Rating (Thumbs)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="sub-question-fields mt-2" data-question-index="${globalQuestionIndex}">
                                        ${questionFieldsHtml}
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });

                // Update question counter for next section
                questionCounter += sectionData.questions.length;

                section.innerHTML = `
                    <div class="section-header custom-section-header d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <span class="section-badge me-2">${sectionNumber}</span>
                            <input type="text" class="form-control fw-bold section-title-input" name="section_titles[]" value="${sectionData.title}" style="width: 200px;" placeholder="Add Section Name" />
                        </div>
                        <span class="drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
                    </div>
                    ${questionsHtml}
                `;

                document.getElementById('questions').appendChild(section);
            }

            function showTemplateSuccessMessage(templateName) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                alertDiv.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Template Applied Successfully!</strong> The "${templateName}" template has been loaded into your form.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                const container = document.querySelector('.container-xxl');
                const firstChild = container.firstElementChild;
                container.insertBefore(alertDiv, firstChild.nextSibling);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (alertDiv && alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }

            async function getTemplateData() {
                try {
                    const response = await fetch('template_manager.php?action=get_templates');
                    const data = await response.json();

                    if (data.success) {
                        // Convert array format to object format for compatibility
                        const templatesObject = {};
                        data.templates.forEach(template => {
                            templatesObject[template.template_key] = {
                                title: template.title,
                                description: template.description,
                                form_type: template.form_type,
                                user_fields: template.user_fields || []
                            };
                        });
                        return templatesObject;
                    } else {
                        console.error('Failed to load templates:', data.error);
                        return getFallbackTemplates();
                    }
                } catch (error) {
                    console.error('Error fetching templates:', error);
                    return getFallbackTemplates();
                }
            }

            // Fallback templates in case database is not available
            function getFallbackTemplates() {
                return {
                    customer_satisfaction: {
                        title: "Customer Satisfaction Survey",
                        description: "Help us improve our service by sharing your experience with us.",
                        form_type: "Feedback",
                        user_fields: ["firstname", "lastname", "email"]
                    },
                    product_feedback: {
                        title: "Product Feedback Form",
                        description: "Share your thoughts about our product to help us make it better.",
                        form_type: "Feedback",
                        user_fields: ["firstname", "email"]
                    }
                };
            }


            function addSection() {
                questionCounter++;
                const section = document.createElement('section');
                section.classList.add('question-section', 'mb-3');
                section.setAttribute('draggable', 'true');
                section.style.cursor = 'move';
                section.ondragstart = dragSection;
                section.ondragover = allowSectionDrop;
                section.ondrop = dropSection;
                section.innerHTML = `
                    <div class="section-header custom-section-header d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <span class="section-badge me-2">${questionCounter}</span>
                            <input type="text" class="form-control fw-bold section-title-input" name="section_titles[]" value="" style="width: 200px;" placeholder="Add Section Name" />
                        </div>
                        <span class="drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
                    </div>
                    <div class="question-block">
                        <div class="row align-items-center mb-2">
                            <div class="col-10 col-md-10">
                                <label class="question-label">Question 1</label>
                            </div>
                            <div class="col-2 col-md-2 text-end">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-btn" onclick="removeQuestion(this)" title="Remove section"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="questions[]" class="form-control mt-2" placeholder="Question text" required data-section="${questionCounter}">
                            </div>
                            <div class="col-md-6">
                                <select id="questionType${questionCounter}" name="types[]" class="form-select mt-2" onchange="updateQuestionFields(${questionCounter})" data-section="${questionCounter}">
                                    <option>Select Option</option>
                                    <option value="text">Short Answer</option>
                                    <option value="textarea">Paragraph</option>
                                    <option value="radio">Radio Button</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="dropdown">Dropdown</option>
                                    <option value="date">Date Picker</option>
                                    <option value="rating_star">Rating (Stars)</option>
                                    <option value="rating_heart">Rating (Hearts)</option>
                                    <option value="rating_thumb">Rating (Thumbs)</option>
                                </select>
                            </div>
                        </div>
                        <div id="questionFields${questionCounter}">
                            <!-- Question-specific fields will be added here -->
                        </div>
                        <div class="text-end mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSubQuestion(this, ${questionCounter}, this.closest('.question-section'))"><i class="fas fa-plus"></i> Add Question</button>
                        </div>
                    </div>
                `;
                document.getElementById('questions').appendChild(section);
            }


// Add a sub-question (additional question) to a section
function addSubQuestion(btn, sectionNumber, sectionElem) {
    // sectionElem is the .question-section element (explicitly passed)
    const section = sectionElem || btn.closest('.question-section');
    const questionBlock = document.createElement('div');
    // Count sub-questions already present in this section
    const subQuestionCount = section.querySelectorAll('.sub-question-block').length;
    const questionNumber = subQuestionCount + 2; // 1 for main, +1 for next sub
    // Find the global index of this sub-question in the questions[] array
    let globalQuestionIndex = 0;
    const allSections = document.querySelectorAll('#questions .question-section');
    for (let i = 0; i < allSections.length; i++) {
        if (allSections[i] === section) break;
        globalQuestionIndex += allSections[i].querySelectorAll('input[name="questions[]"]').length;
    }
    // Add 1 for the main question in this section
    globalQuestionIndex += subQuestionCount + 1;
    questionBlock.className = 'row align-items-center mb-2 sub-question-block';
    questionBlock.innerHTML = `
        <div class="col-10 col-md-10">
            <label class="question-label">Question ${questionNumber}</label>
        </div>
        <div class="col-2 col-md-2 text-end">
            <button type="button" class="btn btn-outline-danger btn-sm remove-btn" onclick="removeSubQuestion(this)" title="Remove question"><i class="fas fa-times"></i></button>
        </div>
        <div class="col-md-6">
            <input type="text" name="questions[]" class="form-control mt-2" placeholder="Question text" required data-section="${sectionNumber}">
        </div>
        <div class="col-md-6">
            <select name="types[]" class="form-select mt-2" onchange="updateQuestionFieldsForSub(this, ${globalQuestionIndex})" data-section="${sectionNumber}">
                <option>Select Option</option>
                <option value="text">Short Answer</option>
                <option value="textarea">Paragraph</option>
                <option value="radio">Radio Button</option>
                <option value="checkbox">Checkbox</option>
                <option value="dropdown">Dropdown</option>
                <option value="date">Date Picker</option>
                <option value="rating_star">Rating (Stars)</option>
                <option value="rating_heart">Rating (Hearts)</option>
                <option value="rating_thumb">Rating (Thumbs)</option>
            </select>
        </div>
        <div class="col-12">
            <div class="sub-question-fields mt-2" data-question-index="${globalQuestionIndex}"></div>
        </div>
    `;
    // Insert before the add button
    section.querySelector('.text-end.mt-2').before(questionBlock);
}

function removeSubQuestion(btn) {
    const block = btn.closest('.sub-question-block');
    block.remove();
}

function updateQuestionFieldsForSub(select, globalQuestionIndex) {
    const questionType = select.value;
    const fieldsDiv = select.closest('.sub-question-block').querySelector('.sub-question-fields');
    fieldsDiv.innerHTML = '';
    if (questionType === 'radio') {
        fieldsDiv.innerHTML = `
            <div class="mt-2">
                <label class="form-label">Radio Options</label>
                <div class="optionsContainerSub"></div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addOptionSub(this, ${globalQuestionIndex}, 'radio')">Add Option</button>
            </div>
        `;
        addOptionSub(fieldsDiv.querySelector('button'), globalQuestionIndex, 'radio');
        addOptionSub(fieldsDiv.querySelector('button'), globalQuestionIndex, 'radio');
    } else if (questionType === 'checkbox') {
        fieldsDiv.innerHTML = `
            <div class="mt-2">
                <label class="form-label">Checkbox Options</label>
                <div class="optionsContainerSub"></div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addOptionSub(this, ${globalQuestionIndex}, 'checkbox')">Add Option</button>
            </div>
        `;
        addOptionSub(fieldsDiv.querySelector('button'), globalQuestionIndex, 'checkbox');
        addOptionSub(fieldsDiv.querySelector('button'), globalQuestionIndex, 'checkbox');
    } else if (questionType === 'dropdown') {
        fieldsDiv.innerHTML = `
            <div class="mt-2">
                <label class="form-label">Dropdown Options</label>
                <div class="optionsContainerSub"></div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addOptionSub(this, ${globalQuestionIndex}, 'dropdown')">Add Option</button>
            </div>
        `;
        addOptionSub(fieldsDiv.querySelector('button'), globalQuestionIndex, 'dropdown');
        addOptionSub(fieldsDiv.querySelector('button'), globalQuestionIndex, 'dropdown');
    } else if (questionType === 'date') {
        fieldsDiv.innerHTML = `<div class="mt-2"><label class="form-label">Date Picker</label><input type="date" class="form-control" name="date_answer[]" placeholder="Select date"></div>`;
    } else if (questionType === 'text') {
        fieldsDiv.innerHTML = `<div class="mt-2"><label class="form-label">Short Answer</label><input type="text" class="form-control" name="text_answer[]" placeholder="Short answer text"></div>`;
    } else if (questionType === 'textarea') {
        fieldsDiv.innerHTML = `<div class="mt-2"><label class="form-label">Paragraph</label><textarea class="form-control" name="paragraph_answer[]" rows="3" placeholder="Paragraph text"></textarea></div>`;
    } else if (questionType === 'rating_star') {
        fieldsDiv.innerHTML = `<div class="mt-2"><label class="form-label">Rating (Stars)</label><div class="rating">
            <div class="star-wrapper" data-rating="1">
                <i class="fas fa-star-half-alt fa-2x half-star" onclick="setSubRating(this, 0.5)" data-value="0.5"></i>
                <i class="fas fa-star fa-2x full-star" onclick="setSubRating(this, 1)" data-value="1"></i>
            </div>
            <div class="star-wrapper" data-rating="2">
                <i class="fas fa-star-half-alt fa-2x half-star" onclick="setSubRating(this, 1.5)" data-value="1.5"></i>
                <i class="fas fa-star fa-2x full-star" onclick="setSubRating(this, 2)" data-value="2"></i>
            </div>
            <div class="star-wrapper" data-rating="3">
                <i class="fas fa-star-half-alt fa-2x half-star" onclick="setSubRating(this, 2.5)" data-value="2.5"></i>
                <i class="fas fa-star fa-2x full-star" onclick="setSubRating(this, 3)" data-value="3"></i>
            </div>
            <div class="star-wrapper" data-rating="4">
                <i class="fas fa-star-half-alt fa-2x half-star" onclick="setSubRating(this, 3.5)" data-value="3.5"></i>
                <i class="fas fa-star fa-2x full-star" onclick="setSubRating(this, 4)" data-value="4"></i>
            </div>
            <div class="star-wrapper" data-rating="5">
                <i class="fas fa-star-half-alt fa-2x half-star" onclick="setSubRating(this, 4.5)" data-value="4.5"></i>
                <i class="fas fa-star fa-2x full-star" onclick="setSubRating(this, 5)" data-value="5"></i>
            </div>
            <input type="hidden" name="rating[]" value="0">
        </div></div>`;
    } else if (questionType === 'rating_heart') {
        fieldsDiv.innerHTML = `<div class="mt-2"><label class="form-label">Rating (Hearts)</label><div class="rating">
            <div class="heart-wrapper" data-rating="1">
                <i class="fas fa-heart-broken fa-2x half-heart" onclick="setSubRating(this, 0.5)" data-value="0.5"></i>
                <i class="fas fa-heart fa-2x full-heart" onclick="setSubRating(this, 1)" data-value="1"></i>
            </div>
            <div class="heart-wrapper" data-rating="2">
                <i class="fas fa-heart-broken fa-2x half-heart" onclick="setSubRating(this, 1.5)" data-value="1.5"></i>
                <i class="fas fa-heart fa-2x full-heart" onclick="setSubRating(this, 2)" data-value="2"></i>
            </div>
            <div class="heart-wrapper" data-rating="3">
                <i class="fas fa-heart-broken fa-2x half-heart" onclick="setSubRating(this, 2.5)" data-value="2.5"></i>
                <i class="fas fa-heart fa-2x full-heart" onclick="setSubRating(this, 3)" data-value="3"></i>
            </div>
            <div class="heart-wrapper" data-rating="4">
                <i class="fas fa-heart-broken fa-2x half-heart" onclick="setSubRating(this, 3.5)" data-value="3.5"></i>
                <i class="fas fa-heart fa-2x full-heart" onclick="setSubRating(this, 4)" data-value="4"></i>
            </div>
            <div class="heart-wrapper" data-rating="5">
                <i class="fas fa-heart-broken fa-2x half-heart" onclick="setSubRating(this, 4.5)" data-value="4.5"></i>
                <i class="fas fa-heart fa-2x full-heart" onclick="setSubRating(this, 5)" data-value="5"></i>
            </div>
            <input type="hidden" name="rating[]" value="0">
        </div></div>`;
    } else if (questionType === 'rating_thumb') {
        fieldsDiv.innerHTML = `<div class="mt-2"><label class="form-label">Rating (Thumbs)</label><div class="rating">
            <div class="thumb-wrapper" data-rating="1">
                <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setSubRating(this, 0.5)" data-value="0.5"></i>
                <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setSubRating(this, 1)" data-value="1"></i>
            </div>
            <div class="thumb-wrapper" data-rating="2">
                <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setSubRating(this, 1.5)" data-value="1.5"></i>
                <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setSubRating(this, 2)" data-value="2"></i>
            </div>
            <div class="thumb-wrapper" data-rating="3">
                <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setSubRating(this, 2.5)" data-value="2.5"></i>
                <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setSubRating(this, 3)" data-value="3"></i>
            </div>
            <div class="thumb-wrapper" data-rating="4">
                <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setSubRating(this, 3.5)" data-value="3.5"></i>
                <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setSubRating(this, 4)" data-value="4"></i>
            </div>
            <div class="thumb-wrapper" data-rating="5">
                <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setSubRating(this, 4.5)" data-value="4.5"></i>
                <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setSubRating(this, 5)" data-value="5"></i>
            </div>
            <input type="hidden" name="rating[]" value="0">
        </div></div>`;
    }
}


// Rating logic for sub-questions
function setSubRating(icon, value) {
    const ratingDiv = icon.closest('.rating');

    // Reset all stars to default color
    const allStars = ratingDiv.querySelectorAll('i');
    allStars.forEach(star => {
        star.style.color = '#ccc';
    });

    // Check if this rating uses the new wrapper structure or old structure
    const starWrappers = ratingDiv.querySelectorAll('.star-wrapper');
    const heartWrappers = ratingDiv.querySelectorAll('.heart-wrapper');
    const thumbWrappers = ratingDiv.querySelectorAll('.thumb-wrapper');

    if (starWrappers.length > 0) {
        // New structure with half-stars
        for (let i = 0; i < starWrappers.length; i++) {
            const starPosition = i + 1;
            const halfStar = starWrappers[i].querySelector('.half-star');
            const fullStar = starWrappers[i].querySelector('.full-star');

            if (value >= starPosition) {
                // Full star highlight
                if (fullStar) fullStar.style.color = '#ffc107';
                if (halfStar) halfStar.style.color = '#ffc107';
            } else if (value >= starPosition - 0.5) {
                // Half star highlight
                if (halfStar) halfStar.style.color = '#ffc107';
                if (fullStar) fullStar.style.color = '#ccc';
            }
        }
    } else if (heartWrappers.length > 0) {
        // New structure with half-hearts
        for (let i = 0; i < heartWrappers.length; i++) {
            const heartPosition = i + 1;
            const halfHeart = heartWrappers[i].querySelector('.half-heart');
            const fullHeart = heartWrappers[i].querySelector('.full-heart');

            if (value >= heartPosition) {
                // Full heart highlight
                if (fullHeart) fullHeart.style.color = '#dc3545';
                if (halfHeart) halfHeart.style.color = '#dc3545';
            } else if (value >= heartPosition - 0.5) {
                // Half heart highlight
                if (halfHeart) halfHeart.style.color = '#dc3545';
                if (fullHeart) fullHeart.style.color = '#ccc';
            }
        }
    } else if (thumbWrappers.length > 0) {
        // New structure with half-thumbs
        for (let i = 0; i < thumbWrappers.length; i++) {
            const thumbPosition = i + 1;
            const halfThumb = thumbWrappers[i].querySelector('.half-thumb');
            const fullThumb = thumbWrappers[i].querySelector('.full-thumb');

            if (value >= thumbPosition) {
                // Full thumb highlight
                if (fullThumb) fullThumb.style.color = '#28a745';
                if (halfThumb) halfThumb.style.color = '#28a745';
            } else if (value >= thumbPosition - 0.5) {
                // Half thumb highlight
                if (halfThumb) halfThumb.style.color = '#28a745';
                if (fullThumb) fullThumb.style.color = '#ccc';
            }
        }
    } else {
        // Old structure - fallback for existing ratings
        const icons = ratingDiv.getElementsByTagName('i');
        for (let i = 0; i < icons.length; i++) {
            if (i < value) {
                if (icons[i].classList.contains('fa-star')) {
                    icons[i].style.color = '#ffc107';
                } else if (icons[i].classList.contains('fa-heart')) {
                    icons[i].style.color = '#dc3545';
                } else if (icons[i].classList.contains('fa-thumbs-up')) {
                    icons[i].style.color = '#28a745';
                }
            }
        }
    }

    const input = ratingDiv.querySelector('input[type="hidden"][name="rating[]"]');
    if (input) input.value = value;
}

// On form submit, collect section mapping for each question
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    // Remove any previous hidden inputs
    document.querySelectorAll('input[name="question_section_ids[]"]').forEach(el => el.remove());
    // For each question input, add a hidden input with its section
    // Only consider visible and enabled question inputs
    const questionInputs = document.querySelectorAll('input[name="questions[]"]');
    questionInputs.forEach((input, idx) => {
        // Find the closest .question-section parent to get the section number
        let sectionElem = input.closest('.question-section');
        let sectionId = '1';
        if (sectionElem) {
            // Find the badge or section-title-input to get the section number (badge is more robust)
            const badge = sectionElem.querySelector('.section-badge');
            if (badge) {
                sectionId = badge.textContent.trim();
            }
        } else {
            // Fallback to data-section attribute
            sectionId = input.getAttribute('data-section') || '1';
        }
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'question_section_ids[]';
        hidden.value = sectionId;
        this.appendChild(hidden);
    });
});

function addOptionSub(btn, globalQuestionIndex, type) {
    const container = btn.closest('div').querySelector('.optionsContainerSub');
    const newOption = document.createElement('div');
    newOption.classList.add('input-group', 'mb-2');
    let iconHtml = '';
    if (type === 'radio') {
        iconHtml = `<span class="input-group-text"><input type="radio" disabled></span>`;
    } else if (type === 'checkbox') {
        iconHtml = `<span class="input-group-text"><input type="checkbox" disabled></span>`;
    } else if (type === 'dropdown') {
        iconHtml = `<span class="input-group-text"><i class="fas fa-caret-down"></i></span>`;
    }
    newOption.innerHTML = `
        ${iconHtml}
        <input type="text" class="form-control" name="options[${globalQuestionIndex}][]" placeholder="Option text" value="">
        <button class="btn btn-outline-secondary" type="button" onclick="removeOptionSub(this)">Remove</button>
    `;
    container.appendChild(newOption);
}

function removeOptionSub(btn) {
    btn.closest('.input-group').remove();
            }

function removeQuestion(element) {
    const section = element.closest('.question-section');
    section.remove();
    // Re-number all remaining sections and questions
    const sections = document.querySelectorAll('#questions .question-section');
    let sectionNum = 1;
    sections.forEach(sec => {
        // Update badge
        const badge = sec.querySelector('.section-badge');
        if (badge) badge.textContent = sectionNum;
        // Update section title input value
        const titleInput = sec.querySelector('.section-title-input');
        if (titleInput) titleInput.value = 'Section ' + sectionNum;
        // Re-number all questions in this section
        let qLabels = sec.querySelectorAll('.question-label');
        let qNum = 1;
        qLabels.forEach(qLabel => {
            qLabel.textContent = 'Question ' + qNum;
            qNum++;
        });
        // Update select and input IDs/names if needed (only for main question)
        const select = sec.querySelector('select[name^="types["]');
        if (select) {
            select.id = 'questionType' + sectionNum;
            select.setAttribute('onchange', `updateQuestionFields(${sectionNum})`);
        }
        const qFields = sec.querySelector('[id^="questionFields"]');
        if (qFields) qFields.id = 'questionFields' + sectionNum;
        sectionNum++;
    });
    // Update questionCounter to match the number of sections
    questionCounter = sections.length;
}

            function updateQuestionFields(questionNumber) {
                // Calculate the global index for this question (main question in its section)
                let globalIndex = 0;
                const allSections = document.querySelectorAll('#questions .question-section');
                for (let i = 0; i < allSections.length; i++) {
                    const badge = allSections[i].querySelector('.section-badge');
                    let secNum = badge ? parseInt(badge.textContent.trim()) : (i + 1);
                    if (secNum === questionNumber) break;
                    globalIndex += allSections[i].querySelectorAll('input[name="questions[]"]').length;
                }
                // For the main question in this section, globalIndex is as above
                const questionType = document.getElementById(`questionType${questionNumber}`).value;
                const questionFieldsDiv = document.getElementById(`questionFields${questionNumber}`);
                questionFieldsDiv.innerHTML = ''; // Clear existing fields

                if (questionType === 'radio' || questionType === 'checkbox' || questionType === 'dropdown') {
                    let iconHtml = '';
                    if (questionType === 'radio') {
                        iconHtml = `<span class="input-group-text"><input type="radio" disabled></span>`;
                    } else if (questionType === 'checkbox') {
                        iconHtml = `<span class="input-group-text"><input type="checkbox" disabled></span>`;
                    } else if (questionType === 'dropdown') {
                        iconHtml = `<span class="input-group-text"><i class="fas fa-caret-down"></i></span>`;
                    }

                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Options</label>
                            <div id="optionsContainer${questionNumber}">
                                <div class="input-group mb-2">
                                    ${iconHtml}
                                    <input type="text" class="form-control" name="options[${globalIndex}][]" placeholder="Option 1" value="Option 1">
                                    <button class="btn btn-outline-secondary" type="button" onclick="removeOption(this)">Remove</button>
                                </div>
                                <div class="input-group mb-2">
                                    ${iconHtml}
                                    <input type="text" class="form-control" name="options[${globalIndex}][]" placeholder="Option 2" value="Option 2">
                                    <button class="btn btn-outline-secondary" type="button" onclick="removeOption(this)">Remove</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary" onclick="addOptionGlobal(${questionNumber}, ${globalIndex})">Add Option</button>
                        </div>
                    `;
                } else if (questionType === 'date') {
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Date Picker</label>
                            <input type="date" class="form-control" name="date_answer[${questionNumber}]" placeholder="Select date">
                        </div>
                    `;
                } else if (questionType === 'text') {
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Short Answer</label>
                            <input type="text" class="form-control" name="text_answer[${questionNumber}]" placeholder="Short answer text">
                        </div>
                    `;
                } else if (questionType === 'textarea') {
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Paragraph</label>
                            <textarea class="form-control" name="paragraph_answer[${questionNumber}]" rows="3" placeholder="Paragraph text"></textarea>
                        </div>
                    `;
                } else if (questionType === 'rating_star') {
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Rating (Stars)</label>
                            <div class="rating" id="rating-container-${questionNumber}">
                                <div class="star-wrapper" data-rating="1">
                                    <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${questionNumber}, 0.5)" data-value="0.5"></i>
                                    <i class="fas fa-star fa-2x full-star" onclick="setRating(${questionNumber}, 1)" data-value="1"></i>
                                </div>
                                <div class="star-wrapper" data-rating="2">
                                    <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${questionNumber}, 1.5)" data-value="1.5"></i>
                                    <i class="fas fa-star fa-2x full-star" onclick="setRating(${questionNumber}, 2)" data-value="2"></i>
                                </div>
                                <div class="star-wrapper" data-rating="3">
                                    <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${questionNumber}, 2.5)" data-value="2.5"></i>
                                    <i class="fas fa-star fa-2x full-star" onclick="setRating(${questionNumber}, 3)" data-value="3"></i>
                                </div>
                                <div class="star-wrapper" data-rating="4">
                                    <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${questionNumber}, 3.5)" data-value="3.5"></i>
                                    <i class="fas fa-star fa-2x full-star" onclick="setRating(${questionNumber}, 4)" data-value="4"></i>
                                </div>
                                <div class="star-wrapper" data-rating="5">
                                    <i class="fas fa-star-half-alt fa-2x half-star" onclick="setRating(${questionNumber}, 4.5)" data-value="4.5"></i>
                                    <i class="fas fa-star fa-2x full-star" onclick="setRating(${questionNumber}, 5)" data-value="5"></i>
                                </div>
                                <input type="hidden" name="rating[${questionNumber}]" id="rating${questionNumber}" value="0">
                            </div>
                        </div>
                    `;
                } else if (questionType === 'rating_heart') {
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Rating (Hearts)</label>
                            <div class="rating" id="rating-container-${questionNumber}">
                                <div class="heart-wrapper" data-rating="1">
                                    <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${questionNumber}, 0.5)" data-value="0.5"></i>
                                    <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${questionNumber}, 1)" data-value="1"></i>
                                </div>
                                <div class="heart-wrapper" data-rating="2">
                                    <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${questionNumber}, 1.5)" data-value="1.5"></i>
                                    <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${questionNumber}, 2)" data-value="2"></i>
                                </div>
                                <div class="heart-wrapper" data-rating="3">
                                    <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${questionNumber}, 2.5)" data-value="2.5"></i>
                                    <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${questionNumber}, 3)" data-value="3"></i>
                                </div>
                                <div class="heart-wrapper" data-rating="4">
                                    <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${questionNumber}, 3.5)" data-value="3.5"></i>
                                    <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${questionNumber}, 4)" data-value="4"></i>
                                </div>
                                <div class="heart-wrapper" data-rating="5">
                                    <i class="fas fa-heart-broken fa-2x half-heart" onclick="setRating(${questionNumber}, 4.5)" data-value="4.5"></i>
                                    <i class="fas fa-heart fa-2x full-heart" onclick="setRating(${questionNumber}, 5)" data-value="5"></i>
                                </div>
                                <input type="hidden" name="rating[${questionNumber}]" id="rating${questionNumber}" value="0">
                            </div>
                        </div>
                    `;
                } else if (questionType === 'rating_thumb') {
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Rating (Thumbs)</label>
                            <div class="rating" id="rating-container-${questionNumber}">
                                <div class="thumb-wrapper" data-rating="1">
                                    <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setRating(${questionNumber}, 0.5)" data-value="0.5"></i>
                                    <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setRating(${questionNumber}, 1)" data-value="1"></i>
                                </div>
                                <div class="thumb-wrapper" data-rating="2">
                                    <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setRating(${questionNumber}, 1.5)" data-value="1.5"></i>
                                    <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setRating(${questionNumber}, 2)" data-value="2"></i>
                                </div>
                                <div class="thumb-wrapper" data-rating="3">
                                    <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setRating(${questionNumber}, 2.5)" data-value="2.5"></i>
                                    <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setRating(${questionNumber}, 3)" data-value="3"></i>
                                </div>
                                <div class="thumb-wrapper" data-rating="4">
                                    <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setRating(${questionNumber}, 3.5)" data-value="3.5"></i>
                                    <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setRating(${questionNumber}, 4)" data-value="4"></i>
                                </div>
                                <div class="thumb-wrapper" data-rating="5">
                                    <i class="fas fa-thumbs-down fa-2x half-thumb" onclick="setRating(${questionNumber}, 4.5)" data-value="4.5"></i>
                                    <i class="fas fa-thumbs-up fa-2x full-thumb" onclick="setRating(${questionNumber}, 5)" data-value="5"></i>
                                </div>
                                <input type="hidden" name="rating[${questionNumber}]" id="rating${questionNumber}" value="0">
                            </div>
                        </div>
                    `;
                }
            }

            function setRating(questionNumber, value) {
                // Set the hidden input value for the selected rating
                const ratingInput = document.getElementById(`rating${questionNumber}`);
                if (ratingInput) ratingInput.value = value;

                // Find the rating container
                let ratingContainer = document.getElementById(`rating-container-${questionNumber}`);
                if (!ratingContainer) {
                    // Try to find in main question fields
                    const mainFields = document.getElementById(`questionFields${questionNumber}`);
                    if (mainFields && mainFields.querySelector('.rating')) {
                        ratingContainer = mainFields.querySelector('.rating');
                    }
                }

                if (!ratingContainer) return;

                // Reset all stars to default color
                const allStars = ratingContainer.querySelectorAll('i');
                allStars.forEach(star => {
                    star.style.color = '#ccc';
                });

                // Highlight based on the rating value
                const starWrappers = ratingContainer.querySelectorAll('.star-wrapper');
                const heartWrappers = ratingContainer.querySelectorAll('.heart-wrapper');
                const thumbWrappers = ratingContainer.querySelectorAll('.thumb-wrapper');

                // Handle star ratings
                if (starWrappers.length > 0) {
                    for (let i = 0; i < starWrappers.length; i++) {
                        const starPosition = i + 1;
                        const halfStar = starWrappers[i].querySelector('.half-star');
                        const fullStar = starWrappers[i].querySelector('.full-star');

                        if (value >= starPosition) {
                            // Full star highlight
                            if (fullStar) fullStar.style.color = '#ffc107';
                            if (halfStar) halfStar.style.color = '#ffc107';
                        } else if (value >= starPosition - 0.5) {
                            // Half star highlight
                            if (halfStar) halfStar.style.color = '#ffc107';
                            if (fullStar) fullStar.style.color = '#ccc';
                        }
                    }
                }

                // Handle heart ratings
                if (heartWrappers.length > 0) {
                    for (let i = 0; i < heartWrappers.length; i++) {
                        const heartPosition = i + 1;
                        const halfHeart = heartWrappers[i].querySelector('.half-heart');
                        const fullHeart = heartWrappers[i].querySelector('.full-heart');

                        if (value >= heartPosition) {
                            // Full heart highlight
                            if (fullHeart) fullHeart.style.color = '#dc3545';
                            if (halfHeart) halfHeart.style.color = '#dc3545';
                        } else if (value >= heartPosition - 0.5) {
                            // Half heart highlight
                            if (halfHeart) halfHeart.style.color = '#dc3545';
                            if (fullHeart) fullHeart.style.color = '#ccc';
                        }
                    }
                }

                // Handle thumb ratings
                if (thumbWrappers.length > 0) {
                    for (let i = 0; i < thumbWrappers.length; i++) {
                        const thumbPosition = i + 1;
                        const halfThumb = thumbWrappers[i].querySelector('.half-thumb');
                        const fullThumb = thumbWrappers[i].querySelector('.full-thumb');

                        if (value >= thumbPosition) {
                            // Full thumb highlight
                            if (fullThumb) fullThumb.style.color = '#28a745';
                            if (halfThumb) halfThumb.style.color = '#28a745';
                        } else if (value >= thumbPosition - 0.5) {
                            // Half thumb highlight
                            if (halfThumb) halfThumb.style.color = '#28a745';
                            if (fullThumb) fullThumb.style.color = '#ccc';
                        }
                    }
                }
            }


            // Add option for main question using global index
            function addOptionGlobal(questionNumber, globalIndex) {
                const optionsContainer = document.getElementById(`optionsContainer${questionNumber}`);
                const newOption = document.createElement('div');
                newOption.classList.add('input-group', 'mb-2');
                newOption.innerHTML = `
                    <input type="text" class="form-control" name="options[${globalIndex}][]" placeholder="Option text" value="">
                    <button class="btn btn-outline-secondary" type="button" onclick="removeOption(this)">Remove</button>
                `;
                optionsContainer.appendChild(newOption);
            }

            function removeOption(element) {
                element.closest('.input-group').remove();
            }

            // Auto-hide alert after 5 seconds
            setTimeout(() => {
                const alert = document.getElementById('form-limit-alert');
                if (alert) alert.remove();
            }, 5000);
        </script>

        <script>
                function toggleThankYouMessage() {
                    const checkbox = document.getElementById('enableThankYou');
                    const container = document.getElementById('thankYouMessageContainer');
                    container.style.display = checkbox.checked ? 'block' : 'none';
                }
                </script>

        <!-- Add Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            /* Enhanced Form Styling */
            .card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                border: 1px solid #e3f2fd;
            }

            .form-control, .form-select {
                border: 1px solid #e0e6ed;
                border-radius: 8px;
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
                transition: all 0.3s ease;
            }

            .form-control:focus, .form-select:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
            }

            .form-check-card {
                padding: 0.75rem;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                transition: all 0.3s ease;
                background: #f8f9fa;
            }

            .form-check-card:hover {
                background: #e3f2fd;
                border-color: #007bff;
            }

            .form-check-input:checked + .form-check-label {
                color: #007bff;
            }

            .rating i {
                cursor: pointer;
                color: #dee2e6;
                margin: 0 5px;
                transition: all 0.2s ease;
                font-size: 1.5rem;
            }
            .rating i:hover {
                color: #ffc107;
                transform: scale(1.1);
            }

            /* Enhanced Star Rating with Half-Stars */
            .star-wrapper, .heart-wrapper, .thumb-wrapper {
                display: inline-block;
                position: relative;
                margin: 0 2px;
            }

            .star-wrapper .half-star,
            .heart-wrapper .half-heart,
            .thumb-wrapper .half-thumb {
                position: absolute;
                top: 0;
                left: 0;
                width: 50%;
                overflow: hidden;
                z-index: 1;
                color: #ccc;
                transition: color 0.2s ease;
                margin: 0;
            }

            .star-wrapper .full-star,
            .heart-wrapper .full-heart,
            .thumb-wrapper .full-thumb {
                color: #ccc;
                transition: color 0.2s ease;
                z-index: 0;
                margin: 0;
            }

            .star-wrapper:hover .half-star,
            .star-wrapper:hover .full-star,
            .heart-wrapper:hover .half-heart,
            .heart-wrapper:hover .full-heart,
            .thumb-wrapper:hover .half-thumb,
            .thumb-wrapper:hover .full-thumb {
                transform: scale(1.1);
            }

            /* Hover effects for better UX */
            .star-wrapper:hover .half-star {
                color: #ffc107 !important;
            }

            .heart-wrapper:hover .half-heart {
                color: #dc3545 !important;
            }

            .thumb-wrapper:hover .half-thumb {
                color: #28a745 !important;
            }

            /* Enhanced Question Section Styling */
            .question-section {
                cursor: move;
                border: 2px solid #e9ecef;
                border-radius: 16px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.05);
                margin: 1.5rem 0;
                padding: 1.5rem;
                transition: all 0.3s ease;
                background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
                position: relative;
                overflow: hidden;
            }

            .question-section:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
                border-radius: 16px 16px 0 0;
            }

            .question-section:hover {
                border-color: #007bff;
                box-shadow: 0 6px 25px rgba(0,123,255,0.15);
                transform: translateY(-2px);
            }
            .custom-section-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 12px;
                padding: 1rem 1.25rem;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
                border: none;
                cursor: move;
                margin: -0.5rem -0.5rem 1rem -0.5rem;
                position: relative;
            }

            .custom-section-header:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
                border-radius: 12px;
            }

            .section-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: rgba(255,255,255,0.2);
                color: #fff;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                font-weight: bold;
                font-size: 1.1rem;
                margin-right: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                backdrop-filter: blur(10px);
                border: 2px solid rgba(255,255,255,0.3);
            }

            .section-title-input {
                background: rgba(255,255,255,0.15) !important;
                border: 2px solid rgba(255,255,255,0.3) !important;
                color: #fff !important;
                font-weight: 600 !important;
                backdrop-filter: blur(10px);
            }

            .section-title-input::placeholder {
                color: rgba(255,255,255,0.8);
            }

            .section-title-input:focus {
                background: rgba(255,255,255,0.25) !important;
                border-color: rgba(255,255,255,0.6) !important;
                box-shadow: 0 0 0 3px rgba(255,255,255,0.1) !important;
            }

            .drag-handle {
                color: rgba(255,255,255,0.8);
                font-size: 1.4rem;
                cursor: grab;
                transition: all 0.3s ease;
                padding: 8px;
                border-radius: 8px;
                background: rgba(255,255,255,0.1);
            }
            .drag-handle:hover {
                color: #fff;
                background: rgba(255,255,255,0.2);
                transform: scale(1.1);
            }
            .question-section .question-label {
                font-weight: 600;
                font-size: 1.1rem;
                color: #2c3e50;
                margin: 0.75rem 0.75rem 1rem 0.75rem;
                display: flex;
                align-items: center;
                padding: 0.5rem 0.75rem;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 8px;
                border-left: 4px solid #007bff;
            }

            .question-section .question-label:before {
                content: '\f059';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                margin-right: 0.5rem;
                color: #007bff;
            }

            .question-section .form-control,
            .question-section .form-select {
                margin: 0.5rem 0.75rem 0.75rem 0.75rem;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .question-section .form-control:focus,
            .question-section .form-select:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
            }

            .remove-btn {
                border-radius: 50%;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }

            .remove-btn:hover {
                background: #dc3545;
                border-color: #dc3545;
                transform: scale(1.1);
            }

            .question-section.drag-over {
                border: 2px dashed #007bff !important;
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                box-shadow: 0 8px 32px rgba(0,123,255,0.15);
                transform: scale(1.02);
            }

            .question-section .input-group {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin: 0.5rem 0.75rem 0.5rem 0.75rem;
            }

            .question-section .input-group .form-control {
                flex: 1 1 auto;
                margin: 0;
            }

            .question-section .input-group .btn {
                flex: 0 0 auto;
                margin: 0;
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .question-section .input-group .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }

            /* Enhanced Button Styling */
            .btn {
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
                border: none;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }

            .btn-primary {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            }

            .btn-outline-primary {
                border: 2px solid #007bff;
                color: #007bff;
                background: transparent;
            }

            .btn-outline-primary:hover {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                border-color: #007bff;
            }

            /* Profile Preview Styling */
            .profile-preview-container {
                padding: 1rem;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 12px;
                border: 2px solid #e9ecef;
            }

            /* Switch Styling */
            .form-check-input:checked {
                background-color: #28a745;
                border-color: #28a745;
            }

            .form-switch .form-check-input {
                width: 3rem;
                height: 1.5rem;
            }

            /* Alert Styling */
            .alert {
                border-radius: 12px;
                border: none;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }

            /* Custom Entry Styling */
            #customEntryHelper {
                border-left: 4px solid #17a2b8;
                background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
                animation: slideDown 0.5s ease-out;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Manual Logo Preview */
            #manualLogoPreview {
                animation: fadeIn 0.3s ease-out;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            /* Enhanced form states for custom entry */
            .form-control:required {
                border-left: 4px solid #dc3545;
            }

            .form-control:required:valid {
                border-left: 4px solid #28a745;
            }

            /* Custom dropdown option styling */
            option[value="custom"] {
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                font-weight: 600;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .question-section {
                    margin: 1rem 0;
                    padding: 1rem;
                }

                .section-title-input {
                    width: 150px !important;
                    font-size: 0.9rem !important;
                }

                .section-badge {
                    width: 32px;
                    height: 32px;
                    font-size: 0.9rem;
                }

                .custom-section-header {
                    padding: 0.75rem 1rem;
                }

                .question-section .question-label {
                    font-size: 1rem;
                    margin: 0.5rem 0.5rem 0.75rem 0.5rem;
                }

                .question-section .form-control,
                .question-section .form-select {
                    margin: 0.5rem 0.5rem 0.75rem 0.5rem;
                }

                .question-section .input-group {
                    margin: 0.5rem 0.5rem 0.5rem 0.5rem;
                }

                .d-flex.gap-2 {
                    flex-direction: column;
                    gap: 0.5rem !important;
                }

                .btn {
                    width: 100%;
                }
            }

            /* Animation for drag and drop */
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .question-section {
                animation: slideIn 0.5s ease-out;
            }

            /* Smooth transitions for interactive elements */
            * {
                transition: all 0.3s ease;
            }

            /* Focus states */
            .form-control:focus,
            .form-select:focus,
            .form-check-input:focus {
                outline: none;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }

            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 4px;
            }

            ::-webkit-scrollbar-thumb {
                background: linear-gradient(135deg, #007bff, #0056b3);
                border-radius: 4px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(135deg, #0056b3, #004085);
            }

            /* Template Modal Styling */
            .template-card {
                cursor: pointer;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }

            .template-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                border-color: #007bff;
            }

            .template-card.selected-template {
                border-color: #28a745;
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(40,167,69,0.2);
            }

            .template-card.selected-template::after {
                content: '✓';
                position: absolute;
                top: 10px;
                right: 15px;
                background: #28a745;
                color: white;
                border-radius: 50%;
                width: 25px;
                height: 25px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 0.9rem;
                box-shadow: 0 2px 8px rgba(40,167,69,0.3);
            }

            .template-features .badge {
                font-size: 0.75rem;
                font-weight: 500;
            }

            .modal-xl {
                max-width: 1200px;
            }

            .bg-gradient-success {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            }

            .template-card .card-header i {
                opacity: 0.9;
            }

            .template-card:hover .card-header i {
                opacity: 1;
                transform: scale(1.1);
            }

            /* Template button styling */
            .btn-outline-success {
                border-width: 2px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-outline-success:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(40, 167, 69, 0.2);
            }

            /* Modal animations */
            .modal.fade .modal-dialog {
                transition: transform 0.4s ease-out;
            }

            .modal.show .modal-dialog {
                transform: scale(1);
            }
        </style>
    </body>

    </html>
