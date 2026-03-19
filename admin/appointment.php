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
$doctor_id = $_SESSION['doctor_id'] ?? 1;

// Fetch all token types
$tokenTypes = $db->getAll('token_types', ['is_active' => 1], 'type_name ASC');

// Fetch all token categories
$tokenCategories = $db->getAll('token_categories', ['is_active' => 1], 'category_name ASC');

// Fetch all doctors
$doctors = $db->getAll('doctors', ['is_active' => 1], 'name ASC');

// Fetch all appointments with details
$appointments = $db->getAll(
    'tokens t',
    [],
    't.token_date DESC, t.token_time ASC',
    '',
    [
        [
            'type' => 'LEFT',
            'table' => 'token_types tt',
            'on' => 't.token_type_id = tt.id'
        ],
        [
            'type' => 'LEFT',
            'table' => 'token_categories tc',
            'on' => 't.token_category_id = tc.id'
        ],
        [
            'type' => 'LEFT',
            'table' => 'doctors d',
            'on' => 't.doctor_id = d.id'
        ]
    ],
    't.*, tt.type_name, tc.category_name, d.name as doctor_name, d.specialization'
);

// Status colors and icons
$statusConfig = [
    'pending' => ['badge' => 'warning', 'icon' => 'fa-clock', 'text' => 'Pending'],
    'confirmed' => ['badge' => 'info', 'icon' => 'fa-check-circle', 'text' => 'Confirmed'],
    'completed' => ['badge' => 'success', 'icon' => 'fa-check-double', 'text' => 'Completed'],
    'cancelled' => ['badge' => 'danger', 'icon' => 'fa-times-circle', 'text' => 'Cancelled']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #df12cb;
            --secondary-color: #f2beeb;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            padding: 30px;
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
            background: linear-gradient(135deg, var(--primary-color), #34495e);
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
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-add:hover {
            background: #219a52;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.confirmed {
            background: #cce5ff;
            color: #004085;
        }

        .status-badge.completed {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .type-badge {
            background: var(--info-color);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .category-badge {
            background: var(--success-color);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(223, 18, 203, 0.25);
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: white;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
            min-width: 300px;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast.success {
            border-left: 4px solid var(--success-color);
        }

        .toast.error {
            border-left: 4px solid var(--danger-color);
        }

        .toast.warning {
            border-left: 4px solid var(--warning-color);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.pending {
            background: #fff3cd;
            color: #856404;
        }

        .stat-icon.confirmed {
            background: #cce5ff;
            color: #004085;
        }

        .stat-icon.completed {
            background: #d4edda;
            color: #155724;
        }

        .stat-icon.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .stat-icon.total {
            background: #e2e3e5;
            color: #383d41;
        }

        .dt-buttons {
            margin-bottom: 15px;
        }

        .btn-export {
            background: var(--info-color);
            color: white;
            border: none;
            margin-right: 5px;
        }

        .btn-export:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <?php include_once("sidebar.php"); ?>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <!-- Header -->
                    <div class="page-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Appointment Management</h2>
                            <p class="text-muted mb-0">Manage all patient appointments and token status</p>
                        </div>
                        <div>
                            <span class="badge bg-primary p-2">
                                <i class="fas fa-calendar-check me-1"></i> Total: <?php echo count($appointments); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <?php
                        $stats = [
                            'total' => count($appointments),
                            'pending' => 0,
                            'confirmed' => 0,
                            'completed' => 0,
                            'cancelled' => 0
                        ];
                        
                        foreach ($appointments as $apt) {
                            if (isset($stats[$apt['status']])) {
                                $stats[$apt['status']]++;
                            }
                        }
                        ?>
                        
                        <div class="col-md-2 col-6">
                            <div class="stat-card d-flex align-items-center">
                                <div class="stat-icon total me-3">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo $stats['total']; ?></h5>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2 col-6">
                            <div class="stat-card d-flex align-items-center">
                                <div class="stat-icon pending me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo $stats['pending']; ?></h5>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2 col-6">
                            <div class="stat-card d-flex align-items-center">
                                <div class="stat-icon confirmed me-3">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo $stats['confirmed']; ?></h5>
                                    <small class="text-muted">Confirmed</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2 col-6">
                            <div class="stat-card d-flex align-items-center">
                                <div class="stat-icon completed me-3">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo $stats['completed']; ?></h5>
                                    <small class="text-muted">Completed</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2 col-6">
                            <div class="stat-card d-flex align-items-center">
                                <div class="stat-icon cancelled me-3">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo $stats['cancelled']; ?></h5>
                                    <small class="text-muted">Cancelled</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="filter-section">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label class="fw-bold">Filter by Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold">Filter by Date</label>
                                <input type="date" class="form-control" id="dateFilter">
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold">Filter by Doctor</label>
                                <select class="form-select" id="doctorFilter">
                                    <option value="">All Doctors</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>">
                                            <?php echo htmlspecialchars($doctor['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary w-100" onclick="applyFilters()">
                                    <i class="fas fa-filter me-2"></i>Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments Table Card -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-check me-2"></i> Appointments List</span>
                            <button class="btn btn-sm btn-light" onclick="openAppointmentModal()">
                                <i class="fas fa-plus me-1"></i> New Appointment
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="appointmentsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Token #</th>
                                            <th>Patient</th>
                                            <th>Contact</th>
                                            <th>Doctor</th>
                                            <th>Type</th>
                                            <th>Category</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($appointments)): ?>
                                            <tr>
                                                <td colspan="12" class="text-center py-4">
                                                    <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted">No appointments found. Create your first appointment.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($appointments as $apt): ?>
                                                <tr id="apt-row-<?php echo $apt['id']; ?>">
                                                    <td>#<?php echo $apt['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($apt['token_number']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($apt['patient_name']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($apt['patient_phone']); ?><br>
                                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($apt['patient_email']); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($apt['doctor_name'] ?? 'Unknown'); ?></strong>
                                                        <br><small><?php echo htmlspecialchars($apt['specialization'] ?? ''); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="type-badge">
                                                            <?php echo htmlspecialchars(ucfirst($apt['type_name'] ?? 'Unknown')); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="category-badge">
                                                            <?php echo htmlspecialchars(ucfirst($apt['category_name'] ?? 'Unknown')); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d M Y', strtotime($apt['token_date'])); ?></td>
                                                    <td><?php echo date('h:i A', strtotime($apt['token_time'])); ?></td>
                                                    <td><strong>Rs <?php echo number_format($apt['token_price'], 2); ?></strong></td>
                                                    <td>
                                                        <span class="status-badge <?php echo $apt['status']; ?>">
                                                            <i class="fas <?php echo $statusConfig[$apt['status']]['icon']; ?> me-1"></i>
                                                            <?php echo $statusConfig[$apt['status']]['text']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info btn-action" 
                                                                onclick="viewAppointment(<?php echo htmlspecialchars(json_encode($apt)); ?>)"
                                                                title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-primary btn-action" 
                                                                onclick="editAppointment(<?php echo htmlspecialchars(json_encode($apt)); ?>)"
                                                                title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger btn-action" 
                                                                onclick="deleteAppointment(<?php echo $apt['id']; ?>)"
                                                                title="Delete">
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

    <!-- Appointment Modal (Add/Edit) -->
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalTitle">New Appointment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="appointment_id">
                    <input type="hidden" id="appointment_token_number">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Patient Name *</label>
                                <input type="text" class="form-control" id="patient_name" 
                                       required placeholder="Enter patient name">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phone *</label>
                                <input type="tel" class="form-control" id="patient_phone" 
                                       required placeholder="03XXXXXXXXX">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="patient_email" 
                                       placeholder="patient@example.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Doctor *</label>
                                <select class="form-select" id="doctor_id" required>
                                    <option value="">Select Doctor...</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>">
                                            <?php echo htmlspecialchars($doctor['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Token Type *</label>
                                <select class="form-select" id="token_type_id" required onchange="loadCategories()">
                                    <option value="">Select Type...</option>
                                    <?php foreach ($tokenTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>">
                                            <?php echo htmlspecialchars(ucfirst($type['type_name'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Token Category *</label>
                                <select class="form-select" id="token_category_id" required>
                                    <option value="">Select Category...</option>
                                    <?php foreach ($tokenCategories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                data-type="<?php echo $category['token_type_id']; ?>">
                                            <?php echo htmlspecialchars(ucfirst($category['category_name'])); ?> 
                                            (Rs <?php echo $category['base_price']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Appointment Date *</label>
                                <input type="date" class="form-control" id="token_date" 
                                       required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Appointment Time *</label>
                                <input type="time" class="form-control" id="token_time" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Price (Rs) *</label>
                                <input type="number" class="form-control" id="token_price" 
                                       required step="0.01" min="0" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Meeting Link (For Online)</label>
                                <input type="url" class="form-control" id="meeting_link" 
                                       placeholder="https://meet.google.com/...">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select class="form-select" id="status">
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea class="form-control" id="notes" rows="2" 
                                  placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveAppointment()">Save Appointment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Appointment Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewDetails">
                    <!-- Details will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
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
                    <p id="deleteMessage">Are you sure you want to delete this appointment? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="delete_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <script>
        let appointmentsTable;
        
        // Initialize DataTables
        $(document).ready(function() {
            appointmentsTable = $('#appointmentsTable').DataTable({
                pageLength: 25,
                ordering: true,
                order: [[7, 'desc'], [8, 'asc']], // Order by date desc, time asc
                dom: 'Bfrtip',
                buttons: [
                    {
                        text: '<i class="fas fa-copy me-1"></i> Copy',
                        extend: 'copy',
                        className: 'btn btn-sm btn-export'
                    },
                    {
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        extend: 'excel',
                        className: 'btn btn-sm btn-export'
                    },
                    {
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                        extend: 'pdf',
                        className: 'btn btn-sm btn-export'
                    },
                    {
                        text: '<i class="fas fa-print me-1"></i> Print',
                        extend: 'print',
                        className: 'btn btn-sm btn-export'
                    }
                ],
                columnDefs: [
                    { targets: [3, 11], orderable: false }, // Contact and Actions columns
                    { targets: [10], orderable: true }, // Status column
                    { targets: [9], orderable: true } // Price column
                ],
                language: {
                    emptyTable: "No appointments found",
                    info: "Showing _START_ to _END_ of _TOTAL_ appointments",
                    infoEmpty: "Showing 0 to 0 of 0 appointments",
                    search: "Search:",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });

        // Filter functions
        function applyFilters() {
            const status = $('#statusFilter').val();
            const date = $('#dateFilter').val();
            const doctor = $('#doctorFilter').val();
            
            // Custom filtering logic
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    const rowStatus = data[10].toLowerCase();
                    const rowDate = data[7];
                    const rowDoctor = data[4];
                    
                    let statusMatch = !status || rowStatus.includes(status);
                    let dateMatch = !date || rowDate.includes(date);
                    let doctorMatch = !doctor || rowDoctor.includes(doctor);
                    
                    return statusMatch && dateMatch && doctorMatch;
                }
            );
            
            appointmentsTable.draw();
            $.fn.dataTable.ext.search.pop();
        }

        // Clear filters
        function clearFilters() {
            $('#statusFilter').val('');
            $('#dateFilter').val('');
            $('#doctorFilter').val('');
            appointmentsTable.search('').columns().search('').draw();
        }

        // Load categories based on selected type
        function loadCategories() {
            const typeId = $('#token_type_id').val();
            const categorySelect = $('#token_category_id');
            
            categorySelect.find('option').show();
            
            if (typeId) {
                categorySelect.find('option').each(function() {
                    if ($(this).data('type') != typeId && $(this).val() != '') {
                        $(this).hide();
                    }
                });
            }
            
            categorySelect.val('');
            $('#token_price').val('');
        }

        // Update price when category changes
        $('#token_category_id').change(function() {
            const selected = $(this).find('option:selected');
            if (selected.val()) {
                const price = selected.text().match(/Rs ([\d.]+)/);
                if (price) {
                    $('#token_price').val(price[1]);
                }
            }
        });

        // Show toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle'} me-2"></i>
                    <div>${message}</div>
                </div>
            `;
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Show/hide loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        // Appointment CRUD Functions
        function openAppointmentModal() {
            document.getElementById('appointmentModalTitle').innerText = 'New Appointment';
            document.getElementById('appointment_id').value = '';
            document.getElementById('appointment_token_number').value = '';
            document.getElementById('patient_name').value = '';
            document.getElementById('patient_phone').value = '';
            document.getElementById('patient_email').value = '';
            document.getElementById('doctor_id').value = '';
            document.getElementById('token_type_id').value = '';
            document.getElementById('token_category_id').value = '';
            document.getElementById('token_date').value = '';
            document.getElementById('token_time').value = '';
            document.getElementById('token_price').value = '';
            document.getElementById('meeting_link').value = '';
            document.getElementById('status').value = 'pending';
            document.getElementById('notes').value = '';
            
            // Show all category options
            $('#token_category_id option').show();
            
            new bootstrap.Modal(document.getElementById('appointmentModal')).show();
        }

        function editAppointment(apt) {
            document.getElementById('appointmentModalTitle').innerText = 'Edit Appointment';
            document.getElementById('appointment_id').value = apt.id;
            document.getElementById('appointment_token_number').value = apt.token_number;
            document.getElementById('patient_name').value = apt.patient_name;
            document.getElementById('patient_phone').value = apt.patient_phone;
            document.getElementById('patient_email').value = apt.patient_email || '';
            document.getElementById('doctor_id').value = apt.doctor_id;
            document.getElementById('token_type_id').value = apt.token_type_id;
            document.getElementById('token_category_id').value = apt.token_category_id;
            document.getElementById('token_date').value = apt.token_date;
            document.getElementById('token_time').value = apt.token_time;
            document.getElementById('token_price').value = apt.token_price;
            document.getElementById('meeting_link').value = apt.meeting_link || '';
            document.getElementById('status').value = apt.status;
            document.getElementById('notes').value = apt.notes || '';
            
            loadCategories();
            new bootstrap.Modal(document.getElementById('appointmentModal')).show();
        }

        function viewAppointment(apt) {
            const statusConfig = {
                'pending': { badge: 'warning', icon: 'fa-clock', text: 'Pending' },
                'confirmed': { badge: 'info', icon: 'fa-check-circle', text: 'Confirmed' },
                'completed': { badge: 'success', icon: 'fa-check-double', text: 'Completed' },
                'cancelled': { badge: 'danger', icon: 'fa-times-circle', text: 'Cancelled' }
            };
            
            const status = statusConfig[apt.status] || statusConfig.pending;
            
            const details = `
                <div class="text-center mb-4">
                    <span class="status-badge ${apt.status}" style="font-size: 1rem;">
                        <i class="fas ${status.icon} me-1"></i> ${status.text}
                    </span>
                </div>
                
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Token Number</th>
                        <td><strong>${apt.token_number}</strong></td>
                    </tr>
                    <tr>
                        <th>Patient Name</th>
                        <td>${apt.patient_name}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>${apt.patient_phone}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>${apt.patient_email || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Doctor</th>
                        <td>Dr. ${apt.doctor_name || 'Unknown'} ${apt.specialization ? '- ' + apt.specialization : ''}</td>
                    </tr>
                    <tr>
                        <th>Token Type</th>
                        <td><span class="type-badge">${apt.type_name ? ucfirst(apt.type_name) : 'Unknown'}</span></td>
                    </tr>
                    <tr>
                        <th>Token Category</th>
                        <td><span class="category-badge">${apt.category_name ? ucfirst(apt.category_name) : 'Unknown'}</span></td>
                    </tr>
                    <tr>
                        <th>Date & Time</th>
                        <td>${new Date(apt.token_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })} at ${new Date('2000-01-01T' + apt.token_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}</td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td><strong>Rs ${Number(apt.token_price).toFixed(2)}</strong></td>
                    </tr>
                    ${apt.meeting_link ? `
                    <tr>
                        <th>Meeting Link</th>
                        <td><a href="${apt.meeting_link}" target="_blank">${apt.meeting_link}</a></td>
                    </tr>
                    ` : ''}
                    ${apt.notes ? `
                    <tr>
                        <th>Notes</th>
                        <td>${apt.notes}</td>
                    </tr>
                    ` : ''}
                    <tr>
                        <th>Created</th>
                        <td>${new Date(apt.created_at).toLocaleString()}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>${new Date(apt.updated_at).toLocaleString()}</td>
                    </tr>
                </table>
            `;
            
            document.getElementById('viewDetails').innerHTML = details;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        function saveAppointment() {
            const id = document.getElementById('appointment_id').value;
            const data = {
                appointment_action: 'save',
                appointment_id: id,
                token_number: document.getElementById('appointment_token_number').value,
                patient_name: document.getElementById('patient_name').value.trim(),
                patient_phone: document.getElementById('patient_phone').value.trim(),
                patient_email: document.getElementById('patient_email').value.trim(),
                doctor_id: document.getElementById('doctor_id').value,
                token_type_id: document.getElementById('token_type_id').value,
                token_category_id: document.getElementById('token_category_id').value,
                token_date: document.getElementById('token_date').value,
                token_time: document.getElementById('token_time').value,
                token_price: document.getElementById('token_price').value,
                meeting_link: document.getElementById('meeting_link').value,
                status: document.getElementById('status').value,
                notes: document.getElementById('notes').value
            };

            // Validation
            if (!data.patient_name) {
                showToast('Please enter patient name', 'warning');
                return;
            }
            if (!data.patient_phone) {
                showToast('Please enter phone number', 'warning');
                return;
            }
            if (!data.doctor_id) {
                showToast('Please select a doctor', 'warning');
                return;
            }
            if (!data.token_type_id) {
                showToast('Please select token type', 'warning');
                return;
            }
            if (!data.token_category_id) {
                showToast('Please select token category', 'warning');
                return;
            }
            if (!data.token_date) {
                showToast('Please select appointment date', 'warning');
                return;
            }
            if (!data.token_time) {
                showToast('Please select appointment time', 'warning');
                return;
            }

            showLoading();

            fetch('appointmentApi.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                hideLoading();
                if (result.success) {
                    showToast(result.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('appointmentModal')).hide();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(result.message, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                showToast('Network error occurred', 'error');
                console.error('Error:', error);
            });
        }

        function deleteAppointment(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteMessage').innerText = 'Are you sure you want to delete this appointment? This action cannot be undone.';
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function confirmDelete() {
            const id = document.getElementById('delete_id').value;
            
            const data = {
                appointment_action: 'delete',
                appointment_id: id
            };

            showLoading();

            fetch('appointmentApi.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                hideLoading();
                if (result.success) {
                    showToast(result.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(result.message, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                showToast('Network error occurred', 'error');
                console.error('Error:', error);
            });
        }

        // Utility function
        function ucfirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    </script>
</body>
</html>