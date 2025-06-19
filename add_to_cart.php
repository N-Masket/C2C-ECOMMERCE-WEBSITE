<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: loginRegister.php?redirect=$redirect");
    exit;
}

$user_id = $_SESSION['user_id'];

include('Database/dbConnection.php');


$product_id = intval($_POST['product_id']);
$quantity = max(1, intval($_POST['quantity']));

// Check if item already in cart
$stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Update quantity if already in cart
    $new_quantity = $row['quantity'] + $quantity;
    $update = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
    $update->bind_param("ii", $new_quantity, $row['cart_item_id']);
    $update->execute();
} else {
    // Insert new cart item
    $insert = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $user_id, $product_id, $quantity);
    $insert->execute();
}

//redirect to cart with success message
$_SESSION['message'] = "Item added to cart.";
header("Location: cart.php");
exit;
