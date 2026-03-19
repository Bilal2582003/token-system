<?php
require_once __DIR__ . '/../config/env.php';
session_start();

// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../models/DB.php';

$db = DB::getInstance();
$doctor_id = $_SESSION['doctor_id'] ?? 1; // Default to 1 if not set, adjust as needed
$message = '';
$messageType = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle Schedule CRUD
    if (isset($_POST['schedule_action'])) {
        
        // Add/Edit Schedule
        if ($_POST['schedule_action'] === 'save') {
            $schedule_id = $_POST['schedule_id'] ?? '';
            $day_of_week = $_POST['day_of_week'];
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            
            if (empty($schedule_id)) {
                $already = $db->getOne("doctor_schedules", [
                    'doctor_id' => $doctor_id,
                    'day_of_week' => $day_of_week
                    ]
                    );
                    if(!$already){
                      
                        // Insert new schedule
                        $inserted = $db->insert('doctor_schedules', [
                            'doctor_id' => $doctor_id,
                            'day_of_week' => $day_of_week,
                            'is_available' => $is_available
                            ]);
                            
                            if ($inserted) {
                                $message = "Schedule added successfully!";
                                $messageType = "success";
                                } else {
                                    $message = "Error adding schedule!";
                                    $messageType = "danger";
                                    }
                                    }else{
                                        $message = "Schedule Already Exist!";
                                            $messageType = "danger";
                                     }
            } else {
                // Update existing schedule
                $updated = $db->update('doctor_schedules', ['id' => $schedule_id], [
                    'day_of_week' => $day_of_week,
                    'is_available' => $is_available
                ]);
                
                if ($updated) {
                    $message = "Schedule updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating schedule!";
                    $messageType = "danger";
                }
            }
        }
        
        // Delete Schedule
        if ($_POST['schedule_action'] === 'delete') {
            $schedule_id = $_POST['schedule_id'];
            $deleted = $db->delete('doctor_schedules', ['id' => $schedule_id, 'doctor_id' => $doctor_id]);
            
            if ($deleted) {
                $message = "Schedule deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Error deleting schedule!";
                $messageType = "danger";
            }
        }
    }
    
    // Handle Special Closure CRUD
    if (isset($_POST['closure_action'])) {
        
        // Add/Edit Special Closure
        if ($_POST['closure_action'] === 'save') {
            $closure_id = $_POST['closure_id'] ?? '';
            $closure_date = $_POST['closure_date'];
            $reason = $_POST['reason'];
            
            if (empty($closure_id)) {
                // Insert new closure
                $inserted = $db->insert('special_closures', [
                    'doctor_id' => $doctor_id,
                    'closure_date' => $closure_date,
                    'reason' => $reason
                ]);
                
                if ($inserted) {
                    $message = "Special closure added successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error adding special closure!";
                    $messageType = "danger";
                }
            } else {
                // Update existing closure
                $updated = $db->update('special_closures', ['id' => $closure_id], [
                    'closure_date' => $closure_date,
                    'reason' => $reason
                ]);
                
                if ($updated) {
                    $message = "Special closure updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating special closure!";
                    $messageType = "danger";
                }
            }
        }
        
        // Delete Special Closure
        if ($_POST['closure_action'] === 'delete') {
            $closure_id = $_POST['closure_id'];
            $deleted = $db->delete('special_closures', ['id' => $closure_id, 'doctor_id' => $doctor_id]);
            
            if ($deleted) {
                $message = "Special closure deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Error deleting special closure!";
                $messageType = "danger";
            }
        }
    }
}

// Fetch all schedules for this doctor
$schedules = $db->getAll('doctor_schedules', ['doctor_id' => $doctor_id], 'day_of_week ASC');

// Fetch all special closures for this doctor
$closures = $db->getAll('special_closures', ['doctor_id' => $doctor_id], 'closure_date DESC');

// Day of week mapping
$dayNames = [
    1 => 'Sunday',
    2 => 'Monday',
    3 => 'Tuesday',
    4 => 'Wednesday',
    5 => 'Thursday',
    6 => 'Friday',
    7 => 'Saturday'
];

// Get doctor name for display
$doctor = $db->getOne('doctors', ['id' => $doctor_id]);
$doctorName = $doctor ? $doctor['name'] : 'Unknown Doctor';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management - <?php echo htmlspecialchars($doctorName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --success-color: #27ae60;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
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
            position: fixed;
            width: 250px;
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
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        } */

    .main-content {
        padding: 40px;
    }

    .page-header {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 15px 20px;
        font-weight: 600;
    }

    .btn-action {
        padding: 5px 10px;
        margin: 0 2px;
        border-radius: 5px;
    }

    .btn-add {
        background: var(--success-color);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
    }

    .btn-add:hover {
        background: #219a52;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
    }

    .available-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
    }

    .available-badge.yes {
        background: #d4edda;
        color: #155724;
    }

    .available-badge.no {
        background: #f8d7da;
        color: #721c24;
    }

    .table th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .modal-content {
        border-radius: 15px;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px 15px 0 0;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 10px 15px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .logout-btn {
        background: var(--danger-color);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .logout-btn:hover {
        background: #c0392b;
        transform: translateY(-2px);
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

    input:checked+.slider {
        background-color: var(--success-color);
    }

    input:checked+.slider:before {
        transform: translateX(26px);
    }

    .doctor-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            position: relative;
            min-height: auto;
        }

        .main-content {
            margin-left: 0;
        }
    }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <?php include_once("sidebar.php") ?>
            </div>
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <!-- Main Content -->
                <div class="main-content"  id="mainContent">
                    <!-- Header -->
                    <div class="page-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Schedule Management</h2>
                            <p class="text-muted mb-0">Manage your weekly schedule and special closures</p>
                        </div>
                        <div>
                            <span class="badge bg-primary p-2 me-2">
                                <i class="fas fa-user-md me-1"></i> <?php echo htmlspecialchars($doctorName); ?>
                            </span>
                            <!-- <a href="?logout=1" class="btn logout-btn">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a> -->
                        </div>
                    </div>

                    <!-- Alert Message -->
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <i
                            class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Doctor Info Card -->
                    <div class="doctor-info">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5><i class="fas fa-info-circle me-2"></i> Working Hours Information</h5>
                                <p class="mb-0">Set your weekly availability and mark special closure dates when you're
                                    unavailable.</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="badge bg-light text-dark p-2">
                                    <i class="fas fa-calendar-week me-1"></i> Week starts from Sunday (1) to Saturday
                                    (7)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Schedule Card -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-week me-2"></i> Weekly Schedule</span>
                            <button class="btn btn-sm btn-info" onclick="openScheduleModal()">
                                <i class="fas fa-plus me-1"></i> Add Schedule
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="scheduleTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Day</th>
                                            <th>Day Number</th>
                                            <th>Status</th>
                                            <th>Availability</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($schedules)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                                <p class="text-muted">No schedules found. Add your first schedule.</p>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($schedules as $schedule): ?>
                                        <tr>
                                            <td>#<?php echo $schedule['id']; ?></td>
                                            <td><strong><?php echo $dayNames[$schedule['day_of_week']]; ?></strong></td>
                                            <td><?php echo $schedule['day_of_week']; ?></td>
                                            <td>
                                                <?php if ($schedule['is_available']): ?>
                                                <span class="available-badge yes">
                                                    <i class="fas fa-check-circle me-1"></i> Available
                                                </span>
                                                <?php else: ?>
                                                <span class="available-badge no">
                                                    <i class="fas fa-times-circle me-1"></i> Unavailable
                                                </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="switch">
                                                    <input type="checkbox"
                                                        onchange="toggleScheduleStatus(<?php echo $schedule['id']; ?>, this.checked)"
                                                        <?php echo $schedule['is_available'] ? 'checked' : ''; ?>>
                                                    <span class="slider"></span>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary btn-action"
                                                    onclick="editSchedule(<?php echo htmlspecialchars(json_encode($schedule)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-action"
                                                    onclick="deleteSchedule(<?php echo $schedule['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Special Closures Card -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-times me-2"></i> Special Closures</span>
                            <button class="btn btn-sm btn-light" onclick="openClosureModal()">
                                <i class="fas fa-plus me-1"></i> Add Closure
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="closureTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Reason</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($closures)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                                                <p class="text-muted">No special closures found.</p>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($closures as $closure): ?>
                                        <tr>
                                            <td>#<?php echo $closure['id']; ?></td>
                                            <td><strong><?php echo date('d M, Y', strtotime($closure['closure_date'])); ?></strong>
                                            </td>
                                            <td><?php echo date('l', strtotime($closure['closure_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($closure['reason'] ?? 'No reason provided'); ?>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($closure['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary btn-action"
                                                    onclick="editClosure(<?php echo htmlspecialchars(json_encode($closure)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-action"
                                                    onclick="deleteClosure(<?php echo $closure['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalTitle">Add Schedule</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="scheduleForm">
                    <div class="modal-body">
                        <input type="hidden" name="schedule_action" value="save">
                        <input type="hidden" name="schedule_id" id="schedule_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Day</label>
                            <select class="form-select" name="day_of_week" id="day_of_week" required>
                                <option value="">Choose a day...</option>
                                <?php foreach ($dayNames as $num => $name): ?>
                                <option value="<?php echo $num; ?>"><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Availability</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_available" id="is_available"
                                    checked>
                                <label class="form-check-label" for="is_available">Available for
                                    appointments</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Special Closure Modal -->
    <div class="modal fade" id="closureModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="closureModalTitle">Add Special Closure</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="closureForm">
                    <div class="modal-body">
                        <input type="hidden" name="closure_action" value="save">
                        <input type="hidden" name="closure_id" id="closure_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Closure Date</label>
                            <input type="date" class="form-control" name="closure_date" id="closure_date"
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason</label>
                            <textarea class="form-control" name="reason" id="reason" rows="3"
                                placeholder="Why are you closed on this date?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Closure</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this item? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="schedule_id" id="delete_id">
                        <input type="hidden" name="schedule_action" id="delete_action">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
    // Initialize DataTables
    $(document).ready(function() {
        $('#scheduleTable').DataTable({
            pageLength: 10,
            ordering: true,
            language: {
                emptyTable: "No schedules found"
            }
        });

        $('#closureTable').DataTable({
            pageLength: 10,
            ordering: true,
            order: [
                [1, 'desc']
            ],
            language: {
                emptyTable: "No closures found"
            }
        });
    });

    // Schedule Modal Functions
    function openScheduleModal() {
        document.getElementById('scheduleModalTitle').innerText = 'Add Schedule';
        document.getElementById('schedule_id').value = '';
        document.getElementById('day_of_week').value = '';
        document.getElementById('is_available').checked = true;
        new bootstrap.Modal(document.getElementById('scheduleModal')).show();
    }

    function editSchedule(schedule) {
        document.getElementById('scheduleModalTitle').innerText = 'Edit Schedule';
        document.getElementById('schedule_id').value = schedule.id;
        document.getElementById('day_of_week').value = schedule.day_of_week;
        document.getElementById('is_available').checked = schedule.is_available == 1;
        new bootstrap.Modal(document.getElementById('scheduleModal')).show();
    }

    function deleteSchedule(id) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_action').value = 'delete';
        document.getElementById('deleteForm').action = '';
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Closure Modal Functions
    function openClosureModal() {
        document.getElementById('closureModalTitle').innerText = 'Add Special Closure';
        document.getElementById('closure_id').value = '';
        document.getElementById('closure_date').value = '';
        document.getElementById('reason').value = '';
        new bootstrap.Modal(document.getElementById('closureModal')).show();
    }

    function editClosure(closure) {
        document.getElementById('closureModalTitle').innerText = 'Edit Special Closure';
        document.getElementById('closure_id').value = closure.id;
        document.getElementById('closure_date').value = closure.closure_date;
        document.getElementById('reason').value = closure.reason || '';
        new bootstrap.Modal(document.getElementById('closureModal')).show();
    }

    function deleteClosure(id) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_action').value = 'delete';
        document.getElementById('deleteForm').action = '';
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Toggle Schedule Status via AJAX
    function toggleScheduleStatus(scheduleId, isAvailable) {
        fetch('updateScheduleStatus.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    schedule_id: scheduleId,
                    is_available: isAvailable ? 1 : 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        Status updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.main-content').insertBefore(alert, document.querySelector(
                        '.main-content').firstChild);

                    setTimeout(() => alert.remove(), 3000);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Form validation
    document.getElementById('scheduleForm').addEventListener('submit', function(e) {
        const day = document.getElementById('day_of_week').value;
        if (!day) {
            e.preventDefault();
            alert('Please select a day');
        }
    });

    document.getElementById('closureForm').addEventListener('submit', function(e) {
        const date = document.getElementById('closure_date').value;
        if (!date) {
            e.preventDefault();
            alert('Please select a date');
        }
    });
    </script>
</body>

</html>