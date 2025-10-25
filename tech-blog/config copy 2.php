<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'tech_blog');
define('DB_USER', 'root');
define('DB_PASS', '');

// إعدادات الموقع - تأكد من تعديل المسار حسب مجلدك
define('SITE_URL', 'http://localhost/tech-blog');
define('SITE_NAME', 'مدونة التقنية');

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// دالة للحماية من XSS
function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// دالة للتحقق من تسجيل الدخول
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// دالة للتحقق من صلاحية المدير
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// دالة لمعالجة أخطاء PDO
function handlePDOException($e, $message = 'حدث خطأ في قاعدة البيانات') {
    error_log("PDO Error: " . $e->getMessage());
    return $message;
}

// دالة لتحميل الصور (محسنة)
function handleImageUpload($file)
{
    $upload_dir = '../uploads/';

    // إنشاء المجلد إذا لم يكن موجوداً
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024;

    // التحقق من وجود أخطاء في الرفع
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['error' => 'حجم الملف كبير جداً'];
            case UPLOAD_ERR_PARTIAL:
                return ['error' => 'تم رفع جزء من الملف فقط'];
            case UPLOAD_ERR_NO_FILE:
                return ['error' => 'لم يتم اختيار ملف'];
            default:
                return ['error' => 'حدث خطأ غير معروف في رفع الملف'];
        }
    }

    // التحقق من أن الملف تم رفعها عبر HTTP POST
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'الملف لم يتم رفعه بطريقة صحيحة'];
    }

    // التحقق من نوع الملف باستخدام mime type أكثر أماناً
    $file_type = mime_content_type($file['tmp_name']);
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($file_extension, $allowed_extensions) || !in_array($file_type, $allowed_types)) {
        return ['error' => 'نوع الملف غير مسموح به. المسموح: JPEG, PNG, GIF, WebP'];
    }

    // التحقق من حجم الملف
    if ($file['size'] > $max_size) {
        return ['error' => 'حجم الملف كبير جداً. الحد الأقصى 5MB'];
    }

    // التحقق من أن الملف صورة حقيقية
    if (!getimagesize($file['tmp_name'])) {
        return ['error' => 'الملف المختار ليس صورة صالحة'];
    }

    // إنشاء اسم فريد للملف
    $file_name = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    // تحريك الملف إلى المجلد المطلوب
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // إرجاع المسار النسبي بدون ../
        $relative_path = 'uploads/' . $file_name;
        return ['success' => true, 'url' => $relative_path];
    }

    return ['error' => 'فشل في حفظ الملف على الخادم'];
}

// دالة للحصول على عنوان URL الحالي
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . '://' . $host . $uri;
}

// دالة لإعادة التوجيه مع رسائل
function redirectWithMessage($url, $type, $message) {
    $_SESSION[$type] = $message;
    header('Location: ' . $url);
    exit;
}

// دالة للتحقق من وجود صورة افتراضية
function getDefaultImage() {
    return 'assets/images/default-post.jpg';
}

// دالة لقص النص
function truncateText($text, $length = 150) {
    if (mb_strlen($text) > $length) {
        $text = mb_substr($text, 0, $length) . '...';
    }
    return $text;
}

// دالة لتحسين عرض التواريخ
function formatDate($date, $format = 'Y-m-d H:i') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

// إعداد المنطقة الزمنية
date_default_timezone_set('Asia/Riyadh');

// تعطيل reporting الأخطاء في production (تفعيل في development فقط)
// error_reporting(0);
// ini_set('display_errors', 0);

// للdevelopment يمكنك تفعيل هذا:
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/
?>