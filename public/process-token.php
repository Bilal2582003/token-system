<?php
session_start();
require_once __DIR__ . '/../controllers/TokenController.php';

$controller = new TokenController();
$controller->handleTokenRequest();