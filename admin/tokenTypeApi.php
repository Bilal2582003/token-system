<?php
require_once __DIR__ . '/../config/env.php';
session_start();

// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../models/DB.php';

$db = DB::getInstance();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Handle Token Type operations
if (isset($input['type_action'])) {
    
    // Save Token Type
    if ($input['type_action'] === 'save') {
        $type_id = $input['type_id'] ?? '';
        $type_name = strtolower(trim($input['type_name']));
        $description = $input['description'] ?? '';
        $is_active = $input['is_active'] ?? 1;
        
        // Check if type name already exists
        if (empty($type_id)) {
            // Insert new type
            $existing = $db->getOne('token_types', ['type_name' => $type_name]);
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Token type name already exists!']);
                exit;
            }
            
            $inserted = $db->insert('token_types', [
                'type_name' => $type_name,
                'description' => $description,
                'is_active' => $is_active
            ]);
            
            if ($inserted) {
                echo json_encode(['success' => true, 'message' => 'Token type added successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding token type!']);
            }
        } else {
            // Update existing type
            $existing = $db->getOne('token_types', [
                'type_name' => $type_name,
                'id' => ['operator' => '!=', 'value' => $type_id]
            ]);
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Token type name already exists!']);
                exit;
            }
            
            $updated = $db->update('token_types', ['id' => $type_id], [
                'type_name' => $type_name,
                'description' => $description,
                'is_active' => $is_active
            ]);
            
            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Token type updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating token type!']);
            }
        }
        exit;
    }
    
    // Delete Token Type
    if ($input['type_action'] === 'delete') {
        $type_id = $input['type_id'];
        
        // Check if type has categories
        $hasCategories = $db->getOne('token_categories', ['token_type_id' => $type_id]);
        
        if ($hasCategories) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete: This type has categories associated with it!']);
            exit;
        }
        
        $deleted = $db->delete('token_types', ['id' => $type_id]);
        
        if ($deleted) {
            echo json_encode(['success' => true, 'message' => 'Token type deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting token type!']);
        }
        exit;
    }
}

// Handle Token Category operations
if (isset($input['category_action'])) {
    
    // Save Token Category
    if ($input['category_action'] === 'save') {
        $category_id = $input['category_id'] ?? '';
        $category_name = strtolower(trim($input['category_name']));
        $token_type_id = $input['token_type_id'];
        $description = $input['description'] ?? '';
        $base_price = $input['base_price'];
        $is_active = $input['is_active'] ?? 1;
        
        if (empty($category_id)) {
            // Insert new category
            $existing = $db->getOne('token_categories', [
                'category_name' => $category_name,
                'token_type_id' => $token_type_id
            ]);
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Category name already exists for this token type!']);
                exit;
            }
            
            $inserted = $db->insert('token_categories', [
                'category_name' => $category_name,
                'token_type_id' => $token_type_id,
                'description' => $description,
                'base_price' => $base_price,
                'is_active' => $is_active
            ]);
            
            if ($inserted) {
                echo json_encode(['success' => true, 'message' => 'Token category added successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding token category!']);
            }
        } else {
            // Update existing category
            $existing = $db->getOne('token_categories', [
                'category_name' => $category_name,
                'token_type_id' => $token_type_id,
                'id' => ['operator' => '!=', 'value' => $category_id]
            ]);
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Category name already exists for this token type!']);
                exit;
            }
            
            $updated = $db->update('token_categories', ['id' => $category_id], [
                'category_name' => $category_name,
                'token_type_id' => $token_type_id,
                'description' => $description,
                'base_price' => $base_price,
                'is_active' => $is_active
            ]);
            
            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Token category updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating token category!']);
            }
        }
        exit;
    }
    
    // Delete Token Category
    if ($input['category_action'] === 'delete') {
        $category_id = $input['category_id'];
        
        // Check if category has tokens
        $hasTokens = $db->getOne('tokens', ['token_category_id' => $category_id]);
        
        if ($hasTokens) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete: This category has tokens associated with it!']);
            exit;
        }
        
        $deleted = $db->delete('token_categories', ['id' => $category_id]);
        
        if ($deleted) {
            echo json_encode(['success' => true, 'message' => 'Token category deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting token category!']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);