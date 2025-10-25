<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

try {
    // جلب التعليقات
    $stmt = $pdo->prepare("
        SELECT c.*, p.title as post_title 
        FROM comments c 
        LEFT JOIN posts p ON c.post_id = p.id 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = handlePDOException($e, "حدث خطأ في جلب البيانات");
}

// تغيير حالة التعليق مع التحقق
if (isset($_GET['action'])) {
    $comment_id = intval($_GET['id']);
    $action = sanitize($_GET['action']);
    
    // التحقق من القيم المسموحة
    $allowed_actions = ['approve', 'reject', 'delete'];
    
    if (!in_array($action, $allowed_actions)) {
        $_SESSION['error'] = "إجراء غير مسموح";
        header('Location: comments.php');
        exit;
    }
    
    try {
        // التحقق من وجود التعليق
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "التعليق غير موجود";
        } else {
            if ($action === 'approve') {
                $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?")->execute([$comment_id]);
                $_SESSION['success'] = "تم قبول التعليق بنجاح";
            } elseif ($action === 'reject') {
                $pdo->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?")->execute([$comment_id]);
                $_SESSION['success'] = "تم رفض التعليق بنجاح";
            } elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$comment_id]);
                $_SESSION['success'] = "تم حذف التعليق بنجاح";
            }
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = handlePDOException($e, "حدث خطأ أثناء معالجة التعليق");
    }
    
    header('Location: comments.php');
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
    <title>إدارة التعليقات - <?php echo SITE_NAME; ?></title>
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

        .btn-success {
            background: #10b981;
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

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .comment-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        @media (max-width: 768px) {
            .admin-layout {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
            }
            
            .table {
                font-size: 0.8rem;
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
                    <li><a href="comments.php" class="active"><i class="fas fa-comments"></i> إدارة التعليقات</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                    <li><a href="../index.php"><i class="fas fa-home"></i> العودة للمدونة</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل خروج</a></li>
                </ul>
            </nav>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="admin-content">
            <div class="page-header">
                <h1>إدارة التعليقات</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($comments) && !empty($comments)): ?>
                <div class="content-section">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>المقال</th>
                                <th>التعليق</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($comment['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($comment['user_email'] ?: '---'); ?></td>
                                    <td><?php echo htmlspecialchars($comment['post_title'] ?: 'مقال محذوف'); ?></td>
                                    <td class="comment-content" title="<?php echo htmlspecialchars($comment['content']); ?>">
                                        <?php echo htmlspecialchars($comment['content']); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $comment['status'] === 'approved' ? 'badge-success' : 
                                                 ($comment['status'] === 'pending' ? 'badge-warning' : 'badge-secondary'); 
                                        ?>">
                                            <?php 
                                            echo $comment['status'] === 'approved' ? 'مقبول' : 
                                                 ($comment['status'] === 'pending' ? 'قيد المراجعة' : 'مرفوض'); 
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($comment['created_at'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                            <?php if ($comment['status'] !== 'approved'): ?>
                                                <a href="?action=approve&id=<?php echo $comment['id']; ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($comment['status'] !== 'rejected'): ?>
                                                <a href="?action=reject&id=<?php echo $comment['id']; ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="?action=delete&id=<?php echo $comment['id']; ?>" class="btn btn-danger btn-sm" 
                                               onclick="return confirm('هل أنت متأكد من حذف هذا التعليق؟')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="content-section">
                    <p>لا توجد تعليقات</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>