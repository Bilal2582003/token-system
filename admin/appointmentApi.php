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

// Handle Appointment operations
if (isset($input['appointment_action'])) {
    
    // Save Appointment
    if ($input['appointment_action'] === 'save') {
        $appointment_id = $input['appointment_id'] ?? '';
        
        $data = [
            'patient_name' => $input['patient_name'],
            'patient_phone' => $input['patient_phone'],
            'patient_email' => $input['patient_email'] ?: null,
            'doctor_id' => $input['doctor_id'],
            'token_type_id' => $input['token_type_id'],
            'token_category_id' => $input['token_category_id'],
            'token_date' => $input['token_date'],
            'token_time' => $input['token_time'],
            'token_price' => $input['token_price'],
            'meeting_link' => $input['meeting_link'] ?: null,
            'status' => $input['status'],
            'notes' => $input['notes'] ?: null
        ];
        
        if (empty($appointment_id)) {
            // Generate token number
            $date = $input['token_date'];
            $typeId = $input['token_type_id'];
            $categoryId = $input['token_category_id'];
            
            // Get last token number for this date/type/category
            $lastToken = $db->getOne('tokens', [
                'token_date' => $date,
                'token_type_id' => $typeId,
                'token_category_id' => $categoryId
            ], [], 'token_number DESC');
            
            $nextNumber = 1;
            if ($lastToken) {
                $lastNum = intval($lastToken['token_number']);
                $nextNumber = $lastNum + 1;
            }
            
            // Get type and category codes
            $type = $db->getOne('token_types', ['id' => $typeId]);
            $category = $db->getOne('token_categories', ['id' => $categoryId]);
            
            $typeCode = strtoupper(substr($type['type_name'], 0, 1));
            $categoryCode = strtoupper(substr($category['category_name'], 0, 1));
            $dateCode = date('Ymd', strtotime($date));
            
            $data['token_number'] = sprintf('%s%s-%s-%03d', $typeCode, $categoryCode, $dateCode, $nextNumber);
            
            // Insert new appointment
            $inserted = $db->insert('tokens', $data);
            
            if ($inserted) {
                echo json_encode(['success' => true, 'message' => 'Appointment created successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error creating appointment!']);
            }
        } else {
            // Update existing appointment
            $data['token_number'] = $input['token_number']; // Keep existing token number
            
            $updated = $db->update('tokens', ['id' => $appointment_id], $data);
            
            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Appointment updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating appointment!']);
            }
        }
        exit;
    }
    
    // Delete Appointment
    if ($input['appointment_action'] === 'delete') {
        $appointment_id = $input['appointment_id'];
        
        $deleted = $db->delete('tokens', ['id' => $appointment_id]);
        
        if ($deleted) {
            echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting appointment!']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);