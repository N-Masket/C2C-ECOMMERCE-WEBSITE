<?php
    session_start();

    // Redirect to login if user is not logged in or not a customer
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: loginRegister.php?redirect=$redirect");
    exit;
    }


    // Database config
    include('Database/dbConnection.php');
    $email_from_db = $_SESSION['email'] ?? ''; // Ensures email is set from session
    $username = $_SESSION['full_name'] ?? 'Customer';

    // Flash message logic(shows temporary feedback messages to the user e.g item added to cart)
    $flash_message = '';
    if (isset($_SESSION['message'])) {
         $flash_message = $_SESSION['message'];
        unset($_SESSION['message']);
    }

    // Handle quantity update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
      $cart_item_id = intval($_POST['cart_item_id']);
      $quantity = max(1, intval($_POST['quantity']));

      $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND user_id = ?");
      $stmt->bind_param("iii", $quantity, $cart_item_id, $_SESSION['user_id']);
      $stmt->execute();

      $_SESSION['message'] = "Quantity updated.";
      header("Location: cart.php");
      exit();
    }

    // Handle item removal
    if (isset($_GET['remove'])) {
        $cart_item_id = intval($_GET['remove']);
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_item_id, $_SESSION['user_id']);
        $stmt->execute();

        $_SESSION['message'] = "Item removed from cart.";
        header("Location: cart.php");
        exit();
    }

    // Fetch cart items
    $stmt = $conn->prepare("
        SELECT ci.cart_item_id, ci.quantity, p.product_name, p.price, pi.image_path
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        JOIN product_images pi ON p.product_id = pi.product_id
        WHERE ci.user_id = ?
        GROUP BY ci.cart_item_id
    ");

    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);

    // Totals
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $shipping_fee = 80.00;
    $total = $subtotal + $shipping_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopelle | Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .cart-header {
            background: linear-gradient(to right, #7f53ac, #647dee);
            color: white;
            padding: 2rem 1rem;
            text-align: center;
        }

        .cart-item {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: white;
        }

        .cart-item img {
            max-width: 100px;
        }

        .summary-box {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 30px;
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            font-size: 1.2rem;
        }

        .btn-checkout {
            background-color: #7f53ac;
            color: white;
        }

        .btn-checkout:hover {
            background-color: #5a3b8e;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="cart-header">
        <h2><i class="bi bi-cart4 me-2"></i>Your Shopping Cart</h2>
    </div>

    <!-- Flash Message -->
    <?php if (!empty($flash_message)): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($flash_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container my-5">
        <?php if (count($cart_items) > 0): ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item d-flex align-items-center justify-content-between flex-wrap">
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <img src="<?= 'Seller/' . htmlspecialchars($item['image_path']) ?>" class="d-block w-100" alt="Product Image">

                                <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                <p class="text-muted mb-1">Price:<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                        <div class="text-end">
                            <form method="post" class="d-flex align-items-center">
                                <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                <input type="number" name="quantity" class="form-control me-2" value="<?php echo $item['quantity']; ?>" min="1" style="width: 70px;">
                                <button type="submit" name="update_quantity" class="btn btn-outline-primary btn-sm me-2"><i class="bi bi-arrow-repeat"></i></button>
                                <a href="cart.php?remove=<?php echo $item['cart_item_id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove this item?');"><i class="bi bi-trash"></i></a>
                            </form>
                        </div>
                </div>
            <?php endforeach; ?>
            </div>

            <!-- Summary Box -->
            <div class="col-lg-4">
                <div class="summary-box">
                    <h5 class="mb-3">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>R<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span>R<?php echo number_format($shipping_fee, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-4">
                        <span>Total</span>
                        <span>R<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-checkout w-100">Proceed to Checkout</a>
                </div>
            </div>
    </div>
<?php else: ?>
    <!-- Empty Cart -->
    <div class="empty-cart">
        <i class="bi bi-bag-x-fill display-3 text-muted"></i>
        <p>Your cart is empty.</p>
        <a href="browse.php" class="btn btn-primary mt-3">Start Shopping</a>
    </div>
<?php endif; ?>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>