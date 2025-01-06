<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('../dbConnect.php');

$user_id = $_SESSION['user_id'];

$query = "SELECT name, email, phone_number, city, area, street_name, house_no FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}

$name = $user['name'];
$email = $user['email'];
$phone_number = $user['phone_number'];
$city = $user['city'];
$area = $user['area'];
$street_name = $user['street_name'];
$house_no = $user['house_no'];
$password = '';
$confirm_password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $city = trim($_POST['city']);
    $area = trim($_POST['area']);
    $street_name = trim($_POST['street_name']);
    $house_no = trim($_POST['house_no']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif ($email !== $user['email'] && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (empty($phone_number) || !preg_match('/^\d{10,15}$/', $phone_number)) {
        $errors[] = "Valid phone number is required (10-15 digits).";
    }
    if (!empty($password) && ($password !== $confirm_password)) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $update_query = "UPDATE users 
                         SET name = :name, email = :email, phone_number = :phone_number, 
                             city = :city, area = :area, street_name = :street_name, house_no = :house_no";
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $update_query .= ", password_hash = :password_hash";
        }
        $update_query .= " WHERE user_id = :user_id";

        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->bindParam(':name', $name);
        $update_stmt->bindParam(':email', $email);
        $update_stmt->bindParam(':phone_number', $phone_number);
        $update_stmt->bindParam(':city', $city);
        $update_stmt->bindParam(':area', $area);
        $update_stmt->bindParam(':street_name', $street_name);
        $update_stmt->bindParam(':house_no', $house_no);
        if (!empty($password)) {
            $update_stmt->bindParam(':password_hash', $password_hash);
        }
        $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            echo "Details updated successfully.";
            header('Location: home.php');
            exit();
        } else {
            $errors[] = "Failed to update details. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>    <style>
        body {
            background: linear-gradient(135deg, #b3e5fc, #e1f5fe);
            min-height: 100vh;
        }

        .navbar {
            background-color: #0d6efd;
        }

        .navbar-brand, .nav-link {
            color: white !important;
        }

        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .status-tag {
            font-size: 1rem;
            padding: 0.3rem 0.8rem;
        }

        .badge-pending {
            background-color: #6c757d;
            color: white;
        }

        .badge-completed {
            background-color: #198754;
            color: white;
        }

        .badge-canceled {
            background-color: #dc3545;
            color: white;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php">User Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Update Your Details</h2>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_number); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($city); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="area" class="form-label">Area</label>
                                <input type="text" id="area" name="area" class="form-control" value="<?php echo htmlspecialchars($area); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="street_name" class="form-label">Street Name</label>
                                <input type="text" id="street_name" name="street_name" class="form-control" value="<?php echo htmlspecialchars($street_name); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="house_no" class="form-label">House Number</label>
                                <input type="text" id="house_no" name="house_no" class="form-control" value="<?php echo htmlspecialchars($house_no); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password (optional)</label>
                                <input type="password" id="password" name="password" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Update Details</button>
                            </div>
                        </form>

                        <div class="mt-3">
                            <a href="home.php" class="btn btn-secondary w-100">Back to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
