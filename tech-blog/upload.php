<?php
session_start();
include 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('HTTP/1.0 403 Forbidden');
    die('ليس لديك صلاحية لرفع الملفات');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $upload_dir = 'uploads/';

    // إنشاء المجلد إذا لم يكن موجوداً
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file = $_FILES['image'];
    
    // التحقق من وجود أخطاء في الرفع
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'حدث خطأ أثناء رفع الملف']);
        exit;
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // التحقق من امتداد الملف
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['error' => 'نوع الملف غير مسموح به']);
        exit;
    }

    // التحقق من نوع الملف باستخدام mime type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime_type, $allowed_mime_types)) {
        echo json_encode(['error' => 'نوع الملف غير مسموح به']);
        exit;
    }

    // التحقق من حجم الملف
    if ($file['size'] > $max_size) {
        echo json_encode(['error' => 'حجم الملف كبير جداً']);
        exit;
    }

    // إنشاء اسم فريد للملف
    $file_name = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    // رفع الملف
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        echo json_encode([
            'success' => true,
            'url' => $file_path // استخدام مسار نسبي بدلاً من كامل
        ]);
    } else {
        echo json_encode(['error' => 'فشل في رفع الملف']);
    }
    exit;
}

// إذا لم يكن طلب POST
header('HTTP/1.0 405 Method Not Allowed');
echo json_encode(['error' => 'الطريقة غير مسموحة']);
?>