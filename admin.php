<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('Database/dbConnection.php'); 
// Handle role update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['user_type'])) {
    $userId = (int)$_POST['user_id'];
    $newRole = $_POST['user_type'] === 'admin' ? 'admin' : 'customer'; // sanitize input

    $stmt = $conn->prepare("UPDATE users SET user_type = ? WHERE user_id = ?");
    $stmt->bind_param("si", $newRole, $userId);
    if ($stmt->execute()) {
        echo "User role updated successfully.";
    } else {
        echo "Error updating role: " . $stmt->error;
    }
    $stmt->close();
}
// Fetch all users
$result = $conn->query("SELECT user_id, full_name, email, user_type FROM users");


// 2. Handle Add Slide
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slide'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $order_number = $_POST['order_number'];
    $text_color = $_POST['text_color'];

    $image = $_FILES['background_image']['name'];
    $target = "Media/" . basename($image);

    if (move_uploaded_file($_FILES['background_image']['tmp_name'], $target)) {
        $stmt = $conn->prepare("INSERT INTO slides (title, description, button_text, button_link, background_image, text_color, order_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $title, $description, $button_text, $button_link, $image, $text_color, $order_number);
        $stmt->execute();
    }
}

// 3. Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM slides WHERE id=$id");
    header("Location: admin-carousel.php");
    exit;
}

// 4. Fetch Slides
$slides = $conn->query("SELECT * FROM slides ORDER BY order_number ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Carousel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            padding: 2rem;
        }

        .carousel-preview img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
        }

        .hero-carousel-section {
            position: relative;
            overflow: hidden;
        }

        .hero-slide {
            min-height: 60vh;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;

        }

        .hero-btn {
            transition: all 0.4s ease;
            font-weight: 600;
        }

        .hero-btn:hover {
            background-color: #0d6efd;
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .hero-slide {
                text-align: center;
                background-attachment: scroll;
            }
        }

        header {
            background: #0d47a1;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            padding: 40px;
            max-width: 1000px;
            margin: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: white;
            background-color: #7A2E88;
            font-size: 50px;
        }


        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        form input[type="text"],
        form textarea,
        form input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        form button {
            background: #0d47a1;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: 0.3s;
        }

        form button:hover {
            background: #09407e;
        }

        .category-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .category-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .category-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .category-card .info {
            padding: 15px;
        }

        .category-card .info h4 {
            margin: 10px 0 5px;
        }

        .category-card .info p {
            font-size: 0.9rem;
            color: #555;
        }
    </style>
</head>

<body>
    <header>
        <h1>Admin Panel SHOPELLE MANAGEMENT</h1>
    </header>

    <div class="container mt-5">
        <h2>User Role Management</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Current Role</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>

                <?php while ($user = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['user_type']) ?></td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>" />
                                <select name="user_type" class="form-select form-select-sm d-inline-block w-auto" required>
                                    <option value="customer" <?= $user['user_type'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                    <option value="admin" <?= $user['user_type'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="container">
        <h2 class="mb-4">Carousel Slide Manager</h2>
        <form method="POST" enctype="multipart/form-data" class="mb-5 p-4 bg-white shadow-sm rounded">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Button Link</label>
                    <input type="text" name="button_link" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <input type="number" name="order_number" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Text Color</label>
                    <input type="color" name="text_color" class="form-control" value="#ffffff">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Background Image</label>
                    <input type="file" name="background_image" class="form-control" required>
                </div>
            </div>
            <button type="submit" name="add_slide" class="btn btn-primary mt-3"><i class="bi bi-plus-circle"></i> Add Slide</button>
        </form>

        <h4 style="background-color: #7A2E88;color:white">Slide Preview</h4>
        <div id="heroCarousel" class="carousel slide carousel-fade carousel-preview hero-carousel-section mb-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $first = true;
                $slides->data_seek(0);
                while ($slide = $slides->fetch_assoc()) {
                    $active = $first ? 'active' : '';
                    echo "
                <div class='carousel-item $active hero-slide' style='background-image:url(Media/{$slide['background_image']});'>
                    <div class='d-flex align-items-center justify-content-center' style='height:300px;'>
                        <div class='text-center text-white' style='color: {$slide['text_color']}'>
                            <h3>{$slide['title']}</h3>
                            <p>{$slide['description']}</p>
                            <a href='{$slide['button_link']}' class='btn btn-light hero-btn'>{$slide['button_text']}</a>
                        </div>
                    </div>
                </div>";
                    $first = false;
                }
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>

        <h4 style="background-color: #7A2E88;color:white">Manage Slides</h4>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Order</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $slides->data_seek(0);
                while ($slide = $slides->fetch_assoc()) {
                    echo "<tr>
                <td>{$slide['order_number']}</td>
                <td>{$slide['title']}</td>
                <td>{$slide['description']}</td>
                <td><img src='Media/{$slide['background_image']}' width='100'></td>
                <td>
                    <a href='edit-slide.php?id={$slide['id']}' class='btn btn-sm btn-warning'><i class='bi bi-pencil'></i></a>
                    <a href='?delete={$slide['id']}' onclick='return confirm(\"Delete this slide?\")' class='btn btn-sm btn-danger'><i class='bi bi-trash'></i></a>
                </td>
            </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <header>
        <h1>Admin Panel - Manage Categories</h1>
    </header>

    <div class="container">
        <h2>Add New Category</h2>
        <form method="POST" enctype="multipart/form-data" action="">
            <input type="text" name="name" placeholder="Category Name" required>
            <input type="text" name="icon_class" placeholder="Icon Class (e.g., bi bi-bag-fill)">
            <textarea name="description" placeholder="Short Description"></textarea>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit" name="add_category">Add Category</button>
        </form>

        <h2>Current Categories</h2>
        <div class="category-list">
            <?php
            include('Database/dbConnection.php'); // your DB connection file

            if (isset($_POST['delete_category'])) {
                $delete_id = $_POST['delete_id'];

                // Optionally delete the image file
                $getImage = $conn->prepare("SELECT image FROM categories WHERE id = ?");
                $getImage->bind_param("i", $delete_id);
                $getImage->execute();
                $result = $getImage->get_result();
                if ($row = $result->fetch_assoc()) {
                    $image_path = "Media/" . $row['image'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }

                // Delete from database
                $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->bind_param("i", $delete_id);
                $stmt->execute();
            }



            if (isset($_POST['add_category'])) {
                $name = $_POST['name'];
                $icon = $_POST['icon_class'];
                $description = $_POST['description'];

                $image = $_FILES['image']['name'];
                $target = "Media/" . basename($image);
                move_uploaded_file($_FILES['image']['tmp_name'], $target);

                $stmt = $conn->prepare("INSERT INTO categories (name, description, icon_class, image) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $description, $icon, $image);
                $stmt->execute();
            }

            $categories = $conn->query("SELECT * FROM categories ORDER BY created_at DESC");
            while ($cat = $categories->fetch_assoc()) {
                echo "
                <div class='category-card'>
                    <img src='Media/{$cat['image']}' alt='{$cat['name']}'>
                    <div class='info'>
                        <div><i class='{$cat['icon_class']}' style='font-size: 1.5rem; color: #0d47a1;'></i></div>
                        <h4>{$cat['name']}</h4>
                        <p>{$cat['description']}</p>
                           <div>
                <!-- Edit Icon -->
                <a href='edit_category.php?id={$cat['id']}' title='Edit' style='margin-right: 10px; color: green;'>
                    <i class='bi bi-pencil-square' style='font-size: 1.2rem;'></i>
                </a>
                <!-- Delete Icon -->
                <form method='POST' style='display: inline;' onsubmit='return confirm(\"Are you sure you want to delete this category?\");'>
                    <input type='hidden' name='delete_id' value='{$cat['id']}'>
                    <button type='submit' name='delete_category' style='background: none; border: none; color: red;'>
                        <i class='bi bi-trash' style='font-size: 1.2rem;'></i>
                    </button>
                </form>
            </div>
                    </div>
                </div>
                ";
            }
            ?>
        </div>
    </div>
    <div>
        <?php
        include('Database/dbConnection.php'); // your DB connection file

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // First, reset all products to not trending
            $conn->query("UPDATE products SET is_trending = 0");

            // Then, set selected ones to trending
            if (!empty($_POST['trending_ids'])) {
                $ids = $_POST['trending_ids'];
                $ids_string = implode(',', array_map('intval', $ids));
                $conn->query("UPDATE products SET is_trending = 1 WHERE id IN ($ids_string)");
                echo "<div style='color: green; font-weight: bold;'>Trending products updated!</div>";
            }
        }

        // Fetch products to display
        $result = $conn->query("
    SELECT 
        p.*,
        (SELECT pi.image_path 
         FROM product_images pi 
         WHERE pi.product_id = p.product_id 
         LIMIT 1) AS image_path
    FROM 
        products p
");


        ?>

        <h2>Select Trending Products</h2>
        <form method="POST" action="">
            <table border="1" cellpadding="10">
                <tr>
                    <th>Trending</th>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="trending_ids[]" value="<?php echo $row['product_id']; ?>"
                                <?php if ($row['is_trending']) echo 'checked'; ?>>
                        </td>
                        <td>
                            <img src="Seller/<?php echo !empty($row['image_path']) ? $row['image_path'] : 'images/default.jpg'; ?>" width="80">
                        </td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td>R<?php echo number_format($row['price'], 2); ?></td>
                    </tr>
                <?php } ?>
            </table>
            <br>
            <input type="submit" value="Update Trending Products">
        </form>
    </div>
    <!-- admin_trending_banner.php -->
    <?php
    // DB connection assumed as $conn
    $msg = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $text = $conn->real_escape_string($_POST['trending_text']);
        $color = $conn->real_escape_string($_POST['trending_color']);

        $conn->query("UPDATE site_settings SET trending_text='$text', trending_color='$color' WHERE id=1");

        // ✅ Refresh the settings after update
        $msg = "<p style='color:green;'>Banner updated successfully!</p>";
    }

    // ✅ Always fetch latest settings
    $setting = $conn->query("SELECT * FROM site_settings WHERE id = 1")->fetch_assoc();
    ?>

    <!-- Admin Form -->
    <?= $msg ?>
    <form method="post" class="p-4 border rounded bg-light">
        <div class="mb-3">
            <label for="trending_text" class="form-label">Trending Banner Text</label>
            <textarea class="form-control" id="trending_text" name="trending_text" rows="3"><?= htmlspecialchars($setting['trending_text']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="trending_color" class="form-label">Text Color</label>
            <input type="color" class="form-control form-control-color" id="trending_color" name="trending_color" value="<?= $setting['trending_color'] ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Banner</button>
    </form>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>