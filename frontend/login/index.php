<?php require '../../backend/config/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — UEP LES Scheduling System</title>
    <!-- Use standard font to match mockup style -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time()?>">
    <link rel="stylesheet" href="style.css?v=<?= time()?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="split-layout">
        <!-- Left Side: Login Form (Dark/Light) -->
        <div class="split-left">
            <div class="header-info">
                <div class="branding">
                    <h1 class="brand-title">University of Eastern Philippines<br>Laboratory Elementary School<br><span
                            class="brand-subtitle">Scheduling System</span></h1>
                </div>
                <div class="live-clock" id="liveClock">
                    <!-- Clock populated by JS -->
                </div>
            </div>

            <div class="login-wrapper">
                <div class="login-header">
                    <h2>Login</h2>
                    <p>Enter your account details</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= e($_GET['error'])?>
                </div>
                <?php
endif; ?>

                <form action="../../backend/auth/login.php" method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken()?>">

                    <div class="form-group custom-group">
                        <input type="text" name="username" class="form-control minimal-input" placeholder="Username"
                            required autofocus>
                    </div>

                    <div class="form-group custom-group">
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-control minimal-input"
                                placeholder="Password" required>
                            <i class="fa-solid fa-eye-slash icon-right" id="togglePassword"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login">
                        Login
                    </button>

                </form>
            </div>

            <div class="login-footer">
                &copy;
                <?= date('Y')?> UEP Laboratory Elementary School
            </div>
        </div>

        <!-- Right Side: Graphic/Welcome (Purple) -->
        <div class="split-right" id="logoContainer">
            <div class="right-content">
                <h1 class="welcome-title">UEPLES<br>Scheduling System</h1>
                <p class="welcome-subtitle">Login to access your account</p>

                <!-- Hero Graphic (Large Logo) -->
                <div class="hero-graphic">
                    <div class="blob blob-1"></div>
                    <div class="blob blob-2"></div>
                </div>
            </div>

            <!-- Draggable Logo Interactive Element -->
            <img src="../assets/images/uep-logo.png" alt="UEP Logo Hero" class="hero-logo" id="bouncingLogo">
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>