<?php
session_start();
include('../dbConnect.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

   
    $stmt = $pdo->prepare("SELECT * FROM electricians WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $electrician = $stmt->fetch(PDO::FETCH_ASSOC);

       
        if (password_verify($password, $electrician['password_hash'])) {
           
            $_SESSION['electrician_id'] = $electrician['electrician_id'];
            $_SESSION['electrician_name'] = $electrician['name'];

           
            header('Location: home.php');
            exit();
        } else {
           
            $error = 'Incorrect password.';
        }
    } else {
       
        $error = 'No electrician found with that email.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electrician Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
        }
        .login-container {
            margin-top: 100px;
            max-width: 400px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
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
        .toggle-password {
            cursor: pointer;
        }
        .back-btn {
            display: block;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container login-container">
    <?php if (isset($error)): ?>
        <div class="error-msg">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <h2>Electrician Login</h2>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" class="form-control" id="password" required>
            <span toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password"></span>
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <p class="back-btn">Don't have an account? <a href="signup.php">Sign up</a></p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    document.querySelector(".toggle-password").addEventListener("click", function() {
        var passwordField = document.getElementById("password");

        if (passwordField.type === "password") {
            passwordField.type = "text";
        } else {
            passwordField.type = "password";
        }
    });
</script>

</body>
</html>
