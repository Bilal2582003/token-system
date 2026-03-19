<style>
/* Sidebar Component Styles - Only apply to sidebar elements */
#sidebarComponent {
    --primary-color: #df12cb;
    --secondary-color: #f2beeb;
    --success-color: #27ae60;
    --danger-color: #e74c3c;
}

/* Sidebar Styles - All prefixed with #sidebarComponent */
#sidebarComponent .sidebar {
    background: linear-gradient(135deg, var(--primary-color), #34495e);
    min-height: 100%;
    padding: 20px;
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    width: 250px;
    z-index: 1000;
    transition: all 0.3s ease-in-out;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

#sidebarComponent .sidebar.collapsed {
    transform: translateX(-100%);
}

#sidebarComponent .sidebar .logo {
    text-align: center;
    padding: 5px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 5px;
}

#sidebarComponent .sidebar .logo img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid white;
    transition: transform 0.3s;
}

#sidebarComponent .sidebar .logo img:hover {
    transform: scale(1.05);
}

#sidebarComponent .sidebar .nav-link {
    color: rgba(255, 255, 255, 0.8);
    padding: 12px 15px;
    margin: 5px 0;
    border-radius: 10px;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    display: block;
    text-decoration: none;
}

#sidebarComponent .sidebar .nav-link::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

#sidebarComponent .sidebar .nav-link:hover::before {
    width: 300px;
    height: 300px;
}

#sidebarComponent .sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    transform: translateX(5px);
}

#sidebarComponent .sidebar .nav-link.active {
    background: var(--secondary-color);
    color: white;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

#sidebarComponent .sidebar .nav-link i {
    margin-right: 10px;
    width: 20px;
    font-size: 1.1rem;
}

/* Main Content Adjustment */
#sidebarComponent ~ .main-content {
    transition: margin-left 0.3s ease-in-out;
    padding: 50px;
    min-height: 100vh;
}

#sidebarComponent ~ .main-content.expanded {
    margin-left: 0;
}

/* Navbar Toggle Button */
#sidebarToggleBtn {
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 1100;
    background: linear-gradient(135deg, var(--primary-color), #34495e);
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: all 0.3s;
}

#sidebarToggleBtn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(223, 18, 203, 0.3);
}

#sidebarToggleBtn i {
    font-size: 1.5rem;
    transition: transform 0.3s;
}

#sidebarToggleBtn.active i {
    transform: rotate(90deg);
}

/* Overlay for mobile */
#sidebarOverlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    backdrop-filter: blur(3px);
}

#sidebarOverlay.show {
    display: block;
    opacity: 1;
}

/* Navigation scroll styles */
#sidebarComponent .sidebar nav {
    max-height: calc(100vh - 180px);
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 5px;
    padding-bottom: 30px;
}

/* Custom scrollbar for navigation only */
#sidebarComponent .sidebar nav::-webkit-scrollbar {
    width: 4px;
}

#sidebarComponent .sidebar nav::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

#sidebarComponent .sidebar nav::-webkit-scrollbar-thumb {
    background: var(--secondary-color);
    border-radius: 10px;
}

/* Firefox scrollbar */
#sidebarComponent .sidebar nav {
    scrollbar-width: thin;
    scrollbar-color: var(--secondary-color) rgba(255, 255, 255, 0.1);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    #sidebarComponent .sidebar {
        transform: translateX(-100%);
        box-shadow: 2px 0 20px rgba(0,0,0,0.2);
        width: 280px;
    }
    
    #sidebarComponent .sidebar.show {
        transform: translateX(0);
    }
    
    #sidebarComponent ~ .main-content {
        margin-left: 0;
    }
    
    #sidebarToggleBtn {
        display: flex;
    }
    
    /* Animation for menu items */
    #sidebarComponent .sidebar.show .nav-link {
        animation: slideIn 0.5s ease forwards;
        opacity: 0;
        transform: translateX(-20px);
    }
    
    @keyframes slideIn {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    /* Stagger animation for nav items */
    #sidebarComponent .sidebar.show .nav-link:nth-child(1) { animation-delay: 0.1s; }
    #sidebarComponent .sidebar.show .nav-link:nth-child(2) { animation-delay: 0.15s; }
    #sidebarComponent .sidebar.show .nav-link:nth-child(3) { animation-delay: 0.2s; }
    #sidebarComponent .sidebar.show .nav-link:nth-child(4) { animation-delay: 0.25s; }
    #sidebarComponent .sidebar.show .nav-link:nth-child(5) { animation-delay: 0.3s; }
    #sidebarComponent .sidebar.show .nav-link:nth-child(6) { animation-delay: 0.35s; }
    #sidebarComponent .sidebar.show .nav-link:nth-child(7) { animation-delay: 0.4s; }
}

/* Tablet view */
@media (min-width: 769px) and (max-width: 1024px) {
    #sidebarComponent .sidebar {
        width: 220px;
    }
    
    #sidebarComponent ~ .main-content {
        padding: 50px;
    }
    
    #sidebarComponent .sidebar .nav-link {
        padding: 10px 12px;
        font-size: 0.95rem;
    }
    
    #sidebarComponent .sidebar .logo h5 {
        font-size: 1rem;
    }
}

/* Desktop specific nav height */
@media (min-width: 1024px) {
    #sidebarComponent .sidebar nav {
        max-height: calc(100vh - 190px) !important;
    }
}

/* Custom scrollbar for sidebar */
#sidebarComponent .sidebar::-webkit-scrollbar {
    width: 5px;
}

#sidebarComponent .sidebar::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
}

#sidebarComponent .sidebar::-webkit-scrollbar-thumb {
    background: var(--secondary-color);
    border-radius: 5px;
}

#sidebarComponent .sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--primary-color);
}
</style>

<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    echo "<script>window.location.href='index.php'</script>";
    exit;
}
?>

<!-- Sidebar Component Wrapper -->
<div id="sidebarComponent">
    <!-- Navbar Toggle Button -->
    <button class="navbar-toggle" id="sidebarToggleBtn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="../assets/images/astana-logo.PNG" alt="Logo">
            <h5 class="mt-3">Admin Panel</h5>
            <p class="text-white-50 small">Management Sys</p>
        </div>

        <nav class="nav">
            <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                <i class="fas fa-user-md"></i> Profile
            </a>
            <a class="nav-link <?php echo ($current_page == 'doctorSchedule.php') ? 'active' : ''; ?>"
                href="doctorSchedule.php">
                <i class="fas fa-calendar-alt"></i> Setup Schedule
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
</div>

<!-- Add this script to handle main content class -->
<script>
// Add main-content class to the main content div in your pages
document.addEventListener('DOMContentLoaded', function() {
    // Find the main content div (you may need to adjust this selector)
    const mainContent = document.querySelector('.main-content') || document.querySelector('main') || document.querySelector('.container-fluid > .row > .col-md-9');
    if (mainContent) {
        mainContent.classList.add('main-content');
    }
});

// Sidebar toggle function
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const icon = toggleBtn.querySelector('i');
    
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
    toggleBtn.classList.toggle('active');
    
    // Change icon based on state
    if (sidebar.classList.contains('show')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
        
        // Prevent body scrolling when sidebar is open on mobile
        if (window.innerWidth <= 768) {
            document.body.style.overflow = 'hidden';
        }
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
        document.body.style.overflow = '';
    }
}

// Close sidebar when clicking on a nav link (mobile only)
document.querySelectorAll('#sidebarComponent .sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            // Don't close if it's the logout link (will redirect anyway)
            if (!this.getAttribute('href').includes('logout')) {
                setTimeout(() => {
                    toggleSidebar();
                }, 100);
            }
        }
    });
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const icon = toggleBtn.querySelector('i');
    
    if (window.innerWidth > 768) {
        // Desktop view
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        toggleBtn.classList.remove('active');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
        document.body.style.overflow = '';
    }
});

// Close sidebar with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    }
});

// Initialize based on screen size
document.addEventListener('DOMContentLoaded', function() {
    // Check if mobile and sidebar should be hidden by default
    if (window.innerWidth <= 768) {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.remove('show');
    }
});

// Touch swipe to close (for mobile)
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
});

document.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    const sidebar = document.getElementById('sidebar');
    const swipeThreshold = 100; // minimum distance for swipe
    
    // Swipe left to close
    if (touchEndX < touchStartX - swipeThreshold && sidebar.classList.contains('show')) {
        toggleSidebar();
    }
    
    // Swipe right to open
    if (touchEndX > touchStartX + swipeThreshold && !sidebar.classList.contains('show')) {
        toggleSidebar();
    }
}
</script>