<?php
include('../dbConnect.php');
session_start();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

   
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
   
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

       
        if (password_verify($password, $user['password_hash'])) {
           
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            
           
            $_SESSION['success_message'] = "Login successful! Welcome " . $_SESSION['user_name'];
            header('Location: home.php');
            exit();
        } else {
            $error_message = "Incorrect password!";
        }
    } else {
        $error_message = "No user found with this email!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css" rel="stylesheet">

    <style>
    .login-container {
        max-width: 400px;
        margin: 50px auto;
    }

    .notification {
        margin-top: 20px;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <h2 class="title is-4 has-text-centered">User Login</h2>
        <?php if (isset($error_message)): ?>
        <div class="notification is-danger">
            <?php echo $error_message; ?>
        </div>
        <?php elseif (isset($_SESSION['success_message'])): ?>
        <div class="notification is-success">
            <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
        </div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="field">
                <label class="label" for="email">Email</label>
                <div class="control">
                    <input class="input" type="email" name="email" id="email" required placeholder="Enter your email">
                </div>
            </div>

            <div class="field">
                <label class="label" for="password">Password</label>
                <div class="control">
                    <input class="input" type="password" name="password" id="password" required
                        placeholder="Enter your password">
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <button class="button is-primary is-fullwidth" type="submit">Login</button>
                </div>
            </div>
        </form>
        <p class="has-text-centered">
            <a href="signup.php">Don't have an account? Register here</a>
        </p>
    </div>

</body>

</html>