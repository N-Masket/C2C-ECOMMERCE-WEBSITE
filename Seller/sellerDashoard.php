<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: index.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
include('../Database/dbConnection.php');
// 1. Total Monthly Sales (Current Month)
$currentMonth = date('m');
$currentYear = date('Y');
$stmt = $conn->prepare("                                                                       
    SELECT SUM(oi.quantity * oi.price_at_purchase) AS total_sales
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE p.user_id = ? AND MONTH(o.order_date) = ? AND YEAR(o.order_date) = ?
");
$stmt->bind_param("iii", $seller_id, $currentMonth, $currentYear);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$monthlySales = $row['total_sales'] ?? 0.00;
$stmt->close();

// 2. Total Orders
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id) AS total_orders
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE p.user_id = ?
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalOrders = $row['total_orders'] ?? 0;
$stmt->close();

// 3. Total Products
$stmt = $conn->prepare("SELECT COUNT(*) AS total_products FROM products WHERE user_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalProducts = $row['total_products'] ?? 0;
$stmt->close();

// 4. Pending Orders 
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id) AS pending_orders
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE p.user_id = ? AND o.status = 'Pending'
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pendingOrders = $row['pending_orders'] ?? 0;
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Seller Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
        body {
            background: #1A355B;
            color: white;
            min-height: 100vh;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border-left: 4px solid #fff;
            color: white;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        h1,h5,p {
            color: white;
        }

        #sidebar .nav-link {
            color: #ffffffcc;
            transition: all 0.2s ease-in-out;
        }

        #sidebar .nav-link:hover {
            background-color: #495057;
            color: #fff;
            padding-left: 1.2rem;
        }

        .back-btn {
            background-color: #f2f2f2;
            border: none;
            color: #333;
            font-size: 16px;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #ddd;
        }
    </style>

</head>

<body>
    <!-- Back Arrow Button -->
    <button onclick="history.back()" class="back-btn">← Back</button>

    <div class="d-flex">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar d-flex flex-column p-3">
            <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4">Seller Panel</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="products.php" class="nav-link text-white">
                        <i class="bi bi-box-seam me-2"></i>
                        Products
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="nav-link text-white">
                        <i class="bi bi-cart-check me-2"></i>
                        Orders
                    </a>
                </li>
                <li>
                    <a href="analytics.php" class="nav-link text-white">
                        <i class="bi bi-graph-up me-2"></i>
                        Analytics
                    </a>
                </li>
                <li>
                    <a href="profile.php" class="nav-link text-white">
                        <i class="bi bi-person me-2"></i>
                        Profile
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
        <!-- Logout Confirmation Modal -->

        <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="logoutModalLabel"><i class="bi bi-box-arrow-right me-2"></i>Confirm Logout
                        </h5>


                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4 class="warning" style="color: #DC3545;">Are you sure you want to log out of your session?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <a href="Seller/logout.php" class="btn btn-danger">Yes, Log Out</a>
                    </div>
                </div>
            </div>


        </div>


        <!-- Page Content -->
        <div class="container py-5">
            <h1 class="text-center mb-4">Welcome to Your Seller Dashboard</h1>
            <p class="text-center mb-5">Manage your products, orders, and sales efficiently.</p>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card dashboard-card p-3" style="background: #0D6EFD">
                        <h5>Monthly Sales</h5>
                        <p class="display-6">R<?php echo number_format($monthlySales, 2); ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card p-3" style="background: #198754">
                        <h5>Orders</h5>
                        <p class="display-6"><?php echo $totalOrders; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card p-3" style="background:#FFC107">
                        <h5>Total Products</h5>
                        <p class="display-6"><?php echo $totalProducts; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card p-3" style="background: #DC3545">
                        <h5>Pending Orders</h5>
                        <p class="display-6"><?php echo $pendingOrders; ?></p>
                    </div>
                </div>

            </div>

            <!-- Toggle Sidebar Script -->
            <script>
                document.getElementById("toggleSidebar").addEventListener("click", function() {
                    document.getElementById("sidebar").classList.toggle("active");
                });
            </script>
        </div>
</body>

</html>