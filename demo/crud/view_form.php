<?php
session_start();
include('../../admin/config/config.php');

include('../assets/inc/incHeader.php');
$formId = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$formId]);
$form = $stmt->fetch();

$stmt = $conn->prepare("SELECT * FROM questions WHERE form_id = ?");
$stmt->execute([$formId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<body>
    <div class="container mt-5 view-form">
        <h2 class="text-center title"><?= htmlspecialchars($form['title']) ?></h2>
        <p><?= htmlspecialchars($form['description']) ?></p>
        <form method="POST" action="process_response.php">
            <input type="hidden" name="form_id" value="<?= $formId ?>">
            <!-- Personal Info Fields -->
            <?php if (!empty($form['firstname'])): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500;">First Name</label>
                    <input type="text" class="form-control" name="firstname" pattern="[A-Za-z\s]+" title="Only letters allowed" required>

                </div>
            <?php endif; ?>

            <?php if (!empty($form['lastname'])): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500;">Last Name</label>
                    <input type="text" class="form-control" name="lastname" pattern="[A-Za-z\s]+" title="Only letters allowed" required>

                </div>
            <?php endif; ?>

            <?php if (!empty($form['email'])): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500;">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
            <?php endif; ?>

            <?php if (!empty($form['number'])): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500;">Phone Number</label>
                    <input type="tel" class="form-control" name="number"
                        pattern="\d{10}"
                        maxlength="10"
                        title="Enter exactly 10 digits"
                        required
                        oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">

                </div>
            <?php endif; ?>

            <?php foreach ($questions as $q): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500;"><?= htmlspecialchars($q['question_text']) ?></label>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ?");
                    $stmt->execute([$q['id']]);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    switch ($q['question_type']) {
                        case 'text':
                            echo '<input type="text" class="form-control" name="q_' . $q['id'] . '">';

                            break;
                        case 'textarea':
                            echo '<textarea class="form-control" name="q_' . $q['id'] . '"></textarea>';
                            break;
                        case 'radio':
                            foreach ($options as $opt) {
                                echo '<div class="form-check">
                                        <input class="form-check-input" type="radio" name="q_' . $q['id'] . '" value="' . htmlspecialchars($opt['option_text']) . '">
                                        <label class="form-check-label">' . htmlspecialchars($opt['option_text']) . '</label>
                                      </div>';
                            }
                            break;
                        case 'checkbox':
                            foreach ($options as $opt) {
                                echo '<div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="q_' . $q['id'] . '[]" value="' . htmlspecialchars($opt['option_text']) . '">
                                        <label class="form-check-label">' . htmlspecialchars($opt['option_text']) . '</label>
                                      </div>';
                            }
                            break;
                        case 'rating_star':
                        case 'rating_heart':
                        case 'rating_thumb':
                            $icon = $q['question_type'] === 'rating_star' ? 'star' : ($q['question_type'] === 'rating_heart' ? 'heart' : 'hand-thumbs-up');
                            echo '<div class="rating-icons" data-question-id="' . $q['id'] . '" data-icon="' .  $icon  . '">';
                            for ($i = 1; $i <= 5; $i++) {
                                echo '<i class="bi bi-' .  $icon  . '" data-value="' .  $i  . '" style="font-size: 1.5rem; cursor: pointer;"></i>';
                            }
                            echo '<input type="hidden" name="q_' . $q['id'] . '" value="0">';
                            echo '</div>';
                            break;
                        default:
                            echo '<input type="text" class="form-control" >';
                    }
                    ?>
                </div>
            <?php endforeach; ?>
            <div class="mb-3">
                <button type="submit" class="btn btn-success">Save</button>
            </div>
            <div class="mb-3">
                <?php if (isset($_SESSION['role_id'])): ?>
                    <?php if ($_SESSION['role_id'] == 1): ?>
                        <a href="../index.php" class="btn btn-secondary">Back to Admin Dashboard</a>
                    <?php elseif ($_SESSION['role_id'] == 2): ?>
                        <a href="../moderator_dashboard.php" class="btn btn-secondary">Back to Moderator Dashboard</a>
                    <?php elseif ($_SESSION['role_id'] == 3): ?>
                        <a href="../user_dashboard.php" class="btn btn-secondary">Back to User Dashboard</a>
                    <?php else: ?>
                        <a href="../feedback_form_list.php" class="btn btn-secondary">Back</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="../feedback_form_list.php" class="btn btn-secondary">Back</a>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <?php if (isset($_SESSION['role_id'])): ?>
                    <?php if ($_SESSION['role_id'] == 1): ?>
                        <a href="../index.php" class="btn btn-primary">View All Forms (Admin)</a>
                    <?php elseif ($_SESSION['role_id'] == 2): ?>
                        <a href="../moderator_dashboard.php" class="btn btn-primary">View All Forms (Moderator)</a>
                    <?php elseif ($_SESSION['role_id'] == 3): ?>
                        <a href="../user_dashboard.php" class="btn btn-primary">View All Forms (User)</a>
                    <?php else: ?>
                        <a href="../feedback_form_list.php" class="btn btn-primary">View All Forms</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="../feedback_form_list.php" class="btn btn-primary">View All Forms</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <script>
        document.querySelectorAll('.rating-icons').forEach(container => {
            const iconType = container.dataset.icon;
            const icons = container.querySelectorAll('i');
            const hiddenInput = container.querySelector('input[type="hidden"]');

            icons.forEach((icon, index) => {
                icon.addEventListener('click', () => {
                    const selectedValue = index + 1;
                    hiddenInput.value = selectedValue;

                    icons.forEach((el, i) => {
                        el.className = 'bi';
                        if (i < selectedValue) {
                            el.classList.add('bi-' + iconType + '-fill', 'text-warning');
                        } else {
                            el.classList.add('bi-' + iconType);
                        }
                        el.style.fontSize = '1.5rem';
                        el.style.cursor = 'pointer';
                    });
                });
            });
        });
    </script>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>