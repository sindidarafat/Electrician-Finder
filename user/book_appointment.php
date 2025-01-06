<?php
session_start();
include('../dbConnect.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['electrician_id'])) {
    echo "Electrician ID is missing!";
    exit();
}

$electrician_id = $_GET['electrician_id'];
$user_id = $_SESSION['user_id'];

$query = "SELECT e.electrician_id, e.name AS electrician_name, c.category_name
          FROM electricians e
          JOIN electrician_categories ec ON e.electrician_id = ec.electrician_id
          JOIN categories c ON ec.category_id = c.category_id
          WHERE e.electrician_id = :electrician_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':electrician_id', $electrician_id, PDO::PARAM_INT);
$stmt->execute();
$electrician = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$electrician) {
    echo "Electrician not found!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_date = $_POST['appointment_date'];

    if (empty($appointment_date)) {
        echo "Please select a valid appointment date.";
    } else {
        $insert_query = "INSERT INTO appointments (user_id, electrician_id, appointment_date, status)
                         VALUES (:user_id, :electrician_id, :appointment_date, 'pending')";
        $stmt = $pdo->prepare($insert_query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':electrician_id', $electrician_id, PDO::PARAM_INT);
        $stmt->bindParam(':appointment_date', $appointment_date, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $success_message = "Your appointment has been successfully booked!";
        } else {
            $error_message = "Failed to book the appointment. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #dbeafe, #bfdbfe);
            min-height: 100vh;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .notification {
            margin-top: 20px;
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
</head>

<body>       <nav class="navbar navbar-expand-lg">
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

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title">Book an Appointment</h2>
                        <h4 class="card-text">Electrician: <?php echo htmlspecialchars($electrician['electrician_name']); ?></h4>
                        <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($electrician['category_name']); ?></p>
                    </div>
                </div>
                <form action="book_appointment.php?electrician_id=<?php echo $electrician_id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="appointment_date" class="form-label">Select Appointment Date:</label>
                        <input type="datetime-local" id="appointment_date" name="appointment_date" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Book Appointment</button>
                    </div>
                </form>
                <div class="mt-3">
                    <a href="home.php" class="btn btn-secondary w-100">Back to Home</a>
                </div>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success mt-3">
                        <?php echo $success_message; ?>
                    </div>
                <?php elseif (isset($error_message)): ?>
                    <div class="alert alert-danger mt-3">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
