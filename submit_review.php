<?php
session_start();
include('Database/dbConnection.php');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginRegister.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Get and validate input
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($product_id === 0 || $rating < 1 || $rating > 5 || empty($comment)) {
    $_SESSION['message'] = "Invalid review input.";
    header("Location: viewProduct.php?id=" . $product_id);
    exit();
}

// 3. Insert into reviews table
$stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);

if ($stmt->execute()) {
    $_SESSION['message'] = "Thank you for your review!";
} else {
    $_SESSION['message'] = "Error submitting review. Please try again.";
}

// 4. Redirect back to product page
header("Location: viewProduct.php?id=" . $product_id);
exit();
