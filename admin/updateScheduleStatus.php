<?php
require_once __DIR__ . '/../config/env.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../models/DB.php';

$db = DB::getInstance();
$doctor_id = $_SESSION['doctor_id'] ?? 1;

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['schedule_id']) && isset($data['is_available'])) {
    $updated = $db->update('doctor_schedules', 
        ['id' => $data['schedule_id'], 'doctor_id' => $doctor_id], 
        ['is_available' => $data['is_available']]
    );
    
    echo json_encode(['success' => $updated]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}