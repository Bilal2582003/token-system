 <style>
:root {
    /* --primary-color: #2c3e50;
            --secondary-color: #3498db; */
    --primary-color: #df12cb;
    --secondary-color: #f2beeb;
    --success-color: #27ae60;
    --danger-color: #e74c3c;
}

.sidebar {
    background: linear-gradient(135deg, var(--primary-color), #34495e);
    min-height: 100vh;
    padding: 20px;
    color: white;
}

.sidebar .logo {
    text-align: center;
    padding: 20px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 20px;
}

.sidebar .logo img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid white;
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.8);
    padding: 12px 15px;
    margin: 5px 0;
    border-radius: 10px;
    transition: all 0.3s;
}

.sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.sidebar .nav-link.active {
    background: var(--secondary-color);
    color: white;
}

.sidebar .nav-link i {
    margin-right: 10px;
    width: 20px;
}
 </style>
 <?php 
$current_page = basename($_SERVER['PHP_SELF']); 
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    // header("Location: index.php");
    echo "<script>window.location.href='index.php'</script>";
    exit;
}
?>
 <!-- Sidebar -->
 <div class="sidebar">
     <div class="logo">
         <img src="../assets/images/astana-logo.PNG" alt="Logo">
         <h5 class="mt-3">Admin Panel</h5>
         <p class="text-white-50 small">Management Sys</p>
     </div>

     <nav class="nav flex-column">
         <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
             <i class="fas fa-user-md"></i> Profile
         </a>
         <a class="nav-link <?php echo ($current_page == 'doctorSchedule.php') ? 'active' : ''; ?>"
             href="doctorSchedule.php">
             <i class="fas fa-chart-bar"></i> Setup Schedule
         </a>
         <a class="nav-link <?php echo ($current_page == 'appointment.php') ? 'active' : ''; ?>" href="appointment.php">
             <i class="fas fa-calendar-check"></i> Appointments
         </a>
         <a class="nav-link <?php echo ($current_page == 'tokenTypeManagement.php') ? 'active' : ''; ?>"
             href="tokenTypeManagement.php">
             <i class="fas fa-tags"></i> Token Types
         </a>
         <a class="nav-link <?php echo ($current_page == 'tokenLimit.php') ? 'active' : ''; ?>" href="tokenLimit.php">
             <i class="fas fa-chart-line"></i> Token Limits
         </a>
         <a class="nav-link" href="?logout=1">
             <i class="fas fa-sign-out-alt"></i> Logout
         </a>
     </nav>
 </div>