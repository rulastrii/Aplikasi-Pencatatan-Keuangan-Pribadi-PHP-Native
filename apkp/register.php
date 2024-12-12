<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = hash('sha256', $_POST['password']);
    $email = $_POST['email'];
    $name = $_POST['name'];

    $photo = 'uploads/default.png';
    if (!empty($_FILES['photo']['name'])) {
        $photo = 'uploads/' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    $conn->query("INSERT INTO users (username, password, email, name, photo) 
                  VALUES ('$username', '$password', '$email', '$name', '$photo')");
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Register</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="file" name="photo">
        <button type="submit">Register</button>
    </form>
    <p><a href="login.php">Back to Login</a></p>
</div>
</body>
</html>
