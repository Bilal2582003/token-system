<?php

require_once __DIR__ . '/../models/DB.php';
class Clinic {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function getClinicInfo() {
        return $this->db->getOne('clinic_settings');
    }

    public function getClinicTimings() {
        $info = $this->getClinicInfo();
        return [
            'opening_time' => $info['opening_time'],
            'closing_time' => $info['closing_time']
        ];
    }

    public function getTokenPricing() {
        return $this->db->getAll('token_categories', ['is_active' => 1]);
    }
}