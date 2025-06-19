<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: index.php");
    exit;
}

// Database config
include('Database/dbConnection.php');

$email = isset($_SESSION['email']) ? trim($_SESSION['email']) : '';

$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();
}

// Shipping fee
define("SHIPPING_FEE", 8.00);
$shipping_fee = SHIPPING_FEE;

// Fetch cart items
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT ci.cart_item_id, ci.quantity, p.product_id, p.product_name, p.price
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal + $shipping_fee;

$error = '';
$shipping_address = '';
$payment_method = '';
$card_number = '';
$card_expiry = '';
$card_cvc = '';
$street = '';
$city = '';
$province = '';
$postal_code = '';
$country = '';




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form fields
    $street = isset($_POST['street']) ? trim($_POST['street']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $province = isset($_POST['province']) ? trim($_POST['province']) : '';
    $postal_code = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

    // Combine into one address string
    $shipping_address = $street . ", " . $city . ", " . $province . ", " . $postal_code . ", " . $country;
}
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

    // Basic validation
    if (empty($street) || empty($city) || empty($province) || empty($postal_code) || empty($country)) {
        $error = "Please complete all address fields.";
    } elseif (empty($cart_items)) {
        $error = "Your cart is empty.";
    } elseif (!in_array($payment_method, ['card', 'paypal', 'cod'])) {
        $error = "Please select a valid payment method.";
    } else {
        // If card is selected, validate card details
        if ($payment_method === 'card') {
            $card_number = isset($_POST['card_number']) ? trim($_POST['card_number']) : '';
            $card_expiry = isset($_POST['card_expiry']) ? trim($_POST['card_expiry']) : '';
            $card_cvc = isset($_POST['card_cvc']) ? trim($_POST['card_cvc']) : '';

            if (empty($card_number) || empty($card_expiry) || empty($card_cvc)) {
                $error = "Please fill in all card details.";
            } elseif (!preg_match('/^\d{13,19}$/', str_replace(' ', '', $card_number))) {
                $error = "Invalid card number format.";
            } elseif (!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
                $error = "Invalid expiry date format. Use MM/YY.";
            } elseif (!preg_match('/^\d{3,4}$/', $card_cvc)) {
                $error = "Invalid CVC format.";
            }
        }
    }

    if (empty($error)) {
        $conn->begin_transaction();

        try {
            // Insert order - store payment method and card last4 if card
            $card_last4 = '';
            if ($payment_method === 'card') {
                $card_last4 = substr(preg_replace('/\D/', '', $card_number), -4);
            }

            $stmt = $conn->prepare("
                INSERT INTO orders (user_id, total, shipping_fee, shipping_address, payment_method, card_last4)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iddsss", $user_id, $total, $shipping_fee, $shipping_address, $payment_method, $card_last4);
            $stmt->execute();
            $order_id = $conn->insert_id;

            // Insert order items
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($cart_items as $item) {
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }

            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $conn->commit();
            $subject = "Order Confirmation - Shopelle";
            $message = "Thank you for your order! Your order ID is #" . $order_id . ".\nTotal: $" . number_format($total, 2);
            $headers = "From: no-reply@shopelle.com";

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                mail($email, $subject, $message, $headers);
            } else {
                // handle the error -ec maybe log it or show a message 
                echo "Invalid email address.";
            }


            // Optionally send to store too:
            mail("store@shopelle.com", "New Order Received", "Order ID: #" . $order_id, $headers);

            // Redirect flow for PayPal - here you’d redirect to PayPal payment page.
            if ($payment_method === 'paypal') {
                // Redirect to PayPal payment gateway (this is a placeholder)
                header("Location: paypal_redirect.php?order_id=" . $order_id);
                exit();
            } else {
                // For card and COD, go to confirmation page
                echo '<div class="confirmation">';
                echo '<h2>Order placed successfully!</h2>';
                echo '<p>Thank you for shopping with us.</p>';
                echo '<a href="index.html"><button>Continue Shopping</button></a>';
                echo '</div>';
                exit();

                exit();
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "An error occurred while processing your order: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Shopelle | Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .checkout-header {
            background: linear-gradient(to right, #7f53ac, #647dee);
            color: white;
            padding: 2rem 1rem;
            text-align: center;
        }

        .order-summary,
        .shipping-form {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .btn-place-order {
            background-color: #7f53ac;
            color: white;
        }

        .btn-place-order:hover {
            background-color: #5a3b8e;
        }
    </style>

</head>

<body>

    <div class="checkout-header">
        <h2><i class="bi bi-credit-card me-2"></i>Checkout</h2>
    </div>

    <div class="container my-5">
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Order Summary -->
            <div class="col-lg-6">
                <div class="order-summary">
                    <h5 class="mb-3">Order Summary</h5>
                    <?php if (count($cart_items) > 0) : ?>
                        <ul class="list-group mb-3">
                            <?php foreach ($cart_items as $item) : ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="my-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                        <small class="text-muted">Quantity: <?php echo htmlspecialchars($item['quantity']); ?></small>
                                    </div>
                                    <span class="text-muted">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Subtotal</span>
                                <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Shipping</span>
                                <strong>$<?php echo number_format($shipping_fee, 2); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total</span>
                                <strong>$<?php echo number_format($total, 2); ?></strong>
                            </li>
                        </ul>
                    <?php else : ?>
                        <p>Your cart is empty.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Shipping & Payment Form -->
            <div class="col-lg-6">
                <div class="shipping-form">
                    <h5 class="mb-3">Shipping Details</h5>
                    <form method="post" novalidate>
                        <div class="mb-3">
                            <label for="street" class="form-label">Street Address</label>
                            <input type="text" class="form-control" id="street" name="street" required value="<?php echo htmlspecialchars($street); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required value="<?php echo htmlspecialchars($city); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="province" class="form-label">Province/State</label>
                            <input type="text" class="form-control" id="province" name="province" required value="<?php echo htmlspecialchars($province); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required value="<?php echo htmlspecialchars($postal_code); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" required value="<?php echo htmlspecialchars($country); ?>">
                        </div>


                        <h5 class="mb-3 mt-4">Payment Method</h5>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_card" value="card" <?php echo ($payment_method === 'card' || $payment_method === '') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="pay_card">Credit/Debit Card</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_paypal" value="paypal" <?php echo ($payment_method === 'paypal') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="pay_paypal">PayPal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cod" <?php echo ($payment_method === 'cod') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="pay_cod">Cash on Delivery</label>
                            </div>
                        </div>

                        <div id="card-details" style="display:none;">
                            <h6 class="mb-3">Card Details</h6>
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" value="<?php echo htmlspecialchars($card_number); ?>">
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="card_expiry" class="form-label">Expiry (MM/YY)</label>
                                    <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" value="<?php echo htmlspecialchars($card_expiry); ?>">
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="card_cvc" class="form-label">CVC</label>
                                    <input type="text" class="form-control" id="card_cvc" name="card_cvc" placeholder="123" value="<?php echo htmlspecialchars($card_cvc); ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-place-order w-100 mt-3">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($orderSuccess) && $orderSuccess): ?>
        <script>
            alert("Order received!"); // Show confirmation alert
            window.location.href = "checkout.php"; // Redirect to checkout page
        </script>
    <?php endif; ?>

    <script>
        // Show or hide card payment details based on selected payment method
        function toggleCardDetails() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            const cardDetails = document.getElementById('card-details');
            if (paymentMethod === 'card') {
                cardDetails.style.display = 'block'; // Show card details form
            } else {
                cardDetails.style.display = 'none'; // Hide card details form
            }
        }

        // Add event listeners to payment method radio buttons after page loads
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('input[name="payment_method"]');
            radios.forEach(radio => {
                radio.addEventListener('change', toggleCardDetails);
            });
            toggleCardDetails(); // Set initial visibility on page load
        });
    </script>


</body>

</html>