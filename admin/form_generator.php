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

                            <form method="post" action="crud/save_form.php">
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
                                    <!-- Default Question -->
                                    <div class="mb-3 question-block">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Question 1</label>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-secondary  btn-sm" style="float: right;" onclick="removeQuestion(this)">X</button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="text" name="questions[]" class="form-control mt-2" placeholder="Question text" required>
                                            </div>
                                            <div class="col-md-6">
                                                <select id="questionType1" name="types[]" class="form-select mt-2" onchange="updateQuestionFields(1)">
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
                                    </div>
                                </div>

                                <button type="button" class="btn btn-secondary" onclick="addQuestion()">+ Add Question</button>
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

            function addQuestion() {
                questionCounter++;
                const questionBlock = document.createElement('div');
                questionBlock.classList.add('mb-3', 'question-block');
                questionBlock.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <label>Question ${questionCounter}</label>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary btn-sm" style="float: right;" onclick="removeQuestion(this)">X</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" name="questions[]" class="form-control mt-2" placeholder="Question text" required>
                        </div>
                        <div class="col-md-6">
                            <select id="questionType${questionCounter}" name="types[]" class="form-select mt-2" onchange="updateQuestionFields(${questionCounter})">
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
                `;
                document.getElementById('questions').appendChild(questionBlock);
            }

            function removeQuestion(element) {
                element.closest('.question-block').remove();
            }

            function updateQuestionFields(questionNumber) {
                const questionType = document.getElementById(`questionType${questionNumber}`).value;
                const questionFieldsDiv = document.getElementById(`questionFields${questionNumber}`);
                questionFieldsDiv.innerHTML = ''; // Clear existing fields

                if (questionType === 'radio' || questionType === 'checkbox' || questionType === 'dropdown') {
                    questionFieldsDiv.innerHTML = `
                        <div class="mt-3">
                            <label class="form-label">Options</label>
                            <div id="optionsContainer${questionNumber}">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="options[${questionNumber}][]" placeholder="Option text">
                                    <button class="btn btn-outline-secondary" type="button" onclick="removeOption(this)">Remove</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary" onclick="addOption(${questionNumber})">Add Option</button>
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
                document.getElementById(`rating${questionNumber}`).value = value;
                const ratingDiv = document.getElementById(`questionFields${questionNumber}`).querySelector('.rating');
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

            function addOption(questionNumber) {
                const optionsContainer = document.getElementById(`optionsContainer${questionNumber}`);
                const newOption = document.createElement('div');
                newOption.classList.add('input-group', 'mb-2');
                newOption.innerHTML = `
                    <input type="text" class="form-control" name="options[${questionNumber}][]" placeholder="Option text" value="">
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
        </style>

    </body>

    </html>