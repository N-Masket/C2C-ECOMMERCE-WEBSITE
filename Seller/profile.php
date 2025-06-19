<?php
// 1. Database connection
$servername = "localhost";  // Database server (usually localhost)
$username = "root";         // Database username
$password = "";             // Database password (leave empty for default)
$dbname = "sa-ecomm";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Fetch seller's profile data (assuming seller is logged in, and their ID is available)
$seller_id = 1;  // Example: Fetch profile for seller with ID 1, replace with actual session data or authentication method

$sql = "SELECT * FROM sellers WHERE seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);  // Bind the seller_id parameter
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();  // Fetch the data as an associative array

// 3. Handle profile image upload if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle the profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $image_name = $_FILES['profile_image']['name'];
        $image_tmp_name = $_FILES['profile_image']['tmp_name'];
        $image_size = $_FILES['profile_image']['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        // Validate image type (example: only allow JPG and PNG)
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($image_ext), $allowed_extensions)) {
            // Generate a unique filename and move the image
            $image_new_name = uniqid() . '.' . $image_ext;
            $image_upload_path = 'uploads/' . $image_new_name;
            move_uploaded_file($image_tmp_name, $image_upload_path);

            // Update the seller's profile image in the database
            $update_sql = "UPDATE sellers SET profile_image = ? WHERE seller_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $image_new_name, $seller_id);
            $update_stmt->execute();

            // Refresh the page to display the updated profile image
            header("Location: seller_profile.php");
            exit();
        }
    }

    // 4. Handle other profile fields update (Name, Email, Phone, etc.)
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $business_name = $_POST['business_name'];
    $address = $_POST['address'];
    $bank_account_number = $_POST['bank_account_number'];
    $bank_name = $_POST['bank_name'];
    $business_description = $_POST['business_description'];

    // Update profile fields in the database
    $update_sql = "UPDATE sellers SET full_name = ?, email = ?, phone_number = ?, business_name = ?, address = ?, bank_account_number = ?, bank_name = ?, business_description = ? WHERE seller_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssssi", $full_name, $email, $phone_number, $business_name, $address, $bank_account_number, $bank_name, $business_description, $seller_id);
    $update_stmt->execute();

    // Refresh the page to display updated profile data
    header("Location: seller_profile.php");
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            background-color: #0d6efd;
        }

        .navbar-brand,
        .nav-link {
            color: #fff !important;
        }

        .profile-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 16px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .profile-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .mb-3 label {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">My Store</a>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Seller Profile</h2>
        <div class="profile-card">
            <form method="POST" action="seller_profile.php" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <!-- Display current profile image if exists -->
                        <?php if ($seller['profile_image']) : ?>
                        <img src="uploads/<?php echo $seller['profile_image']; ?>" alt="Profile Image"
                            class="profile-image">
                        <?php else : ?>
                        <img src="uploads/default.jpg" alt="Profile Image" class="profile-image">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image"
                            onchange="previewImage(event)">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name"
                        value="<?php echo htmlspecialchars($seller['full_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($seller['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number"
                        value="<?php echo htmlspecialchars($seller['phone_number']); ?>">
                </div>
                <div class="mb-3">
                    <label for="business_name" class="form-label">Business Name</label>
                    <input type="text" class="form-control" id="business_name" name="business_name"
                        value="<?php echo htmlspecialchars($seller['business_name']); ?>">
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address"
                        rows="3"><?php echo htmlspecialchars($seller['address']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="bank_account_number" class="form-label">Bank Account Number</label>
                    <input type="text" class="form-control" id="bank_account_number" name="bank_account_number"
                        value="<?php echo htmlspecialchars($seller['bank_account_number']); ?>">
                </div>
                <div class="mb-3">
                    <label for="bank_name" class="form-label">Bank Name</label>
                    <input type="text" class="form-control" id="bank_name" name="bank_name"
                        value="<?php echo htmlspecialchars($seller['bank_name']); ?>">
                </div>
                <div class="mb-3">
                    <label for="business_description" class="form-label">Business Description</label>
                    <textarea class="form-control" id="business_description" name="business_description"
                        rows="3"><?php echo htmlspecialchars($seller['business_description']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>