<?php
session_start();
 
 
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
 
include('../dbConnect.php');
 
 
$electricians_stmt = $pdo->prepare("SELECT * FROM electricians");
$electricians_stmt->execute();
$electricians = $electricians_stmt->fetchAll(PDO::FETCH_ASSOC);
 
 
$users_stmt = $pdo->prepare("SELECT * FROM users");
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
 
 
$appointments_stmt = $pdo->prepare("SELECT * FROM appointments");
$appointments_stmt->execute();
$appointments = $appointments_stmt->fetchAll(PDO::FETCH_ASSOC);
 
 
$total_users_stmt = $pdo->prepare("SELECT COUNT(*) AS total_users FROM users");
$total_users_stmt->execute();
$total_users = $total_users_stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
 
 
$total_electricians_stmt = $pdo->prepare("SELECT COUNT(*) AS total_electricians FROM electricians");
$total_electricians_stmt->execute();
$total_electricians = $total_electricians_stmt->fetch(PDO::FETCH_ASSOC)['total_electricians'];
 
 
$total_completed_stmt = $pdo->prepare("SELECT COUNT(*) AS total_completed FROM appointments WHERE status = 'completed'");
$total_completed_stmt->execute();
$total_completed = $total_completed_stmt->fetch(PDO::FETCH_ASSOC)['total_completed'];
 
$total_confirmed_stmt = $pdo->prepare("SELECT COUNT(*) AS total_confirmed FROM appointments WHERE status = 'confirmed'");
$total_confirmed_stmt->execute();
$total_confirmed = $total_confirmed_stmt->fetch(PDO::FETCH_ASSOC)['total_confirmed'];
 
$total_pending_stmt = $pdo->prepare("SELECT COUNT(*) AS total_pending FROM appointments WHERE status = 'pending'");
$total_pending_stmt->execute();
$total_pending = $total_pending_stmt->fetch(PDO::FETCH_ASSOC)['total_pending'];
 
$total_cancelled_stmt = $pdo->prepare("SELECT COUNT(*) AS total_cancelled FROM appointments WHERE status = 'canceled'");
$total_cancelled_stmt->execute();
$total_cancelled = $total_cancelled_stmt->fetch(PDO::FETCH_ASSOC)['total_cancelled'];
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
 
    <!-- Bootstrap CSS (via CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin-top: 20px;
        }
        .container {
            margin-top: 30px;
        }
        .card-header {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .table th, .table td {
            text-align: center;
        }
        .card-body {
            background-color: #ffffff;
        }
        .logout-btn {
            font-size: 16px;
            color: #fff;
            background-color: #dc3545;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        .dashboard-item {
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .dashboard-item span {
            font-size: 24px;
            color: #007bff;
        }
    </style>
</head>
<body>
 
<div class="container">
    <div class="row">
        <div class="col-12 text-right mb-3">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
 
    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">Total Users</div>
                <div class="card-body">
                    <p class="dashboard-item">Users: <span><?php echo $total_users; ?></span></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">Total Electricians</div>
                <div class="card-body">
                    <p class="dashboard-item">Electricians: <span><?php echo $total_electricians; ?></span></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">Completed Appointments</div>
                <div class="card-body">
                    <p class="dashboard-item">Completed: <span><?php echo $total_completed; ?></span></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">Pending Appointments</div>
                <div class="card-body">
                    <p class="dashboard-item">Pending: <span><?php echo $total_pending; ?></span></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">Confirmed Appointments</div>
                <div class="card-body">
                    <p class="dashboard-item">Confirmed: <span><?php echo $total_confirmed; ?></span></p>
                </div>
            </div>
        </div>
    </div>
 
    <div class="card mb-4">
        <div class="card-header">Electricians</div>
        <div class="card-body">
            <?php if (count($electricians) > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Electrician ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Area</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($electricians as $electrician): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($electrician['electrician_id']); ?></td>
                                <td><?php echo htmlspecialchars($electrician['name']); ?></td>
                                <td><?php echo htmlspecialchars($electrician['email']); ?></td>
                                <td><?php echo htmlspecialchars($electrician['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($electrician['city']); ?></td>
                                <td><?php echo htmlspecialchars($electrician['area']); ?></td>
                                <td><a href="edit_electrician.php?id=<?php echo $electrician['electrician_id']; ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No electricians found.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Users</div>
        <div class="card-body">
            <?php if (count($users) > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Area</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($user['city']); ?></td>
                                <td><?php echo htmlspecialchars($user['area']); ?></td>
                                <td><a href="edit_user.php?id=<?php echo $user['user_id']; ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </div>
 
    <div class="card mb-4">
        <div class="card-header">Appointments</div>
        <div class="card-body">
            <?php if (count($appointments) > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Appointment ID</th>
                            <th>User ID</th>
                            <th>Electrician ID</th>
                            <th>Appointment Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['electrician_id']); ?></td>
                                <td><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($appointment['appointment_date']))); ?></td>
                                <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No appointments found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
 
</body>
</html>