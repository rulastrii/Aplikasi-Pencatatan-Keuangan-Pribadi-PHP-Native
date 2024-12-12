<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$user_id = $_SESSION['user']['id'];
$message = ""; // Inisialisasi variabel pesan

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $description = $_POST['description'];
    $photo = null;

    // Handle file upload
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['photo']['name']);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo = $target_file;
        } else {
            echo "Error uploading file.";
            exit;
        }
    }

    $query = "INSERT INTO transactions (user_id, type, amount, description, photo, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isdss", $user_id, $type, $amount, $description, $photo);

    if ($stmt->execute()) {
        $message = "Transaction added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Transaction</title>
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
        <h1>Add Transaction</h1>
        <form action="create.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="type">Type:</label>
                <select name="type" id="type" required>
                    <option>--Silahkan Pilih--</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" name="amount" id="amount" step="0.01" placeholder="Amount" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <input type="text" name="description" id="description" rows="4" placeholder="Description" required>
            </div>

            <div class="form-group">
                <label for="photo">Photo:</label>
                <input type="file" name="photo" id="photo" placeholder="Photo">
            </div>

            <div class="form-actions">
                <button type="submit">Submit</button>
                <a href="index.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>