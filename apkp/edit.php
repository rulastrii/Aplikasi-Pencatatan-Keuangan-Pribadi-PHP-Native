<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM transactions WHERE id = $id AND user_id = " . $_SESSION['user']['id']);
if ($result->num_rows === 0) {
    die("Transaction not found.");
}
$transaction = $result->fetch_assoc();

$saldo_akhir = 0; // Variabel untuk saldo akhir

// Calculate the final balance before handling the form
$query_balance = "SELECT SUM(CASE WHEN type = 'Income' THEN amount ELSE -amount END) AS balance FROM transactions WHERE user_id = ?";
$stmt_balance = $conn->prepare($query_balance);
$stmt_balance->bind_param("i", $_SESSION['user']['id']);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();
$row = $result_balance->fetch_assoc();
$saldo_akhir = $row['balance'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8') : '';

    $photo = $transaction['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $photo = 'uploads/' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    // Update transaction in the database
    $stmt = $conn->prepare("UPDATE transactions SET type = ?, amount = ?, description = ?, photo = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sdssii", $type, $amount, $description, $photo, $id, $_SESSION['user']['id']);
    $stmt->execute();

    // Recalculate the final balance after the update
    $query_balance = "SELECT SUM(CASE WHEN type = 'Income' THEN amount ELSE -amount END) AS balance FROM transactions WHERE user_id = ?";
    $stmt_balance = $conn->prepare($query_balance);
    $stmt_balance->bind_param("i", $_SESSION['user']['id']);
    $stmt_balance->execute();
    $result_balance = $stmt_balance->get_result();
    $row = $result_balance->fetch_assoc();
    $saldo_akhir = $row['balance'];

    // Redirect with a success flag and updated balance
    header("Location: edit.php?id=$id&updated=true&balance=" . urlencode($saldo_akhir));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Edit Transaction</title>
</head>
<body>
<div class="container">
    <h1>Edit Transaction</h1>

    <!-- Success Message -->
    <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
        <script>
            alert("Transaction updated successfully!");
        </script>
    <?php endif; ?>

    <!-- Display Balance -->
    <h3>Current Balance: Rp<?= number_format($saldo_akhir, 2, ',', '.') ?></h3>

    <form method="POST" enctype="multipart/form-data">
        <label for="type">Type:</label>
        <select name="type" id="type" required>
            <option>--Silahkan Pilih--</option>
            <option value="Income" <?= $transaction['type'] === 'Income' ? 'selected' : '' ?>>Income</option>
            <option value="Expense" <?= $transaction['type'] === 'Expense' ? 'selected' : '' ?>>Expense</option>
        </select>
        <label for="amount">Amount:</label>
        <input type="number" name="amount" id="amount" value="<?= htmlspecialchars($transaction['amount'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Amount" required>
        <label for="description">Description:</label>
        <input type="text" name="description" id="description" value="<?= htmlspecialchars($transaction['description'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Description" required>
        <label for="photo">Photo:</label>
        <?php if (!empty($transaction['photo'])): ?>
            <img src="<?= htmlspecialchars($transaction['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="Transaction Photo" class="transaction-photo" onclick="showModal(this.src)">
            <br>
        <?php endif; ?>
        <input type="file" name="photo" id="photo">
        <button type="submit">Update</button>
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
