<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }

        .thankyou-container {
            max-width: 500px;
            margin: 80px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 16px rgba(0, 0, 0, 0.08);
            padding: 40px 30px 30px 30px;
            text-align: center;
        }

        .thankyou-icon {
            font-size: 3.5rem;
            color: #28a745;
            margin-bottom: 18px;
        }

        .thankyou-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .thankyou-msg {
            font-size: 1.1rem;
            color: #555;
            margin: 18px 0 0 0;
        }
    </style>
</head>

<body>
    <div class="thankyou-container">
        <?php
        include('config/config.php');
        // echo $_GET['form_id'];
        $form_id = $_GET['form_id'];    // fetch thank you message from `forms` table:
        $stmt = $conn->prepare("SELECT fc.thankyou_message, fc.allow_another_response, u.business_name 
                        FROM forms_combined fc
                        JOIN users u ON fc.created_for = u.id
                        WHERE fc.id = ?");
        $stmt->execute([$form_id]);
        $form_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debugging output
        // error_log("Debugging form_id: " . $form_id);
        // error_log("Debugging form_data: " . print_r($form_data, true));
        // echo "<div class='debug-output alert alert-info'>Debugging form_id: " . htmlspecialchars($form_id) . "</div>";
        // echo "<div class='debug-output alert alert-info'>Debugging form_data: " . htmlspecialchars(print_r($form_data, true)) . "</div>";

        $thankyou = $form_data['thankyou_message'] ?? '';
        $allow_another_response = $form_data['allow_another_response'] ?? 0;

        // error_log("Debugging thankyou: " . $thankyou);
        // error_log("Debugging allow_another_response: " . $allow_another_response);
        // echo "<div class='debug-output alert alert-info'>Debugging thankyou: " . htmlspecialchars($thankyou) . "</div>";
        // echo "<div class='debug-output alert alert-info'>Debugging allow_another_response: " . htmlspecialchars($allow_another_response) . "</div>";
        $business_name = $form_data['business_name'] ?? '';
        $sanitized_business_name = preg_replace('/[^a-zA-Z0-9_ -]/', '', $business_name);
        $sanitized_business_name = str_replace(' ', '_', $sanitized_business_name);
        $sanitized_business_name = strtolower($sanitized_business_name);
        ?>
        <div class="thankyou-icon">✅</div>
        <div class="thankyou-title">Thank You!</div>
        <?php
        if ($thankyou && trim($thankyou) !== '') {
            echo "<div class='alert alert-success text-center'>$thankyou</div>";
        } else {
            echo "<div class='alert alert-success text-center'>Thank you for your submission!</div>";
        }
        ?>
        <div class="thankyou-msg">Your response has been submitted successfully.<br>We appreciate your feedback.</div>
        <?php if ($allow_another_response == 1): ?>

            <div class="mt-4">
                <a href="/feedback-system/forms/<?= htmlspecialchars($sanitized_business_name) ?>/feedback-form-<?= htmlspecialchars($form_id) ?>.php" class="btn btn-primary">Submit Another Response</a>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>