<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Our Site</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9f9f9;
            color: #333;
        }

        header {
            background: url('images/banner.jpg') center/cover no-repeat;
            color: white;
            text-align: center;
            padding: 100px 20px;
            position: relative;
        }

        header::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
        }

        header h1,
        header p,
        .btn {
            position: relative;
            z-index: 1;
        }

        header h1 {
            margin: 0;
            font-size: 3rem;
        }

        header p {
            font-size: 1.3rem;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            background: white;
            color: #007BFF;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn:hover {
            background: #0056b3;
            color: white;
        }

        section {
            padding: 40px 20px;
            max-width: 1000px;
            margin: auto;
        }

        .features {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .feature {
            flex: 1 1 30%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .feature img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .about {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 20px;
        }

        .about img {
            flex: 1 1 40%;
            max-width: 100%;
            border-radius: 10px;
        }

        .about p {
            flex: 1 1 55%;
        }

        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .features {
                flex-direction: column;
            }

            .about {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>Welcome to Our Platform</h1>
        <p>Your gateway to smart user management and secure access</p>
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn">Register</a>
    </header>

    <section>
        <h2 style="text-align:center;">Our Features</h2>
        <div class="features">
            <div class="feature">
                <img src="images/feature1.png" alt="User Roles">
                <h3>User Roles</h3>
                <p>Admin, Moderator, and User role-based access control system.</p>
            </div>
            <div class="feature">
                <img src="images/feature2.png" alt="Secure Login">
                <h3>Secure Login</h3>
                <p>Google Sign-in with Firebase Authentication ensures security.</p>
            </div>
            <div class="feature">
                <img src="images/feature3.png" alt="Session Control">
                <h3>Session Control</h3>
                <p>Automatic session expiration with countdown and access lock.</p>
            </div>
        </div>
    </section>

    <section>
        <h2 style="text-align:center;">About Us</h2>
        <div class="about">
            <img src="images/about.jpg" alt="About Us">
            <p>
                We are passionate about building fast, secure, and easy-to-use platforms for businesses and users.
                Our mission is to provide complete user control, secure authentication, and responsive systems
                suitable for modern digital environments.
            </p>
        </div>
    </section>

    <div class="footer">
        &copy; <?= date("Y") ?> YourCompany. All rights reserved.
    </div>

</body>

</html>