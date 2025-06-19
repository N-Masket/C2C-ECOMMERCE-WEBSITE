<?php
session_start();
include('../Database/dbConnection.php'); // DB connection using PDO

$userId = $_SESSION['user_id']; // Current user
$categoryOptions = [];
$catQuery = mysqli_query($conn, "SELECT DISTINCT category FROM products WHERE user_id = $userId");
while ($row = mysqli_fetch_assoc($catQuery)) {
    $categoryOptions[] = $row['category'];
}

// Handle Add Product
if (isset($_POST['addProduct'])) {
    $productName = mysqli_real_escape_string($conn, $_POST['productName']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $categoryName = mysqli_real_escape_string($conn, $_POST['category']); // This is the category name
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);

    // Get the corresponding category_id from the categories table
    $catQuery = "SELECT id FROM categories WHERE name = '$categoryName' LIMIT 1";
    $catResult = mysqli_query($conn, $catQuery);

    if ($catRow = mysqli_fetch_assoc($catResult)) {
        $categoryId = $catRow['id'];

        // Insert into products table with both category and category_id
        $query = "INSERT INTO products (
                    user_id, product_name, description, category, category_id, price, quantity, date_added, status, is_trending
                  ) 
                  VALUES (
                    $userId, '$productName', '$description', '$categoryName', $categoryId, $price, $quantity, NOW(), 1, 0
                  )";

        if (mysqli_query($conn, $query)) {
            $productId = mysqli_insert_id($conn);

            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                    $fileName = basename($_FILES['images']['name'][$index]);
                    $targetPath = "uploads/" . time() . '_' . $fileName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $imgQuery = "INSERT INTO product_images (product_id, image_path) VALUES ($productId, '$targetPath')";
                        mysqli_query($conn, $imgQuery);
                    }
                }
            }
        } else {
            echo "Error adding product: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid category name — check that it exists in the categories table.";
    }

    header("Location: products.php");
    exit;
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Delete images for the product
    $imgRes = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = $id");
    while ($row = mysqli_fetch_assoc($imgRes)) {
        if (file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
    }
    mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $id");

    // Delete product
    mysqli_query($conn, "DELETE FROM products WHERE product_id = $id AND user_id = $userId");

    header("Location: products.php");
    exit;
}

// Fetch all user's products
$where = "WHERE user_id = $userId";
if (!empty($_GET['min_price'])) {
    $min = floatval($_GET['min_price']);
    $where .= " AND price >= $min";
}
if (!empty($_GET['max_price'])) {
    $max = floatval($_GET['max_price']);
    $where .= " AND price <= $max";
}
if (!empty($_GET['category'])) {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $where .= " AND category LIKE '%$category%'";
}
if (!empty($_GET['date_added'])) {
    $date = mysqli_real_escape_string($conn, $_GET['date_added']);
    $where .= " AND DATE(date_added) = '$date'";
}

$productsRes = mysqli_query($conn, "SELECT * FROM products $where ORDER BY date_added DESC");


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<style>
    body {
        background-color: #1A355B;
    }

    h1 {
        color: white;
        text-align: center;
        font-size: 50px;
    }

    .product-card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .product-card img {
        height: 200px;
        object-fit: cover;
    }

    .product-card .card-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
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


<body class="container py-5">
    <!-- Back Arrow Button -->
    <button onclick="history.back()" class="back-btn">← Back</button>

    <h1>Your Products</h1>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="number" step="0.01" name="min_price" class="form-control" placeholder="Min Price" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="number" step="0.01" name="max_price" class="form-control" placeholder="Max Price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="date_added" class="form-control" value="<?= htmlspecialchars($_GET['date_added'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categoryOptions as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($_GET['category']) && $_GET['category'] === $cat) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>

        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="products.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="row">
        <?php while ($product = mysqli_fetch_assoc($productsRes)): ?>
            <?php
            $pid = $product['product_id'];
            $imgRes = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = $pid");
            $images = [];
            while ($img = mysqli_fetch_assoc($imgRes)) {
                $images[] = $img;
            }
            ?>
            <div class="col-12 col-sm-6 col-md-3 d-flex">
                <div class="card mb-5 product-card w-100">
                    <?php if (count($images) > 0): ?>
                        <img src="<?= htmlspecialchars($images[0]['image_path']) ?>" class="card-img-top" alt="Product Image">
                    <?php else: ?>
                        <img src="placeholder.jpg" class="card-img-top" alt="No Image">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                        <p>Category: <?= htmlspecialchars($product['category']) ?></p>
                        <p>Price: R<?= number_format($product['price'], 2) ?></p>
                        <p>Qty: <?= intval($product['quantity']) ?></p>
                        <div class="mt-auto">
                            <button
                                class="btn btn-sm btn-warning edit-btn"
                                data-id="<?= $product['product_id'] ?>"
                                data-name="<?= htmlspecialchars($product['product_name']) ?>"
                                data-description="<?= htmlspecialchars($product['description']) ?>"
                                data-category="<?= htmlspecialchars($product['category']) ?>"
                                data-price="<?= htmlspecialchars($product['price']) ?>"
                                data-quantity="<?= htmlspecialchars($product['quantity']) ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#editProductModal">
                                Edit
                            </button>

                            <a href="?delete=<?= $product['product_id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this product?')">Delete</a>
                        </div>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" enctype="multipart/form-data" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="productName" class="form-control mb-2" placeholder="Product Name" required />
                    <textarea name="description" class="form-control mb-2" placeholder="Description" required></textarea>
                    <?php
                    $catptions = [];
                    $catQuery = mysqli_query($conn, "SELECT id, name FROM categories");
                    while ($row = mysqli_fetch_assoc($catQuery)) {
                        $catoptions[] = $row;
                    }
                    ?>
                    <select name="category" id="category" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($catoptions as $cat): ?>
                            <option value="<?= $cat['id']; ?>">
                                <?= htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="price" class="form-control mb-2" placeholder="Price" step="0.01" min="0" required />
                    <input type="number" name="quantity" class="form-control mb-2" placeholder="Quantity" min="0" required />
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*" />
                </div>
                <div class="modal-footer">
                    <button type="submit" name="addProduct" class="btn btn-success">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="edit_name" id="edit_name" class="form-control mb-2" placeholder="Product Name" required>
                    <textarea name="edit_description" id="edit_description" class="form-control mb-2" placeholder="Description" required></textarea>
                    <input type="text" name="edit_category" id="edit_category" class="form-control mb-2" placeholder="Category" required>
                    <input type="number" name="edit_price" id="edit_price" class="form-control mb-2" placeholder="Price" step="0.01" min="0" required>
                    <input type="number" name="edit_quantity" id="edit_quantity" class="form-control mb-2" placeholder="Quantity" min="0" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="updateProduct" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_name').value = this.dataset.name;
                document.getElementById('edit_description').value = this.dataset.description;
                document.getElementById('edit_category').value = this.dataset.category;
                document.getElementById('edit_price').value = this.dataset.price;
                document.getElementById('edit_quantity').value = this.dataset.quantity;
            });
        });
    </script>

</body>

</html>