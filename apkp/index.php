<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

// Fetch transactions for the logged-in user
$user_id = $_SESSION['user']['id'];
$transactions = $conn->query("SELECT * FROM transactions WHERE user_id = $user_id ORDER BY created_at DESC");

// Ambil pesan dari sesi jika ada
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); // Hapus pesan setelah diambil
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Personal Finance Tracker</title>
    <style>
        .tabs {
            display: flex;
            border-bottom: 1px solid #ccc;
            margin-bottom: 10px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid #ccc;
            border-bottom: none;
            background-color: #f1f1f1;
            margin-right: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .tab.active {
            background-color: #fff;
            font-weight: bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .transaction-photo {
            width: 50px;
            cursor: pointer;
        }

        .profile-photo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid #ccc;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .actions a {
    margin-right: 10px; /* Space between the buttons */
    display: inline-block; /* Align the buttons horizontally */
}

.actions a:last-child {
    margin-right: 0; /* Remove margin on the last button */
}

    </style>
</head>
<body>
<div class="container">
<div class="header" style="text-align: right;">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user']['name']); ?></h1>
        <?php if ($_SESSION['user']['photo']): ?>
            <img src="<?= htmlspecialchars($_SESSION['user']['photo']); ?>" alt="Profile Picture" style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid #ccc; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);">
        <?php else: ?>
            <img src="default-profile.png" alt="Default Profile Picture" style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid #ccc; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);">
        <?php endif; ?>
    <nav>
        <a href="javascript:void(0);" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </nav>
</div>


    <div class="tabs">
        <div class="tab active" data-tab="transactions">
            <i class="fas fa-list"></i> Transactions
        </div>
        <div class="tab" data-tab="profile">
            <i class="fas fa-user"></i> Profile
        </div>
    </div>

    <div id="transactions" class="tab-content active">
        <h2>Transaction List</h2>
        <a href="create.php" class="btn btn-success" style="margin-bottom: 20px;">
        <i class="fas fa-plus-circle"></i> Create Transaction
</a>

        <?php if ($transactions->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Photo</th>
                        <th rowspan="2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['created_at']); ?></td>
                            <td><?= htmlspecialchars($row['type']); ?></td>
                            <td><?= number_format($row['amount'], 2); ?></td>
                            <td><?= htmlspecialchars($row['description']); ?></td>
                            <td>
                            <?php if ($row['photo']): ?>
                                <img src="<?= htmlspecialchars($row['photo']); ?>" alt="Transaction Photo" class="transaction-photo" onclick="showModal(this.src)">
                            <?php else: ?>
                                <img src="uploads/default.png" alt="Default Transaction Photo" class="transaction-photo" onclick="showModal(this.src)">
                            <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a> |
                                <a href="delete.php?id=<?= $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No transactions found. <a href="create.php">Add a transaction</a></p>
        <?php endif; ?>
    </div>

    <div id="profile" class="tab-content">
        <h2>Your Profile</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($_SESSION['user']['name']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['user']['email']); ?></p>
        <a href="profile.php"><i class="fas fa-edit"></i>Edit Profile</a>
    </div>
</div>

<!-- Modal for zooming images -->
<div id="imageModal" class="modal" onclick="closeModal()">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelector('.tab.active').classList.remove('active');
            tab.classList.add('active');

            document.querySelector('.tab-content.active').classList.remove('active');
            const tabContent = document.getElementById(tab.dataset.tab);
            tabContent.classList.add('active');
        });
    });

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

    function confirmLogout() {
        const confirmLogout = confirm("Are you sure you want to log out?");
        if (confirmLogout) {
            window.location.href = "logout.php";  // Redirect to logout page
        }
    }

    // Show login success message if it exists
    <?php if ($message): ?>
        alert("<?= $message; ?>");
    <?php endif; ?>
</script>
</body>
</html>