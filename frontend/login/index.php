<?php require '../../backend/config/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — UEP LES System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-container">
        <!-- Decoration -->
        <div class="glow glow-1"></div>
        <div class="glow glow-2"></div>

        <div class="login-card">
            <div class="login-header">
                <div class="logo-box">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h2>Welcome Back</h2>
                <p>Sign in to UEP LES Scheduling System</p>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= e($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="../../backend/auth/login.php" method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="form-group custom-group">
                    <label class="form-label">Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user icon-left"></i>
                        <input type="text" name="username" class="form-control with-icon" placeholder="Enter your username" required autofocus>
                    </div>
                </div>

                <div class="form-group custom-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock icon-left"></i>
                        <input type="password" id="password" name="password" class="form-control with-icon" placeholder="Enter your password" required>
                        <i class="fa-solid fa-eye icon-right" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    Sign In <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <div class="login-footer">
                &copy; <?= date('Y') ?> UEP Laboratory Elementary School
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>