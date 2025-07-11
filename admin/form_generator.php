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
                            <h3 class="text-center title">Create Feedback Form</h3>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger" id="form-limit-alert"><?= $_SESSION['error'];
                                                                                        unset($_SESSION['error']); ?></div>
                            <?php endif; ?>

                            <form method="post" action="crud/save_form.php" id="feedbackForm">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Created for</label>
                                        <select class="form-select" name="created_for" id="businessNameSelect"  onchange="showProfileImage()">
                                            <option value="">Select business name</option>
                                            <?php
                                            // Fetch users for dropdown (with profile image)
                                            $userStmt = $conn->prepare("SELECT id, business_name, profile_image FROM users WHERE  business_name IS NOT NULL AND business_name != ''");
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
                                            if (!$hasUser) {
                                                echo '<option value="">No users available</option>';
                                            }
                                            ?>
                                        </select>            
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div id="profileImagePreview" style="margin-top:10px; display:none; border:1px solid #ccc; border-radius:8px; padding:5px;">
                                            <img id="profileImgTag" src="" alt="Profile Image" style="max-width:100px; max-height:100px; border-radius:8px; border:1px solid #ccc;" />
                                        </div>
                                        <script>
                                        function showProfileImage() {
                                            var select = document.getElementById('businessNameSelect');
                                            var selected = select.options[select.selectedIndex];
                                            var img = selected.getAttribute('data-img');
                                            var previewDiv = document.getElementById('profileImagePreview');
                                            var imgTag = document.getElementById('profileImgTag');
                                            var companyNameInput = document.getElementById('companyNameInput');
                                            var companyLogoInput = document.getElementById('companyLogoInput');

                                            if (img && img.trim() !== '' && img !== 'null') {
                                                imgTag.src = img;
                                                previewDiv.style.display = '';
                                                companyNameInput.disabled = true;
                                                companyLogoInput.disabled = true;
                                            } else {
                                                imgTag.src = '';
                                                previewDiv.style.display = 'none';
                                                companyNameInput.disabled = false;
                                                companyLogoInput.disabled = false;
                                            }
                                        }
                                        // Show image if already selected on page load (edit mode)
                                        document.addEventListener('DOMContentLoaded', showProfileImage);
                                        </script>
                                        </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" class="form-control" name="company_name" id="companyNameInput">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Company Logo</label>
                                        <input type="file" class="form-control" name="company_logo" id="companyLogoInput">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Form Type</label>
                                        <select name="types1[]" class="form-select">
                                            <option>Select Option</option>
                                            <option value="Suggection">Suggection</option>
                                            <option value="Complaints">Complaints</option>
                                            <option value="Feedback">Feedback</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Form Title</label>
                                        <input type="text" class="form-control" name="title" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description"></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="firstname" value="1">
                                        <label class="form-check-label">Enable First Name</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="lastname" value="1">
                                        <label class="form-check-label">Enable Last Name</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="email" value="1">
                                        <label class="form-check-label">Enable Email</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="number" value="1">
                                        <label class="form-check-label">Enable Phone Number</label>
                                    </div>
                                </div>

                                <div id="questions">
            <!-- Default Question Section with Add Question button for sub-questions -->
            <section class="question-section mb-3" draggable="true" ondragstart="dragSection(event)" ondragover="allowSectionDrop(event)" ondrop="dropSection(event)">
                <div class="section-header custom-section-header d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <span class="section-badge me-2">1</span>
                        <input type="text" class="form-control fw-bold section-title-input" name="section_titles[]" value="Add Section Name" style="width: 180px; font-weight: bold; font-size: 1.1rem; background: transparent; border: 2px solid #007bff; outline: none; padding: 0; margin: 0; color: #333; box-shadow: 0 0 0 2px #b3d7ff; transition: box-shadow 0.2s, border-color 0.2s;" onfocus="this.style.background='#fff'; this.style.border='2px solid #0056b3'; this.style.boxShadow='0 0 0 3px #80bdff';" onblur="this.style.background='transparent'; this.style.border='2px solid #007bff'; this.style.boxShadow='0 0 0 2px #b3d7ff';" />
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
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSubQuestion(this, 1)"><i class="fas fa-plus"></i> Add Question</button>
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

                                <button type="button" class="btn btn-secondary" onclick="addSection()">+ Add Section</button>
                                <button type="submit" class="btn btn-primary">Create Form</button>
                            </form>
                        </div>
                        <!-- / Content -->

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
            let questionCounter = 1; // Initialize to 1 to match the default question


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
                            <input type="text" class="form-control fw-bold section-title-input" name="section_titles[]" value="Add Section Name" style="width: 180px; font-weight: bold; font-size: 1.1rem; background: transparent; border: 2px solid #007bff; outline: none; padding: 0; margin: 0; color: #333; box-shadow: 0 0 0 2px #b3d7ff; transition: box-shadow 0.2s, border-color 0.2s;" onfocus="this.style.background='#fff'; this.style.border='2px solid #0056b3'; this.style.boxShadow='0 0 0 3px #80bdff';" onblur="this.style.background='transparent'; this.style.border='2px solid #007bff'; this.style.boxShadow='0 0 0 2px #b3d7ff';" placeholder="Add Section Name" />
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
            <i class="fas fa-star fa-2x" onclick="setSubRating(this, 1)" data-value="1"></i>
            <i class="fas fa-star fa-2x" onclick="setSubRating(this, 2)" data-value="2"></i>
            <i class="fas fa-star fa-2x" onclick="setSubRating(this, 3)" data-value="3"></i>
            <i class="fas fa-star fa-2x" onclick="setSubRating(this, 4)" data-value="4"></i>
            <i class="fas fa-star fa-2x" onclick="setSubRating(this, 5)" data-value="5"></i>
            <input type="hidden" name="rating[]" value="0">
        </div></div>`;
    } else if (questionType === 'rating_heart') {
        fieldsDiv.innerHTML = `<div class="mt-2"><label class="form-label">Rating (Hearts)</label><div class="rating">
            <i class="fas fa-heart fa-2x" onclick="setSubRating(this, 1)" data-value="1"></i>
            <i class="fas fa-heart fa-2x" onclick="setSubRating(this, 2)" data-value="2"></i>
            <i class="fas fa-heart fa-2x" onclick="setSubRating(this, 3)" data-value="3"></i>
            <i class="fas fa-heart fa-2x" onclick="setSubRating(this, 4)" data-value="4"></i>
            <i class="fas fa-heart fa-2x" onclick="setSubRating(this, 5)" data-value="5"></i>
            <input type="hidden" name="rating[]" value="0">
        </div></div>`;
    } else if (questionType === 'rating_thumb') {
        fieldsDiv.innerHTML = `<div class="mt-2"><label class="form-label">Rating (Thumbs)</label><div class="rating">
            <i class="fas fa-thumbs-up fa-2x" onclick="setSubRating(this, 1)" data-value="1"></i>
            <i class="fas fa-thumbs-up fa-2x" onclick="setSubRating(this, 2)" data-value="2"></i>
            <i class="fas fa-thumbs-up fa-2x" onclick="setSubRating(this, 3)" data-value="3"></i>
            <i class="fas fa-thumbs-up fa-2x" onclick="setSubRating(this, 4)" data-value="4"></i>
            <i class="fas fa-thumbs-up fa-2x" onclick="setSubRating(this, 5)" data-value="5"></i>
            <input type="hidden" name="rating[]" value="0">
        </div></div>`;
    }
}


// Rating logic for sub-questions
function setSubRating(icon, value) {
    const ratingDiv = icon.closest('.rating');
    const icons = ratingDiv.getElementsByTagName('i');
    for (let i = 0; i < icons.length; i++) {
        icons[i].style.color = '#ccc';
    }
    for (let i = 0; i < icons.length; i++) {
        if (i < value) {
            icons[i].style.color = '#ffc107';
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
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Options</label>
                            <div id="optionsContainer${questionNumber}">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="options[${globalIndex}][]" placeholder="Option text">
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
                            <div class="rating">
                                <i class="fas fa-star fa-2x" onclick="setRating(${questionNumber}, 1)" data-value="1"></i>
                                <i class="fas fa-star fa-2x" onclick="setRating(${questionNumber}, 2)" data-value="2"></i>
                                <i class="fas fa-star fa-2x" onclick="setRating(${questionNumber}, 3)" data-value="3"></i>
                                <i class="fas fa-star fa-2x" onclick="setRating(${questionNumber}, 4)" data-value="4"></i>
                                <i class="fas fa-star fa-2x" onclick="setRating(${questionNumber}, 5)" data-value="5"></i>
                                <input type="hidden" name="rating[${questionNumber}]" id="rating${questionNumber}" value="0">
                            </div>
                        </div>
                    `;
                } else if (questionType === 'rating_heart') {
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Rating (Hearts)</label>
                            <div class="rating">
                                <i class="fas fa-heart fa-2x" onclick="setRating(${questionNumber}, 1)" data-value="1"></i>
                                <i class="fas fa-heart fa-2x" onclick="setRating(${questionNumber}, 2)" data-value="2"></i>
                                <i class="fas fa-heart fa-2x" onclick="setRating(${questionNumber}, 3)" data-value="3"></i>
                                <i class="fas fa-heart fa-2x" onclick="setRating(${questionNumber}, 4)" data-value="4"></i>
                                <i class="fas fa-heart fa-2x" onclick="setRating(${questionNumber}, 5)" data-value="5"></i>
                                <input type="hidden" name="rating[${questionNumber}]" id="rating${questionNumber}" value="0">
                            </div>
                        </div>
                    `;
                } else if (questionType === 'rating_thumb') {
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Rating (Thumbs)</label>
                            <div class="rating">
                                <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${questionNumber}, 1)" data-value="1"></i>
                                <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${questionNumber}, 2)" data-value="2"></i>
                                <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${questionNumber}, 3)" data-value="3"></i>
                                <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${questionNumber}, 4)" data-value="4"></i>
                                <i class="fas fa-thumbs-up fa-2x" onclick="setRating(${questionNumber}, 5)" data-value="5"></i>
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
    // Find the correct rating container (main or sub-question)
    let ratingDiv = null;
    // Try main question fields first
    const mainFields = document.getElementById(`questionFields${questionNumber}`);
    if (mainFields && mainFields.querySelector('.rating')) {
        ratingDiv = mainFields.querySelector('.rating');
    } else {
        // Try sub-question fields
        const allSubRatings = document.querySelectorAll('.sub-question-fields .rating');
        for (const div of allSubRatings) {
            if (div.querySelector('input[type="hidden"][name="rating[]"]')) {
                ratingDiv = div;
                break;
            }
        }
    }
    if (!ratingDiv) return;
    const icons = ratingDiv.getElementsByTagName('i');
    // Reset all icons
    for (let i = 0; i < icons.length; i++) {
        icons[i].style.color = '#ccc';
    }
    // Highlight the correct icons from left to right
    for (let i = 0; i < icons.length; i++) {
        if (i < value) {
            icons[i].style.color = '#ffc107';
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

        <!-- Add Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <style>
            .rating i {
                cursor: pointer;
                color: #ccc;
                margin: 0 5px;
            }
            .rating i:hover {
                color: #ffc107;
            }
            .question-section {
                cursor: move;
                border: 2px solid #b3b3b3;
                border-radius: 10px;
                box-shadow: 0 2px 12px rgba(0,0,0,0.06);
                margin: 1.5rem 0.75rem 1.5rem 0.75rem; /* top, right, bottom, left */
                padding: 1.25rem 1.25rem 1rem 1.25rem;
                transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
                background: #fff;
            }
            .custom-section-header {
                background: linear-gradient(90deg, #f5f7fa 0%, #c3cfe2 100%);
                border-radius: 10px 10px 0 0;
                padding: 0.75rem 1rem;
                box-shadow: 0 2px 6px rgba(0,0,0,0.03);
                border-bottom: 1px solid #e0e0e0;
                cursor: move;
            }
            .section-badge {
                display: inline-block;
                background: #007bff;
                color: #fff;
                border-radius: 50%;
                width: 32px;
                height: 32px;
                text-align: center;
                line-height: 32px;
                font-weight: bold;
                font-size: 1.1rem;
                margin-right: 8px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            }
            .section-title {
                font-size: 1.1rem;
                color: #333;
            }
            .drag-handle {
                color: #888;
                font-size: 1.3rem;
                cursor: grab;
                margin-left: 10px;
                opacity: 0.7;
                transition: opacity 0.2s;
            }
            .drag-handle:hover {
                opacity: 1;
                color: #007bff;
            }
            .question-section .question-label {
                font-weight: 500;
                font-size: 1rem;
                color: #444;
                margin: 0.5rem 0.5rem 0.75rem 0.5rem;
                display: block;
            }
            .question-section .form-control {
                margin: 0.5rem 0.5rem 0.75rem 0.5rem;
            }
            .remove-btn {
                margin-left: 8px;
            }
            .question-section.drag-over {
                border: 2px dashed #007bff !important;
                background: #e9f5ff;
                box-shadow: 0 4px 16px rgba(0,123,255,0.08);
            }
            .question-section .input-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.5rem 0.5rem 0.5rem 0.5rem;
}
.question-section .input-group .form-control {
    flex: 1 1 auto;
    margin: 0;
}
.question-section .input-group .btn {
    flex: 0 0 auto;
    margin: 0;
}
        </style>

    </body>

    </html>