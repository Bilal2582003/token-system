<?php

require_once __DIR__ . '/../models/DB.php';
class Token {
    private $db;
    public $tokenCon;
    public function __construct() {
        $this->db = DB::getInstance();
         $this->tokenCon = $this->db;
    }
 
    public function createToken($data) {
        // Generate sequential token number for the day
        $tokenNumber = $this->generateSequentialTokenNumber($data['token_date'], $data['token_type_id'], $data['token_category_id']);
        
        if (!$tokenNumber) {
            throw new Exception("No available slots for selected type and category");
        }

        $tokenData = [
            'token_number' => $tokenNumber,
            'token_type_id' => $data['token_type_id'],
            'token_category_id' => $data['token_category_id'],
            'doctor_id' => $data['doctor_id'],
            'patient_name' => $data['patient_name'],
            'patient_phone' => $data['patient_phone'],
            'patient_email' => $data['patient_email'],
            'token_date' => $data['token_date'],
            'token_time' => $data['token_time'],
            'token_price' => $data['token_price'],
            'status' => 'pending'
        ];

        $result = $this->db->insert('tokens', $tokenData);
        
        if ($result) {
            return $tokenNumber; // Return the generated token number
        }
        
        return false;
    }

    private function generateSequentialTokenNumber($date, $typeId, $categoryId) {
        // First check if we haven't reached the daily limit
        if (!$this->checkDailyLimit($date, $typeId, $categoryId)) {
            return false;
        }

        // Get the last token number for this date, type, and category
        $lastToken = $this->db->getOne('tokens', [
            'token_date' => $date,
            'token_type_id' => $typeId,
            'token_category_id' => $categoryId,
            'status'=> ['operator' => '!=', 'value' => 'cancel']
        ], [], 'token_number', 'token_number DESC');

        $nextNumber = 1;
        
        if ($lastToken && isset($lastToken['token_number'])) {
            // Extract the numeric part from token number (format: TYPE-CATEGORY-001)
            $lastNumber = intval($lastToken['token_number']);
            $nextNumber = $lastNumber + 1;
        }

        // Return just the sequential number (we'll format it in the controller)
        return $nextNumber;
    }

    public function checkDailyLimit($date, $typeId, $categoryId) {
        $limits = $this->db->getOne('token_limits', [
            'token_type_id' => $typeId,
            'token_category_id' => $categoryId
        ]);

        if (!$limits) return false;

        $booked = $this->db->getAll('tokens', [
            'token_date' => $date,
            'token_type_id' => $typeId,
            'token_category_id' => $categoryId,
            'status' => ['operator' => 'IN', 'value' => ['pending', 'confirmed']]
        ]);

        return count($booked) < $limits['daily_limit'];
    }

    public function getAvailableSlots($date, $typeId = null, $categoryId = null) {
        $conditions = ['token_date' => $date, 'status' => ['operator' => 'IN', 'value' => ['pending', 'confirmed']]];
        
        if ($typeId) {
            $conditions['token_type_id'] = $typeId;
        }
        if ($categoryId) {
            $conditions['token_category_id'] = $categoryId;
        }

        return $this->db->getAll('tokens', $conditions);
    }

    public function getTokenTypes() {
        return $this->db->getAll('token_types', ['is_active' => 1]);
    }

    public function getTokenCategories($drId='', $type_id='', $date= '') {
        if(!empty($type_id) && isset($type_id)){
           return $this->db->getAll("token_categories tc", ["tc.token_type_id"=> $type_id, "is_active"=> 1], '', '', [],"tc.*, (SELECT count(*) from tokens where token_type_id = $type_id and token_Category_id = tc.id and doctor_id = $drId and token_date = '$date' ) as total_given, (SELECT sum(daily_limit) from token_limits where token_type_id = $type_id and token_category_id = tc.id and is_active = 1) as limitdata");
        }else{
            return $this->db->getAll('token_categories', ['is_active' => 1]);
            }
    }

    public function getDoctors() {
        return $this->db->getAll('doctors', ['is_active' => 1]);
    }
    public function getOneDoctors($id) {
        return $this->db->getOne('doctors', ['is_active' => 1, 'id'=> $id]);
    }

    public function getFormattedTokenNumber($sequentialNumber, $typeId, $categoryId, $date) {
        // Get type and category names for formatting
        $type = $this->db->getOne('token_types', ['id' => $typeId]);
        $category = $this->db->getOne('token_categories', ['id' => $categoryId]);
        
        $typeCode = strtoupper(substr($type['type_name'], 0, 1));
        $categoryCode = strtoupper(substr($category['category_name'], 0, 1));
        $dateCode = date('Ymd', strtotime($date));
        
        return sprintf('%s%s-%s-%03d', $typeCode, $categoryCode, $dateCode, $sequentialNumber);
    }


    // Add these methods to your Token.php model

public function checkDoctorAvailability($doctorId, $date) {
    $dayOfWeek = date('N', strtotime($date)); // 1=Monday, 7=Sunday
    $doctorDay = $dayOfWeek == 7 ? 1 : $dayOfWeek + 1; // Convert to 1=Sunday
    
    // Check if doctor is generally available
    $doctor = $this->db->getOne('doctors', [
        'id' => $doctorId,
        'is_active' => 1,
        'is_available' => 1
    ]);
    
    if (!$doctor) {
        return false;
    }
    
    // Check doctor's specific schedule
    $schedule = $this->db->getOne('doctor_schedules', [
        'doctor_id' => $doctorId,
        'day_of_week' => $doctorDay
    ]);
    
    // If schedule exists and doctor is not available
    if ($schedule && $schedule['is_available'] == 0) {
        return false;
    }
    
    // Check for special closures affecting this doctor
    $closure = $this->db->getOne('special_closures', [
        'closure_date' => $date,
        'affects_token_type' => null // All types
    ]);
    
    if ($closure) {
        return false;
    }
    
    return true;
}

public function checkTokenTypeAvailability($tokenTypeId, $date) {
    $dayOfWeek = date('N', strtotime($date));
    $doctorDay = $dayOfWeek == 7 ? 1 : $dayOfWeek + 1; // Convert to 1=Sunday
    
    // Check if token type is active
    $tokenType = $this->db->getOne('token_types', [
        'id' => $tokenTypeId,
        'is_active' => 1
    ]);
    
    if (!$tokenType) {
        return false;
    }
    
    // Check token type restrictions for this day
    $restriction = $this->db->getOne('token_type_restrictions', [
        'token_type_id' => $tokenTypeId,
        'day_of_week' => $doctorDay
    ]);
    
    // If restriction exists and not allowed
    if ($restriction && $restriction['is_allowed'] == 0) {
        return false;
    }
    
    // Check date range restrictions
    $dateRestrictions = $this->db->getAll('token_type_restrictions', [
        'token_type_id' => $tokenTypeId,
        'start_date' => ['operator' => '<=', 'value' => $date],
        'end_date' => ['operator' => '>=', 'value' => $date]
    ]);
    
    foreach ($dateRestrictions as $restriction) {
        if ($restriction['is_allowed'] == 0) {
            return false;
        }
    }
    
    // Check for special closures affecting this token type
    // $closures = $this->db->getAll('special_closures', [
    //     'OR' => [
    //         ['closure_date' => $date, 'affects_token_type' => null],
    //         ['closure_date' => $date, 'affects_token_type' => $tokenTypeId]
    //     ]
    // ]);
    $closures = [];
    if (count($closures) > 0) {
        return false;
    }
    
    return true;
}

public function getAvailableDoctors($date, $tokenTypeId = null) {
    $allDoctors = $this->getDoctors();
    $availableDoctors = [];
    
    foreach ($allDoctors as $doctor) {
        if ($this->checkDoctorAvailability($doctor['id'], $date)) {
            // If token type specified, check additional constraints
            if ($tokenTypeId) {
                // You can add type-specific doctor availability logic here
            }
            $availableDoctors[] = $doctor;
        }
    }
    
    return $availableDoctors;
}

public function getAvailableDates($doctorId = null, $daysAhead = 30) {
    $availableDates = [];
    $currentDate = date('Y-m-d');
    
    for ($i = 0; $i < $daysAhead; $i++) {
        $date = date('Y-m-d', strtotime("+$i days"));
        $dayOfWeek = date('N', strtotime($date));
         $dayOfWeek = $dayOfWeek == 7 ? 1 : $dayOfWeek + 1; // Convert to 1=Sunday
        $isAvailable = true;
        
        
        // Check doctor availability
        if ($doctorId && !$this->checkDoctorAvailability($doctorId,  $date)) {
            $isAvailable = false;
        }
        
        if ($isAvailable) {
            $availableDates[] = [
                'date' => $date,
                'display' => date('D, M j, Y', strtotime($date))
            ];
        }
    }
    
    return $availableDates;
}

// public function tokenAllowThisDay($drId,$date){
    
//      $dayOfWeek = date('N', strtotime($date));
//       $doctorDay = $dayOfWeek == 7 ? 1 : $dayOfWeek + 1; // Convert to 1=Sunday
//     return $this->db->getAll("token_type_restrictions", ['doctor_id' => $drId, "day_of_week" => $doctorDay]);
     

// } 

public function getDoctorSchedule($doctorId) {
    return $this->db->getAll('doctor_schedules', [
        'doctor_id' => $doctorId
    ], 'day_of_week ASC');
}

public function getTokenTypeRestrictions($tokenTypeId) {
    return $this->db->getAll('token_type_restrictions', [
        'token_type_id' => $tokenTypeId
    ]);
}
}