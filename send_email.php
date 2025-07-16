<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// إعدادات الحماية
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self';");

// معالجة النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  try {
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(strip_tags($_POST['subject']));
    $message = htmlspecialchars(strip_tags($_POST['message']));

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
      die("يرجى ملء جميع الحقول المطلوبة.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      die("صيغة البريد الإلكتروني غير صحيحة.");
    }

    // إعدادات Gmail
    $smtp_host = 'smtp.gmail.com';
    $smtp_port = 465;
    $smtp_username = 'di9ital.site@gmail.com';       // ✅ غيّر هذا
    $smtp_password = 'mkwklqjrcunnfzay';          // ✅ غيّر هذا (App Password)

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $smtp_port;

    $mail->setFrom($smtp_username, 'Digital Art');
    $mail->addAddress("di9ital.site@gmail.com");
    $mail->addReplyTo($email, $name);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    $mail->Subject = "New Contact Form: $subject";
    $mail->Body = "
      <h2>تفاصيل الرسالة</h2>
      <p><strong>الاسم:</strong> $name</p>
      <p><strong>البريد الإلكتروني:</strong> $email</p>
      <p><strong>الموضوع:</strong> $subject</p>
      <p><strong>الرسالة:</strong></p>
      <p>$message</p>
    ";

    if ($mail->send()) {
      echo 'OK';
    } else {
      echo 'فشل في الإرسال.';
    }
  } catch (Exception $e) {
    echo 'حدث خطأ أثناء الإرسال: ' . $mail->ErrorInfo;
  }
} else {
  echo 'طريقة الطلب غير صحيحة.';
}
