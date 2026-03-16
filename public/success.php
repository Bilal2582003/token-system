<?php
session_start();
require_once __DIR__ . '/../models/Clinic.php';
require_once __DIR__ . '/../models/Token.php';

$clinic = new Clinic();
$tokenModel = new Token();
$clinicInfo = $clinic->getClinicInfo();
$tokenNumber = $_GET['token'] ?? 'N/A';
$sequentialNumber = $_GET['sequential'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment Confirmed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            background: linear-gradient(135deg, #f58feb 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .checkmark {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .token-display {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            font-size: 2rem;
            font-weight: bold;
            margin: 2rem 0;
        }
        .sequential-number {
            font-size: 4rem;
            color: #ffc107;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="success-card">
                    <div class="checkmark">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="text-success">Appointment Confirmed!</h2>
                    <p class="lead">Your token has been successfully booked.</p>
                    
                    <div class="token-display">
                        <div>Your Token Number</div>
                        <div class="sequential-number"><?php echo htmlspecialchars($sequentialNumber); ?></div>
                        <div style="font-size: 1rem;">(<?php echo htmlspecialchars($tokenNumber); ?>)</div>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <h5><i class="fas fa-info-circle me-2"></i>Important Information</h5>
                        <ul class="text-start mt-3">
                            <li>Please arrive 15 minutes before your scheduled time</li>
                            <li>Bring your ID proof and previous medical records</li>
                            <li>Token number <?php echo htmlspecialchars($sequentialNumber); ?> will be called in sequence</li>
                            <li>Estimated waiting time: <?php echo (intval($sequentialNumber) * 15); ?> minutes</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-plus me-2"></i>Book Another Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>