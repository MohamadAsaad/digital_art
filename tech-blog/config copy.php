<?php
// إزالة session_start() من هنا لأننا نستدعيها في كل ملف
// session_start();

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
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// دالة للحماية من XSS
function sanitize($data)
{
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

// دالة لتحميل الصور
function handleImageUpload($file)
{
    $upload_dir = 'uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024;

    // التحقق من نوع الملف باستخدام extension أكثر أماناً
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['error' => 'نوع الملف غير مسموح به'];
    }

    if ($file['size'] > $max_size) {
        return ['error' => 'حجم الملف كبير جداً'];
    }

    // إنشاء اسم فريد للملف
    $file_name = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return ['success' => true, 'url' => $file_path];
    }

    return ['error' => 'فشل في رفع الملف'];
}
?>