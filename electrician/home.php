<?php
session_start();

if (!isset($_SESSION['electrician_id'])) {
    header('Location: login.php');
    exit();
}

include('../dbConnect.php');

$electrician_id = $_SESSION['electrician_id'];


$query = "SELECT e.name AS electrician_name, c.category_name
          FROM electricians e
          JOIN electrician_categories ec ON e.electrician_id = ec.electrician_id
          JOIN categories c ON ec.category_id = c.category_id
          WHERE e.electrician_id = :electrician_id";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':electrician_id', $electrician_id, PDO::PARAM_INT);
$stmt->execute();

$electrician = $stmt->fetch(PDO::FETCH_ASSOC);

if ($electrician) {
    $electrician_name = $electrician['electrician_name'];
    $category_name = $electrician['category_name'];
} else {
    $electrician_name = 'Unknown Electrician';
    $category_name = 'No category assigned';
}


$availability_query = "SELECT is_available FROM electrician_availability WHERE electrician_id = :electrician_id";
$availability_stmt = $pdo->prepare($availability_query);
$availability_stmt->bindParam(':electrician_id', $electrician_id, PDO::PARAM_INT);
$availability_stmt->execute();

$availability = $availability_stmt->fetch(PDO::FETCH_ASSOC);
$is_available = $availability ? $availability['is_available'] : false;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_availability'])) {
    $new_availability = ($is_available) ? 0 : 1;

    $update_availability_query = "UPDATE electrician_availability SET is_available = :is_available WHERE electrician_id = :electrician_id";
    $update_availability_stmt = $pdo->prepare($update_availability_query);
    $update_availability_stmt->bindParam(':is_available', $new_availability, PDO::PARAM_INT);
    $update_availability_stmt->bindParam(':electrician_id', $electrician_id, PDO::PARAM_INT);
    $update_availability_stmt->execute();

   
    header('Location: home.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];

   
    switch ($action) {
        case 'confirm':
            $update_status_query = "UPDATE appointments SET status = 'confirmed' WHERE appointment_id = :appointment_id";
            break;
        case 'cancel':
            $update_status_query = "UPDATE appointments SET status = 'canceled' WHERE appointment_id = :appointment_id";
            break;
        case 'complete':
            $update_status_query = "UPDATE appointments SET status = 'completed' WHERE appointment_id = :appointment_id";
            break;
        default:
            $update_status_query = null;
    }

   
    if ($update_status_query) {
        $update_stmt = $pdo->prepare($update_status_query);
        $update_stmt->bindParam(':appointment_id', $appointment_id, PDO::PARAM_INT);
        $update_stmt->execute();
    }

   
    header('Location: home.php');
    exit();
}


$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : ''; 

$appointments_query = "SELECT 
                            a.appointment_id, 
                            a.appointment_date, 
                            a.status, 
                            u.name AS user_name, 
                            u.email AS user_email, 
                            u.phone_number, 
                            u.city, 
                            u.area, 
                            u.street_name, 
                            u.house_no 
                        FROM 
                            appointments a
                        JOIN users u ON a.user_id = u.user_id
                        WHERE 
                            a.electrician_id = :electrician_id";

if ($status_filter) {
    $appointments_query .= " AND a.status = :status_filter";
}

$appointments_query .= " ORDER BY a.appointment_date DESC";

$appointments_stmt = $pdo->prepare($appointments_query);
$appointments_stmt->bindParam(':electrician_id', $electrician_id, PDO::PARAM_INT);

if ($status_filter) {
    $appointments_stmt->bindParam(':status_filter', $status_filter, PDO::PARAM_STR);
}

$appointments_stmt->execute();

$appointments = $appointments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electrician Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">

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
        .appointment-table th, .appointment-table td {
            vertical-align: middle;
        }
        .appointment-table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .status-btn {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        .status-btn:focus {
            outline: none;
        }
        .logout-btn {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border-radius: 20px;
            text-align: center;
            font-size: 16px;
            display: inline-block;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        .appointment-status {
            text-transform: capitalize;
            font-weight: bold;
        }
        .appointment-date {
            color: #5bc0de;
        }
        .availability-toggle {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="card">
        <div class="card-header">
            <h3>Welcome, <?php echo htmlspecialchars($electrician_name); ?>!</h3>
            <p>Your category: <strong><?php echo htmlspecialchars($category_name); ?></strong></p>
        </div>
            <!-- Availability Toggle -->
            <div class="availability-toggle">
                <form method="POST">
                    <button type="submit" name="toggle_availability" class="btn <?php echo $is_available ? 'btn-success' : 'btn-danger'; ?>">
                        <?php echo $is_available ? 'Set as Unavailable' : 'Set as Available'; ?>
                    </button>
                </form>
            </div>
        <div class="card-body">
            <h4>Your Appointments</h4>

            <!-- Filter by Status -->
            <form method="GET" class="mb-4">
                <select name="status_filter" class="form-control w-25 d-inline" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo ($status_filter == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="canceled" <?php echo ($status_filter == 'canceled') ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </form>


            <?php if (count($appointments) > 0): ?>
                <table class="table table-bordered appointment-table">
                    <thead class="thead-dark">
                        <tr>
                            <th>Appointment ID</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['user_email']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['phone_number']); ?></td>
                                <td>
                                    <?php 
                                        echo htmlspecialchars($appointment['house_no'] . ', ' . 
                                                         $appointment['street_name'] . ', ' . 
                                                         $appointment['area'] . ', ' . 
                                                         $appointment['city']); 
                                    ?>
                                </td>
                                <td class="appointment-date"><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($appointment['appointment_date']))); ?></td>
                                <td class="appointment-status">
                                    <?php
                                   
                                    if ($appointment['status'] === 'canceled') {
                                        echo 'Canceled';
                                    } else {
                                        echo htmlspecialchars(ucfirst($appointment['status']));
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] === 'pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                            <button type="submit" name="action" value="confirm" class="btn btn-success status-btn">Confirm <i class="fa fa-check"></i></button>
                                            <button type="submit" name="action" value="cancel" class="btn btn-danger status-btn">Cancel <i class="fa fa-times"></i></button>
                                        </form>
                                    <?php elseif ($appointment['status'] === 'confirmed'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                            <button type="submit" name="action" value="complete" class="btn btn-primary status-btn">Complete <i class="fa fa-check-circle"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No appointments assigned to you yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="settings.php" class="logout-btn">Settings</a>
        <a href="logout.php" class="logout-btn">Log Out</a>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
