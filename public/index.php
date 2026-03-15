<?php
require_once __DIR__ . '/../config/env.php';
session_start();
require_once __DIR__ . '/../models/Clinic.php';
require_once __DIR__ . '/../models/Token.php';
require_once __DIR__ . '/../models/Validation.php';

$clinic = new Clinic();
$token = new Token();
$validation = new Validation();

$clinicInfo = $clinic->getClinicInfo();
$tokenTypes = $token->getTokenTypes();
$tokenCategories = $token->getTokenCategories();
$doctors = $token->getDoctors();
$csrfToken = $validation->generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($clinicInfo['clinic_name']); ?> - Online Token Booking</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.1/aos.css" rel="stylesheet"> -->
    <link href="../assets/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/all.min.css" rel="stylesheet">
    <link href="../assets/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    :root {
        /* --primary-color: #2c3e50; */
        /* --secondary-color: #3498db; */
        --primary-color: #df12cb;
        --secondary-color: #3498db;
        --accent-color: #e74c3c;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --light-bg: #f8f9fa;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        background: linear-gradient(135deg, #f58feb 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow-x: hidden;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 25px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .header-section {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 3rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .header-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        animation: float 20s linear infinite;
    }

    @keyframes float {
        0% {
            transform: translate(0, 0) rotate(0deg);
        }

        100% {
            transform: translate(-20px, -20px) rotate(360deg);
        }
    }

    .form-section {
        padding: 3rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        border: none;
        border-radius: 50px;
        padding: 15px 40px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .form-control {
        border-radius: 15px;
        border: 2px solid #e9ecef;
        padding: 15px 20px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.8);
    }

    .form-control:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.3rem rgba(52, 152, 219, 0.15);
        background: white;
        transform: translateY(-2px);
    }

    .token-type-card {
        border: 3px solid #e9ecef;
        border-radius: 20px;
        padding: 2rem;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        margin-bottom: 1.5rem;
        background: white;
        position: relative;
        overflow: hidden;
    }

    .token-type-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .token-type-card:hover {
        border-color: var(--secondary-color);
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .token-type-card:hover::before {
        transform: scaleX(1);
    }

    .token-type-card.selected {
        border-color: var(--secondary-color);
        background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(44, 62, 80, 0.05));
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(52, 152, 219, 0.2);
    }

    .token-type-card.selected::before {
        transform: scaleX(1);
    }

    .price-tag {
        background: var(--success-color);
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 1rem;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
    }

    .urgent-tag {
        background: linear-gradient(135deg, var(--accent-color), #c0392b);
    }

    .online-tag {
        background: linear-gradient(135deg, #9b59b6, #8e44ad);
    }

    .floating {
        animation-name: floating;
        animation-duration: 3s;
        animation-iteration-count: infinite;
        animation-timing-function: ease-in-out;
    }

    @keyframes floating {
        0% {
            transform: translate(0, 0px) rotate(0deg);
        }

        50% {
            transform: translate(0, 20px) rotate(5deg);
        }

        100% {
            transform: translate(0, 0px) rotate(0deg);
        }
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7);
        }

        70% {
            transform: scale(1.05);
            box-shadow: 0 0 0 15px rgba(52, 152, 219, 0);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
        }
    }

    .section-title {
        color: var(--primary-color);
        font-weight: 700;
        margin-bottom: 2rem;
        position: relative;
        display: inline-block;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
        border-radius: 2px;
    }

    .info-badge {
        background: linear-gradient(135deg, var(--warning-color), #e67e22);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .availability-indicator {
        display: inline-flex;
        align-items: center;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-left: 10px;
    }

    .available {
        background: rgba(39, 174, 96, 0.15);
        color: var(--success-color);
        border: 2px solid var(--success-color);
    }

    .limited {
        background: rgba(243, 156, 18, 0.15);
        color: var(--warning-color);
        border: 2px solid var(--warning-color);
    }

    .full {
        background: rgba(231, 76, 60, 0.15);
        color: var(--accent-color);
        border: 2px solid var(--accent-color);
    }

    .loading-dots {
        display: inline-block;
        position: relative;
        width: 80px;
        height: 80px;
    }

    .loading-dots div {
        position: absolute;
        top: 33px;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        background: var(--secondary-color);
        animation-timing-function: cubic-bezier(0, 1, 1, 0);
    }

    .loading-dots div:nth-child(1) {
        left: 8px;
        animation: loading-dots1 0.6s infinite;
    }

    .loading-dots div:nth-child(2) {
        left: 8px;
        animation: loading-dots2 0.6s infinite;
    }

    .loading-dots div:nth-child(3) {
        left: 32px;
        animation: loading-dots2 0.6s infinite;
    }

    .loading-dots div:nth-child(4) {
        left: 56px;
        animation: loading-dots3 0.6s infinite;
    }

    @keyframes loading-dots1 {
        0% {
            transform: scale(0);
        }

        100% {
            transform: scale(1);
        }
    }

    @keyframes loading-dots3 {
        0% {
            transform: scale(1);
        }

        100% {
            transform: scale(0);
        }
    }

    @keyframes loading-dots2 {
        0% {
            transform: translate(0, 0);
        }

        100% {
            transform: translate(24px, 0);
        }
    }

    .particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    .particle {
        position: absolute;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        animation: float-particle 20s infinite linear;
    }

    @keyframes float-particle {
        0% {
            transform: translateY(100vh) rotate(0deg);
            opacity: 0;
        }

        10% {
            opacity: 1;
        }

        90% {
            opacity: 1;
        }

        100% {
            transform: translateY(-100px) rotate(360deg);
            opacity: 0;
        }
    }

    .success-checkmark {
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }

    .success-checkmark .check-icon {
        width: 80px;
        height: 80px;
        position: relative;
        border-radius: 50%;
        box-sizing: content-box;
        border: 4px solid var(--success-color);
    }

    .success-checkmark .check-icon::before {
        top: 3px;
        left: -2px;
        width: 30px;
        transform-origin: 100% 50%;
        border-radius: 100px 0 0 100px;
    }

    .success-checkmark .check-icon::after {
        top: 0;
        left: 30px;
        width: 60px;
        transform-origin: 0 50%;
        border-radius: 0 100px 100px 0;
        animation: rotate-circle 4.25s ease-in;
    }

    .success-checkmark .check-icon .icon-line {
        height: 5px;
        background-color: var(--success-color);
        display: block;
        border-radius: 2px;
        position: absolute;
        z-index: 10;
    }

    .success-checkmark .check-icon .icon-line.line-tip {
        top: 46px;
        left: 14px;
        width: 25px;
        transform: rotate(45deg);
        animation: icon-line-tip 0.75s;
    }

    .success-checkmark .check-icon .icon-line.line-long {
        top: 38px;
        right: 8px;
        width: 47px;
        transform: rotate(-45deg);
        animation: icon-line-long 0.75s;
    }

    @keyframes rotate-circle {
        0% {
            transform: rotate(-45deg);
        }

        5% {
            transform: rotate(-45deg);
        }

        12% {
            transform: rotate(-405deg);
        }

        100% {
            transform: rotate(-405deg);
        }
    }

    @keyframes icon-line-tip {
        0% {
            width: 0;
            left: 1px;
            top: 19px;
        }

        54% {
            width: 0;
            left: 1px;
            top: 19px;
        }

        70% {
            width: 50px;
            left: -8px;
            top: 37px;
        }

        84% {
            width: 17px;
            left: 21px;
            top: 48px;
        }

        100% {
            width: 25px;
            left: 14px;
            top: 45px;
        }
    }

    @keyframes icon-line-long {
        0% {
            width: 0;
            right: 46px;
            top: 54px;
        }

        65% {
            width: 0;
            right: 46px;
            top: 54px;
        }

        84% {
            width: 55px;
            right: 0px;
            top: 35px;
        }

        100% {
            width: 47px;
            right: 8px;
            top: 38px;
        }
    }

    .time-slot {
        transition: all 0.3s ease;
        margin-bottom: 8px;
        padding: 10px 5px;
        font-size: 0.9rem;
    }

    .time-slot:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .time-slot.btn-primary {
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        border-color: var(--secondary-color);
        color: white;
    }

    .time-slot:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    @media (max-width: 768px) {
        .form-section {
            padding: 2rem 1rem;
        }

        .header-section {
            padding: 2rem 1rem;
        }

        .token-type-card {
            padding: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <!-- Animated Background Particles -->
    <div class="particles" id="particles"></div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <div class="glass-card animate__animated animate__fadeInUp" data-aos="zoom-in" data-aos-duration="1000">
                    <!-- Header Section -->
                    <div class="header-section text-center position-relative">
                        <!-- <div class="floating mb-4"> -->
                        <div class=" mb-4">
                            <!-- <i class="fas fa-heartbeat fa-4x text-white"></i> -->
                            <img src="../assets/images/astana-logo.PNG"
                                style="width:150px; height: 150px; border-radius:100%">
                        </div>
                        <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($clinicInfo['clinic_name']); ?>
                        </h1>
                        <p class="lead mb-4 fs-5">Book Your Token Online - Fast & Easy</p>

                        <div class="row g-4 mt-4">
                            <div class="col-6 col-md-6 col-sm-12">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-phone-alt me-2 fs-5"></i>
                                    <div>
                                        <small class="d-block opacity-75">Call Us</small>
                                        <strong
                                            class="fs-6"><?php echo htmlspecialchars($clinicInfo['phone_number']); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-6 col-sm-12">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-envelope me-2 fs-5"></i>
                                    <div>
                                        <small class="d-block opacity-75">Email</small>
                                        <strong
                                            class="fs-6"><?php echo htmlspecialchars($clinicInfo['email']); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-6 col-sm-12">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-clock me-2 fs-5"></i>
                                    <div>
                                        <small class="d-block opacity-75">Open</small>
                                        <strong
                                            class="fs-6"><?php echo date('h:i A', strtotime($clinicInfo['opening_time'])); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-6 col-sm-12">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-door-closed me-2 fs-5"></i>
                                    <div>
                                        <small class="d-block opacity-75">Close</small>
                                        <strong
                                            class="fs-6"><?php echo date('h:i A', strtotime($clinicInfo['closing_time'])); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="info-badge">
                                <i class="fas fa-shield-alt me-2"></i>100% Secure Booking
                            </span>
                        </div>
                    </div>

                    <!-- Token Booking Form -->
                    <div class="form-section">
                        <form id="tokenForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                            <!-- Progress Indicator -->
                            <div class="row mb-5">
                                <div class="col-12">
                                    <div class="progress" style="height: 8px; border-radius: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar"
                                            style="width: 0%; background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));"
                                            id="formProgress">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">Personal Details</small>
                                        <small class="text-muted">Date & Time</small>
                                        <small class="text-muted">Appointment Type</small>
                                        <small class="text-muted">Confirmation</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Patient Details -->
                            <div class="form-step active" id="step1">
                                <h3 class="section-title mb-4">
                                    <i class="fas fa-user-circle me-2"></i>Personal Information
                                </h3>
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold fs-6">Full Name *</label>
                                        <input type="text" class="form-control" name="patient_name" required
                                            pattern="[A-Za-z\s]{3,}"
                                            title="Please enter a valid name (minimum 3 characters)"
                                            placeholder="Enter your full name">
                                        <div class="form-text">As per your government ID</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold fs-6">Phone Number *</label>
                                        <input type="tel" class="form-control" name="patient_phone" required
                                            pattern="[\+\d\s\-\(\)]{10,}" title="Please enter a valid phone number"
                                            placeholder="+92 3XXXXXXXXX">
                                        <div class="form-text">We'll send SMS confirmation</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold fs-6">Email Address *</label>
                                        <input type="email" class="form-control" name="patient_email" required
                                            placeholder="your.email@example.com">
                                        <div class="form-text">For token details</div>
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="button" class="btn btn-primary next-step" data-next="step2">
                                        Continue <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Doctor & Time Selection -->
                            <div class="form-step" id="step2" style="display: none;">
                                <h3 class="section-title mb-4">
                                    <i class="fas fa-user-md me-2"></i>Hazrat & Time Selection
                                </h3>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold fs-6">Select *</label>
                                        <select class="form-select" name="doctor" id="doctorId" required>
                                            <option value="">Choose...</option>
                                            <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?php echo $doctor['id']; ?>">
                                                <?php echo htmlspecialchars($doctor['name']); ?> -
                                                <?php echo htmlspecialchars($doctor['specialization']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Choose your preferred</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold fs-6">Appointment Date *</label>
                                        <select class="form-select" name="token_date" required id="appointmentDate">
                                            <option value="">Select Date</option>
                                            <!-- Dates will be loaded dynamically -->
                                        </select>
                                        <div class="form-text">Only available dates are shown</div>
                                    </div>

                                    <!-- <div class="col-12">
                                        <label class="form-label fw-bold fs-6">Preferred Time Slot *</label>
                                        <div class="time-slots-container" id="timeSlotsContainer">
                                            <div class="text-center py-4">
                                                <div class="loading-dots">
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                </div>
                                                <p class="text-muted mt-2">Loading available time slots...</p>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary prev-step" data-prev="step1">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </button>
                                    <button type="button" class="btn btn-primary next-step" data-next="step3">
                                        Continue <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Token Type Selection -->
                            <div class="form-step" id="step3" style="display: none;">
                                <h3 class="section-title mb-4">
                                    <i class="fas fa-calendar-alt me-2"></i>Token Type
                                </h3>

                                <div class="mb-5">
                                    <h5 class="fw-bold mb-3">Choose Consultation Type</h5>
                                    <div class="row" id="tokenTypeSelection">
                                        <?php foreach ($tokenTypes as $type): ?>
                                        <div class="col-md-6">
                                            <div class="token-type-card" data-type-id="<?php echo $type['id']; ?>"
                                                data-type-name="<?php echo $type['type_name']; ?>">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h5 class="mb-0">
                                                        <i
                                                            class="fas <?php echo $type['type_name'] === 'online' ? 'fa-video text-purple' : 'fa-user-md text-blue'; ?> me-2"></i>
                                                        <?php echo htmlspecialchars(ucfirst($type['type_name'])); ?>
                                                    </h5>
                                                    <span
                                                        class="badge <?php echo $type['type_name'] === 'online' ? 'online-tag' : 'bg-primary'; ?>">
                                                        <?php echo $type['type_name'] === 'online' ? 'Remote' : 'In-Person'; ?>
                                                    </span>
                                                </div>
                                                <p class="text-muted mb-3">
                                                    <?php echo htmlspecialchars($type['description']); ?></p>
                                                <small class="text-info">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <?php echo $type['type_name'] === 'online' ? 'Video call link will be provided' : 'Visit our clinic physically'; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Categories will be loaded dynamically based on selected type -->
                                <div class="mb-4" id="categorySection" style="display: none;">
                                    <h5 class="fw-bold mb-3">Select Token Category</h5>
                                    <div class="row" id="tokenCategorySelection">
                                        <!-- Categories will be loaded here dynamically -->
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary prev-step" data-prev="step2">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </button>
                                    <button type="button" class="btn btn-primary next-step" data-next="step4"
                                        id="step2Next" disabled>
                                        Continue <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>



                            <!-- Confirmation -->
                            <div class="form-step" id="step4" style="display: none;">
                                <h3 class="section-title mb-4">
                                    <i class="fas fa-clipboard-check me-2"></i>Appointment Summary
                                </h3>

                                <div class="alert alert-success animate__animated animate__fadeIn" id="summarySection">
                                    <h5 class="alert-heading">
                                        <i class="fas fa-receipt me-2"></i>Appointment Details
                                    </h5>
                                    <div id="appointmentSummary" class="mt-3"></div>
                                </div>

                                <div class="alert alert-info mt-4">
                                    <h6><i class="fas fa-info-circle me-2"></i>Important Notes</h6>
                                    <ul class="mb-0 mt-2">
                                        <li>Please arrive 15 minutes before your scheduled time</li>
                                        <li>Bring your ID proof and previous medical records</li>
                                        <li>Cancellation must be made 24 hours in advance</li>
                                        <li>Late arrivals may need to be rescheduled</li>
                                    </ul>
                                </div>

                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="termsAgreement" required>
                                    <label class="form-check-label" for="termsAgreement">
                                        I agree to the <a href="#" class="text-primary">terms and conditions</a> and
                                        <a href="#" class="text-primary">privacy policy</a>
                                    </label>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary prev-step" data-prev="step3">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg pulse" id="submitButton">
                                        <i class="fas fa-calendar-check me-2"></i>Confirm Booking
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-white mb-0">
                        &copy; 2024 <?php echo htmlspecialchars($clinicInfo['clinic_name']); ?>. All rights reserved.
                    </p>
                    <p class="text-white-50 small">
                        <i class="fas fa-lock me-1"></i>Your data is securely encrypted
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center py-5">
                    <div class="loading-dots mb-3">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    <h6 class="text-primary">Processing your appointment...</h6>
                    <p class="text-muted small mt-2">Please don't close this window</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center py-5">
                    <div class="success-checkmark mb-4">
                        <div class="check-icon">
                            <span class="icon-line line-tip"></span>
                            <span class="icon-line line-long"></span>
                            <div class="icon-circle"></div>
                            <div class="icon-fix"></div>
                        </div>
                    </div>
                    <h4 class="text-success mb-3">Appointment Confirmed!</h4>
                    <p class="text-muted mb-4" id="successMessage">Your appointment has been successfully booked.</p>
                    <div class="d-grid gap-2">
                        <button onclick="gotoSuccess()" class="btn btn-primary">View Appointment Details</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">Book
                            Another</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.1/aos.js"></script> -->
    <script src="../assets/bootstrap.bundle.min.js"></script>
    <script src="../assets/aos.js"></script>
    <script>
    // Initialize animations
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });

    // Create background particles
    function createParticles() {
        const particlesContainer = document.getElementById('particles');
        const particleCount = 15;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';

            // Random properties
            const size = Math.random() * 6 + 2;
            const left = Math.random() * 100;
            const animationDuration = Math.random() * 20 + 10;
            const animationDelay = Math.random() * 5;

            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            particle.style.left = `${left}%`;
            particle.style.animationDuration = `${animationDuration}s`;
            particle.style.animationDelay = `${animationDelay}s`;

            particlesContainer.appendChild(particle);
        }
    }

    // Form state management
    let selectedType = null;
    let selectedTypeName = null;
    let selectedCategory = null;
    let selectedPrice = 0;
    let selectedTime = null;
    let allCategories = <?php echo json_encode($tokenCategories); ?>;


    // Load available dates based on selections
    function loadAvailableDates(drId) {
        console.log(drId)
        const dateSelect = document.getElementById('appointmentDate');
        // const tokenType = selectedType;

        if (!drId) {
            dateSelect.innerHTML = '<option value="">Select Hazrat First!</option>';
            return;
        }
        console.log(drId)
        var csrfToken = document.querySelector('input[name="csrf_token"]').value;


        dateSelect.innerHTML = '<option value="">Loading dates...</option>';

        fetch(`process-token.php?action=get_available_dates&drId=${drId}&csrf_token=${csrfToken}`)
            .then(response => response.json())
            .then(data => {
                console.log(drId)
                if (data.success && data.dates.length > 0) {
                    let options = '<option value="">Select Date</option>';
                    var count = 0;
                    data.dates.forEach(date => {
                        options +=
                            `<option ${ count == 0 ? 'selected' : '' } value="${date.date}">${date.display}</option>`;

                        if (count == 0) updateTokenType(drId, date.date);
                        count++;
                    });
                    dateSelect.innerHTML = options;


                    // updateSummary();
                } else {
                    dateSelect.innerHTML = '<option value="">No available dates</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                dateSelect.innerHTML = '<option value="">Error loading dates</option>';
            });
    }

    var dateSelect = document.getElementById('appointmentDate');
    dateSelect.addEventListener("change", (ele) => {

        updateTokenType(document.getElementById('doctorId').value, ele.value)
    })

    function updateTokenType(drId, date) {
        var csrfToken = document.querySelector('input[name="csrf_token"]').value;
        fetch(`process-token.php?action=getAllowToken&drId=${drId}&date=${date}&csrf_token=${csrfToken}`)
            .then(response => response.json())
            .then(data => {
                console.log(drId)
                if (data.success) {
                    data.data.forEach((d) => {
                        var ele = document.querySelector(`[data-type-id="${d.token_type_id}"]`);

                        if (ele) {
                            ele.style.display = d.is_allowed == 1 ? "block" : "none";
                        }
                    })
                }
            })
    }
    // Load available doctors based on date and token type
    function loadAvailableDoctors(date) {
        const doctorSelect = document.querySelector('select[name="doctor"]');

        if (!date || !selectedType) {
            doctorSelect.innerHTML = '<option value="">Select date first</option>';
            return;
        }

        doctorSelect.innerHTML = '<option value="">Loading doctors...</option>';
        var csrfToken = document.querySelector('input[name="csrf_token"]').value;
        fetch(
                `process-token.php?action=get_available_doctors&date=${date}&token_type=${selectedType}&csrf_token=${csrfToken}`
            )
            .then(response => response.json())
            .then(data => {
                if (data.success && data.doctors.length > 0) {
                    let options = '<option value="">Choose...</option>';
                    data.doctors.forEach(doctor => {
                        options +=
                            `<option value="${doctor.id}">${doctor.name} - ${doctor.specialization}</option>`;
                    });
                    doctorSelect.innerHTML = options;
                } else {
                    doctorSelect.innerHTML = '<option value="">No doctors available</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                doctorSelect.innerHTML = '<option value="">Error loading doctors</option>';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        createParticles();
        initializeForm();
    });

    function initializeForm() {
        // Step navigation
        document.querySelectorAll('.next-step').forEach(button => {
            button.addEventListener('click', function() {
                const nextStep = this.getAttribute('data-next');
                navigateToStep(nextStep);
            });
        });

        document.querySelectorAll('.prev-step').forEach(button => {
            button.addEventListener('click', function() {
                const prevStep = this.getAttribute('data-prev');
                navigateToStep(prevStep);
            });
        });

        // Token type selection
        document.querySelectorAll('#tokenTypeSelection .token-type-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('#tokenTypeSelection .token-type-card').forEach(c => {
                    c.classList.remove('selected');
                });
                this.classList.add('selected');
                selectedType = this.getAttribute('data-type-id');
                selectedTypeName = this.getAttribute('data-type-name');

                // Load categories for selected type
                loadCategoriesForType(selectedType);
                // loadAvailableDates();
                updateProgress();
            });
        });

        // Date change handler
        // document.getElementById('appointmentDate').addEventListener('change', function() {
        //    const date = this.value;
        // if (date) {
        //     loadAvailableDoctors(date);
        //     // generateTimeSlots();
        //     updateSummary();
        // }
        // });

        document.getElementById('doctorId').addEventListener('change', function() {
            const drId = this.value;
            if (drId) {
                loadAvailableDates(drId);
                // generateTimeSlots();
            }
        });

        // Form submission
        document.getElementById('tokenForm').addEventListener('submit', function(e) {
            // alert("now")
            e.preventDefault();
            submitForm();
        });

        // Initialize time slots
        // generateTimeSlots();
    }

    function loadCategoriesForType(typeId) {
        const categorySection = document.getElementById('categorySection');
        const categoryContainer = document.getElementById('tokenCategorySelection');
        const step2Next = document.getElementById('step2Next');

        var drId = document.getElementById("doctorId").value
        var date = document.querySelector("#appointmentDate").value
        var csrfToken = document.querySelector('input[name="csrf_token"]').value;

        fetch(
                `process-token.php?action=get_category&date=${date}&token_type=${typeId}&drId=${drId}&csrf_token=${csrfToken}`
            )
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {

                    allCategories = data.data
                    // Filter categories for selected type

                    const filteredCategories = allCategories.filter(category => category.token_type_id == typeId);

                    if (filteredCategories.length > 0) {
                        let categoriesHTML = '';

                        filteredCategories.forEach(category => {
                            const isUrgent = category.category_name.includes('urgent');
                            var total_remain = category.limitdata - category.total_given
                            console.log(category.limitdata + ' ' + category.total_given);
                            if (total_remain > 0) {

                                categoriesHTML += `
                    <div class="col-md-6">
                    <div class="token-type-card" data-category-id="${category.id}" data-price="${category.base_price}">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="mb-0">
                    ${category.category_name}
                    ${isUrgent ? '<i class="fas fa-bolt ms-2 text-warning"></i>' : ''}
                    </h5>
                                    <span class="price-tag ${isUrgent ? 'urgent-tag' : ''}">
                                    ${category.base_price}
                                    </span>
                                </div>
                                <p class="text-muted mb-3">${category.description || 'Standard appointment category'}</p>
                                <div class="availability-indicator  ${total_remain < (category.limitdata / 2) ? 'limited' : 'available' }" id="availability-${category.id}">
                                    <i class="fas fa-check me-1"></i> ${total_remain} Available 
                                    </div>
                                    </div>
                                    </div>
                                    `;
                            } else {
                                categoriesHTML += ` <div class="col-md-6">
                        <h5 class="text-danger">
                            ${category.category_name} Tokens have finished!
                        </h5>
                    </div>`
                            }
                        });

                        categoryContainer.innerHTML = categoriesHTML;
                        categorySection.style.display = 'block';

                        // Add event listeners to category cards
                        document.querySelectorAll('#tokenCategorySelection .token-type-card').forEach(card => {
                            card.addEventListener('click', function() {
                                document.querySelectorAll(
                                    '#tokenCategorySelection .token-type-card').forEach(c => {
                                    c.classList.remove('selected');
                                });
                                this.classList.add('selected');
                                selectedCategory = this.getAttribute('data-category-id');
                                selectedPrice = this.getAttribute('data-price');

                                // Enable next button
                                step2Next.disabled = false;
                                updateSummary();
                                // checkAvailability();
                            });
                        });

                        // Reset category selection
                        selectedCategory = null;
                        step2Next.disabled = true;
                    } else {
                        categoryContainer.innerHTML =
                            '<div class="col-12"><div class="alert alert-warning text-center">No categories available for this type</div></div>';
                        categorySection.style.display = 'block';
                        step2Next.disabled = true;
                    }

                } else {
                    // doctorSelect.innerHTML = '<option value="">No doctors available</option>';
                }
            })


    }

    function navigateToStep(stepId) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(step => {
            step.style.display = 'none';
        });

        // Show target step
        document.getElementById(stepId).style.display = 'block';

        // Update progress bar
        updateProgress();

        // Animate the step
        document.getElementById(stepId).classList.add('animate__animated', 'animate__fadeIn');
    }

    function updateProgress() {
        const progressBar = document.getElementById('formProgress');
        let progress = 0;

        if (document.getElementById('step1').style.display !== 'none') progress = 25;
        if (document.getElementById('step2').style.display !== 'none') progress = 50;
        if (document.getElementById('step3').style.display !== 'none') progress = 75;
        if (document.getElementById('step4').style.display !== 'none') progress = 100;

        progressBar.style.width = `${progress}%`;
    }

    // function generateTimeSlots() {
    //     const container = document.getElementById('timeSlotsContainer');
    //     const date = document.getElementById('appointmentDate').value;

    //     if (!date) {
    //         container.innerHTML = '<div class="alert alert-warning text-center">Please select a date first</div>';
    //         return;
    //     }

    //     // Show loading
    //     container.innerHTML = `
    //         <div class="text-center py-4">
    //             <div class="loading-dots">
    //                 <div></div>
    //                 <div></div>
    //                 <div></div>
    //                 <div></div>
    //             </div>
    //             <p class="text-muted mt-2">Loading available time slots...</p>
    //         </div>
    //     `;

    //     // Fetch clinic timings and booked slots from API
    //     fetch(`process-token.php?action=get_timings&date=${date}`)
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.success) {
    //                 displayTimeSlots(data.timings, data.bookedSlots, data.clinicHours);
    //             } else {
    //                 container.innerHTML = `<div class="alert alert-danger text-center">${data.message}</div>`;
    //             }
    //         })
    //         .catch(error => {
    //             console.error('Error:', error);
    //             container.innerHTML = '<div class="alert alert-danger text-center">Failed to load time slots</div>';
    //         });
    // }

    function displayTimeSlots(timings, bookedSlots, clinicHours) {
        const container = document.getElementById('timeSlotsContainer');

        if (!clinicHours || !clinicHours.opening_time || !clinicHours.closing_time) {
            container.innerHTML = '<div class="alert alert-warning text-center">Clinic timings not available</div>';
            return;
        }

        const openingTime = new Date(`2000-01-01T${clinicHours.opening_time}`);
        const closingTime = new Date(`2000-01-01T${clinicHours.closing_time}`);
        const interval = 30; // 30 minutes interval

        let slotsHTML = '<div class="row g-2">';
        let slotCount = 0;
        let availableSlots = 0;

        // Generate time slots between opening and closing time
        let currentTime = new Date(openingTime);

        while (currentTime < closingTime) {
            const timeString = currentTime.toTimeString().split(' ')[0]; // HH:MM:SS
            const displayTime = currentTime.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            // Check if this time slot is booked
            const isBooked = bookedSlots.includes(timeString);
            const isAvailable = !isBooked;

            if (isAvailable) {
                availableSlots++;
            }

            slotsHTML += `
                    <div class="col-6 col-md-4 col-lg-3">
                        <button type="button" 
                                class="btn time-slot w-100 ${isAvailable ? 'btn-outline-primary' : 'btn-outline-secondary'}" 
                                data-time="${timeString}"
                                ${!isAvailable ? 'disabled' : ''}>
                            ${displayTime}
                            ${!isAvailable ? '<br><small class="text-muted">Booked</small>' : ''}
                        </button>
                    </div>
                `;

            slotCount++;
            // Add interval minutes
            currentTime.setMinutes(currentTime.getMinutes() + interval);
        }

        slotsHTML += '</div>';

        // Add slot summary
        if (slotCount === 0) {
            container.innerHTML =
                '<div class="alert alert-warning text-center">No time slots available for selected date</div>';
        } else {
            const summaryHTML = `
                    <div class="alert alert-info mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong><i class="fas fa-clock me-2"></i>Clinic Hours:</strong><br>
                                ${openingTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })} - 
                                ${closingTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar-check me-2"></i>Availability:</strong><br>
                                ${availableSlots} of ${slotCount} slots available
                            </div>
                        </div>
                    </div>
                    ${slotsHTML}
                `;
            container.innerHTML = summaryHTML;

            // Add event listeners to time slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                if (!slot.disabled) {
                    slot.addEventListener('click', function() {
                        document.querySelectorAll('.time-slot').forEach(s => {
                            s.classList.remove('btn-primary');
                            s.classList.add('btn-outline-primary');
                        });
                        this.classList.remove('btn-outline-primary');
                        this.classList.add('btn-primary');
                        selectedTime = this.getAttribute('data-time') ?? <?php echo date("Y-m-d") ?>;
                        updateSummary();
                    });
                }
            });
        }
    }

    function updateSummary() {
        const summarySection = document.getElementById('appointmentSummary');

        if (selectedType && selectedCategory && selectedTime) {
            const typeElement = document.querySelector('#tokenTypeSelection .selected h5');
            const categoryElement = document.querySelector('#tokenCategorySelection .selected h5');
            const date = document.getElementById('appointmentDate').value;
            const doctorSelect = document.querySelector('select[name="doctor"]');
            const doctorText = doctorSelect.options[doctorSelect.selectedIndex].text;

            const typeText = typeElement ? typeElement.textContent.trim() : 'Not selected';
            const categoryText = categoryElement ? categoryElement.textContent.trim() : 'Not selected';
            const displayDate = new Date(date).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            const displayTime = new Date(`2000-01-01T${selectedTime}`).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            summarySection.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user me-2"></i>Patient:</strong><br>
                            ${document.querySelector('input[name="patient_name"]').value}
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-phone me-2"></i>Contact:</strong><br>
                            ${document.querySelector('input[name="patient_phone"]').value}
                        </div>
                        <div class="col-md-6 mt-3">
                            <strong><i class="fas fa-calendar me-2"></i>Date & Time:</strong><br>
                            ${displayDate} at ${displayTime}
                        </div>
                        <div class="col-md-6 mt-3">
                            <strong><i class="fas fa-user-md me-2"></i>Doctor:</strong><br>
                            ${doctorText}
                        </div>
                        <div class="col-md-6 mt-3">
                            <strong><i class="fas fa-stethoscope me-2"></i>Type:</strong><br>
                            ${typeText}
                        </div>
                        <div class="col-md-6 mt-3">
                            <strong><i class="fas fa-tag me-2"></i>Category:</strong><br>
                            ${categoryText}
                        </div>
                        <div class="col-12 mt-3">
                            <div class="alert alert-success">
                                <strong><i class="fas fa-receipt me-2"></i>Total Fee: ${selectedPrice}</strong>
                            </div>
                        </div>
                    </div>
                `;
        }
    }

    function checkAvailability() {
        // In a real application, this would make an API call
        // For now, we'll simulate availability check
        if (selectedType && selectedCategory) {
            const availabilityIndicators = document.querySelectorAll('[id^="availability-"]');
            availabilityIndicators.forEach(indicator => {
                // Simulate random availability
                <?php
                    $today = date("Y-m-d");
                     $check = $token->tokenCon->query("SELECT count(*) as count from tokens where token_Date = :today and status != 'cancel'", ["today"=>$today]) ;
                     $ans = $check[0]['count'];
                    ?>
                const random = <?php  echo $ans ?? 0; ?>;
                // alert(random)
                if (random >= 0) {
                    indicator.className = 'availability-indicator available';
                    indicator.innerHTML = '<i class="fas fa-check me-1"></i> Available';
                }
                if (random > 10) {
                    indicator.className = 'availability-indicator limited';
                    indicator.innerHTML = '<i class="fas fa-exclamation me-1"></i> Limited';
                }
                if (random == 50) {
                    indicator.className = 'availability-indicator full';
                    indicator.innerHTML = '<i class="fas fa-times me-1"></i> Full';
                }
            });
        }
    }
    let tokenNumber = 0;
    let sequential_number = 0;

    function gotoSuccess() {
        window.location.assign("success.php?token=" + tokenNumber + "&sequential=" + sequential_number)
    }

    function submitForm() {
        if (!validateForm()) {
            showAlert('Please fill all required fields correctly and agree to the terms.', 'warning');
            return;
        }
        // alert("this")
        selectedTime = <?= date("Y-m-d") ?>

        const form = document.getElementById('tokenForm');
        const formData = new FormData(form);
        formData.append('token_type', selectedType);
        formData.append('token_category', selectedCategory);
        formData.append('token_time', selectedTime);
        formData.append('token_price', selectedPrice);

        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();

        // Submit form via AJAX
        fetch('process-token.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingModal.hide();

                if (data.success) {
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    document.getElementById('successMessage').innerHTML = `
                        ${data.message}<br>
                        <strong>Your Token Number: ${data.token_number}</strong><br>
                        <strong>Total Amount: ${selectedPrice}</strong>
                    `;
                    tokenNumber = data.token_number
                    sequential_number = data.sequential_number
                    // alert(tokenNumber)
                    successModal.show();

                    // Reset form after success
                    setTimeout(() => {
                        form.reset();
                        selectedType = null;
                        selectedTypeName = null;
                        selectedCategory = null;
                        selectedPrice = 0;
                        selectedTime = null;
                        navigateToStep('step1');
                        document.querySelectorAll('.token-type-card').forEach(card => {
                            card.classList.remove('selected');
                        });
                        document.getElementById('categorySection').style.display = 'none';
                        document.getElementById('step2Next').disabled = true;
                    }, 3000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                loadingModal.hide();
                showAlert('Network error. Please check your connection and try again.', 'danger');
                console.error('Error:', error);
            });
    }

    function validateForm() {
        const form = document.getElementById('tokenForm');
        // Check required fields
        const required = ['patient_name', 'patient_phone', 'patient_email', 'doctor', 'token_date'];
        for (let field of required) {
            const input = form.querySelector(`[name="${field}"]`);
            if (!input || !input.value.trim()) {
                return false;
            }
        }

        // Check selections
        if (!selectedType || !selectedCategory) return false;

        // Check terms agreement
        if (!document.getElementById('termsAgreement').checked) return false;

        return true;
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

        const form = document.getElementById('tokenForm');
        form.prepend(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Security: Prevent form tampering
    Object.freeze(document.getElementById('tokenForm'));

    // Helper function to escape HTML
    function htmlspecialchars(str) {
        if (typeof str !== 'string') return '';
        return str.replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Helper function to capitalize first letter
    function ucfirst(str) {
        if (typeof str !== 'string') return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    </script>
</body>

</html>