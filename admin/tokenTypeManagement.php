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

// Fetch all token types
$tokenTypes = $db->getAll('token_types', [], 'type_name ASC');

// Fetch all token categories with type names
$tokenCategories = $db->getAll(
    'token_categories tc',
    [],
    'tc.token_type_id, tc.category_name ASC',
    '',
    [
        [
            'type' => 'LEFT',
            'table' => 'token_types tt',
            'on' => 'tc.token_type_id = tt.id'
        ]
    ],
    'tc.*, tt.type_name'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Type & Category Management</title>
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

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(223, 18, 203, 0.25);
        }

        .price-input {
            position: relative;
        }

        .price-input:before {
            content: 'Rs';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: bold;
            color: var(--primary-color);
        }

        .price-input input {
            padding-left: 40px;
        }

        .type-badge {
            background: var(--info-color);
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
                            <h2 class="mb-1">Token Management</h2>
                            <p class="text-muted mb-0">Manage token types and categories</p>
                        </div>
                        <div>
                            <span class="badge bg-primary p-2">
                                <i class="fas fa-tags me-1"></i> Token Configuration
                            </span>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="info-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5><i class="fas fa-info-circle me-2"></i> Token Configuration</h5>
                                <p class="mb-0">Manage token types (Physical/Online) and their categories (Normal/Urgent) with pricing.</p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-light text-dark p-2">
                                    <i class="fas fa-rupee-sign me-1"></i> Set base prices for categories
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="types-tab" data-bs-toggle="tab" data-bs-target="#types" type="button" role="tab">
                                <i class="fas fa-list me-2"></i>Token Types
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab">
                                <i class="fas fa-tags me-2"></i>Token Categories
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="myTabContent">
                        <!-- Token Types Tab -->
                        <div class="tab-pane fade show active" id="types" role="tabpanel">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-list me-2"></i> Token Types</span>
                                    <button class="btn btn-sm btn-light" onclick="openTypeModal()">
                                        <i class="fas fa-plus me-1"></i> Add Type
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="typesTable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Type Name</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($tokenTypes)): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4">
                                                            <i class="fas fa-list fa-2x text-muted mb-2"></i>
                                                            <p class="text-muted">No token types found. Add your first type.</p>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($tokenTypes as $type): ?>
                                                        <tr id="type-row-<?php echo $type['id']; ?>">
                                                            <td>#<?php echo $type['id']; ?></td>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars(ucfirst($type['type_name'])); ?></strong>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($type['description'] ?? 'No description'); ?></td>
                                                            <td>
                                                                <span class="status-badge <?php echo $type['is_active'] ? 'active' : 'inactive'; ?>">
                                                                    <i class="fas <?php echo $type['is_active'] ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                                                    <?php echo $type['is_active'] ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo date('d M Y', strtotime($type['created_at'])); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary btn-action" 
                                                                        onclick="editType(<?php echo htmlspecialchars(json_encode($type)); ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger btn-action" 
                                                                        onclick="deleteType(<?php echo $type['id']; ?>)">
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

                        <!-- Token Categories Tab -->
                        <div class="tab-pane fade" id="categories" role="tabpanel">
                            <div class="card">
                                <div class="card-header light d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-tags me-2"></i> Token Categories</span>
                                    <button class="btn btn-sm btn-light" onclick="openCategoryModal()">
                                        <i class="fas fa-plus me-1"></i> Add Category
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="categoriesTable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Category</th>
                                                    <th>Token Type</th>
                                                    <th>Description</th>
                                                    <th>Base Price</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($tokenCategories)): ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center py-4">
                                                            <i class="fas fa-tags fa-2x text-muted mb-2"></i>
                                                            <p class="text-muted">No categories found. Add your first category.</p>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($tokenCategories as $category): ?>
                                                        <tr id="category-row-<?php echo $category['id']; ?>">
                                                            <td>#<?php echo $category['id']; ?></td>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars(ucfirst($category['category_name'])); ?></strong>
                                                            </td>
                                                            <td>
                                                                <span class="type-badge">
                                                                    <?php echo htmlspecialchars(ucfirst($category['type_name'] ?? 'Unknown')); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($category['description'] ?? 'No description'); ?></td>
                                                            <td>
                                                                <strong>Rs <?php echo number_format($category['base_price'], 2); ?></strong>
                                                            </td>
                                                            <td>
                                                                <span class="status-badge <?php echo $category['is_active'] ? 'active' : 'inactive'; ?>">
                                                                    <i class="fas <?php echo $category['is_active'] ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                                                    <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo date('d M Y', strtotime($category['created_at'])); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary btn-action" 
                                                                        onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger btn-action" 
                                                                        onclick="deleteCategory(<?php echo $category['id']; ?>)">
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

    <!-- Token Type Modal -->
    <div class="modal fade" id="typeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="typeModalTitle">Add Token Type</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="type_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type Name</label>
                        <input type="text" class="form-control" id="type_name" 
                               required placeholder="e.g., physical, online">
                        <small class="text-muted">Will be stored in lowercase</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="type_description" 
                                  rows="3" placeholder="Describe this token type"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="type_is_active" checked>
                            <label class="form-check-label" for="type_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveType()">Save Type</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Token Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header light">
                    <h5 class="modal-title" id="categoryModalTitle">Add Token Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="category_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category Name</label>
                        <input type="text" class="form-control" id="category_name" 
                               required placeholder="e.g., normal, urgent">
                        <small class="text-muted">Will be stored in lowercase</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Token Type</label>
                        <select class="form-select" id="token_type_id" required>
                            <option value="">Select Type...</option>
                            <?php foreach ($tokenTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($type['type_name'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="category_description" 
                                  rows="2" placeholder="Describe this category"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Base Price (Rs)</label>
                        <div class="price-input">
                            <input type="number" class="form-control" id="base_price" 
                                   required step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="category_is_active" checked>
                            <label class="form-check-label" for="category_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveCategory()">Save Category</button>
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
                    <p class="text-danger mb-0"><small>Note: Items with dependencies cannot be deleted.</small></p>
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
            $('#typesTable').DataTable({
                pageLength: 10,
                ordering: true,
                language: {
                    emptyTable: "No token types found"
                }
            });

            $('#categoriesTable').DataTable({
                pageLength: 10,
                ordering: true,
                language: {
                    emptyTable: "No categories found"
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

        // Token Type Functions
        function openTypeModal() {
            document.getElementById('typeModalTitle').innerText = 'Add Token Type';
            document.getElementById('type_id').value = '';
            document.getElementById('type_name').value = '';
            document.getElementById('type_description').value = '';
            document.getElementById('type_is_active').checked = true;
            new bootstrap.Modal(document.getElementById('typeModal')).show();
        }

        function editType(type) {
            document.getElementById('typeModalTitle').innerText = 'Edit Token Type';
            document.getElementById('type_id').value = type.id;
            document.getElementById('type_name').value = type.type_name;
            document.getElementById('type_description').value = type.description || '';
            document.getElementById('type_is_active').checked = type.is_active == 1;
            new bootstrap.Modal(document.getElementById('typeModal')).show();
        }

        function saveType() {
            const id = document.getElementById('type_id').value;
            const data = {
                type_action: 'save',
                type_id: id,
                type_name: document.getElementById('type_name').value.trim(),
                description: document.getElementById('type_description').value,
                is_active: document.getElementById('type_is_active').checked ? 1 : 0
            };

            if (!data.type_name) {
                showToast('Please enter a type name', 'warning');
                return;
            }

            showLoading();

            fetch('tokenTypeApi.php', {
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
                    bootstrap.Modal.getInstance(document.getElementById('typeModal')).hide();
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

        function deleteType(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_type').value = 'type';
            document.getElementById('deleteMessage').innerText = 'Are you sure you want to delete this token type?';
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Token Category Functions
        function openCategoryModal() {
            document.getElementById('categoryModalTitle').innerText = 'Add Token Category';
            document.getElementById('category_id').value = '';
            document.getElementById('category_name').value = '';
            document.getElementById('token_type_id').value = '';
            document.getElementById('category_description').value = '';
            document.getElementById('base_price').value = '';
            document.getElementById('category_is_active').checked = true;
            new bootstrap.Modal(document.getElementById('categoryModal')).show();
        }

        function editCategory(category) {
            document.getElementById('categoryModalTitle').innerText = 'Edit Token Category';
            document.getElementById('category_id').value = category.id;
            document.getElementById('category_name').value = category.category_name;
            document.getElementById('token_type_id').value = category.token_type_id;
            document.getElementById('category_description').value = category.description || '';
            document.getElementById('base_price').value = category.base_price;
            document.getElementById('category_is_active').checked = category.is_active == 1;
            new bootstrap.Modal(document.getElementById('categoryModal')).show();
        }

        function saveCategory() {
            const id = document.getElementById('category_id').value;
            const data = {
                category_action: 'save',
                category_id: id,
                category_name: document.getElementById('category_name').value.trim(),
                token_type_id: document.getElementById('token_type_id').value,
                description: document.getElementById('category_description').value,
                base_price: document.getElementById('base_price').value,
                is_active: document.getElementById('category_is_active').checked ? 1 : 0
            };

            if (!data.category_name) {
                showToast('Please enter a category name', 'warning');
                return;
            }
            if (!data.token_type_id) {
                showToast('Please select a token type', 'warning');
                return;
            }
            if (!data.base_price || data.base_price <= 0) {
                showToast('Please enter a valid price', 'warning');
                return;
            }

            showLoading();

            fetch('tokenTypeApi.php', {
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
                    bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
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

        function deleteCategory(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_type').value = 'category';
            document.getElementById('deleteMessage').innerText = 'Are you sure you want to delete this token category?';
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Confirm Delete
        function confirmDelete() {
            const id = document.getElementById('delete_id').value;
            const type = document.getElementById('delete_type').value;
            
            const data = {
                type_action: 'delete',
                type_id: id
            };

            if (type === 'category') {
                data.category_action = 'delete';
                data.category_id = id;
            }

            showLoading();

            fetch('tokenTypeApi.php', {
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