<?php
// ini_set('session.gc_maxlifetime', 300);
// session_set_cookie_params(lifetime_or_options: 300);
session_start();

include('../admin/config/config.php');

// Set session on login
// if (!isset($_SESSION['login_time'])) {
//     $_SESSION['login_time'] = time();
// }

// Set session duration (in seconds)
// $session_duration = 300; // 24 hours = 86400 seconds

// // Remaining time
// $time_left = ($_SESSION['login_time'] + $session_duration) - time();
// $email = $_SESSION['email'] ?? $_SESSION['demo_user'] ?? null;


// if ($time_left <= 0) {
//     // Set approved=0 for this user in demo_requests if email is set
//     if ($email) {
//         $stmt = $conn->prepare("UPDATE demo_requests SET approved = 0 WHERE email = ?");
//         $stmt->execute([$email]);
//     }
//     session_unset();
//     session_destroy();
//     header("Location: login.php?expired=1");
//     exit();
// }

// // $isGoogleLogin = isset($_SESSION['auth_method']) && $_SESSION['auth_method'] === 'google';
// $isTraditional = isset($_SESSION['role_id']) && in_array($_SESSION['role_id'], [4]);
// // var_dump($_SESSION['role_id']);
// // exit;
// if (!$isTraditional) {
//     header("Location: login.php");
//     exit();
// }


include('../admin/config/config.php');
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

            if (questionType === 'radio' || questionType === 'checkbox') {
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
                            <input type="radio" id="star5-${questionNumber}" name="rating[${questionNumber}]" value="5" /><label class = "full star" for="star5-${questionNumber}" title="5 stars"></label>
                            <input type="radio" id="star4-${questionNumber}" name="rating[${questionNumber}]" value="4" /><label class = "full star" for="star4-${questionNumber}" title="4 stars"></label>
                            <input type="radio" id="star3-${questionNumber}" name="rating[${questionNumber}]" value="3" /><label class = "full star" for="star3-${questionNumber}" title="3 stars"></label>
                            <input type="radio" id="star2-${questionNumber}" name="rating[${questionNumber}]" value="2" /><label class = "full star" for="star2-${questionNumber}" title="2 stars"></label>
                            <input type="radio" id="star1-${questionNumber}" name="rating[${questionNumber}]" value="1" /><label class = "full star" for="star1-${questionNumber}" title="1 star"></label>
                        </div>
                    </div>
                `;
            } else if (questionType === 'rating_heart') {
                questionFieldsDiv.innerHTML = `
                    <div class="mt-3">
                        <label class="form-label">Rating (Hearts)</label>
                        <div class="rating">
                            <input type="radio" id="heart5-${questionNumber}" name="rating[${questionNumber}]" value="5" /><label class = "full heart" for="heart5-${questionNumber}" title="5 hearts"></label>
                            <input type="radio" id="heart4-${questionNumber}" name="rating[${questionNumber}]" value="4" /><label class = "full heart" for="heart4-${questionNumber}" title="4 hearts"></label>
                            <input type="radio" id="heart3-${questionNumber}" name="rating[${questionNumber}]" value="3" /><label class = "full heart" for="heart3-${questionNumber}" title="3 hearts"></label>
                            <input type="radio" id="heart2-${questionNumber}" name="rating[${questionNumber}]" value="2" /><label class = "full heart" for="heart2-${questionNumber}" title="2 hearts"></label>
                            <input type="radio" id="heart1-${questionNumber}" name="rating[${questionNumber}]" value="1" /><label class = "full heart" for="heart1-${questionNumber}" title="1 heart"></label>
                        </div>
                    </div>
                `;
            } else if (questionType === 'rating_thumb') {
                questionFieldsDiv.innerHTML = `
                    <div class="mt-3">
                        <label class="form-label">Rating (Thumbs)</label>
                         <div class="rating">
                            <input type="radio" id="thumb5-${questionNumber}" name="rating[${questionNumber}]" value="5" /><label class = "full thumb" for="thumb5-${questionNumber}" title="5 thumbs"></label>
                            <input type="radio" id="thumb4-${questionNumber}" name="rating[${questionNumber}]" value="4" /><label class = "full thumb" for="thumb4-${questionNumber}" title="4 thumbs"></label>
                            <input type="radio" id="thumb3-${questionNumber}" name="rating[${questionNumber}]" value="3" /><label class = "full thumb" for="thumb3-${questionNumber}" title="3 thumbs"></label>
                            <input type="radio" id="thumb2-${questionNumber}" name="rating[${questionNumber}]" value="2" /><label class = "full thumb" for="thumb2-${questionNumber}" title="2 thumbs"></label>
                            <input type="radio" id="thumb1-${questionNumber}" name="rating[${questionNumber}]" value="1" /><label class = "full thumb" for="thumb1-${questionNumber}" title="1 thumb"></label>
                        </div>
                    </div>
                `;
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
    <!-- <script>
        // PHP seconds passed to JS
        let timeLeft = <?= $time_left ?>;

        function formatTime(seconds) {
            const d = Math.floor(seconds / (3600 * 24));
            const h = Math.floor((seconds % (3600 * 24)) / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            return `${d}d ${h}h ${m}m ${s}s`;
        }

        function countdown() {
            if (timeLeft <= 0) {
                // Call session_expire.php to update approved=0 and destroy session
                fetch('session_expire.php', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        window.location.href = 'login.php?session_expired=1';
                    });
                return;
            }

            document.getElementById('sessionCountdown').textContent = formatTime(timeLeft);
            timeLeft--;
        }

        // Start countdown every second
        countdown(); // call immediately
        setInterval(countdown, 1000);
    </script> -->

</body>

</html>