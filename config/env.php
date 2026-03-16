<?php
  require __DIR__ . '/../vendor/autoload.php'; // Composer autoload

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

define('DB_HOST', $_SERVER['SERVER_NAME'] == 'localhost' ? $_ENV['DB_HOST'] : $_ENV['LIVEDB_HOST']);
define('DB_NAME', $_SERVER['SERVER_NAME'] == 'localhost' ? $_ENV['DB_DATABASE'] :  $_ENV['LIVEDB_DATABASE']);
define('DB_USER', $_SERVER['SERVER_NAME'] == 'localhost' ? $_ENV['DB_USERNAME'] : $_ENV['LIVEDB_USERNAME']);
define('DB_PASS', $_SERVER['SERVER_NAME'] == 'localhost' ? $_ENV['DB_PASSWORD'] : $_ENV['LIVEDB_PASSWORD']);
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost/astana-project');