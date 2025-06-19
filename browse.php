<?php
include_once 'Database/dbConnection.php';
$slides = $conn->query("SELECT * FROM slides ORDER BY order_number ASC");

session_start();
?>



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
        :root {
            --primary-color: #7a2e88;
            --secondary-color: #f8f9fa;
            --dark-bg: #121212;
            --light-text: #ffffff;
        }

        body {
            background-color: whitesmoke;
            transition: background-color 0.3s, color 0.3s;
        }

        .dark-mode {
            background-color: var(--dark-bg);
            color: var(--light-text);
        }

        .navbar {
            transition: all 0.3s ease-in-out;
            background-color: #7a2e88;

        }

        .navbar-brand {
            font-size: 2rem;
            font-weight: bold;
            color: #1A355B;
        }

        .navbar-dark-mode {
            background-color: #1f1f1f !important;
        }

        .btn-outline-dark i,
        .btn-outline-light i {
            transition: transform 0.2s ease-in-out;
        }

        .btn-outline-dark:hover i,
        .btn-outline-light:hover i {
            transform: rotate(20deg);
        }

        .navbar .nav-link,
        .navbar,
        .navbar i {
            color: white !important;
            font-size: 15px;
            font-weight: bolder;
        }

        .navbar-brand {
            font-size: 30px;
            font-weight: bolder;
            color: white !important;
        }

        /*search bar*/
        .search-bar {
            display: flex;
            width: 100%;
            max-width: 700px;
            background: #fff;
            border-radius: 50px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .search-bar input {
            border: none;
            outline: none;
            flex: 1;
            padding: 0.75rem 1rem;
        }

        .search-bar select,
        .search-bar button {
            border: none;
            background: transparent;
            padding: 0.75rem;
        }

        .search-bar select {
            border-left: 1px solid blueviolet;
            border-right: 1px solid blueviolet;
        }

        .category-card img {
            height: 150px;
            object-fit: cover;
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem 0;
        }

        .footer a {
            color: #f0e6ff;
            text-decoration: none;
        }

        .dark-toggle {
            cursor: pointer;
        }

        #google_translate_element select {
            background-color: #fff;
            border: 1px solid #ccc;
            color: #333;
            padding: 5px;
            font-size: 1rem;
            border-radius: 5px;
        }

        #google_translate_element {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /*/* Hero Section */
        .hero-carousel-section {
            position: relative;
            overflow: hidden;
        }

        .hero-slide {
            min-height: 70vh;
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

        /* Shop by Category Section */
        .category-section {
            background: yellowgreen;
            color: white;
            padding: 50px 0;
            border-radius: 80px;
            border: #0d47a1;
            margin: 40px;
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
            height: 120px;
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
            margin-top: 5px;
            border-radius: 20px;
            background-color: #007bff;
            color: white;
            padding: 5px 20px;
            transition: background 0.3s;
        }

        /* Trending Products Section */
        .trending-section {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 60px 0;
            margin: 0px 100px;
        }

        .section-title {
            font-size: 60px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 40px;
            color: #0d47a1;
        }

        .carousel-item img {
            margin: 0 auto;
            border-radius: 15px;
        }

        .product-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 280px;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
        }

        .product-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-details {
            padding: 15px;
            text-align: center;
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 10px 0;
            color: #333;
        }

        .product-price {
            font-size: 1rem;
            color: #1e88e5;
            margin-bottom: 15px;
        }

        .product-actions .btn {
            margin: 5px;
            border-radius: 20px;
        }

        .carousel-item {
            height: 500px;
        }

        .carousel-item img {
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light  px-3 py-2 shadow-sm" id="mainNavbar">
        <a class="navbar-brand fw-bold" href="#">Shopelle</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
            aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <!-- Search bar with filtering -->
            <form class="search-bar mx-auto my-2 my-lg-0" onsubmit="event.preventDefault(); filterSearch()">
                <input type="text" id="searchInput" placeholder="Search products..." aria-label="Search" />
                <select id="filterSelect">
                    <option value="all">All Categories</option>
                    <option value="electronics">Electronics</option>
                    <option value="clothing">Clothing</option>
                    <option value="beauty">Beauty</option>
                    <option value="home">Home</option>
                </select>
                <button class="btn" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <!-- Links and Actions -->
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item mx-2"><a class="nav-link" href="#"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="Seller/logout.php"><i class="bi bi-person-circle"></i>
                        Logout</a></li>
                <li class="nav-item mx-2"><a class="nav-link" href="cart.php"><i class="bi bi-cart"></i> Cart</a>
                </li>
                <li class="nav-item mx-2" id="google_translate_element"></li>
                <li class="nav-item mx-2">
                    <button class="btn btn-outline-dark" id="darkToggleBtn" onclick="toggleDarkMode()"
                        title="Toggle Dark Mode"><i class="bi bi-moon-stars"></i></button>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Placeholder for Search Results -->
    <div class="container">
        <h5 id="searchResult" class="text-muted"></h5>
    </div>

    <!-- Hero Section -->
    <section class="hero-carousel-section">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">
                <?php
                $first = true;
                while ($slide = $slides->fetch_assoc()) {
                    $active = $first ? 'active' : '';
                    echo "
                <div class='carousel-item $active'>
                    <div class='hero-slide d-flex align-items-center text-white img-fluid'
                         style='background-image: url(\"Media/{$slide['background_image']}\");'>
                        <div class='container text-start'>
                            <div class='animate__animated animate__fadeInUp'>
                                <h1 class='display-4 fw-bold' style='font-family: \"Playfair Display\", serif; color: {$slide['text_color']};'>{$slide['title']}</h1>
                                <p class='lead' style='font-family: \"Open Sans\", sans-serif; color: {$slide['text_color']};'>{$slide['description']}</p>
                                <a href='{$slide['button_link']}' class='btn btn-lg btn-light hero-btn mt-3'>{$slide['button_text']}</a>
                            </div>
                        </div>
                    </div>
                </div>";
                    $first = false;
                }
                ?>
            </div>
        </div>
    </section>


    <!-- SHOP BY CATEGORY -->
    <section class="category-section">
        <div class="container">
            <h2 class="category-title">Shop by Category</h2>
            <div class="category-scroll">
                <?php
                $categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
                while ($cat = $categories->fetch_assoc()) {
                    echo "
                <div class='category-card'>
                    <img src='Media/{$cat['image']}' alt='{$cat['name']}'class='img-fluid' style:object-fit:cover;'>
                    <div class='category-body'>
                        <div class='category-icon'><i class='{$cat['icon_class']}'></i></div>
                        <div class='category-name'>{$cat['name']}</div>
                        <div class='category-desc'>{$cat['description']}</div>
                        <a href='category.php?category_id={$cat['id']}'>
                           <button class='btn view-btn'>View</button>
                        </a>

                    </div>
                </div>
                ";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Trending Products Section -->
    <section class="trending-section py-5" style="background-color: #008080; color: white;">
        <div class="container">
            <div class="trending-banner text-center py-3 mb-5 rounded"
                style="color:#121212;font-size: 60px; font-weight: bolder;">
                🔥 Check out this week's Trending Products!
            </div>

            <div class="row g-4">
                <!-- Product Card Template -->
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow border-0">
                        <div class="position-relative">
                            <img src="Media/sneaker.webp" class="card-img-top" alt="Product Image">
                            <i class="bi bi-heart-fill position-absolute top-0 end-0 m-2 text-danger fs-5"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Stylish sneaker</h5>
                            <p class="text-primary fs-6 mb-1">R499.00</p>
                            <div class="d-flex gap-2">
                                <a href="viewProduct.php?id=5" class="btn btn-primary btn-sm w-50"><i class="bi bi-eye"></i> View</a>
                                <button class="btn btn-outline-dark btn-sm w-50"><i class="bi bi-cart"></i> Add</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Duplicate the above card with different content for 4 more products -->
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow border-0">
                        <div class="position-relative">
                            <img src="Media/brushes.jpg" class="card-img-top" alt="Product Image">
                            <i class="bi bi-heart-fill position-absolute top-0 end-0 m-2 text-danger fs-5"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Facial clensing brushes</h5>
                            <p class="text-primary fs-6 mb-1">R85</p>
                            <div class="d-flex gap-2">
                                <a href="viewProduct.php?id=20" class="btn btn-primary btn-sm w-50"><i class="bi bi-eye"></i> View</a>
                                <button class="btn btn-outline-dark btn-sm w-50"><i class="bi bi-cart"></i> Add</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow border-0">
                        <div class="position-relative">
                            <img src="Media/cushions.jpg" class="card-img-top" alt="Product Image">
                            <i class="bi bi-heart-fill position-absolute top-0 end-0 m-2 text-danger fs-5"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Cushions</h5>
                            <p class="text-primary fs-6 mb-1">R500.00</p>
                            <div class="d-flex gap-2">
                                <a href="viewProduct.php?id=13" class="btn btn-primary btn-sm w-50"><i class="bi bi-eye"></i> View</a>
                                <button class="btn btn-outline-dark btn-sm w-50"><i class="bi bi-cart"></i> Add</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow border-0">
                        <div class="position-relative">
                            <img src="Media/bag.webp" class="card-img-top" alt="Product Image">
                            <i class="bi bi-heart-fill position-absolute top-0 end-0 m-2 text-danger fs-5"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Urban Backpack</h5>
                            <p class="text-primary fs-6 mb-1">R349.00</p>
                            <div class="d-flex gap-2">
                                <a href="viewProduct.php?id=17" class="btn btn-primary btn-sm w-50"><i class="bi bi-eye"></i> View</a>
                                <button class="btn btn-outline-dark btn-sm w-50"><i class="bi bi-cart"></i> Add</button>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </section>

    <section class="py-5" style="background-color: #e0f7f7;">
        <div class="container">
            <h2 class="text-center text-dark mb-4 fw-bold">Winter Essentials</h2>

            <!-- Scrollable container -->
            <div class="d-flex overflow-auto gap-3" style="scrollbar-width: thin; scroll-behavior: smooth;">

                <!-- Product Card 1 -->
                <div class="card shadow-sm" style="min-width: 320px; height: 420px; flex-shrink: 0;">
                    <img src="Media/1729174240fa93553f7d3e25b091ad8fefdc22d7a0_thumbnail_405x.webp" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Winter Jacket">
                    <div class="card-body text-center">
                        <h5 class="card-title">Winter Jacket</h5>
                        <p class="card-text text-muted">Insulated warmth with urban style.</p>
                        <p class="fw-bold">325.00</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="viewProduct.php?id=8" class="btn btn-info">View</a>
                            <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Product Card 2 -->
                <div class="card shadow-sm" style="min-width: 320px; height: 420px; flex-shrink: 0;">
                    <img src="Media/coffe.webp" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Scarf">
                    <div class="card-body text-center">
                        <h5 class="card-title">Coffee Machine</h5>
                        <p class="card-text text-muted">Classic Coffee Maker.</p>
                        <p class="fw-bold">R500.00</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="viewProduct.php?id=21" class="btn btn-info">View</a>
                            <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                        </div>
                    </div>
                </div>
                <!-- Product Card 3 -->
                <div class="card shadow-sm" style="min-width: 320px; height: 420px; flex-shrink: 0;">
                    <img src="Media/shopping (1).webp" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Beanie Hat">
                    <div class="card-body text-center">
                        <h5 class="card-title">Beanie Hat</h5>
                        <p class="card-text text-muted">Soft, stylish & essential for winter.</p>
                        <p class="fw-bold">R129.00</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="viewProduct.php?id=10" class="btn btn-info">View</a>
                            <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                        </div>
                    </div>
                </div>


                <!-- Product Card 4-->
                <div class="card shadow-sm" style="min-width: 320px; height: 420px; flex-shrink: 0;">
                    <img src="Media/boots2.webp" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Beanie Hat">
                    <div class="card-body text-center">
                        <h5 class="card-title">Winter Boots</h5>
                        <p class="card-text text-muted">Soft, stylish & essential for winter.</p>
                        <p class="fw-bold">R295.00</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="viewProduct.php?id=22" class="btn btn-info">View</a>
                            <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm" style="min-width: 320px; height: 420px; flex-shrink: 0;">
                    <img src="Media/kitchen.webp" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Beanie Hat">
                    <div class="card-body text-center">
                        <h5 class="card-title">3L Kettle</h5>
                        <p class="card-text text-muted">Enjoy a warm cup of tea or coffe this winter</p>
                        <p class="fw-bold"></p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="viewProduct.php?id=12" class="btn btn-info">View</a>
                            <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </section>


    <!-- Product Slider -->
    <div id="multiItemCarousel" class="carousel slide container my-5" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <!-- First slide with 3 cards -->
            <div class="carousel-item active">
                <h1 style="color:#008080;text-align:center;font-size:30px bolder;">NEW ARRIVALS </h1>
                <div class="row">


                    <!-- Product Card 1 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <img src="Media/pots.webp" class="card-img-top card-body" alt="...">
                            <div class="card-body">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="viewProduct.php?id=11" class="btn btn-info">View</a>
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Product Card 2 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <img src="Media/bedding.jpg" class="card-img-top card-body" alt="...">
                            <div class="card-body">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="viewProduct.php?id=14" class="btn btn-info">View</a>
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Product Card 3 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <img src="Media/beauty.jpg" class="card-img-top card-body" style="height:380px" alt="...">
                            <div class="card-body">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="viewProduct.php?id=11" class="btn btn-info">View</a>
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second slide with 3 more cards -->
            <div class="carousel-item">
                <h1 style="color:red;font-size:50px bolder;text-align:center">FLASH DEALS ON WATCHS </h1>
                <div class="row">
                    <!-- Product Card 4 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <img src="Media/Smart watch.jpeg" class="card-img-top " style="height:350px;" alt="...">
                            <div class="card-body">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="viewProduct.php?id=15" class="btn btn-info">View</a>
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Product Card 5 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <img src="Media/smartwarch black.webp" class="card-img-top card-body" style="height:350px;" alt=" ...">
                            <div class="card-body">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="viewProduct.php?id=16" class="btn btn-info">View</a>
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Product Card 6 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <img src="Media/1.3_41abe673-b924-480d-bc7e-7f31047186c1.webp" class="card-img-top card-body" style="height:350px;" alt=" ...">
                            <div class="card-body">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="viewProduct.php?id=1" class="btn btn-info">View</a>
                                    <button class="btn btn-primary btn-sm"><i class="bi bi-cart"></i></button>
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-heart"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <section class="py-5 bg-light text-center">
        <div class="container">
            <div class="mb-4">
                <i class="bi bi-envelope-paper-fill display-4 text-primary"></i>
                <h2 class="fw-bold mt-3">Stay Updated!</h2>
                <p class="text-muted">Subscribe to our newsletter and never miss the latest deals, products & tips.</p>
            </div>
            <form class="row justify-content-center g-2" onsubmit="subscribeNewsletter(event)">
                <div class="col-sm-8 col-md-6 col-lg-4">
                    <input type="email" class="form-control form-control-lg" id="newsletterEmail"
                        placeholder="Enter your email..." required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send-fill"></i> Subscribe</button>
                </div>
            </form>
            <div id="newsletterAlert" class="mt-3" style="display: none;">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Subscribed!</strong> You've been added to our list. 🥳
                    <button type="button" class="btn-close"
                        onclick="document.getElementById('newsletterAlert').style.display='none';"></button>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function subscribeNewsletter(event) {
            event.preventDefault();
            // You can replace this with actual backend logic
            document.getElementById('newsletterEmail').value = '';
            document.getElementById('newsletterAlert').style.display = 'block';
        }
    </script>





    <section class="bg-warning py-5 text-dark">
        <div class="container text-center">
            <h2 class="mb-4">Why Shopelle?</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <i class="bi bi-truck display-5"></i>
                    <h5 class="mt-2">Fast Delivery</h5>
                    <p class="small">Get your items quickly and reliably.</p>
                </div>
                <div class="col-md-3">
                    <i class="bi bi-shield-lock display-5"></i>
                    <h5 class="mt-2">Secure Payments</h5>
                    <p class="small">Your data and money are safe with us.</p>
                </div>
                <div class="col-md-3">
                    <i class="bi bi-emoji-smile display-5"></i>
                    <h5 class="mt-2">Customer Support</h5>
                    <p class="small">Friendly support whenever you need it.</p>
                </div>
                <div class="col-md-3">
                    <i class="bi bi-tags display-5"></i>
                    <h5 class="mt-2">Great Deals</h5>
                    <p class="small">Save big on your favorite items every day.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="text-light bg-dark pt-5 pb-4">
        <div class="container text-md-left">
            <div class="row text-md-left">

                <!-- Brand Info -->
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold text-info">Shopelle</h5>
                    <p>Discover top deals, stylish picks, and curated finds all in one place. Shopelle brings convenience
                        and charm to your shopping journey.</p>
                </div>

                <!-- Useful Links -->
                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold text-info">Quick Links</h5>
                    <p><a href="checkout.php" class="text-light text-decoration-none">Home</a></p>
                    <p><a href="about.html" class="text-light text-decoration-none">About</a></p>
                    <p><a href="#" class="text-light text-decoration-none">Shop</a></p>
                    <p><a href="#" class="text-light text-decoration-none">Categories</a></p>
                    <p><a href="#" class="text-light text-decoration-none">Contact</a></p>
                </div>

                <!-- Contact Info -->
                <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold text-info">Contact</h5>
                    <p><i class="bi bi-house-fill me-2"></i> 123 Market Street, Cityville</p>
                    <p><i class="bi bi-envelope-fill me-2"></i> support@shopelle.com</p>
                    <p><i class="bi bi-telephone-fill me-2"></i> +123 456 7890</p>
                </div>

                <!-- Socials -->
                <div class="col-md-4 col-lg-4 col-xl-4 mx-auto mt-3">
                    <h5 class="text-uppercase mb-4 font-weight-bold text-info">Stay Connected</h5>
                    <p>Follow us on social platforms to get the latest updates and promotions.</p>
                    <div>
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook fs-4"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram fs-4"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter fs-4"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-youtube fs-4"></i></a>
                    </div>
                </div>

            </div>

            <!-- Divider -->
            <hr class="mb-4 mt-4">

            <!-- Copyright -->
            <div class="row align-items-center">
                <div class="col-md-7 col-lg-8">
                    <p class="text-light">© 2025 Shopelle. All Rights Reserved.</p>
                </div>
                <div class="col-md-5 col-lg-4">
                    <p class="text-end">
                        <a href="#" class="text-light text-decoration-none">Privacy Policy</a> |
                        <a href="#" class="text-light text-decoration-none">Terms</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Dark Mode
        document.querySelector('.dark-toggle').addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
        });
    </script>
</body>

</html>