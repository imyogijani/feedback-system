<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'admin/vendor/autoload.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'admin/config/config.php';

    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $business_name = trim($_POST['business_name']);
    $business_type = trim($_POST['business_type']);
    if ($business_type === 'Other') {
        $business_type = trim($_POST['other_business_type']);
    }
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $comment = trim($_POST['comment']);

    $stmt = $conn->prepare("
        INSERT INTO demo_requests 
        (first_name, last_name, business_name, business_type, email, mobile, comment) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    if ($stmt->execute([$first_name, $last_name, $business_name, $business_type, $email, $mobile, $comment])) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'foramparikh1234@gmail.com';
            $mail->Password   = 'sgis ocuy nolq kujo';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('foramparikh1234@gmail.com', 'Feedback System');
            $mail->addAddress($email, "{$first_name} {$last_name}");
            $mail->isHTML(true);
            $mail->Subject = 'Demo Request Received';

            $mailContent = "
                <h4>Hello {$first_name} {$last_name},</h4>
                <p>Thank you for requesting a demo. Here are your submitted details:</p>
                <ul>
                    <li><strong>Business Name:</strong> {$business_name}</li>
                    <li><strong>Business Type:</strong> {$business_type}</li>
                    <li><strong>Email:</strong> {$email}</li>
                    <li><strong>Mobile:</strong> {$mobile}</li>
                    <li><strong>Comment:</strong> {$comment}</li>
                </ul>
                <p><strong>Login Credentials:</strong><br>
                Username: {$email}<br>
                Password: {$mobile}</p>
                <p><a href='http://localhost/feedback-system/demo/login.php'>Click here to login</a></p>
                <p>We will get back to you soon.</p>
            ";
            $mail->Body = $mailContent;
            $mail->AltBody = "Demo requested. Username: {$email}, Password: {$mobile}. Login: http://localhost/feedback-system/demo/login.php";

            $mail->send();
            $success = "Demo request submitted successfully! Please check your email.";
        } catch (Exception $e) {
            $error = "Request saved but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Failed to save your request. Please try again.";
    }
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FeedbackPro - Complete Feedback Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode.js/lib/qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .modal {
            transition: opacity 0.3s ease;
        }

        .rating-star {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .rating-star:hover {
            transform: scale(1.2);
        }

        .tab-active {
            color: #4f46e5;
            border-bottom: 2px solid #4f46e5;
        }
    </style>
    <style>
        *,
        ::before,
        ::after {
            --tw-border-spacing-x: 0;
            --tw-border-spacing-y: 0;
            --tw-translate-x: 0;
            --tw-translate-y: 0;
            --tw-rotate: 0;
            --tw-skew-x: 0;
            --tw-skew-y: 0;
            --tw-scale-x: 1;
            --tw-scale-y: 1;
            --tw-pan-x: ;
            --tw-pan-y: ;
            --tw-pinch-zoom: ;
            --tw-scroll-snap-strictness: proximity;
            --tw-gradient-from-position: ;
            --tw-gradient-via-position: ;
            --tw-gradient-to-position: ;
            --tw-ordinal: ;
            --tw-slashed-zero: ;
            --tw-numeric-figure: ;
            --tw-numeric-spacing: ;
            --tw-numeric-fraction: ;
            --tw-ring-inset: ;
            --tw-ring-offset-width: 0px;
            --tw-ring-offset-color: #fff;
            --tw-ring-color: rgb(59 130 246 / 0.5);
            --tw-ring-offset-shadow: 0 0 #0000;
            --tw-ring-shadow: 0 0 #0000;
            --tw-shadow: 0 0 #0000;
            --tw-shadow-colored: 0 0 #0000;
            --tw-blur: ;
            --tw-brightness: ;
            --tw-contrast: ;
            --tw-grayscale: ;
            --tw-hue-rotate: ;
            --tw-invert: ;
            --tw-saturate: ;
            --tw-sepia: ;
            --tw-drop-shadow: ;
            --tw-backdrop-blur: ;
            --tw-backdrop-brightness: ;
            --tw-backdrop-contrast: ;
            --tw-backdrop-grayscale: ;
            --tw-backdrop-hue-rotate: ;
            --tw-backdrop-invert: ;
            --tw-backdrop-opacity: ;
            --tw-backdrop-saturate: ;
            --tw-backdrop-sepia: ;
            --tw-contain-size: ;
            --tw-contain-layout: ;
            --tw-contain-paint: ;
            --tw-contain-style:
        }

        ::backdrop {
            --tw-border-spacing-x: 0;
            --tw-border-spacing-y: 0;
            --tw-translate-x: 0;
            --tw-translate-y: 0;
            --tw-rotate: 0;
            --tw-skew-x: 0;
            --tw-skew-y: 0;
            --tw-scale-x: 1;
            --tw-scale-y: 1;
            --tw-pan-x: ;
            --tw-pan-y: ;
            --tw-pinch-zoom: ;
            --tw-scroll-snap-strictness: proximity;
            --tw-gradient-from-position: ;
            --tw-gradient-via-position: ;
            --tw-gradient-to-position: ;
            --tw-ordinal: ;
            --tw-slashed-zero: ;
            --tw-numeric-figure: ;
            --tw-numeric-spacing: ;
            --tw-numeric-fraction: ;
            --tw-ring-inset: ;
            --tw-ring-offset-width: 0px;
            --tw-ring-offset-color: #fff;
            --tw-ring-color: rgb(59 130 246 / 0.5);
            --tw-ring-offset-shadow: 0 0 #0000;
            --tw-ring-shadow: 0 0 #0000;
            --tw-shadow: 0 0 #0000;
            --tw-shadow-colored: 0 0 #0000;
            --tw-blur: ;
            --tw-brightness: ;
            --tw-contrast: ;
            --tw-grayscale: ;
            --tw-hue-rotate: ;
            --tw-invert: ;
            --tw-saturate: ;
            --tw-sepia: ;
            --tw-drop-shadow: ;
            --tw-backdrop-blur: ;
            --tw-backdrop-brightness: ;
            --tw-backdrop-contrast: ;
            --tw-backdrop-grayscale: ;
            --tw-backdrop-hue-rotate: ;
            --tw-backdrop-invert: ;
            --tw-backdrop-opacity: ;
            --tw-backdrop-saturate: ;
            --tw-backdrop-sepia: ;
            --tw-contain-size: ;
            --tw-contain-layout: ;
            --tw-contain-paint: ;
            --tw-contain-style:
        }

        /* ! tailwindcss v3.4.16 | MIT License | https://tailwindcss.com */
        *,
        ::after,
        ::before {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            border-color: #e5e7eb
        }

        ::after,
        ::before {
            --tw-content: ''
        }

        :host,
        html {
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
            -moz-tab-size: 4;
            tab-size: 4;
            font-family: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-feature-settings: normal;
            font-variation-settings: normal;
            -webkit-tap-highlight-color: transparent
        }

        body {
            margin: 0;
            line-height: inherit
        }

        hr {
            height: 0;
            color: inherit;
            border-top-width: 1px
        }

        abbr:where([title]) {
            -webkit-text-decoration: underline dotted;
            text-decoration: underline dotted
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-size: inherit;
            font-weight: inherit
        }

        a {
            color: inherit;
            text-decoration: inherit
        }

        b,
        strong {
            font-weight: bolder
        }

        code,
        kbd,
        pre,
        samp {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-feature-settings: normal;
            font-variation-settings: normal;
            font-size: 1em
        }

        small {
            font-size: 80%
        }

        sub,
        sup {
            font-size: 75%;
            line-height: 0;
            position: relative;
            vertical-align: baseline
        }

        sub {
            bottom: -.25em
        }

        sup {
            top: -.5em
        }

        table {
            text-indent: 0;
            border-color: inherit;
            border-collapse: collapse
        }

        button,
        input,
        optgroup,
        select,
        textarea {
            font-family: inherit;
            font-feature-settings: inherit;
            font-variation-settings: inherit;
            font-size: 100%;
            font-weight: inherit;
            line-height: inherit;
            letter-spacing: inherit;
            color: inherit;
            margin: 0;
            padding: 0
        }

        button,
        select {
            text-transform: none
        }

        button,
        input:where([type=button]),
        input:where([type=reset]),
        input:where([type=submit]) {
            -webkit-appearance: button;
            background-color: transparent;
            background-image: none
        }

        :-moz-focusring {
            outline: auto
        }

        :-moz-ui-invalid {
            box-shadow: none
        }

        progress {
            vertical-align: baseline
        }

        ::-webkit-inner-spin-button,
        ::-webkit-outer-spin-button {
            height: auto
        }

        [type=search] {
            -webkit-appearance: textfield;
            outline-offset: -2px
        }

        ::-webkit-search-decoration {
            -webkit-appearance: none
        }

        ::-webkit-file-upload-button {
            -webkit-appearance: button;
            font: inherit
        }

        summary {
            display: list-item
        }

        blockquote,
        dd,
        dl,
        figure,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        hr,
        p,
        pre {
            margin: 0
        }

        fieldset {
            margin: 0;
            padding: 0
        }

        legend {
            padding: 0
        }

        menu,
        ol,
        ul {
            list-style: none;
            margin: 0;
            padding: 0
        }

        dialog {
            padding: 0
        }

        textarea {
            resize: vertical
        }

        input::placeholder,
        textarea::placeholder {
            opacity: 1;
            color: #9ca3af
        }

        [role=button],
        button {
            cursor: pointer
        }

        :disabled {
            cursor: default
        }

        audio,
        canvas,
        embed,
        iframe,
        img,
        object,
        svg,
        video {
            display: block;
            vertical-align: middle
        }

        img,
        video {
            max-width: 100%;
            height: auto
        }

        [hidden]:where(:not([hidden=until-found])) {
            display: none
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0
        }

        .fixed {
            position: fixed
        }

        .absolute {
            position: absolute
        }

        .relative {
            position: relative
        }

        .bottom-0 {
            bottom: 0px
        }

        .left-0 {
            left: 0px
        }

        .z-10 {
            z-index: 10
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto
        }

        .mb-2 {
            margin-bottom: 0.5rem
        }

        .mb-3 {
            margin-bottom: 0.75rem
        }

        .mb-4 {
            margin-bottom: 1rem
        }

        .mb-5 {
            margin-bottom: 1.25rem
        }

        .mb-6 {
            margin-bottom: 1.5rem
        }

        .mb-8 {
            margin-bottom: 2rem
        }

        .ml-2 {
            margin-left: 0.5rem
        }

        .ml-3 {
            margin-left: 0.75rem
        }

        .mr-1 {
            margin-right: 0.25rem
        }

        .mr-2 {
            margin-right: 0.5rem
        }

        .mr-3 {
            margin-right: 0.75rem
        }

        .mt-10 {
            margin-top: 2.5rem
        }

        .mt-12 {
            margin-top: 3rem
        }

        .mt-16 {
            margin-top: 4rem
        }

        .mt-2 {
            margin-top: 0.5rem
        }

        .mt-3 {
            margin-top: 0.75rem
        }

        .mt-4 {
            margin-top: 1rem
        }

        .mt-6 {
            margin-top: 1.5rem
        }

        .mt-8 {
            margin-top: 2rem
        }

        .block {
            display: block
        }

        .flex {
            display: flex
        }

        .inline-flex {
            display: inline-flex
        }

        .grid {
            display: grid
        }

        .hidden {
            display: none
        }

        .h-12 {
            height: 3rem
        }

        .h-16 {
            height: 4rem
        }

        .h-4 {
            height: 1rem
        }

        .h-48 {
            height: 12rem
        }

        .h-5 {
            height: 1.25rem
        }

        .h-6 {
            height: 1.5rem
        }

        .h-8 {
            height: 2rem
        }

        .w-12 {
            width: 3rem
        }

        .w-4 {
            width: 1rem
        }

        .w-48 {
            width: 12rem
        }

        .w-5 {
            width: 1.25rem
        }

        .w-6 {
            width: 1.5rem
        }

        .w-8 {
            width: 2rem
        }

        .w-full {
            width: 100%
        }

        .max-w-2xl {
            max-width: 42rem
        }

        .max-w-7xl {
            max-width: 80rem
        }

        .max-w-md {
            max-width: 28rem
        }

        .flex-1 {
            flex: 1 1 0%
        }

        .flex-shrink-0 {
            flex-shrink: 0
        }

        .scale-105 {
            --tw-scale-x: 1.05;
            --tw-scale-y: 1.05;
            transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .transform {
            transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .cursor-pointer {
            cursor: pointer
        }

        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr))
        }

        .flex-col {
            flex-direction: column
        }

        .items-center {
            align-items: center
        }

        .justify-end {
            justify-content: flex-end
        }

        .justify-center {
            justify-content: center
        }

        .justify-between {
            justify-content: space-between
        }

        .gap-6 {
            gap: 1.5rem
        }

        .gap-8 {
            gap: 2rem
        }

        .space-x-2> :not([hidden])~ :not([hidden]) {
            --tw-space-x-reverse: 0;
            margin-right: calc(0.5rem * var(--tw-space-x-reverse));
            margin-left: calc(0.5rem * calc(1 - var(--tw-space-x-reverse)))
        }

        .space-x-3> :not([hidden])~ :not([hidden]) {
            --tw-space-x-reverse: 0;
            margin-right: calc(0.75rem * var(--tw-space-x-reverse));
            margin-left: calc(0.75rem * calc(1 - var(--tw-space-x-reverse)))
        }

        .space-x-4> :not([hidden])~ :not([hidden]) {
            --tw-space-x-reverse: 0;
            margin-right: calc(1rem * var(--tw-space-x-reverse));
            margin-left: calc(1rem * calc(1 - var(--tw-space-x-reverse)))
        }

        .space-x-6> :not([hidden])~ :not([hidden]) {
            --tw-space-x-reverse: 0;
            margin-right: calc(1.5rem * var(--tw-space-x-reverse));
            margin-left: calc(1.5rem * calc(1 - var(--tw-space-x-reverse)))
        }

        .space-y-3> :not([hidden])~ :not([hidden]) {
            --tw-space-y-reverse: 0;
            margin-top: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
            margin-bottom: calc(0.75rem * var(--tw-space-y-reverse))
        }

        .space-y-4> :not([hidden])~ :not([hidden]) {
            --tw-space-y-reverse: 0;
            margin-top: calc(1rem * calc(1 - var(--tw-space-y-reverse)));
            margin-bottom: calc(1rem * var(--tw-space-y-reverse))
        }

        .space-y-6> :not([hidden])~ :not([hidden]) {
            --tw-space-y-reverse: 0;
            margin-top: calc(1.5rem * calc(1 - var(--tw-space-y-reverse)));
            margin-bottom: calc(1.5rem * var(--tw-space-y-reverse))
        }

        .overflow-hidden {
            overflow: hidden
        }

        .rounded-full {
            border-radius: 9999px
        }

        .rounded-lg {
            border-radius: 0.5rem
        }

        .rounded-md {
            border-radius: 0.375rem
        }

        .rounded-xl {
            border-radius: 0.75rem
        }

        .border {
            border-width: 1px
        }

        .border-2 {
            border-width: 2px
        }

        .border-b {
            border-bottom-width: 1px
        }

        .border-t {
            border-top-width: 1px
        }

        .border-blue-100 {
            --tw-border-opacity: 1;
            border-color: rgb(219 234 254 / var(--tw-border-opacity, 1))
        }

        .border-gray-200 {
            --tw-border-opacity: 1;
            border-color: rgb(229 231 235 / var(--tw-border-opacity, 1))
        }

        .border-gray-300 {
            --tw-border-opacity: 1;
            border-color: rgb(209 213 219 / var(--tw-border-opacity, 1))
        }

        .border-gray-700 {
            --tw-border-opacity: 1;
            border-color: rgb(55 65 81 / var(--tw-border-opacity, 1))
        }

        .border-indigo-100 {
            --tw-border-opacity: 1;
            border-color: rgb(224 231 255 / var(--tw-border-opacity, 1))
        }

        .border-indigo-500 {
            --tw-border-opacity: 1;
            border-color: rgb(99 102 241 / var(--tw-border-opacity, 1))
        }

        .border-indigo-600 {
            --tw-border-opacity: 1;
            border-color: rgb(79 70 229 / var(--tw-border-opacity, 1))
        }

        .border-purple-100 {
            --tw-border-opacity: 1;
            border-color: rgb(243 232 255 / var(--tw-border-opacity, 1))
        }

        .border-transparent {
            border-color: transparent
        }

        .bg-black {
            --tw-bg-opacity: 1;
            background-color: rgb(0 0 0 / var(--tw-bg-opacity, 1))
        }

        .bg-blue-50 {
            --tw-bg-opacity: 1;
            background-color: rgb(239 246 255 / var(--tw-bg-opacity, 1))
        }

        .bg-blue-600 {
            --tw-bg-opacity: 1;
            background-color: rgb(37 99 235 / var(--tw-bg-opacity, 1))
        }

        .bg-gray-50 {
            --tw-bg-opacity: 1;
            background-color: rgb(249 250 251 / var(--tw-bg-opacity, 1))
        }

        .bg-gray-800 {
            --tw-bg-opacity: 1;
            background-color: rgb(31 41 55 / var(--tw-bg-opacity, 1))
        }

        .bg-green-600 {
            --tw-bg-opacity: 1;
            background-color: rgb(22 163 74 / var(--tw-bg-opacity, 1))
        }

        .bg-indigo-50 {
            --tw-bg-opacity: 1;
            background-color: rgb(238 242 255 / var(--tw-bg-opacity, 1))
        }

        .bg-indigo-500 {
            --tw-bg-opacity: 1;
            background-color: rgb(99 102 241 / var(--tw-bg-opacity, 1))
        }

        .bg-indigo-600 {
            --tw-bg-opacity: 1;
            background-color: rgb(79 70 229 / var(--tw-bg-opacity, 1))
        }

        .bg-purple-50 {
            --tw-bg-opacity: 1;
            background-color: rgb(250 245 255 / var(--tw-bg-opacity, 1))
        }

        .bg-purple-600 {
            --tw-bg-opacity: 1;
            background-color: rgb(147 51 234 / var(--tw-bg-opacity, 1))
        }

        .bg-red-600 {
            --tw-bg-opacity: 1;
            background-color: rgb(220 38 38 / var(--tw-bg-opacity, 1))
        }

        .bg-white {
            --tw-bg-opacity: 1;
            background-color: rgb(255 255 255 / var(--tw-bg-opacity, 1))
        }

        .bg-opacity-60 {
            --tw-bg-opacity: 0.6
        }

        .p-4 {
            padding: 1rem
        }

        .p-6 {
            padding: 1.5rem
        }

        .p-8 {
            padding: 2rem
        }

        .px-1 {
            padding-left: 0.25rem;
            padding-right: 0.25rem
        }

        .px-3 {
            padding-left: 0.75rem;
            padding-right: 0.75rem
        }

        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem
        }

        .px-5 {
            padding-left: 1.25rem;
            padding-right: 1.25rem
        }

        .px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem
        }

        .px-8 {
            padding-left: 2rem;
            padding-right: 2rem
        }

        .py-12 {
            padding-top: 3rem;
            padding-bottom: 3rem
        }

        .py-16 {
            padding-top: 4rem;
            padding-bottom: 4rem
        }

        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem
        }

        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem
        }

        .py-8 {
            padding-top: 2rem;
            padding-bottom: 2rem
        }

        .pb-12 {
            padding-bottom: 3rem
        }

        .pb-8 {
            padding-bottom: 2rem
        }

        .pt-1 {
            padding-top: 0.25rem
        }

        .pt-24 {
            padding-top: 6rem
        }

        .pt-8 {
            padding-top: 2rem
        }

        .text-center {
            text-align: center
        }

        .text-2xl {
            font-size: 1.5rem;
            line-height: 2rem
        }

        .text-3xl {
            font-size: 1.875rem;
            line-height: 2.25rem
        }

        .text-4xl {
            font-size: 2.25rem;
            line-height: 2.5rem
        }

        .text-base {
            font-size: 1rem;
            line-height: 1.5rem
        }

        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem
        }

        .text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem
        }

        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem
        }

        .font-bold {
            font-weight: 700
        }

        .font-extrabold {
            font-weight: 800
        }

        .font-medium {
            font-weight: 500
        }

        .font-normal {
            font-weight: 400
        }

        .font-semibold {
            font-weight: 600
        }

        .uppercase {
            text-transform: uppercase
        }

        .tracking-tight {
            letter-spacing: -0.025em
        }

        .tracking-wide {
            letter-spacing: 0.025em
        }

        .tracking-wider {
            letter-spacing: 0.05em
        }

        .text-blue-600 {
            --tw-text-opacity: 1;
            color: rgb(37 99 235 / var(--tw-text-opacity, 1))
        }

        .text-gray-300 {
            --tw-text-opacity: 1;
            color: rgb(209 213 219 / var(--tw-text-opacity, 1))
        }

        .text-gray-400 {
            --tw-text-opacity: 1;
            color: rgb(156 163 175 / var(--tw-text-opacity, 1))
        }

        .text-gray-500 {
            --tw-text-opacity: 1;
            color: rgb(107 114 128 / var(--tw-text-opacity, 1))
        }

        .text-gray-600 {
            --tw-text-opacity: 1;
            color: rgb(75 85 99 / var(--tw-text-opacity, 1))
        }

        .text-gray-700 {
            --tw-text-opacity: 1;
            color: rgb(55 65 81 / var(--tw-text-opacity, 1))
        }

        .text-gray-800 {
            --tw-text-opacity: 1;
            color: rgb(31 41 55 / var(--tw-text-opacity, 1))
        }

        .text-gray-900 {
            --tw-text-opacity: 1;
            color: rgb(17 24 39 / var(--tw-text-opacity, 1))
        }

        .text-green-500 {
            --tw-text-opacity: 1;
            color: rgb(34 197 94 / var(--tw-text-opacity, 1))
        }

        .text-indigo-100 {
            --tw-text-opacity: 1;
            color: rgb(224 231 255 / var(--tw-text-opacity, 1))
        }

        .text-indigo-200 {
            --tw-text-opacity: 1;
            color: rgb(199 210 254 / var(--tw-text-opacity, 1))
        }

        .text-indigo-600 {
            --tw-text-opacity: 1;
            color: rgb(79 70 229 / var(--tw-text-opacity, 1))
        }

        .text-indigo-700 {
            --tw-text-opacity: 1;
            color: rgb(67 56 202 / var(--tw-text-opacity, 1))
        }

        .text-purple-600 {
            --tw-text-opacity: 1;
            color: rgb(147 51 234 / var(--tw-text-opacity, 1))
        }

        .text-white {
            --tw-text-opacity: 1;
            color: rgb(255 255 255 / var(--tw-text-opacity, 1))
        }

        .shadow {
            --tw-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --tw-shadow-colored: 0 1px 3px 0 var(--tw-shadow-color), 0 1px 2px -1px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .shadow-lg {
            --tw-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --tw-shadow-colored: 0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .shadow-md {
            --tw-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --tw-shadow-colored: 0 4px 6px -1px var(--tw-shadow-color), 0 2px 4px -2px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .shadow-sm {
            --tw-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --tw-shadow-colored: 0 1px 2px 0 var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .transition {
            transition-property: color, background-color, border-color, fill, stroke, opacity, box-shadow, transform, filter, -webkit-text-decoration-color, -webkit-backdrop-filter;
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter, -webkit-text-decoration-color, -webkit-backdrop-filter;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms
        }

        .duration-300 {
            transition-duration: 300ms
        }

        .hover\:border-gray-300:hover {
            --tw-border-opacity: 1;
            border-color: rgb(209 213 219 / var(--tw-border-opacity, 1))
        }

        .hover\:bg-gray-50:hover {
            --tw-bg-opacity: 1;
            background-color: rgb(249 250 251 / var(--tw-bg-opacity, 1))
        }

        .hover\:bg-indigo-50:hover {
            --tw-bg-opacity: 1;
            background-color: rgb(238 242 255 / var(--tw-bg-opacity, 1))
        }

        .hover\:bg-indigo-700:hover {
            --tw-bg-opacity: 1;
            background-color: rgb(67 56 202 / var(--tw-bg-opacity, 1))
        }

        .hover\:bg-opacity-70:hover {
            --tw-bg-opacity: 0.7
        }

        .hover\:text-gray-300:hover {
            --tw-text-opacity: 1;
            color: rgb(209 213 219 / var(--tw-text-opacity, 1))
        }

        .hover\:text-gray-700:hover {
            --tw-text-opacity: 1;
            color: rgb(55 65 81 / var(--tw-text-opacity, 1))
        }

        .hover\:text-indigo-600:hover {
            --tw-text-opacity: 1;
            color: rgb(79 70 229 / var(--tw-text-opacity, 1))
        }

        .hover\:text-indigo-800:hover {
            --tw-text-opacity: 1;
            color: rgb(55 48 163 / var(--tw-text-opacity, 1))
        }

        .hover\:text-white:hover {
            --tw-text-opacity: 1;
            color: rgb(255 255 255 / var(--tw-text-opacity, 1))
        }

        .hover\:shadow-xl:hover {
            --tw-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --tw-shadow-colored: 0 20px 25px -5px var(--tw-shadow-color), 0 8px 10px -6px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .focus\:border-indigo-500:focus {
            --tw-border-opacity: 1;
            border-color: rgb(99 102 241 / var(--tw-border-opacity, 1))
        }

        .focus\:outline-none:focus {
            outline: 2px solid transparent;
            outline-offset: 2px
        }

        .focus\:ring-2:focus {
            --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
            --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
            box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
        }

        .focus\:ring-indigo-500:focus {
            --tw-ring-opacity: 1;
            --tw-ring-color: rgb(99 102 241 / var(--tw-ring-opacity, 1))
        }

        .focus\:ring-offset-2:focus {
            --tw-ring-offset-width: 2px
        }

        @media (min-width: 640px) {
            .sm\:ml-3 {
                margin-left: 0.75rem
            }

            .sm\:mt-0 {
                margin-top: 0px
            }

            .sm\:mt-5 {
                margin-top: 1.25rem
            }

            .sm\:flex {
                display: flex
            }

            .sm\:px-6 {
                padding-left: 1.5rem;
                padding-right: 1.5rem
            }

            .sm\:text-4xl {
                font-size: 2.25rem;
                line-height: 2.5rem
            }

            .sm\:text-5xl {
                font-size: 3rem;
                line-height: 1
            }

            .sm\:text-xl {
                font-size: 1.25rem;
                line-height: 1.75rem
            }
        }

        @media (min-width: 768px) {
            .md\:order-2 {
                order: 2
            }

            .md\:ml-6 {
                margin-left: 1.5rem
            }

            .md\:flex {
                display: flex
            }

            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .md\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr))
            }

            .md\:grid-cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr))
            }

            .md\:flex-row {
                flex-direction: row
            }

            .md\:items-center {
                align-items: center
            }

            .md\:justify-between {
                justify-content: space-between
            }

            .md\:space-x-6> :not([hidden])~ :not([hidden]) {
                --tw-space-x-reverse: 0;
                margin-right: calc(1.5rem * var(--tw-space-x-reverse));
                margin-left: calc(1.5rem * calc(1 - var(--tw-space-x-reverse)))
            }

            .md\:space-x-8> :not([hidden])~ :not([hidden]) {
                --tw-space-x-reverse: 0;
                margin-right: calc(2rem * var(--tw-space-x-reverse));
                margin-left: calc(2rem * calc(1 - var(--tw-space-x-reverse)))
            }

            .md\:space-y-0> :not([hidden])~ :not([hidden]) {
                --tw-space-y-reverse: 0;
                margin-top: calc(0px * calc(1 - var(--tw-space-y-reverse)));
                margin-bottom: calc(0px * var(--tw-space-y-reverse))
            }

            .md\:px-10 {
                padding-left: 2.5rem;
                padding-right: 2.5rem
            }

            .md\:py-4 {
                padding-top: 1rem;
                padding-bottom: 1rem
            }

            .md\:text-6xl {
                font-size: 3.75rem;
                line-height: 1
            }

            .md\:text-lg {
                font-size: 1.125rem;
                line-height: 1.75rem
            }
        }

        @media (min-width: 1024px) {
            .lg\:mt-0 {
                margin-top: 0px
            }

            .lg\:flex {
                display: flex
            }

            .lg\:w-1\/2 {
                width: 50%
            }

            .lg\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr))
            }

            .lg\:items-center {
                align-items: center
            }

            .lg\:justify-between {
                justify-content: space-between
            }

            .lg\:px-8 {
                padding-left: 2rem;
                padding-right: 2rem
            }

            .lg\:text-lg {
                font-size: 1.125rem;
                line-height: 1.75rem
            }
        }

        @media (min-width: 1280px) {
            .xl\:text-xl {
                font-size: 1.25rem;
                line-height: 1.75rem
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm fixed w-full z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <svg class="h-8 w-8 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 12h-2v-2h2v2zm0-4h-2V6h2v4z"></path>
                        </svg>
                        <span class="ml-2 text-xl font-bold text-gray-800">FeedbackPro</span>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="#features" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-indigo-600">Features</a>
                        <a href="#roles" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-indigo-600">Roles</a>
                        <a href="#demo" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-indigo-600">Demo</a>
                        <a href="#pricing" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-indigo-600">Pricing</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <!-- <button class="ml-3 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Login</button> -->
                    <a href="demo\login.php" class="ml-3 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Log in</a>
                    <!-- <button onclick="openModal('signupModal')" class="ml-3 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Sign up</button> -->
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-24 pb-12 gradient-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:flex lg:items-center lg:justify-between">
                <div class="lg:w-1/2">
                    <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl md:text-6xl">
                        <span class="block">Collect &amp; Manage</span>
                        <span class="block text-indigo-200">Feedback Effortlessly</span>
                    </h1>
                    <p class="mt-3 text-base text-indigo-100 sm:mt-5 sm:text-xl lg:text-lg xl:text-xl">
                        A complete feedback management system with role-based access, customizable forms, QR code generation, and powerful analytics.
                    </p>
                    <div class="mt-8 sm:flex">
                        <div class="rounded-md shadow">
                            <a href="http://localhost/feedback-system/demo/login.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 md:py-4 md:text-lg md:px-10">
                                Try Demo
                            </a>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:ml-3">
                            <a href="#features" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-500 bg-opacity-60 hover:bg-opacity-70 md:py-4 md:text-lg md:px-10">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="mt-10 lg:mt-0 lg:w-1/2">
                    <div class="relative mx-auto w-full rounded-lg shadow-lg overflow-hidden">
                        <svg class="w-full" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                            <rect width="400" height="300" fill="#f8fafc"></rect>
                            <rect x="50" y="50" width="300" height="200" rx="10" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"></rect>
                            <rect x="70" y="70" width="260" height="40" rx="5" fill="#f1f5f9"></rect>
                            <rect x="70" y="130" width="120" height="20" rx="3" fill="#e2e8f0"></rect>
                            <rect x="70" y="160" width="260" height="30" rx="3" fill="#f1f5f9"></rect>
                            <rect x="70" y="200" width="80" height="30" rx="5" fill="#4f46e5"></rect>
                            <text x="110" y="220" font-size="12" fill="white" text-anchor="middle">Submit</text>
                            <circle cx="350" cy="70" r="15" fill="#4f46e5"></circle>
                            <path d="M345 70 L350 75 L355 65" stroke="white" stroke-width="2" fill="none"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full absolute bottom-0 left-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120">
                <path fill="#f9fafb" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Powerful Features
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Everything you need to collect, manage, and analyze feedback in one place.
                </p>
            </div>

            <div class="mt-16 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 card-hover">
                    <div class="text-4xl feature-icon mb-4">
                        <i class="fas fa-edit"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Custom Form Builder</h3>
                    <p class="text-gray-600">Create customized feedback forms with various question types, branding options, and conditional logic.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 card-hover">
                    <div class="text-4xl feature-icon mb-4">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">QR Code Generation</h3>
                    <p class="text-gray-600">Generate QR codes for your feedback forms to collect responses easily from physical locations.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 card-hover">
                    <div class="text-4xl feature-icon mb-4">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Role-Based Access</h3>
                    <p class="text-gray-600">Manage permissions with three distinct roles: Admin, Moderator, and User for secure access control.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 card-hover">
                    <div class="text-4xl feature-icon mb-4">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Advanced Analytics</h3>
                    <p class="text-gray-600">Gain insights with real-time analytics, custom reports, and exportable data in multiple formats.</p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 card-hover">
                    <div class="text-4xl feature-icon mb-4">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Rating System</h3>
                    <p class="text-gray-600">Collect star ratings, NPS scores, and other quantitative feedback metrics with beautiful interfaces.</p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white rounded-lg shadow-md p-6 transition duration-300 card-hover">
                    <div class="text-4xl feature-icon mb-4">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Secure Authentication</h3>
                    <p class="text-gray-600">Google login integration, profile management, and secure password recovery system.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section id="roles" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Role-Based System
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Our platform offers three distinct roles with specific permissions and capabilities.
                </p>
            </div>

            <div class="mt-16">
                <div class="flex flex-col md:flex-row justify-center space-y-6 md:space-y-0 md:space-x-6">
                    <!-- Admin Role -->
                    <div class="bg-indigo-50 rounded-xl p-8 flex-1 max-w-md border-2 border-indigo-100 transition duration-300 card-hover">
                        <div class="h-12 w-12 rounded-md bg-indigo-600 flex items-center justify-center mb-5">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">System Admin</h3>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Full system management
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Create and manage users
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Assign moderator roles
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Access all system analytics
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Configure system settings
                            </li>
                        </ul>
                    </div>

                    <!-- Moderator Role -->
                    <div class="bg-purple-50 rounded-xl p-8 flex-1 max-w-md border-2 border-purple-100 transition duration-300 card-hover">
                        <div class="h-12 w-12 rounded-md bg-purple-600 flex items-center justify-center mb-5">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Moderator</h3>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Review feedback submissions
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Approve or reject content
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Generate reports
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Manage assigned forms
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Limited user management
                            </li>
                        </ul>
                    </div>

                    <!-- User Role -->
                    <div class="bg-blue-50 rounded-xl p-8 flex-1 max-w-md border-2 border-blue-100 transition duration-300 card-hover">
                        <div class="h-12 w-12 rounded-md bg-blue-600 flex items-center justify-center mb-5">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">User</h3>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Create feedback forms
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Edit and publish forms
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Generate QR codes
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                View response analytics
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Manage personal profile
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <!-- <section id="demo" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Interactive Demo
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Experience the key features of our feedback system.
                </p>
            </div>

            <div class="mt-12">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="border-b border-gray-200">
                        <div class="flex">
                            <button onclick="changeTab('formBuilder')" id="formBuilderTab" class="px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none tab-active">
                                Form Builder
                            </button>
                            <button onclick="changeTab('qrCode')" id="qrCodeTab" class="px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                QR Code Generator
                            </button>
                            <button onclick="changeTab('feedback')" id="feedbackTab" class="px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                Feedback Form
                            </button>
                        </div>
                    </div> -->

    <!-- Form Builder Tab -->
    <!-- <div id="formBuilder" class="p-6">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Form Title</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" value="Customer Satisfaction Survey">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Form Description</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" rows="2">Please help us improve our services by providing your valuable feedback.</textarea>
                        </div>

                        <div class="border rounded-md p-4 mb-4 bg-gray-50">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-medium">Question 1</h4>
                                <div class="flex space-x-2">
                                    <button class="text-gray-500 hover:text-gray-700">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </button>
                                </div>
                            </div>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 mb-2" value="How satisfied are you with our service?">
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option>Rating (1-5 stars)</option>
                                <option>Multiple Choice</option>
                                <option>Text Response</option>
                                <option>Yes/No</option>
                            </select>
                        </div>

                        <div class="border rounded-md p-4 mb-4 bg-gray-50">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-medium">Question 2</h4>
                                <div class="flex space-x-2">
                                    <button class="text-gray-500 hover:text-gray-700">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </button>
                                </div>
                            </div>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 mb-2" value="What could we improve?">
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option>Text Response</option>
                                <option>Multiple Choice</option>
                                <option>Rating (1-5 stars)</option>
                                <option>Yes/No</option>
                            </select>
                        </div>

                        <button class="flex items-center text-indigo-600 hover:text-indigo-800">
                            <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Question
                        </button>

                        <div class="mt-6 flex justify-end">
                            <button class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                                Save Draft
                            </button>
                            <button class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Publish Form
                            </button>
                        </div>
                    </div> -->

    <!-- QR Code Generator Tab -->
    <!-- <div id="qrCode" class="p-6 hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Form</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option>Customer Satisfaction Survey</option>
                                        <option>Product Feedback Form</option>
                                        <option>Event Evaluation</option>
                                    </select>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code Size</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option>Medium (300x300px)</option>
                                        <option>Small (200x200px)</option>
                                        <option>Large (400x400px)</option>
                                    </select>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code Color</label>
                                    <div class="flex space-x-3">
                                        <div class="w-8 h-8 bg-black rounded-full border-2 border-indigo-600 cursor-pointer"></div>
                                        <div class="w-8 h-8 bg-indigo-600 rounded-full cursor-pointer"></div>
                                        <div class="w-8 h-8 bg-green-600 rounded-full cursor-pointer"></div>
                                        <div class="w-8 h-8 bg-red-600 rounded-full cursor-pointer"></div>
                                        <div class="w-8 h-8 bg-purple-600 rounded-full cursor-pointer"></div>
                                    </div>
                                </div>

                                <button onclick="generateQRCode()" class="w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Generate QR Code
                                </button>
                            </div>

                            <div class="flex flex-col items-center justify-center bg-gray-50 p-6 rounded-lg">
                                <div id="qrCodeDisplay" class="mb-4 flex items-center justify-center">
                                    <svg class="w-48 h-48" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="0" y="0" width="100" height="100" fill="#ffffff"></rect>
                                        <rect x="10" y="10" width="10" height="10" fill="#000000"></rect>
                                        <rect x="20" y="10" width="10" height="10" fill="#000000"></rect>
                                        <rect x="30" y="10" width="10" height="10" fill="#000000"></rect>
                                        <rect x="40" y="10" width="10" height="10" fill="#000000"></rect>
                                        <rect x="50" y="10" width="10" height="10" fill="#000000"></rect>
                                        <rect x="60" y="10" width="10" height="10" fill="#000000"></rect>
                                        <rect x="70" y="10" width="10" height="10" fill="#000000"></rect>
                                        <rect x="10" y="20" width="10" height="10" fill="#000000"></rect>
                                        <rect x="70" y="20" width="10" height="10" fill="#000000"></rect>
                                        <rect x="10" y="30" width="10" height="10" fill="#000000"></rect>
                                        <rect x="30" y="30" width="10" height="10" fill="#000000"></rect>
                                        <rect x="40" y="30" width="10" height="10" fill="#000000"></rect>
                                        <rect x="50" y="30" width="10" height="10" fill="#000000"></rect>
                                        <rect x="70" y="30" width="10" height="10" fill="#000000"></rect>
                                        <rect x="10" y="40" width="10" height="10" fill="#000000"></rect>
                                        <rect x="30" y="40" width="10" height="10" fill="#000000"></rect>
                                        <rect x="50" y="40" width="10" height="10" fill="#000000"></rect>
                                        <rect x="70" y="40" width="10" height="10" fill="#000000"></rect>
                                        <rect x="10" y="50" width="10" height="10" fill="#000000"></rect>
                                        <rect x="30" y="50" width="10" height="10" fill="#000000"></rect>
                                        <rect x="50" y="50" width="10" height="10" fill="#000000"></rect>
                                        <rect x="70" y="50" width="10" height="10" fill="#000000"></rect>
                                        <rect x="10" y="60" width="10" height="10" fill="#000000"></rect>
                                        <rect x="70" y="60" width="10" height="10" fill="#000000"></rect>
                                        <rect x="10" y="70" width="10" height="10" fill="#000000"></rect>
                                        <rect x="20" y="70" width="10" height="10" fill="#000000"></rect>
                                        <rect x="30" y="70" width="10" height="10" fill="#000000"></rect>
                                        <rect x="40" y="70" width="10" height="10" fill="#000000"></rect>
                                        <rect x="50" y="70" width="10" height="10" fill="#000000"></rect>
                                        <rect x="60" y="70" width="10" height="10" fill="#000000"></rect>
                                        <rect x="70" y="70" width="10" height="10" fill="#000000"></rect>
                                        <rect x="30" y="20" width="10" height="10" fill="#000000"></rect>
                                        <rect x="40" y="20" width="10" height="10" fill="#000000"></rect>
                                        <rect x="50" y="20" width="10" height="10" fill="#000000"></rect>
                                    </svg>
                                </div>
                                <div class="flex space-x-3">
                                    <button class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Download
                                    </button>
                                    <button class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Share
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div> -->

    <!-- Feedback Form Tab -->
    <!-- <div id="feedback" class="p-6 hidden">
                        <div class="max-w-2xl mx-auto">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Customer Satisfaction Survey</h3>
                            <p class="text-gray-600 mb-6">Please help us improve our services by providing your valuable feedback.</p>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">How satisfied are you with our service?</label>
                                <div class="flex space-x-2">
                                    <span class="rating-star text-2xl cursor-pointer" onclick="setRating(1)">★</span>
                                    <span class="rating-star text-2xl cursor-pointer" onclick="setRating(2)">★</span>
                                    <span class="rating-star text-2xl cursor-pointer" onclick="setRating(3)">★</span>
                                    <span class="rating-star text-2xl cursor-pointer" onclick="setRating(4)">★</span>
                                    <span class="rating-star text-2xl cursor-pointer" onclick="setRating(5)">★</span>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">What could we improve?</label>
                                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" rows="4" placeholder="Your feedback here..."></textarea>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Would you recommend our service to others?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio h-4 w-4 text-indigo-600" name="recommend" value="yes">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio h-4 w-4 text-indigo-600" name="recommend" value="no">
                                        <span class="ml-2">No</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio h-4 w-4 text-indigo-600" name="recommend" value="maybe">
                                        <span class="ml-2">Maybe</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Email (optional)</label>
                                <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="email@example.com">
                            </div>

                            <div class="flex justify-end">
                                <button class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="submitFeedback()">
                                    Submit Feedback
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Pricing Section -->
    <section id="pricing" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Simple, Transparent Pricing
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Choose the plan that fits your needs.
                </p>
            </div>

            <div class="mt-16 grid gap-8 md:grid-cols-3">
                <!-- Basic Plan -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 transition duration-300 hover:shadow-xl">
                    <div class="px-6 py-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Basic</h3>
                        <p class="text-gray-500 mb-6">For individuals and small teams</p>
                        <p class="text-4xl font-bold text-gray-900 mb-6">$29<span class="text-xl text-gray-500 font-normal">/month</span></p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Up to 5 users</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">10 feedback forms</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Basic analytics</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">QR code generation</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Email support</span>
                            </li>
                        </ul>
                    </div>
                    <div class="px-6 pb-8">
                        <button class="w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Get Started
                        </button>
                    </div>
                </div>

                <!-- Pro Plan -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 border-indigo-500 transform scale-105">
                    <div class="bg-indigo-500 px-6 py-2 text-center">
                        <span class="text-xs font-semibold uppercase tracking-wide text-white">Most Popular</span>
                    </div>
                    <div class="px-6 py-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Professional</h3>
                        <p class="text-gray-500 mb-6">For growing businesses</p>
                        <p class="text-4xl font-bold text-gray-900 mb-6">$79<span class="text-xl text-gray-500 font-normal">/month</span></p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Up to 20 users</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Unlimited feedback forms</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Advanced analytics</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Custom branding</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Priority support</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Data export</span>
                            </li>
                        </ul>
                    </div>
                    <div class="px-6 pb-8">
                        <button class="w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Get Started
                        </button>
                    </div>
                </div>

                <!-- Enterprise Plan -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 transition duration-300 hover:shadow-xl">
                    <div class="px-6 py-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Enterprise</h3>
                        <p class="text-gray-500 mb-6">For large organizations</p>
                        <p class="text-4xl font-bold text-gray-900 mb-6">$199<span class="text-xl text-gray-500 font-normal">/month</span></p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Unlimited users</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Unlimited everything</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Advanced security</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Custom integrations</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Dedicated support</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">SLA guarantees</span>
                            </li>
                        </ul>
                    </div>
                    <div class="px-6 pb-8">
                        <button class="w-full px-4 py-2 bg-white border border-indigo-600 rounded-md shadow-sm text-sm font-medium text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Contact Sales
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 gradient-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                    Ready to transform your feedback process?
                </h2>
                <p class="mt-4 max-w-2xl text-xl text-indigo-100 mx-auto">
                    Start collecting valuable insights from your customers today.
                </p>
                <div class="mt-8 flex justify-center">
                    <div class="inline-flex rounded-md shadow">
                        <a href="#" class="px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50">
                            Get Started for Free
                        </a>
                    </div>
                    <div class="ml-3 inline-flex">
                        <button id="openDemoModalBtn" type="button" class="px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-500 bg-opacity-60 hover:bg-opacity-70 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Schedule a Demo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 12h-2v-2h2v2zm0-4h-2V6h2v4z"></path>
                        </svg>
                        <span class="ml-2 text-xl font-bold text-white">FeedbackPro</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-300">
                        The complete feedback management system for businesses of all sizes.
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Product</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Features</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Pricing</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Security</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Roadmap</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Support</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Documentation</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Guides</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">API Status</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Company</h3>
                    <ul class="mt-4 space-y-4">
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">About</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Blog</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Careers</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Press</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-700 pt-8 md:flex md:items-center md:justify-between">
                <div class="flex space-x-6 md:order-2">
                    <a href="#" class="text-gray-400 hover:text-gray-300">
                        <span class="sr-only">Facebook</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-300">
                        <span class="sr-only">Twitter</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-300">
                        <span class="sr-only">LinkedIn</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5V5c0-2.761-2.238-5-5-5zm-7 22.5h-2v-7h2v7zm-1-8.5a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm8 8h-2v-4.5c0-1.125-.225-2.25-1.5-2.25s-1.5 1.125-1.5 2.25V22h-2v-7h2v1.125c.375-.75 1.125-1.125 1.875-1.125 1.125 0 2.625.75 2.625 3V22z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Demo Modal -->
    <div id="demoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-8 relative">
            <button id="closeDemoModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl font-bold focus:outline-none" aria-label="Close">&times;</button>
            <h3 class="text-2xl font-bold mb-4 text-gray-900">Schedule a Demo</h3>
            <form id="demoRequestForm" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="First Name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Last Name">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                    <input type="text" name="business_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Business Name">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type of Business</label>
                    <select name="business_type" id="businessTypeSelect" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="" disabled selected>Select your business type</option>
                        <option value="Retail">Retail</option>
                        <option value="Education">Education</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="IT Services">IT Services</option>
                        <option value="Manufacturing">Manufacturing</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mt-4 hidden" id="otherBusinessTypeDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Please specify your business type</label>
                    <input type="text" name="other_business_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Type of business">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Email">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                        <input type="text" name="mobile" required pattern="^\d{10}$" maxlength="10" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="10-digit Mobile">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                    <textarea name="comment" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Your comment..."></textarea>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal open/close logic
        const openDemoModalBtn = document.getElementById('openDemoModalBtn');
        const closeDemoModalBtn = document.getElementById('closeDemoModalBtn');
        const demoModal = document.getElementById('demoModal');

        openDemoModalBtn.addEventListener('click', () => {
            demoModal.classList.remove('hidden');
        });
        closeDemoModalBtn.addEventListener('click', () => {
            demoModal.classList.add('hidden');
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') demoModal.classList.add('hidden');
        });
        demoModal.addEventListener('click', (e) => {
            if (e.target === demoModal) demoModal.classList.add('hidden');
        });

        // Show/hide 'Other' business type field
        const businessTypeSelect = document.getElementById('businessTypeSelect');
        const otherBusinessTypeDiv = document.getElementById('otherBusinessTypeDiv');
        businessTypeSelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                otherBusinessTypeDiv.classList.remove('hidden');
                otherBusinessTypeDiv.querySelector('input').required = true;
            } else {
                otherBusinessTypeDiv.classList.add('hidden');
                otherBusinessTypeDiv.querySelector('input').required = false;
            }
        });
    </script>
</body>

</html>