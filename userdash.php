<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index2.php");
    exit();
}

$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];
$user_type = $_SESSION['user_id'];
 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #f8f9fa, #e3f2fd);
            font-family: 'Segoe UI', sans-serif;
        }

        .dashboard-card {
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header {
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .btn-sell {
            background-color: #198754;
            color: white;
        }

        .btn-sell:hover {
            background-color: #157347;
        }
    </style>
</head>

<body>
    <div class="container mt-5">

        <div class="card dashboard-card p-4">
            <div class="card-body text-center">
                <h2 class="dashboard-header">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

                <div class="mt-4 d-grid gap-3 col-6 mx-auto">
                    <a href="browse.php" class="btn btn-primary btn-lg">🛍️ Browse Products</a>
                    <a href="cart.php" class="btn btn-warning btn-lg">🛒 View Cart</a>
                    <a href="purchase_history.php" class="btn btn-info btn-lg">📜 Purchase History</a>
                    <a href="/Seller/sellerDashoard.php" class="btn btn-sell btn-lg">💼 Start Selling</a>
                    <a href="logout.php" class="btn btn-outline-danger btn-lg">🚪 Logout</a>
                </div>
            </div>
        </div>

    </div>
</body>

</html>