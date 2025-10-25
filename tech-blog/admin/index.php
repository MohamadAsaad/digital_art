<?php
session_start();
include '../config.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// إحصائيات المدونة
try {
    $total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $published_posts = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
    $total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $pending_comments = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
} catch (PDOException $e) {
    $total_posts = $published_posts = $total_comments = $pending_comments = 0;
}

// جلب المقالات الأخيرة
try {
    $recent_posts = $pdo->query("
        SELECT p.*, u.username, c.name as category_name 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_posts = [];
}

// جلب التعليقات الأخيرة
try {
    $recent_comments = $pdo->query("
        SELECT c.*, p.title as post_title 
        FROM comments c 
        LEFT JOIN posts p ON c.post_id = p.id 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_comments = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - <?php echo SITE_NAME; ?></title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
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

        .table th,
        .table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid var(--border-color);
        }

        .table th {
            background: var(--light-background);
            font-weight: 600;
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

        @media (max-width: 768px) {
            .admin-layout {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> الرئيسية</a></li>
                    <li><a href="posts.php"><i class="fas fa-file-alt"></i> إدارة المقالات</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> إدارة التصنيفات</a></li>
                    <li><a href="tags.php"><i class="fas fa-hashtag"></i> إدارة الوسوم</a></li>
                    <li><a href="comments.php"><i class="fas fa-comments"></i> إدارة التعليقات</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                    <li><a href="../index.php"><i class="fas fa-home"></i> العودة للمدونة</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل خروج</a></li>
                </ul>
            </nav>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="admin-content">
            <h1 style="margin-bottom: 30px;">مرحباً، <?php echo $_SESSION['username']; ?></h1>

            <!-- الإحصائيات -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_posts; ?></div>
                    <div class="stat-label">إجمالي المقالات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $published_posts; ?></div>
                    <div class="stat-label">مقالات منشورة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_comments; ?></div>
                    <div class="stat-label">إجمالي التعليقات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pending_comments; ?></div>
                    <div class="stat-label">تعليقات قيد المراجعة</div>
                </div>
            </div>

            <!-- المقالات الأخيرة -->
            <div class="content-section">
                <h3><i class="fas fa-clock"></i> المقالات الأخيرة</h3>
                <?php if (!empty($recent_posts)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>التصنيف</th>
                                <th>المؤلف</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_posts as $post): ?>
                                <tr>
                                    <td><?php echo $post['title']; ?></td>
                                    <td><?php echo $post['category_name'] ?? 'بدون تصنيف'; ?></td>
                                    <td><?php echo $post['username']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $post['status'] === 'published' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $post['status'] === 'published' ? 'منشور' : 'مسودة'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>لا توجد مقالات</p>
                <?php endif; ?>
            </div>

            <!-- التعليقات الأخيرة -->
            <div class="content-section">
                <h3><i class="fas fa-comments"></i> التعليقات الأخيرة</h3>
                <?php if (!empty($recent_comments)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المستخدم</th>
                                <th>المقال</th>
                                <th>التعليق</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_comments as $comment): ?>
                                <tr>
                                    <td><?php echo $comment['user_name']; ?></td>
                                    <td><?php echo $comment['post_title'] ?? 'مقال محذوف'; ?></td>
                                    <td><?php echo substr($comment['content'], 0, 50) . '...'; ?></td>
                                    <td>
                                        <span class="badge <?php echo $comment['status'] === 'approved' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $comment['status'] === 'approved' ? 'مقبول' : 'قيد المراجعة'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($comment['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>لا توجد تعليقات</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>