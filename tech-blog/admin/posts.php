<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

try {
    // جلب المقالات مع معالجة الأخطاء
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, c.name as category_name 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // جلب التصنيفات للقائمة المنسدلة
    $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = handlePDOException($e, "حدث خطأ في جلب البيانات");
}

// حذف مقال مع التحقق من الوجود
if (isset($_GET['delete'])) {
    $post_id = intval($_GET['delete']);
    
    try {
        // التحقق من وجود المقال قبل الحذف
        $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        
        if ($stmt->fetch()) {
            // حذف الوسوم المرتبطة أولاً
            $pdo->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$post_id]);
            // ثم حذف المقال
            $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$post_id]);
            $_SESSION['success'] = "تم حذف المقال بنجاح";
        } else {
            $_SESSION['error'] = "المقال غير موجود";
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = handlePDOException($e, "حدث خطأ أثناء حذف المقال");
    }
    
    header('Location: posts.php');
    exit;
}

// تغيير حالة المقال مع التحقق من الوجود
if (isset($_GET['toggle_status'])) {
    $post_id = intval($_GET['toggle_status']);
    
    try {
        $stmt = $pdo->prepare("SELECT id, status FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        
        if ($post) {
            $new_status = $post['status'] === 'published' ? 'draft' : 'published';
            $pdo->prepare("UPDATE posts SET status = ? WHERE id = ?")->execute([$new_status, $post_id]);
            $_SESSION['success'] = "تم تغيير حالة المقال بنجاح";
        } else {
            $_SESSION['error'] = "المقال غير موجود";
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = handlePDOException($e, "حدث خطأ أثناء تغيير حالة المقال");
    }
    
    header('Location: posts.php');
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
    <title>إدارة المقالات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* نفس التنسيقات من index.php */
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            font-size: 0.8rem;
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

        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
                    <li><a href="posts.php" class="active"><i class="fas fa-file-alt"></i> إدارة المقالات</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> إدارة التصنيفات</a></li>
                    <li><a href="comments.php"><i class="fas fa-comments"></i> إدارة التعليقات</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                    <li><a href="../index.php"><i class="fas fa-home"></i> العودة للمدونة</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل خروج</a></li>
                </ul>
            </nav>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="admin-content">
            <div class="page-header">
                <h1>إدارة المقالات</h1>
                <a href="add_post.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة مقال جديد
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($posts) && !empty($posts)): ?>
                <div class="content-section">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>التصنيف</th>
                                <th>المؤلف</th>
                                <th>الحالة</th>
                                <th>المشاهدات</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo sanitize($post['title']); ?></td>
                                    <td><?php echo sanitize($post['category_name'] ?? 'بدون تصنيف'); ?></td>
                                    <td><?php echo sanitize($post['username']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $post['status'] === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $post['status'] === 'published' ? 'منشور' : 'مسودة'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo intval($post['views']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="../post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?toggle_status=<?php echo $post['id']; ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-toggle-<?php echo $post['status'] === 'published' ? 'on' : 'off'; ?>"></i>
                                            </a>
                                            <a href="?delete=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذا المقال؟')">
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
                    <p>لا توجد مقالات</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>