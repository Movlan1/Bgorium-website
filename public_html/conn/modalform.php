<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/vendor/autoload.php';

// EMAIL YOXLA
if (!isset($_POST['userMail']) || empty($_POST['userMail'])) {
    http_response_code(400);
    echo 'Email is required';
    exit;
}

$userMail = trim($_POST['userMail']);

if (!filter_var($userMail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Invalid email';
    exit;
}

try {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    $mail->isSMTP();
    $mail->Host = 'mail.bgorium.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'info@bgorium.com';
    $mail->Password = 'bgorium_2390%';
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->setFrom('info@bgorium.com', 'Bgorium Website');
    $mail->addAddress('info@bgorium.com');

    $mail->Subject = 'New subscription';
    $mail->Body = "New subscriber:\n$userMail";

    $mail->send();

    echo 'Message sent successfully';

} catch (Exception $e) {
    http_response_code(500);
    echo 'Mailer error';
}

exit;
