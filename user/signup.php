<?php
include('../dbConnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $city = trim($_POST['city']);
    $area = trim($_POST['area']);
    $street_name = trim($_POST['street_name']);
    $house_no = trim($_POST['house_no']);
    $password = trim($_POST['password']);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check for duplicate email
    $email_check_query = "SELECT email FROM users WHERE email = :email";
    $email_check_stmt = $pdo->prepare($email_check_query);
    $email_check_stmt->bindParam(':email', $email);
    $email_check_stmt->execute();

    if ($email_check_stmt->rowCount() > 0) {
        $error_message = "Email is already registered. Please use a different email.";
    } else {
        // Insert user details
        $sql = "INSERT INTO users (name, email, phone_number, city, area, street_name, house_no, password_hash) 
                VALUES (:name, :email, :phone_number, :city, :area, :street_name, :house_no, :password_hash)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':area', $area);
        $stmt->bindParam(':street_name', $street_name);
        $stmt->bindParam(':house_no', $house_no);
        $stmt->bindParam(':password_hash', $hashed_password);

        if ($stmt->execute()) {
            $success_message = "User registered successfully!";
        } else {
            $error_message = "Error registering user!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Signup</title>

    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css" rel="stylesheet">

    <style>
        .signup-container {
            max-width: 600px;
            margin: 50px auto;
        }

        .notification {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2 class="title is-4 has-text-centered">User Signup</h2>

    <?php if (isset($success_message)): ?>
        <div class="notification is-success">
            <?php echo $success_message; ?>
        </div>
    <?php elseif (isset($error_message)): ?>
        <div class="notification is-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="signup.php">
        <div class="field">
            <label class="label" for="name">Name:</label>
            <div class="control">
                <input class="input" type="text" name="name" id="name" required placeholder="Enter your full name">
            </div>
        </div>

        <div class="field">
            <label class="label" for="email">Email:</label>
            <div class="control">
                <input class="input" type="email" name="email" id="email" required placeholder="Enter your email address">
            </div>
        </div>

        <div class="field">
            <label class="label" for="phone_number">Phone Number:</label>
            <div class="control">
                <input class="input" type="text" name="phone_number" id="phone_number" required placeholder="Enter your phone number">
            </div>
        </div>

        <div class="field">
            <label class="label" for="city">City:</label>
            <div class="control">
                <select class="input" name="city" id="city" required>
                    <option value="Dhaka" selected>Dhaka</option>
                </select>
            </div>
        </div>

        <div class="field">
            <label class="label" for="area">Area:</label>
            <div class="control">
                <select class="input" name="area" id="area" required>
                    <option value="Badda">Badda</option>
                    <option value="Uttara">Uttara</option>
                    <option value="Mirpur">Mirpur</option>
                    <option value="Airport Area">Airport Area</option>
                    <option value="Gulshan">Gulshan</option>
                </select>
            </div>
        </div>
        <div class="field">
            <label class="label" for="street_name">Street Name:</label>
            <div class="control">
                <input class="input" type="text" name="street_name" id="street_name" required placeholder="Enter your street name">
            </div>
        </div>

        <div class="field">
            <label class="label" for="house_no">House Number:</label>
            <div class="control">
                <input class="input" type="text" name="house_no" id="house_no" required placeholder="Enter your house number">
            </div>
        </div>

        <div class="field">
            <label class="label" for="password">Password:</label>
            <div class="control">
                <input class="input" type="password" name="password" id="password" required placeholder="Choose a strong password">
            </div>
        </div>

        <div class="field">
            <div class="control">
                <button class="button is-primary is-fullwidth" type="submit">Signup</button>
            </div>
        </div>
    </form>

    <p class="has-text-centered">
        <a href="login.php">Already have an account? Login here</a>
    </p>
</div>

</body>
</html>
