<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('Database/dbConnection.php');
session_start();

// Store redirect target (e.g., ?redirect=addToCart.php?id=12)
if (!isset($_SESSION['redirect_after_login']) && isset($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = $_GET['redirect'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --- Registration Logic ---
    if (isset($_POST['registerForm'])) {
        $full_name = trim($_POST["fullname"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $user_type = 'customer'; // Default user type

        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $_SESSION["error"] = "Email already registered.";
            header("Location: index.php");
            exit;
        }

        // Check if passwords match
        if ($password !== $confirm_password) {
            $_SESSION["error"] = "Passwords do not match.";
            header("Location: index.php");
            exit;
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, hashed_password, user_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $user_type);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $full_name;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['email'] = $email;

            header("Location: userdash.php");
            exit;
        } else {
            $_SESSION["error"] = "Registration failed.";
            header("Location: index.php");
            exit;
        }
    }

    // --- Login Logic ---
    elseif (isset($_POST['loginForm'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['hashed_password'])) {
                // Set session values
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['email'] = $user['email'];

                // Redirect to previous page if set
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $redirect);
                    exit;
                }

                // Otherwise, go to dashboard
                if ($user['user_type'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: userdash.php");
                }
                exit();
            } else {
                $_SESSION["error"] = "Incorrect password.";
                header("Location: index.php");
                exit;
            }
        } else {
            $_SESSION["error"] = "No account found with that email.";
            header("Location: index.php");
            exit;
        }
    }
}
