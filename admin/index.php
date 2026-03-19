<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../models/DB.php';
session_start();

// Simple authentication check (you can enhance this later)
if (!isset($_SESSION['admin_logged_in'])) {
    // For now, let's create a simple login
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $db = DB::getInstance();
        $email = $_POST['email'];
        $password = $_POST['password'];
        $data = $db->getOne("doctors", ["email"=>$email, "password"=> $password, "is_available"=>1]);
        if(is_array($data) && sizeof($data) > 0){
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['doctor_id'] = $data['id'];
            header("location: index.php");
        }else{

            $error = "Invalid password!";
            }
    }
    
    if (!isset($_SESSION['admin_logged_in'])) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login - Astana</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                body {
                    /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
                    background: linear-gradient(135deg, #f358e3 0%, #efa7e6 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                }
                .login-card {
                    background: white;
                    border-radius: 15px;
                    padding: 40px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                }
                .login-header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .login-header img {
                    width: 80px;
                    height: 80px;
                    border-radius: 50%;
                    margin-bottom: 15px;
                }
                .btn-login {
                    /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
                    background: linear-gradient(135deg, #f358e3 0%, #f2beeb 100%);
                    border: none;
                    color: white;
                    padding: 12px;
                    border-radius: 10px;
                    width: 100%;
                    font-weight: 600;
                }
                .btn-login:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="login-card">
                            <div class="login-header">
                                <img src="../assets/images/astana-logo.PNG" alt="Logo">
                                <h4>Admin Login</h4>
                                <p class="text-muted">Enter password to access panel</p>
                            </div>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required placeholder="Enter admin Email">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required placeholder="Enter admin password">
                                </div>
                                <button type="submit" name="login" class="btn btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}




// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = DB::getInstance();
    
    if (isset($_POST['update_profile'])) {
        $data = [
            'name' => $_POST['name'],
            'specialization' => $_POST['specialization'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_available' => isset($_POST['is_available']) ? 1 : 0
        ];
        
        $db->update('doctors', ['id' => $_POST['doctor_id']], $data);
        $success = "Profile updated successfully!";
    }
}

// Get doctor data (assuming first doctor for now - modify as needed)
$db = DB::getInstance();
$doctor_id = $_SESSION['doctor_id'];
$doctor = $db->getOne('doctors', ['id' => $doctor_id ]); // Get doctor with ID 1
$todayDate = date("Y-m-d");
// Get all today's appointments for this doctor (excluding cancelled)
$result = $db->query("SELECT count(*) as total, status FROM tokens WHERE doctor_id = :doctor_id AND token_date = :token_date group by status", [
    'doctor_id' => $doctor_id,
    'token_date' => $todayDate
]);
$statusCounts = [
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0
];
foreach($result as $row) {
    $statusCounts[$row['status']] = $row['total'];
}
$activeCount = $statusCounts['pending'] + $statusCounts['confirmed'] + $statusCounts['completed'];
$cancelledCount = $statusCounts['cancelled'];

$tptalP = $db->query("SELECT DISTINCT patient_name FROM tokens WHERE doctor_id = :doctor_id AND token_date = :token_date ", [
    'doctor_id' => $doctor_id,
    'token_date' => $todayDate
]);
$TodaypatientCount = count($tptalP);


if (!$doctor) {
    die("Doctor not found!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Doctor Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            /* --primary-color: #2c3e50;
            --secondary-color: #3498db; */
            --primary-color: #df12cb;
        --secondary-color: #f2beeb;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* .sidebar {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            min-height: 100vh;
            padding: 20px;
            color: white;
        }
        
        .sidebar .logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar .logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 15px;
            margin: 5px 0;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar .nav-link.active {
            background: var(--secondary-color);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        } */
        
        .main-content {
            padding: 40px;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .profile-header h3 {
            margin: 0;
            color: var(--primary-color);
        }
        
        .info-table {
            margin-top: 20px;
        }
        
        .info-table table {
            width: 100%;
        }
        
        .info-table td {
            padding: 12px 5px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-table td:first-child {
            font-weight: 600;
            width: 150px;
            color: var(--primary-color);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.available {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-badge.unavailable {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn-edit {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-save {
            background: var(--success-color);
            color: white;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--success-color);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 30px;
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <!-- <div class="sidebar">
                    <div class="logo">
                        <img src="../assets/images/astana-logo.PNG" alt="Logo">
                        <h5 class="mt-3">Admin Panel</h5>
                        <p class="text-white-50 small">Doctor Management</p>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-user-md"></i> Profile
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-check"></i> Appointments
                        </a>
                        <a class="nav-link" href="">
                            <i class="fas fa-chart-bar"></i> Setup Schedule
                        </a>
                        <a class="nav-link" href="?logout=1">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div> -->
                <?php include_once("sidebar.php"); ?>
            </div>

            
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content"  id="mainContent">
                    <!-- Logout Button -->
                    <!-- <a href="?logout=1" class="btn logout-btn">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a> -->
                    
                    <!-- Success Message -->
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Stats -->
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-muted">Today's Appointments</h6>
                                    <h3><?= $activeCount ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-muted">Pending</h6>
                                    <h3><?= $statusCounts['pending'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-muted">Completed</h6>
                                    <h3><?= $statusCounts['completed'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-muted">Total Patients</h6>
                                    <h3><?= $TodaypatientCount ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Section -->
                    <div class="profile-card" id="profileCard">
                        <div class="profile-header d-flex justify-content-between align-items-center">
                            <h3>
                                <i class="fas fa-user-circle me-2" style="color: var(--secondary-color);"></i>
                                Doctor Profile
                            </h3>
                            <button class="btn btn-edit" onclick="toggleEdit()">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </button>
                        </div>
                        
                        <!-- View Mode -->
                        <div id="viewMode">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="info-table">
                                        <table>
                                            <tr>
                                                <td>Doctor ID</td>
                                                <td>:</td>
                                                <td><strong>#<?php echo $doctor['id']; ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td>Full Name</td>
                                                <td>:</td>
                                                <td><strong><?php echo htmlspecialchars($doctor['name']); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td>Specialization</td>
                                                <td>:</td>
                                                <td><strong><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td>Email</td>
                                                <td>:</td>
                                                <td><strong><?php echo htmlspecialchars($doctor['email'] ?? 'Not provided'); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td>Phone</td>
                                                <td>:</td>
                                                <td><strong><?php echo htmlspecialchars($doctor['phone'] ?? 'Not provided'); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td>Status</td>
                                                <td>:</td>
                                                <td>
                                                    <?php if ($doctor['is_active']): ?>
                                                        <span class="status-badge active">
                                                            <i class="fas fa-check-circle me-1"></i>Active
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="status-badge inactive">
                                                            <i class="fas fa-times-circle me-1"></i>Inactive
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Availability</td>
                                                <td>:</td>
                                                <td>
                                                    <?php if ($doctor['is_available']): ?>
                                                        <span class="status-badge available">
                                                            <i class="fas fa-clock me-1"></i>Available
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="status-badge unavailable">
                                                            <i class="fas fa-hourglass-end me-1"></i>Not Available
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Joined Date</td>
                                                <td>:</td>
                                                <td><strong><?php echo date('d M, Y', strtotime($doctor['created_at'])); ?></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="profile-image">
                                        <div style="font-size: 120px; color: var(--secondary-color);">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <h5 class="mt-3"><?php echo htmlspecialchars($doctor['name']); ?></h5>
                                        <p class="text-muted"><?php echo htmlspecialchars($doctor['specialization'] ?? 'Doctor'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit Mode -->
                        <div id="editMode" style="display: none;">
                            <form method="POST">
                                <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Full Name</label>
                                            <input type="text" class="form-control" name="name" 
                                                   value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Specialization</label>
                                            <input type="text" class="form-control" name="specialization" 
                                                   value="<?php echo htmlspecialchars($doctor['specialization']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Email</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?php echo htmlspecialchars($doctor['email']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Phone</label>
                                            <input type="text" class="form-control" name="phone" 
                                                   value="<?php echo htmlspecialchars($doctor['phone']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Account Status</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active" 
                                                       id="isActive" <?php echo $doctor['is_active'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="isActive">Active Account</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold d-block">Availability</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_available" 
                                                       id="isAvailable" <?php echo $doctor['is_available'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="isAvailable">Available for Appointments</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" name="update_profile" class="btn btn-save me-2">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                    <button type="button" class="btn btn-cancel" onclick="toggleEdit()">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleEdit() {
            document.getElementById('viewMode').style.display = 
                document.getElementById('viewMode').style.display === 'none' ? 'block' : 'none';
            document.getElementById('editMode').style.display = 
                document.getElementById('editMode').style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>