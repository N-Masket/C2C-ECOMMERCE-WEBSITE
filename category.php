<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shopelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Open+Sans&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css">


    <!-- Animate.css for subtle text/image animation -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'af,en,zu,xh,st,tn,nso,ve,ts,nr,ss',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
        }
    </script>
    <script type="text/javascript"
        src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <style>
        /* Shop by Category Section */
        .category-section {
            background: yellowgreen;
            color: white;
            padding: 50px 0;
            border-radius: 80px;
            border: #0d47a1;
            margin: 50px;
        }

        .category-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .category-scroll {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 0 20px;
            scroll-snap-type: x mandatory;
        }

        .category-scroll::-webkit-scrollbar {
            display: none;
        }

        .category-card {
            flex: 0 0 auto;
            width: 250px;
            height: 300px;
            background-color: #fff;
            border-radius: 15px;
            color: #333;
            scroll-snap-align: start;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-8px);
        }

        .category-card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .category-body {
            padding: 15px;
            text-align: center;
        }

        .category-icon {
            font-size: 1.8rem;
            color: #007bff;
        }

        .category-name {
            font-weight: 600;
            font-size: 1.2rem;
            margin-top: 10px;
        }

        .category-desc {
            font-size: 0.9rem;
            color: #555;
        }

        .view-btn {
            margin-top: 10px;
            border-radius: 20px;
            background-color: #007bff;
            color: white;
            padding: 5px 20px;
            transition: background 0.3s;
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <?php include 'includes/nav.php'; ?>


    <!-- Placeholder for Search Results -->
    <div class="container my-4">
        <h5 id="searchResult" class="text-muted"></h5>
    </div>
    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar: Filters -->
            <?php
            // Grab current filter values or set defaults
            $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
            $min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : '';
            $max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : '';
            ?>

            <div class="col-md-3">
                <h4>Filter Products</h4>
                <form method="GET">
                    <!-- Keep category filter fixed -->
                    <input type="hidden" name="category_id" value="<?= $category_id ?>">

                    <div class="form-group">
                        <label for="min_price">Min Price</label>
                        <input type="number" id="min_price" name="min_price" class="form-control" placeholder="Min Price" value="<?= htmlspecialchars($min_price) ?>">
                    </div>

                    <div class="form-group">
                        <label for="max_price">Max Price</label>
                        <input type="number" id="max_price" name="max_price" class="form-control" placeholder="Max Price" value="<?= htmlspecialchars($max_price) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary mt-2">Apply Filters</button>
                    <!-- Reset button -->
                    <a href="category.php?category_id=<?= $category_id ?>" class="btn btn-secondary mt-2 ml-2">Reset Filters</a>
                </form>
            </div>

            <?php
            include('Database/dbConnection.php');


            // Initialize variables
            $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
            $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
            $min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
            $max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? floatval($_GET['max_price']) : 9999999;


            // Build the SQL query based on filters
            $sql = "SELECT products.*, 
                        (SELECT image_path 
                        FROM product_images 
                        WHERE product_id = products.product_id LIMIT 1) AS image_path 
                    FROM products 
                    WHERE 1=1";

            // Add search filter
            // Add filters
            if (!empty($search)) {
                $sql .= " AND products.name LIKE '%" . $conn->real_escape_string($search) . "%'";
            }

            if ($category_id > 0) {
                $sql .= " AND products.category_id = $category_id";
            }

            $sql .= " AND products.price BETWEEN $min_price AND $max_price";

            // Execute query
            $result = $conn->query($sql);
            ?>

            <div class="col-md-9">
                <?php
                if ($result && $result->num_rows > 0) {
                    echo "<div class='row'>";
                    while ($row = $result->fetch_assoc()) {
                        $product_id = $row['product_id'];

                        $imgStmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY image_id ASC LIMIT 1");
                        $imgStmt->bind_param('i', $product_id);
                        $imgStmt->execute();
                        $imgResult = $imgStmt->get_result();
                        $imgRow = $imgResult->fetch_assoc();

                        $imagePath = $imgRow && !empty($imgRow['image_path']) ? 'Seller/' . $imgRow['image_path'] : 'images/default.jpg';
                ?>

                        <div class='col-md-3 mb-4'>
                            <div class='card product-card shadow-sm'>
                                <img src="<?= htmlspecialchars($imagePath) ?>" class='card-img-top' alt="<?= htmlspecialchars($row['product_name']) ?>"
                                    onerror="this.onerror=null; this.src='images/default.jpg';">
                                <div class='card-body'>
                                    <h5 class='card-title text-truncate'><?= htmlspecialchars($row['product_name']) ?></h5>
                                    <p class='card-text'>R<?= htmlspecialchars($row['price']) ?></p>
                                    <a href='viewProduct.php?id=<?= $row['product_id'] ?>' class='btn btn-primary btn-sm'>View</a>
                                </div>
                            </div>
                        </div>

                <?php
                    }
                    echo "</div>";
                } else {
                    echo "<p>No products match your filters.</p>";
                }
                ?>

            </div>
        </div>
    </div>
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

</body>

</html>