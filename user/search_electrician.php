<?php
session_start();
include('../dbConnect.php');

$category_filter = '';
$area_filter = '';
$search_results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_filter = isset($_POST['category']) ? $_POST['category'] : '';
    $area_filter = isset($_POST['area']) ? $_POST['area'] : '';

    $query = "
        SELECT 
            e.electrician_id, 
            e.name AS electrician_name, 
            c.category_name, 
            e.city, 
            e.area,
            ea.is_available
        FROM 
            electricians e
        JOIN 
            electrician_categories ec ON e.electrician_id = ec.electrician_id
        JOIN 
            categories c ON ec.category_id = c.category_id
        LEFT JOIN 
            electrician_availability ea ON e.electrician_id = ea.electrician_id
        WHERE 
            1=1";

    if ($category_filter) {
        $query .= " AND c.category_name = :category";
    }
    if ($area_filter) {
        $query .= " AND e.area = :area";
    }

    $stmt = $pdo->prepare($query);

    if ($category_filter) {
        $stmt->bindParam(':category', $category_filter, PDO::PARAM_STR);
    }
    if ($area_filter) {
        $stmt->bindParam(':area', $area_filter, PDO::PARAM_STR);
    }

    $stmt->execute();

    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getElectricianStats($pdo, $electrician_id) {
    $stats_query = "
        SELECT 
            COUNT(CASE WHEN status = 'completed' THEN 1 END) AS completed_count,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending_count,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) AS cancelled_count
        FROM 
            appointments
        WHERE 
            electrician_id = :electrician_id";
    
    $stats_stmt = $pdo->prepare($stats_query);
    $stats_stmt->bindParam(':electrician_id', $electrician_id, PDO::PARAM_INT);
    $stats_stmt->execute();
    
    return $stats_stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Electricians</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>        body {
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
        body {
            background: linear-gradient(to bottom right, #d7eaff, #b3d4fc);
        }
        .card {
            margin-bottom: 20px;
        }
        .disabled {
            pointer-events: none;
            opacity: 0.5;
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
        <div class="row mb-4">
            <div class="col text-center">
                <h1 class="text-primary">Search for Electricians</h1>
            </div>
        </div>
        <form action="search_electrician.php" method="POST">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">Select Category</option>
                        <option value="TV Expert" <?php echo ($category_filter == 'TV Expert') ? 'selected' : ''; ?>>TV Expert</option>
                        <option value="Refrigerator Expert" <?php echo ($category_filter == 'Refrigerator Expert') ? 'selected' : ''; ?>>Refrigerator Expert</option>
                        <option value="AC Expert" <?php echo ($category_filter == 'AC Expert') ? 'selected' : ''; ?>>AC Expert</option>
                        <option value="Daily Necessary Expert" <?php echo ($category_filter == 'Daily Necessary Expert') ? 'selected' : ''; ?>>Daily Necessary Expert</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="area" class="form-label">Area</label>
                    <select id="area" name="area" class="form-select">
                        <option value="">Select Area</option>
                        <option value="Badda" <?php echo ($area_filter == 'Badda') ? 'selected' : ''; ?>>Badda</option>
                        <option value="Uttara" <?php echo ($area_filter == 'Uttara') ? 'selected' : ''; ?>>Uttara</option>
                        <option value="Mirpur" <?php echo ($area_filter == 'Mirpur') ? 'selected' : ''; ?>>Mirpur</option>
                        <option value="Airport Area" <?php echo ($area_filter == 'Airport Area') ? 'selected' : ''; ?>>Airport Area</option>
                        <option value="Gulshan" <?php echo ($area_filter == 'Gulshan') ? 'selected' : ''; ?>>Gulshan</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>

        <div class="row mt-5">
            <div class="col">
                <h2 class="text-center">Search Results</h2>
                <?php if (count($search_results) > 0): ?>
                    <div class="row">
                        <?php foreach ($search_results as $electrician): ?>
                            <?php $stats = getElectricianStats($pdo, $electrician['electrician_id']); ?>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary"><?php echo htmlspecialchars($electrician['electrician_name']); ?></h5>
                                        <p class="card-text">
                                            <strong>Category:</strong> <?php echo htmlspecialchars($electrician['category_name']); ?><br>
                                            <strong>Area:</strong> <?php echo htmlspecialchars($electrician['area']); ?><br>
                                        </p>

                                        <!-- Availability Status -->
                                        <div>
                                            <?php if ($electrician['is_available']): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not Available</span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Appointment Stats -->
                                        <div class="mt-2">
                                            <span class="badge bg-success">Completed: <?php echo $stats['completed_count']; ?></span>
                                            <span class="badge bg-warning text-dark">Pending: <?php echo $stats['pending_count']; ?></span>
                                            <span class="badge bg-danger">Cancelled: <?php echo $stats['cancelled_count']; ?></span>
                                        </div>

                                        <!-- Book Appointment Button -->
                                        <a href="book_appointment.php?electrician_id=<?php echo $electrician['electrician_id']; ?>" class="btn btn-primary mt-3 <?php echo ($electrician['is_available'] ? '' : 'disabled'); ?>">
                                            Book Appointment
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">No electricians found matching your search criteria.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
