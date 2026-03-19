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

// Fetch all token limits with details
$tokenLimits = $db->getAll(
    'token_limits tl',
    [],
    'tl.token_type_id, tl.token_category_id ASC',
    '',
    [
        [
            'type' => 'LEFT',
            'table' => 'token_types tt',
            'on' => 'tl.token_type_id = tt.id'
        ],
        [
            'type' => 'LEFT',
            'table' => 'token_categories tc',
            'on' => 'tl.token_category_id = tc.id'
        ]
    ],
    'tl.*, tt.type_name, tc.category_name'
);

// Fetch all token type restrictions with details
$restrictions = $db->getAll(
    'token_type_restrictions tr',
    [],
    'tr.token_type_id, tr.day_of_week ASC',
    '',
    [
        [
            'type' => 'LEFT',
            'table' => 'token_types tt',
            'on' => 'tr.token_type_id = tt.id'
        ],
        [
            'type' => 'LEFT',
            'table' => 'doctors d',
            'on' => 'tr.doctor_id = d.id'
        ]
    ],
    'tr.*, tt.type_name, d.name as doctor_name'
);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Limits & Restrictions Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
        }

        .card-header.light {
            background: linear-gradient(135deg, var(--info-color), var(--primary-color));
        }

        .card-header.warning {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
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
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
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

        .limit-badge {
            background: var(--info-color);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
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

        .modal-header.light {
            background: linear-gradient(135deg, var(--info-color), var(--primary-color));
        }

        .modal-header.warning {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
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

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
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
            transition: .3s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--success-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 8px 8px 0 0;
        }

        .nav-tabs .nav-link:hover {
            border: none;
            color: var(--primary-color);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background: white;
            border-bottom: 3px solid var(--primary-color);
        }

        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
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

        .day-checkbox {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .day-checkbox label {
            margin-left: 5px;
            cursor: pointer;
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
                <div class="main-content"  id="mainContent">
                    <!-- Header -->
                    <div class="page-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Token Limits & Restrictions</h2>
                            <p class="text-muted mb-0">Manage daily token limits and type restrictions per doctor</p>
                        </div>
                        <div>
                            <span class="badge bg-primary p-2">
                                <i class="fas fa-chart-line me-1"></i> Token Configuration
                            </span>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="info-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5><i class="fas fa-info-circle me-2"></i> Limits & Restrictions</h5>
                                <p class="mb-0">Set daily token limits for each type/category combination and configure which token types are allowed for each doctor on specific days.</p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-light text-dark p-2">
                                    <i class="fas fa-calendar-week me-1"></i> Days: 1=Sun, 7=Sat
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="limits-tab" data-bs-toggle="tab" data-bs-target="#limits" type="button" role="tab">
                                <i class="fas fa-chart-line me-2"></i>Daily Token Limits
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="restrictions-tab" data-bs-toggle="tab" data-bs-target="#restrictions" type="button" role="tab">
                                <i class="fas fa-ban me-2"></i>Token Type Restrictions
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="myTabContent">
                        <!-- Token Limits Tab -->
                        <div class="tab-pane fade show active" id="limits" role="tabpanel">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-chart-line me-2"></i> Daily Token Limits</span>
                                    <button class="btn btn-sm btn-light" onclick="openLimitModal()">
                                        <i class="fas fa-plus me-1"></i> Add Limit
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="limitsTable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Token Type</th>
                                                    <th>Category</th>
                                                    <th>Daily Limit</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Updated</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($tokenLimits)): ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center py-4">
                                                            <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                                                            <p class="text-muted">No token limits found. Add your first limit.</p>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($tokenLimits as $limit): ?>
                                                        <tr id="limit-row-<?php echo $limit['id']; ?>">
                                                            <td>#<?php echo $limit['id']; ?></td>
                                                            <td>
                                                                <span class="type-badge">
                                                                    <?php echo htmlspecialchars(ucfirst($limit['type_name'] ?? 'Unknown')); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="category-badge">
                                                                    <?php echo htmlspecialchars(ucfirst($limit['category_name'] ?? 'Unknown')); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="limit-badge">
                                                                    <?php echo $limit['daily_limit']; ?> tokens
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="status-badge <?php echo $limit['is_active'] ? 'active' : 'inactive'; ?>">
                                                                    <i class="fas <?php echo $limit['is_active'] ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                                                    <?php echo $limit['is_active'] ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo date('d M Y', strtotime($limit['created_at'])); ?></td>
                                                            <td><?php echo date('d M Y', strtotime($limit['updated_at'])); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary btn-action" 
                                                                        onclick="editLimit(<?php echo htmlspecialchars(json_encode($limit)); ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger btn-action" 
                                                                        onclick="deleteLimit(<?php echo $limit['id']; ?>)">
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

                        <!-- Token Restrictions Tab -->
                        <div class="tab-pane fade" id="restrictions" role="tabpanel">
                            <div class="card">
                                <div class="card-header warning d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-ban me-2"></i> Token Type Restrictions</span>
                                    <button class="btn btn-sm btn-light" onclick="openRestrictionModal()">
                                        <i class="fas fa-plus me-1"></i> Add Restriction
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="restrictionsTable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Doctor</th>
                                                    <th>Token Type</th>
                                                    <th>Day</th>
                                                    <th>Day Number</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($restrictions)): ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center py-4">
                                                            <i class="fas fa-ban fa-2x text-muted mb-2"></i>
                                                            <p class="text-muted">No restrictions found. Add your first restriction.</p>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($restrictions as $restriction): ?>
                                                        <tr id="restriction-row-<?php echo $restriction['id']; ?>">
                                                            <td>#<?php echo $restriction['id']; ?></td>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($restriction['doctor_name'] ?? 'Unknown'); ?></strong>
                                                            </td>
                                                            <td>
                                                                <span class="type-badge">
                                                                    <?php echo htmlspecialchars(ucfirst($restriction['type_name'] ?? 'Unknown')); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo $dayNames[$restriction['day_of_week']] ?? 'Unknown'; ?></td>
                                                            <td><?php echo $restriction['day_of_week']; ?></td>
                                                            <td>
                                                                <?php if ($restriction['is_allowed']): ?>
                                                                    <span class="status-badge active">
                                                                        <i class="fas fa-check-circle me-1"></i> Allowed
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="status-badge inactive">
                                                                        <i class="fas fa-times-circle me-1"></i> Restricted
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo date('d M Y', strtotime($restriction['created_at'])); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary btn-action" 
                                                                        onclick="editRestriction(<?php echo htmlspecialchars(json_encode($restriction)); ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger btn-action" 
                                                                        onclick="deleteRestriction(<?php echo $restriction['id']; ?>)">
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
        </div>
    </div>

    <!-- Token Limit Modal -->
    <div class="modal fade" id="limitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="limitModalTitle">Add Token Limit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="limit_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Token Type</label>
                        <select class="form-select" id="limit_token_type_id" required>
                            <option value="">Select Token Type...</option>
                            <?php foreach ($tokenTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($type['type_name'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Token Category</label>
                        <select class="form-select" id="limit_token_category_id" required>
                            <option value="">Select Token Category...</option>
                            <?php foreach ($tokenCategories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($category['category_name'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Daily Limit</label>
                        <input type="number" class="form-control" id="daily_limit" 
                               required min="1" max="1000" placeholder="Enter daily limit">
                        <small class="text-muted">Maximum number of tokens per day</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="limit_is_active" checked>
                            <label class="form-check-label" for="limit_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveLimit()">Save Limit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Token Restriction Modal -->
    <div class="modal fade" id="restrictionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header warning">
                    <h5 class="modal-title" id="restrictionModalTitle">Add Token Restriction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="restriction_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Doctor</label>
                        <select class="form-select" id="restriction_doctor_id" required>
                            <option value="">Select Doctor...</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>">
                                    <?php echo htmlspecialchars($doctor['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Token Type</label>
                        <select class="form-select" id="restriction_token_type_id" required>
                            <option value="">Select Token Type...</option>
                            <?php foreach ($tokenTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($type['type_name'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Day of Week</label>
                        <select class="form-select" id="restriction_day_of_week" required>
                            <option value="">Select Day...</option>
                            <?php foreach ($dayNames as $num => $name): ?>
                                <option value="<?php echo $num; ?>"><?php echo $name; ?> (<?php echo $num; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="restriction_is_allowed" checked>
                            <label class="form-check-label" for="restriction_is_allowed">Allowed (Uncheck to Restrict)</label>
                        </div>
                        <small class="text-muted">When checked, this token type is allowed for this doctor on this day</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveRestriction()">Save Restriction</button>
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
                    <p id="deleteMessage">Are you sure you want to delete this item? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="delete_id">
                    <input type="hidden" id="delete_type">
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

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#limitsTable').DataTable({
                pageLength: 10,
                ordering: true,
                language: {
                    emptyTable: "No token limits found"
                }
            });

            $('#restrictionsTable').DataTable({
                pageLength: 10,
                ordering: true,
                 order: [
        [1, 'asc'], // Column 2 (Doctor) - index 1
        [4, 'asc']  // Column 4 (Day) - index 3
    ],
                language: {
                    emptyTable: "No restrictions found"
                }
            });
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

        // Token Limit Functions
        function openLimitModal() {
            document.getElementById('limitModalTitle').innerText = 'Add Token Limit';
            document.getElementById('limit_id').value = '';
            document.getElementById('limit_token_type_id').value = '';
            document.getElementById('limit_token_category_id').value = '';
            document.getElementById('daily_limit').value = '';
            document.getElementById('limit_is_active').checked = true;
            new bootstrap.Modal(document.getElementById('limitModal')).show();
        }

        function editLimit(limit) {
            document.getElementById('limitModalTitle').innerText = 'Edit Token Limit';
            document.getElementById('limit_id').value = limit.id;
            document.getElementById('limit_token_type_id').value = limit.token_type_id;
            document.getElementById('limit_token_category_id').value = limit.token_category_id;
            document.getElementById('daily_limit').value = limit.daily_limit;
            document.getElementById('limit_is_active').checked = limit.is_active == 1;
            new bootstrap.Modal(document.getElementById('limitModal')).show();
        }

        function saveLimit() {
            const id = document.getElementById('limit_id').value;
            const data = {
                limit_action: 'save',
                limit_id: id,
                token_type_id: document.getElementById('limit_token_type_id').value,
                token_category_id: document.getElementById('limit_token_category_id').value,
                daily_limit: document.getElementById('daily_limit').value,
                is_active: document.getElementById('limit_is_active').checked ? 1 : 0
            };

            if (!data.token_type_id) {
                showToast('Please select a token type', 'warning');
                return;
            }
            if (!data.token_category_id) {
                showToast('Please select a token category', 'warning');
                return;
            }
            if (!data.daily_limit || data.daily_limit < 1) {
                showToast('Please enter a valid daily limit', 'warning');
                return;
            }

            showLoading();

            fetch('tokenLimitApi.php', {
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
                    bootstrap.Modal.getInstance(document.getElementById('limitModal')).hide();
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

        function deleteLimit(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_type').value = 'limit';
            document.getElementById('deleteMessage').innerText = 'Are you sure you want to delete this token limit?';
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Token Restriction Functions
        function openRestrictionModal() {
            document.getElementById('restrictionModalTitle').innerText = 'Add Token Restriction';
            document.getElementById('restriction_id').value = '';
            document.getElementById('restriction_doctor_id').value = '';
            document.getElementById('restriction_token_type_id').value = '';
            document.getElementById('restriction_day_of_week').value = '';
            document.getElementById('restriction_is_allowed').checked = true;
            new bootstrap.Modal(document.getElementById('restrictionModal')).show();
        }

        function editRestriction(restriction) {
            document.getElementById('restrictionModalTitle').innerText = 'Edit Token Restriction';
            document.getElementById('restriction_id').value = restriction.id;
            document.getElementById('restriction_doctor_id').value = restriction.doctor_id;
            document.getElementById('restriction_token_type_id').value = restriction.token_type_id;
            document.getElementById('restriction_day_of_week').value = restriction.day_of_week;
            document.getElementById('restriction_is_allowed').checked = restriction.is_allowed == 1;
            new bootstrap.Modal(document.getElementById('restrictionModal')).show();
        }

        function saveRestriction() {
            const id = document.getElementById('restriction_id').value;
            const data = {
                restriction_action: 'save',
                restriction_id: id,
                doctor_id: document.getElementById('restriction_doctor_id').value,
                token_type_id: document.getElementById('restriction_token_type_id').value,
                day_of_week: document.getElementById('restriction_day_of_week').value,
                is_allowed: document.getElementById('restriction_is_allowed').checked ? 1 : 0
            };

            if (!data.doctor_id) {
                showToast('Please select a doctor', 'warning');
                return;
            }
            if (!data.token_type_id) {
                showToast('Please select a token type', 'warning');
                return;
            }
            if (!data.day_of_week) {
                showToast('Please select a day', 'warning');
                return;
            }

            showLoading();

            fetch('tokenLimitApi.php', {
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
                    bootstrap.Modal.getInstance(document.getElementById('restrictionModal')).hide();
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

        function deleteRestriction(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_type').value = 'restriction';
            document.getElementById('deleteMessage').innerText = 'Are you sure you want to delete this token restriction?';
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Confirm Delete
        function confirmDelete() {
            const id = document.getElementById('delete_id').value;
            const type = document.getElementById('delete_type').value;
            
            const data = {};
            
            if (type === 'limit') {
                data.limit_action = 'delete';
                data.limit_id = id;
            } else {
                data.restriction_action = 'delete';
                data.restriction_id = id;
            }

            showLoading();

            fetch('tokenLimitApi.php', {
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
    </script>
</body>
</html>