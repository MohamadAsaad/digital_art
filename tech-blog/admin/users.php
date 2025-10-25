<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

try {
    // جلب المستخدمين
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = handlePDOException($e, "حدث خطأ في جلب البيانات");
}

// حذف مستخدم مع التحقق
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    try {
        // التحقق من وجود المستخدم
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "المستخدم غير موجود";
        } else {
            // منع حذف المستخدم الحالي
            if ($user_id != $_SESSION['user_id']) {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                $_SESSION['success'] = "تم حذف المستخدم بنجاح";
            } else {
                $_SESSION['error'] = "لا يمكن حذف حسابك الشخصي";
            }
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = handlePDOException($e, "حدث خطأ أثناء حذف المستخدم");
    }
    
    header('Location: users.php');
    exit;
}

// تغيير صلاحية المستخدم مع التحقق
if (isset($_GET['change_role'])) {
    $user_id = intval($_GET['change_role']);
    $new_role = sanitize($_GET['role']);
    
    // التحقق من القيم المسموحة
    $allowed_roles = ['admin', 'user'];
    
    if (!in_array($new_role, $allowed_roles)) {
        $_SESSION['error'] = "صلاحية غير مسموحة";
        header('Location: users.php');
        exit;
    }
    
    try {
        // التحقق من وجود المستخدم
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "المستخدم غير موجود";
        } else {
            // منع تغيير صلاحية المستخدم الحالي
            if ($user_id != $_SESSION['user_id']) {
                $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $user_id]);
                $_SESSION['success'] = "تم تغيير صلاحية المستخدم بنجاح";
            } else {
                $_SESSION['error'] = "لا يمكن تغيير صلاحية حسابك الشخصي";
            }
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = handlePDOException($e, "حدث خطأ أثناء تغيير صلاحية المستخدم");
    }
    
    header('Location: users.php');
    exit;
}

// عرض رسائل النجاح أو الخطأ
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --text-color: #1f2937;
            --light-text: #6b7280;
            --background-color: #ffffff;
            --light-background: #f9fafb;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-background);
            color: var(--text-color);
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 250px;
            background: var(--text-color);
            color: white;
            padding: 20px 0;
        }

        .admin-content {
            flex: 1;
            background: var(--light-background);
            padding: 20px;
        }

        .admin-logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid #374151;
            margin-bottom: 20px;
        }

        .admin-nav ul {
            list-style: none;
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #d1d5db;
            text-decoration: none;
            transition: all 0.3s;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: var(--primary-color);
            color: white;
        }

        .content-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }

        .table th {
            background: #f9fafb;
            font-weight: 600;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 0.75rem;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .page-header {
            margin-bottom: 20px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .current-user {
            background: #f0f9ff;
        }

        @media (max-width: 768px) {
            .admin-layout {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- الشريط الجانبي -->
        <div class="admin-sidebar">
            <div class="admin-logo">
                <h2><i class="fas fa-cogs"></i> لوحة التحكم</h2>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> الرئيسية</a></li>
                    <li><a href="posts.php"><i class="fas fa-file-alt"></i> إدارة المقالات</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> إدارة التصنيفات</a></li>
                    <li><a href="comments.php"><i class="fas fa-comments"></i> إدارة التعليقات</a></li>
                    <li><a href="users.php" class="active"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                    <li><a href="../index.php"><i class="fas fa-home"></i> العودة للمدونة</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل خروج</a></li>
                </ul>
            </nav>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="admin-content">
            <div class="page-header">
                <h1>إدارة المستخدمين</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($users) && !empty($users)): ?>
                <div class="content-section">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>اسم المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الصلاحية</th>
                                <th>تاريخ التسجيل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="<?php echo $user['id'] == $_SESSION['user_id'] ? 'current-user' : ''; ?>">
                                    <td>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span style="color: #2563eb; font-size: 0.8rem;">(أنت)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo $user['role'] === 'admin' ? 'مدير' : 'مستخدم'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <?php if ($user['role'] === 'admin'): ?>
                                                    <a href="?change_role=<?php echo $user['id']; ?>&role=user" class="btn btn-warning btn-sm" 
                                                       onclick="return confirm('هل تريد تغيير صلاحية هذا المستخدم إلى مستخدم عادي؟')">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="?change_role=<?php echo $user['id']; ?>&role=admin" class="btn btn-primary btn-sm" 
                                                       onclick="return confirm('هل تريد منح هذا المستخدم صلاحية المدير؟')">
                                                        <i class="fas fa-user-shield"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #6b7280; font-size: 0.8rem;">حسابك</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="content-section">
                    <p>لا توجد مستخدمين</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>