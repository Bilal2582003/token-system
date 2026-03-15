<?php
  require __DIR__ . '/../vendor/autoload.php'; // Composer autoload

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_DATABASE']);
define('DB_USER', $_ENV['DB_USERNAME']);
define('DB_PASS', $_ENV['DB_PASSWORD']);
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost/astana-project');