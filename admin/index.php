<?php
session_start();
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === 'admin' && $password === 'admin123') {

        $_SESSION['admin_logged_in'] = true;

        header("Location: dashboard.php");
        exit();

    } else {
        $error = "Invalid username or password";
    }
}
?>
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Ensemble Lin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <h1 class="login-title">Admin Login</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">
                        Username
                    </label>
                    <input class="form-control"
                           id="username" type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">
                        Password
                    </label>
                    <input class="form-control"
                           id="password" type="password" name="password" required>
                </div>
                
                <div class="flex-split">
                    <button class="btn btn-primary w-full"
                            type="submit">
                        Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 