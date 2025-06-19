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
            <li class="nav-item mx-2"><a class="nav-link" href="loginRegister.php"><i class="bi bi-person-circle"></i>
                    Login</a></li>
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