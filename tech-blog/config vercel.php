<?php
// إعدادات قاعدة البيانات PlanetScale
define('DB_HOST', $_ENV['DB_HOST'] ?? 'aws.connect.psdb.cloud');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'tech-blog');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('SITE_URL', $_ENV['SITE_URL'] ?? 'https://your-domain.vercel.app');
define('SITE_NAME', 'مدونة التقنية');

// الاتصال بقاعدة البيانات مع SSL لـ PlanetScale
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
        DB_USER, 
        DB_PASS,
        [
            PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/cert.pem',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
} catch (PDOException $e) {
    error_log("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    die("حدث خطأ في الاتصال بالنظام. الرجاء المحاولة لاحقاً.");
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
    
    // في بيئة الإنتاج، لا تعرض تفاصيل الخطأ للمستخدم
    if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
        return $message;
    } else {
        return $message . ": " . $e->getMessage();
    }
}

// دالة لتحميل الصور (معدلة للـ Vercel)
function handleImageUpload($file)
{
    // على Vercel، نستخدم خدمة تخزين خارجية
    // يمكنك استخدام Cloudinary أو Uploadcare أو أي خدمة مشابهة
    
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

    // التحقق من نوع الملف
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

    // على Vercel، نقوم بحفظ الصورة في مجلد مؤقت
    // في الإنتاج الحقيقي، استخدم خدمة تخزين سحابية
    $upload_dir = 'uploads/';
    
    // إنشاء المجلد إذا لم يكن موجوداً
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // إنشاء اسم فريد للملف
    $file_name = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    // تحريك الملف إلى المجلد المطلوب
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return ['success' => true, 'url' => $file_path];
    }

    return ['error' => 'فشل في حفظ الملف على الخادم'];
}

// دالة للحصول على عنوان URL الحالي
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
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

// إعدادات التصحيح - تعطيل في الإنتاج
if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// بدء الجلسة إذا لم تبدأ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// دالة مساعدة لـ PlanetScale (لا يدعم بعض ميزات MySQL)
function isPlanetScale() {
    return strpos(DB_HOST, 'planetscale') !== false || strpos(DB_HOST, 'psdb.cloud') !== false;
}

// تجنب استخدام استعلامات غير مدعومة في PlanetScale
if (isPlanetScale()) {
    // PlanetScale لا يدعم LOCK TABLES, GET_LOCK, etc.
    function checkPlanetScaleCompatibility($query) {
        $unsupported = [
            'LOCK TABLES',
            'UNLOCK TABLES',
            'GET_LOCK',
            'RELEASE_LOCK',
            'SELECT .* FOR UPDATE',
            'SELECT .* LOCK IN SHARE MODE'
        ];
        
        foreach ($unsupported as $pattern) {
            if (preg_match("/$pattern/i", $query)) {
                throw new Exception("استعلام غير مدعوم في PlanetScale: $pattern");
            }
        }
    }
}
?>