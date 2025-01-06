<?php
session_start(); // Start the session to access electrician data

// Check if the electrician is logged in
if (!isset($_SESSION['electrician_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

include('../dbConnect.php'); // Include your database connection file

// Retrieve the logged-in electrician's ID from the session
$electrician_id = $_SESSION['electrician_id'];

// Fetch the electrician's current details
$query = "SELECT name, email, phone_number FROM electricians WHERE electrician_id = :electrician_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':electrician_id', $electrician_id, PDO::PARAM_INT);
$stmt->execute();
$electrician = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$electrician) {
    echo "Electrician not found.";
    exit();
}

// Initialize variables to handle form submissions and errors
$name = $electrician['name'];
$email = $electrician['email'];
$phone_number = $electrician['phone_number'];
$password = '';
$confirm_password = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (empty($phone_number) || !preg_match('/^\d{10,15}$/', $phone_number)) {
        $errors[] = "Valid phone number is required (10-15 digits).";
    }
    if (!empty($password) && ($password !== $confirm_password)) {
        $errors[] = "Passwords do not match.";
    }

    // If no errors, update the details
    if (empty($errors)) {
        $update_query = "UPDATE electricians SET name = :name, email = :email, phone_number = :phone_number";
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $update_query .= ", password_hash = :password_hash";
        }
        $update_query .= " WHERE electrician_id = :electrician_id";

        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->bindParam(':name', $name);
        $update_stmt->bindParam(':email', $email);
        $update_stmt->bindParam(':phone_number', $phone_number);
        if (!empty($password)) {
            $update_stmt->bindParam(':password_hash', $password_hash);
        }
        $update_stmt->bindParam(':electrician_id', $electrician_id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            echo "Details updated successfully.";
            header('Location: home.php'); // Redirect to dashboard
            exit();
        } else {
            $errors[] = "Failed to update details. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electrician Settings</title>
    
    <!-- Bootstrap CSS (via CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #5cb85c;
            color: white;
            text-align: center;
            font-size: 1.5rem;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .card-body {
            background-color: #ffffff;
            padding: 30px;
        }
        .form-control {
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .btn {
            width: 100%;
            border-radius: 20px;
            padding: 12px;
            font-size: 16px;
        }
        .error-msg {
            color: red;
            font-size: 14px;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border-radius: 20px;
            font-size: 16px;
            text-align: center;
        }
        .back-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Electrician Info Card -->
    <div class="card">
        <div class="card-header">
            <h3>Update Your Details</h3>
        </div>
        <div class="card-body">
            <!-- Display Errors -->
            <?php if (!empty($errors)): ?>
                <div class="error-msg">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Update Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">New Password (optional):</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                </div>

                <button type="submit" class="btn btn-success">Update Details</button>
            </form>

            <!-- Back to Dashboard -->
            <div class="mt-3 text-center">
                <a href="home.php" class="back-btn">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and Popper.js (via CDN) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
