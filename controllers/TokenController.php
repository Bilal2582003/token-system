<?php

require_once __DIR__ . '/../models/Token.php';
require_once __DIR__ . '/../models/Clinic.php';
require_once __DIR__ . '/../models/Validation.php';

class TokenController {
    private $token;
    private $clinic;
    private $validation;

    public function __construct() {
        $this->token = new Token();
        $this->clinic = new Clinic();
        $this->validation = new Validation();
    }

    public function getAvailableDates() {
    header('Content-Type: application/json');
    
    try {
        $tokenTypeId = $this->validation->sanitizeInput($_GET['token_type'] ?? null);
        
        if (!$tokenTypeId) {
            throw new Exception("Token type is required");
        }
        
        // Check token type availability first
        if (!$this->token->checkTokenTypeAvailability($tokenTypeId, date('Y-m-d'))) {
            $tokenType = $this->token->tokenCon->getOne('token_types', ['id' => $tokenTypeId]);
            throw new Exception("{$tokenType['type_name']} tokens are not available");
        }
        
        $dates = $this->token->getAvailableDates(null, $tokenTypeId, 60);
        
        echo json_encode([
            'success' => true,
            'dates' => $dates
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

public function getAvailableDoctors() {
    header('Content-Type: application/json');
    
    try {
        $date = $this->validation->sanitizeInput($_GET['date'] ?? null);
        $tokenTypeId = $this->validation->sanitizeInput($_GET['token_type'] ?? null);
        
        if (!$date || !$tokenTypeId) {
            throw new Exception("Date and token type are required");
        }
        
        // Verify date is valid and not in past
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            throw new Exception("Cannot select past dates");
        }
        
        // Check token type availability for this date
        if (!$this->token->checkTokenTypeAvailability($tokenTypeId, $date)) {
            throw new Exception("This token type is not available on selected date");
        }
        
        $doctors = $this->token->getAvailableDoctors($date, $tokenTypeId);
        
        if (empty($doctors)) {
            throw new Exception("No doctors available on selected date");
        }
        
        echo json_encode([
            'success' => true,
            'doctors' => $doctors
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

    public function handleTokenRequest() {
        header('Content-Type: application/json');

        // Verify CSRF token
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'];
        if (!$this->validation->verifyCSRFToken($token ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Security token invalid']);
            return;
        }

        try {

                $action = $_GET['action'] ?? 'book';
  
            switch ($action) {
        case 'get_available_dates':
            return $this->getAvailableDates();
        case 'get_available_doctors':
            return $this->getAvailableDoctors();
        case 'book':
        default:
            return $this->processBooking();
    }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    private function processBooking(){
         $date = $this->validation->sanitizeInput($_POST['token_date'] ?? null);
    $tokenTypeId = $this->validation->sanitizeInput($_POST['token_type'] ?? null);
    $doctorId = $this->validation->sanitizeInput($_POST['doctor'] ?? null);
    
    // Validate token type availability for selected date
    if (!$this->token->checkTokenTypeAvailability($tokenTypeId, $date)) {
        throw new Exception("Selected token type is not available on chosen date");
    }
    
    // Validate doctor availability
    if (!$this->token->checkDoctorAvailability($doctorId, $date)) {
        throw new Exception("Selected doctor is not available on chosen date");
    }
        
            $data = $this->validation->sanitizeInput($_POST);

            // Validate required fields
            $required = ['patient_name', 'patient_phone', 'patient_email', 'token_type', 'token_category', 'doctor', 'token_date', 'token_time'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field {$field} is required");
                }
            }

            // Additional validation
            if (!$this->validation->validateEmail($data['patient_email'])) {
                throw new Exception("Invalid email address");
            }

            if (!$this->validation->validatePhone($data['patient_phone'])) {
                throw new Exception("Invalid phone number");
            }

            if (!$this->validation->validateDate($data['token_date'])) {
                throw new Exception("Invalid date format");
            }

            // Create token
            $tokenData = [
                'token_type_id' => $data['token_type'],
                'token_category_id' => $data['token_category'],
                'doctor_id' => $data['doctor'],
                'patient_name' => $data['patient_name'],
                'patient_phone' => $data['patient_phone'],
                'patient_email' => $data['patient_email'],
                'token_date' => $data['token_date'] ?? date("Y-m-d"),
                'token_time' => $data['token_time'],
                'token_price' => $this->getTokenPrice($data['token_category'])
            ];

            $sequentialTokenNumber = $this->token->createToken($tokenData);

            if ($sequentialTokenNumber) {
                // Get formatted token number
                $formattedTokenNumber = $this->token->getFormattedTokenNumber(
                    $sequentialTokenNumber,
                    $data['token_type'],
                    $data['token_category'],
                    $data['token_date']
                );
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Token booked successfully!',
                    'token_number' => $formattedTokenNumber,
                    'sequential_number' => $sequentialTokenNumber
                ]);
            } else {
                throw new Exception("Failed to create token - No available slots");
            }

    }

    private function getTokenPrice($categoryId) {
        $categories = $this->token->getTokenCategories();
        foreach ($categories as $category) {
            if ($category['id'] == $categoryId) {
                return $category['base_price'];
            }
        }
        return 0;
    }

    public function getAvailableSlots() {
        header('Content-Type: application/json');
        
        $date = $this->validation->sanitizeInput($_GET['date'] ?? date('Y-m-d'));
        $type = $_GET['type'] ?? null;
        $category = $_GET['category'] ?? null;

        $slots = $this->token->getAvailableSlots($date, $type, $category);
        echo json_encode($slots);
    }

    public function getDailyAvailability() {
        header('Content-Type: application/json');
        
        $date = $this->validation->sanitizeInput($_GET['date'] ?? date('Y-m-d'));
        
        $tokenTypes = $this->token->getTokenTypes();
        $tokenCategories = $this->token->getTokenCategories();
        
        $availability = [];
        
        foreach ($tokenTypes as $type) {
            foreach ($tokenCategories as $category) {
                $bookedCount = count($this->token->getAvailableSlots($date, $type['id'], $category['id']));
                $limit = $this->getDailyLimit($type['id'], $category['id']);
                
                $availability[] = [
                    'type_id' => $type['id'],
                    'type_name' => $type['type_name'],
                    'category_id' => $category['id'],
                    'category_name' => $category['category_name'],
                    'booked' => $bookedCount,
                    'limit' => $limit,
                    'available' => $limit - $bookedCount
                ];
            }
        }
        
        echo json_encode($availability);
    }
    
    private function getDailyLimit($typeId, $categoryId) {
        $limit = $this->token->tokenCon->getOne('token_limits', [
            'token_type_id' => $typeId,
            'token_category_id' => $categoryId
        ]);
        
        return $limit ? $limit['daily_limit'] : 0;
    }
}