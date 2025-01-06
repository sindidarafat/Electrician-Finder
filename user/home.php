<?php
session_start();
include('../dbConnect.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $user_name = $user['name'];
} else {
    header('Location: login.php');
    exit();
}


$status_filter = isset($_GET['status']) ? $_GET['status'] : '';


$appointments_stmt = $pdo->prepare("
    SELECT 
        a.appointment_id, 
        a.appointment_date, 
        a.status, 
        e.name AS electrician_name, 
        e.email AS electrician_email, 
        e.phone_number AS electrician_phone, 
        e.city AS electrician_city, 
        e.area AS electrician_area, 
        c.category_name 
    FROM 
        appointments a
    JOIN electricians e ON a.electrician_id = e.electrician_id
    JOIN electrician_categories ec ON e.electrician_id = ec.electrician_id
    JOIN categories c ON ec.category_id = c.category_id
    WHERE 
        a.user_id = :user_id
        " . ($status_filter ? " AND a.status = :status" : "") . "
    ORDER BY 
        CASE 
            WHEN a.status = 'pending' THEN 0 
            WHEN a.status = 'completed' THEN 1 
            WHEN a.status = 'canceled' THEN 2
            ELSE 3
        END, a.appointment_date DESC
");

$appointments_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
if ($status_filter) {
    $appointments_stmt->bindParam(':status', $status_filter, PDO::PARAM_STR);
}
$appointments_stmt->execute();
$appointments = $appointments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            background-color:rgb(70, 79, 86);
            color: white;
        }
        .badge-confirmed {
            background-color:rgb(1, 221, 255);
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

<body>
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

    <div class="container py-5">
        <section>
            <h1 class="display-4 text-primary">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p class="lead">Here are your recent appointments:</p>
        </section>

        <section>
            <!-- Filter by Appointment Status -->
            <form method="GET" class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <label for="status" class="form-label">Filter by Status</label>
                        <select name="status" id="status" class="form-select w-auto" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="canceled" <?php echo $status_filter == 'canceled' ? 'selected' : ''; ?>>Canceled</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="row">
                <?php if (count($appointments) > 0): ?>
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    Appointment ID: <?php echo htmlspecialchars($appointment['appointment_id']); ?>
                                </div>
                                <div class="card-body">
                                    <p><strong>Electrician:</strong> <?php echo htmlspecialchars($appointment['electrician_name']); ?></p>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($appointment['category_name']); ?></p>
                                    <p><strong>Appointment Date:</strong> <?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($appointment['appointment_date']))); ?></p>
                                    <span class="badge status-tag badge-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            You have no appointments at the moment.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <footer class="mt-4">
            <div class="d-flex justify-content-center">
                <a class="btn btn-primary" href="search_electrician.php">Add Appointment</a>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
