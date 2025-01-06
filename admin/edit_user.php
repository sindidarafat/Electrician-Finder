<?php
session_start();


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include('../dbConnect.php');


if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found!";
        exit();
    }
} else {
    echo "No user ID specified!";
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $name = htmlspecialchars(trim($_POST['user_name']));
    $email = filter_var(trim($_POST['user_email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['user_phone']));
    $city = htmlspecialchars(trim($_POST['user_city']));
    $area = htmlspecialchars(trim($_POST['user_area']));

   
    if (empty($name) || empty($email) || empty($phone) || empty($city) || empty($area)) {
        $error_message = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
       
        $update_stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone_number = ?, city = ?, area = ? WHERE user_id = ?");
        $update_stmt->execute([$name, $email, $phone, $city, $area, $user_id]);

       
        $success_message = "User details updated successfully!";

       
        header("Refresh: 2; URL=index.php");
        exit();
    }
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
        }
        .container {
            margin-top: 30px;
        }
        .card {
            background-color: #ffffff;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            margin-top: 20px;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit User</h1>
        <a href="index.php" class="btn btn-secondary">Back to Admin Panel</a>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="card mt-4">
                <div class="card-body">
                    <div class="form-group">
                        <label for="user_name">Name:</label>
                        <input type="text" id="user_name" name="user_name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="user_email">Email:</label>
                        <input type="email" id="user_email" name="user_email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="user_phone">Phone:</label>
                        <input type="text" id="user_phone" name="user_phone" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="user_city">City:</label>
                        <input type="text" id="user_city" name="user_city" class="form-control" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="user_area">Area:</label>
                        <input type="text" id="user_area" name="user_area" class="form-control" value="<?php echo htmlspecialchars($user['area']); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
