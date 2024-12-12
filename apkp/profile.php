<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$message = ""; // Initialize message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'])) {
        // Update name and photo
        $name = $_POST['name'];
        $photo = $_SESSION['user']['photo'];

        if (!empty($_FILES['photo']['name'])) {
            $photo = 'uploads/' . basename($_FILES['photo']['name']);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
                $_SESSION['user']['photo'] = $photo; // Update session photo if upload succeeds
            } else {
                $message = "Failed to upload photo.";
            }
        }

        $stmt = $conn->prepare("UPDATE users SET name = ?, photo = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $photo, $_SESSION['user']['id']);
        $stmt->execute();

        $_SESSION['user']['name'] = $name;
        $message = "Profile updated successfully!";
    }

    if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
        // Update password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user']['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (hash('sha256', $current_password) === $user['password']) {
            if ($new_password === $confirm_password) {
                $hashed_password = hash('sha256', $new_password);

                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $_SESSION['user']['id']);
                $stmt->execute();

                $message = "Password updated successfully!";
            } else {
                $message = "New passwords do not match.";
            }
        } else {
            $message = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <script>
        function showMessage(message) {
            if (message) {
                alert(message);
            }
        }
    </script>
</head>
<body onload="showMessage('<?= $message ?>')">
<div class="container">
    <h1>Your Profile</h1>

    <!-- Update Profile Form -->
    <form method="POST" enctype="multipart/form-data">
        <h2>Update Profile</h2>
        <p>Full Name:</p>
        <input type="text" name="name" value="<?= $_SESSION['user']['name'] ?>" placeholder="Full Name" required>
        <p>Current Photo:</p>
        <img src="<?= $_SESSION['user']['photo'] ?>" alt="Current Photo" width="100" height="100" onclick="showModal(this.src)">
        <input type="file" name="photo">
        <button type="submit">Update Profile</button>
    </form>

    <!-- Update Password Form -->
    <form method="POST">
        <h2>Update Password</h2>
        <p>Current Password:</p>
        <input type="password" name="current_password" placeholder="Current Password" required>
        <p>New Password:</p>
        <input type="password" name="new_password" placeholder="New Password" required>
        <p>Confirm New Password:</p>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit">Update Password</button>
    </form>

    <p><a href="index.php">Back</a></p>
</div>
<!-- Modal for zooming images -->
<div id="imageModal" class="modal" onclick="closeModal()">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
    function showModal(src) {
        const modal = document.getElementById("imageModal");
        const modalImage = document.getElementById("modalImage");
        modal.style.display = "block";
        modalImage.src = src;
    }

    function closeModal() {
        const modal = document.getElementById("imageModal");
        modal.style.display = "none";
    }
</script>
</body>
</html>
