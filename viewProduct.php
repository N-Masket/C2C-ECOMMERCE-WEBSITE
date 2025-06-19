<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'Database/dbConnection.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT product_id, product_name, description, category, price, quantity, status FROM products WHERE product_id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) {
    echo "<h2>Product not found</h2>";
    exit;
}

$imgStmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY image_id ASC");
$imgStmt->bind_param('i', $product_id);
$imgStmt->execute();
$images = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$revStmt = $conn->prepare("SELECT r.rating, r.comment, r.created_at, u.full_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$revStmt->bind_param('i', $product_id);
$revStmt->execute();
$reviews = $revStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$avg = count($reviews) > 0 ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['product_name']) ?> | Shopelle</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f2f4f6;
            font-family: 'Segoe UI', sans-serif;
        }

        .product-title {
            font-size: 2rem;
            font-weight: bold;
        }

        .carousel-inner img {
            height: 450px;
            object-fit: cover;
        }

        .star-rating i {
            color: #ffc107;
            cursor: pointer;
        }

        .review-card {
            background: #fff;
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .add-review textarea {
            resize: none;
        }

        .badge-status {
            font-size: 0.9rem;
        }

        .btn {
            border-radius: 0.5rem;
            background: #7A2E88;
            color: white;
            padding: 10px;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <div class="row g-4">
            <!-- Product Images -->
            <div class="col-md-6">
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner rounded shadow-sm">
                        <?php foreach ($images as $i => $img): ?>
                            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                <img src="<?= 'Seller/' . htmlspecialchars($img['image_path']) ?>" class="d-block w-100" alt="Product Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" style="filter: invert(1);"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" style="filter: invert(1);"></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-md-6">
                <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
                <p class="text-muted">Category: <strong><?= htmlspecialchars($product['category']) ?></strong></p>
                <h4 class="text-success mb-3">R<?= number_format($product['price'], 2) ?></h4>
                <p>Status:
                    <span class="badge bg-<?= $product['status'] === 'available' ? 'success' : 'danger' ?> badge-status">
                        <?= ucfirst($product['status']) ?>
                    </span>
                </p>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                <div class="d-flex align-items-center mb-3">
                    <div class="star-rating me-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= $i <= round($avg) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <small class="text-muted">(<?= $avg ?> / 5 from <?= count($reviews) ?> reviews)</small>
                </div>

                <form action="add_to_cart.php" method="post">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" class="form-control w-25 mb-2">
                    <button type="submit" class="btn" ><i class="bi bi-cart-plus"></i> Add to Cart</button>
                </form>

                <button class="btn btn-outline-secondary"><i class="bi bi-heart"></i> Wishlist</button>
            </div>
        </div>

        <!-- Customer Reviews -->
        <div class="row mt-5">
            <div class="col-md-8">
                <h4 class="mb-3">Customer Reviews</h4>
                <?php if ($reviews): foreach ($reviews as $r): ?>
                        <div class="card review-card mb-3 p-3">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($r['full_name']) ?></strong>
                                <small class="text-muted"><?= date('M j, Y', strtotime($r['created_at'])) ?></small>
                            </div>
                            <div class="star-rating mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?= $i <= $r['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                        </div>
                    <?php endforeach;
                else: ?>
                    <p class="text-muted">No reviews yet. Be the first to review!</p>
                <?php endif; ?>
            </div>

            <!-- Review Form -->
            <div class="col-md-4">
                <h5 class="mb-3">Leave a Review</h5>
                <form class="add-review" method="post" action="submit_review.php">
                    <input type="hidden" name="product_id" value="<?= $product_id ?>">

                    <div class="mb-3">
                        <label class="form-label">Your Rating</label>
                        <div id="ratingStars" class="star-rating fs-4">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star" data-value="<?= $i ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" value="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comment</label>
                        <textarea name="comment" class="form-control" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Submit Review</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Star rating input logic
        document.querySelectorAll('#ratingStars .bi').forEach(star => {
            star.addEventListener('click', () => {
                const val = star.getAttribute('data-value');
                document.getElementById('ratingValue').value = val;

                document.querySelectorAll('#ratingStars .bi').forEach(s => {
                    s.className = 'bi ' + (s.getAttribute('data-value') <= val ? 'bi-star-fill' : 'bi-star');
                });
            });
        });
    </script>
</body>

</html>