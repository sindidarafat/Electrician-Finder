<?php
session_start();
include('../dbConnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $city = 'Dhaka';
    $area = trim($_POST['area']);
    $password = trim($_POST['password']);
    $category = trim($_POST['category']);

    // Check if email already exists in the database
    $email_check_sql = "SELECT COUNT(*) FROM electricians WHERE email = :email";
    $email_check_stmt = $pdo->prepare($email_check_sql);
    $email_check_stmt->bindParam(':email', $email);
    $email_check_stmt->execute();
    $email_count = $email_check_stmt->fetchColumn();

    if ($email_count > 0) {
        $error = 'This email is already in use. Please use a different email.';
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the electrician into the electricians table
        $sql = "INSERT INTO electricians (name, email, phone_number, city, area, password_hash) 
                VALUES (:name, :email, :phone_number, :city, :area, :password_hash)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':area', $area);
        $stmt->bindParam(':password_hash', $hashed_password);

        if ($stmt->execute()) {
            $electrician_id = $pdo->lastInsertId();

            // Insert the electrician's category
            $category_sql = "INSERT INTO electrician_categories (electrician_id, category_id) 
                             VALUES (:electrician_id, :category_id)";
            $category_stmt = $pdo->prepare($category_sql);
            $category_stmt->bindParam(':electrician_id', $electrician_id);
            $category_stmt->bindParam(':category_id', $category);

            if ($category_stmt->execute()) {
                // Insert into the electrician_availability table to set availability to TRUE
                $availability_sql = "INSERT INTO electrician_availability (electrician_id, is_available) 
                                     VALUES (:electrician_id, TRUE)";
                $availability_stmt = $pdo->prepare($availability_sql);
                $availability_stmt->bindParam(':electrician_id', $electrician_id);

                if ($availability_stmt->execute()) {
                    // Redirect to login page after successful registration
                    header('Location: login.php');
                    exit();
                } else {
                    $error = 'Error setting availability. Please try again.';
                }
            } else {
                $error = 'Error adding category. Please try again.';
            }
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electrician Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f7f7f7;
    }

    .signup-container {
        margin-top: 50px;
        max-width: 500px;
        padding: 30px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .signup-container h2 {
        text-align: center;
        margin-bottom: 30px;
    }

    .error-msg {
        color: red;
        font-size: 14px;
        text-align: center;
    }

    .form-control {
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .btn {
        width: 100%;
        border-radius: 20px;
        padding: 12px;
        font-size: 16px;
    }

    .back-btn {
        display: block;
        text-align: center;
        margin-top: 10px;
    }
    </style>
</head>

<body>

    <div class="container signup-container">
        <?php if (isset($error)): ?>
        <div class="error-msg">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>

        <h2>Electrician Signup</h2>

        <form method="POST" action="signup.php">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input type="text" name="phone_number" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="area">Area:</label>
                <select name="area" class="form-control" required>
                    <option value="Badda">Badda</option>
                    <option value="Uttara">Uttara</option>
                    <option value="Mirpur">Mirpur</option>
                    <option value="Airport Area">Airport Area</option>
                    <option value="Gulshan">Gulshan</option>
                </select>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select name="category" class="form-control" required>
                    <option value="1">TV Expert</option>
                    <option value="2">Refrigerator Expert</option>
                    <option value="3">AC Expert</option>
                    <option value="4">Daily Necessary Expert</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>

        <p class="back-btn">Already have an account? <a href="login.php">Login</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
