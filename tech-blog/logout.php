<?php
session_start();
include 'config.php';

// مسح جميع بيانات الجلسة
$_SESSION = array();

// إذا كنت تريد تدمير الكوكيز أيضاً
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// تدمير الجلسة
session_destroy();

// إعادة التوجيه مع رسالة نجاح
header('Location: index.php?logout=success');
exit;
?>