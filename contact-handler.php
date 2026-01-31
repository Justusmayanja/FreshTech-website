<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/config/config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get form data
$name = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$service = trim($_POST['service'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($message)) {
    $response['message'] = 'Please fill in all required fields (Name, Email, Message)';
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address';
    echo json_encode($response);
    exit;
}

// Create subject from service
$subject = 'General Inquiry';
if (!empty($service)) {
    switch ($service) {
        case 'website':
            $subject = 'Website Development Inquiry';
            break;
        case 'branding':
            $subject = 'Graphic Design & Branding Inquiry';
            break;
        case 'accessories':
            $subject = 'Phone Accessories Purchase Inquiry';
            break;
        default:
            $subject = 'General Inquiry';
    }
}

// Try to save to database
$db_available = isset($pdo) && $pdo instanceof PDO;

if ($db_available) {
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        
        $response['success'] = true;
        $response['message'] = 'Thank you for contacting us! We will get back to you soon.';
    } catch (Exception $e) {
        $response['message'] = 'Failed to send message. Please try again or contact us directly via email.';
        error_log('Contact form error: ' . $e->getMessage());
    }
} else {
    // Database not available - fallback (could send email here)
    $response['message'] = 'Database connection unavailable. Please contact us directly at pulsetechsolutions@gmail.com';
}

echo json_encode($response);
exit;