<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shopelle - Login/Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-MB5H8K5E1H"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-MB5H8K5E1H');
    </script>
    
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #fce4ec);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s, color 0.3s;
        }

        .glass-card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 30px;
            max-width: 420px;
            width: 100%;
            color: #000;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .form-control,
        .btn {
            border-radius: 10px;
        }

        .tab-btn {
            cursor: pointer;
            border: none;
            background: transparent;
            font-weight: bold;
            padding: 10px 15px;
            font-size: 1.1rem;
        }

        .tab-btn.active {
            color: #6c5ce7;
            border-bottom: 2px solid #6c5ce7;
        }

        .dark-mode {
            background: #1e1e1e;
            color: white;
        }

        .dark-mode .glass-card {
            background: rgba(30, 30, 30, 0.6);
            color: white;
        }

        .dark-mode .form-control {
            background-color: #333;
            color: white;
            border: none;
        }

        .dark-toggle {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .shopelle-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: #6c5ce7;
            text-align: center;
            margin-bottom: 20px;
        }

        .forgot-link {
            font-size: 0.9rem;
            color: #6c5ce7;
            text-decoration: none;
            display: inline-block;
            margin-top: 5px;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <i class="bi bi-moon dark-toggle" onclick="toggleDarkMode()" title="Toggle dark mode"></i>

    <div class="glass-card">
        <div class="shopelle-title">Shopelle</div>

        <div class="d-flex justify-content-center mb-3">
            <button class="tab-btn active" onclick="switchTab('login')">Login</button>
            <button class="tab-btn" onclick="switchTab('register')">Register</button>
        </div>

        <!-- LOGIN FORM -->
        <form id="loginForm" method="POST" action="auth.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
            <div class="mb-3">
                <label for="loginEmail" class="form-label">Email address</label>
                <input type="email" class="form-control" id="loginEmail" name="email" required
                    placeholder="Enter your email" />
            </div>
            <div class="mb-3">
                <label for="loginPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="loginPassword" name="password" required
                    placeholder="Enter your password" />
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe" />
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-primary w-100" name="loginForm">Login</button>
        </form>

        <!-- REGISTER FORM -->
        <form id="registerForm" class="d-none" method="POST" action="auth.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
            <div class="mb-3">
                <label for="regName" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="regName" name="fullname" required placeholder="Mary Jane" />
            </div>
            <div class="mb-3">
                <label for="regEmail" class="form-label">Email address</label>
                <input type="email" class="form-control" id="regEmail" name="email" required
                    placeholder="example@domain.com" />
            </div>
            <div class="mb-3">
                <label for="regPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="regPassword" name="password" required />
            </div>
            <div class="mb-3">
                <label for="regConfirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="regConfirmPassword" name="confirm_password" required />
            </div>
            <button type="submit" class="btn btn-success w-100" name="registerForm">Register</button>
        </form>
    </div>

    <script>
        // Switch between login and register forms
        function switchTab(tab) {
            const loginForm = document.getElementById("loginForm"); // Get login form element
            const registerForm = document.getElementById("registerForm"); // Get register form element
            const tabs = document.querySelectorAll(".tab-btn"); // Get tab buttons

            if (tab === "login") {
                loginForm.classList.remove("d-none"); // Show login form
                registerForm.classList.add("d-none"); // Hide register form
                tabs[0].classList.add("active"); // Highlight login tab
                tabs[1].classList.remove("active"); // Unhighlight register tab
            } else {
                registerForm.classList.remove("d-none"); // Show register form
                loginForm.classList.add("d-none"); // Hide login form
                tabs[1].classList.add("active"); // Highlight register tab
                tabs[0].classList.remove("active"); // Unhighlight login tab
            }
        }

        // Toggle dark mode on the page
        function toggleDarkMode() {
            document.body.classList.toggle("dark-mode"); // Add or remove dark-mode class on body
        }
    </script>


</body>

</html>