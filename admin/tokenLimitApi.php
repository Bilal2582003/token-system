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

// Handle Token Limit operations
if (isset($input['limit_action'])) {
    
    // Save Token Limit
    if ($input['limit_action'] === 'save') {
        $limit_id = $input['limit_id'] ?? '';
        $token_type_id = $input['token_type_id'];
        $token_category_id = $input['token_category_id'];
        $daily_limit = $input['daily_limit'];
        $is_active = $input['is_active'] ?? 1;
        
        if (empty($limit_id)) {
            // Check if combination already exists
            $existing = $db->getOne('token_limits', [
                'token_type_id' => $token_type_id,
                'token_category_id' => $token_category_id
            ]);
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Limit for this type/category combination already exists!']);
                exit;
            }
            
            $inserted = $db->insert('token_limits', [
                'token_type_id' => $token_type_id,
                'token_category_id' => $token_category_id,
                'daily_limit' => $daily_limit,
                'is_active' => $is_active
            ]);
            
            if ($inserted) {
                echo json_encode(['success' => true, 'message' => 'Token limit added successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding token limit!']);
            }
        } else {
            // Check if combination exists for other records
            $existing = $db->getOne('token_limits', [
                'token_type_id' => $token_type_id,
                'token_category_id' => $token_category_id,
                'id' => ['operator' => '!=', 'value' => $limit_id]
            ]);
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Limit for this type/category combination already exists!']);
                exit;
            }
            
            $updated = $db->update('token_limits', ['id' => $limit_id], [
                'token_type_id' => $token_type_id,
                'token_category_id' => $token_category_id,
                'daily_limit' => $daily_limit,
                'is_active' => $is_active
            ]);
            
            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Token limit updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating token limit!']);
            }
        }
        exit;
    }
    
    // Delete Token Limit
    if ($input['limit_action'] === 'delete') {
        $limit_id = $input['limit_id'];
        
        $deleted = $db->delete('token_limits', ['id' => $limit_id]);
        
        if ($deleted) {
            echo json_encode(['success' => true, 'message' => 'Token limit deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting token limit!']);
        }
        exit;
    }
}

// Handle Token Restriction operations
if (isset($input['restriction_action'])) {
    
    // Save Token Restriction
    if ($input['restriction_action'] === 'save') {
        $restriction_id = $input['restriction_id'] ?? '';
        $doctor_id = $input['doctor_id'];
        $token_type_id = $input['token_type_id'];
        $day_of_week = $input['day_of_week'];
        $is_allowed = $input['is_allowed'] ?? 1;
        
        if (empty($restriction_id)) {
            // Check if combination already exists
            $existing = $db->getOne('token_type_restrictions', [
                'doctor_id' => $doctor_id,
                'token_type_id' => $token_type_id,
                'day_of_week' => $day_of_week
            ]);
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Restriction for this doctor/type/day already exists!']);
                exit;
            }
            
            $inserted = $db->insert('token_type_restrictions', [
                'doctor_id' => $doctor_id,
                'token_type_id' => $token_type_id,
                'day_of_week' => $day_of_week,
                'is_allowed' => $is_allowed
            ]);
            
            if ($inserted) {
                echo json_encode(['success' => true, 'message' => 'Token restriction added successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding token restriction!']);
            }
        } else {
            // Check if combination exists for other records
            $existing = $db->getOne('token_type_restrictions', [
                'doctor_id' => $doctor_id,
                'token_type_id' => $token_type_id,
                'day_of_week' => $day_of_week,
                'id' => ['operator' => '!=', 'value' => $restriction_id]
            ]);
            
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Restriction for this doctor/type/day already exists!']);
                exit;
            }
            
            $updated = $db->update('token_type_restrictions', ['id' => $restriction_id], [
                'doctor_id' => $doctor_id,
                'token_type_id' => $token_type_id,
                'day_of_week' => $day_of_week,
                'is_allowed' => $is_allowed
            ]);
            
            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Token restriction updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating token restriction!']);
            }
        }
        exit;
    }
    
    // Delete Token Restriction
    if ($input['restriction_action'] === 'delete') {
        $restriction_id = $input['restriction_id'];
        
        $deleted = $db->delete('token_type_restrictions', ['id' => $restriction_id]);
        
        if ($deleted) {
            echo json_encode(['success' => true, 'message' => 'Token restriction deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting token restriction!']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);