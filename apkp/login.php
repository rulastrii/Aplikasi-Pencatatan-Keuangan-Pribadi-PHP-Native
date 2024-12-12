<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (hash('sha256', $password) === $user['password']) {
            $_SESSION['user'] = $user;
            $_SESSION['message'] = 'Login successful!'; // Set a session message
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body>
<div class="container">
    <h1>Login</h1>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p><a href="register.php">Register</a> | <a href="forgot_password.php">Forgot Password?</a></p>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</div>

<script>
    // Check for PHP variables and show alerts
    <?php if (isset($_SESSION['message'])): ?>
        alert("<?= $_SESSION['message']; ?>");
        <?php unset($_SESSION['message']); // Clear message ?>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        alert("<?= $error; ?>");
    <?php endif; ?>
</script>
</body>
</html>
