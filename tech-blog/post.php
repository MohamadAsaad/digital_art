<?php
session_start(); // إضافة هذا السطر
include 'config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = intval($_GET['id']);

try {
    // زيادة عدد المشاهدات
    $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post_id]);

    // جلب بيانات المقال
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, c.name as category_name, c.color 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.status = 'published'
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header('Location: index.php');
        exit;
    }

    // جلب وسوم المقال
    $tags_stmt = $pdo->prepare("
        SELECT t.name FROM tags t 
        JOIN post_tags pt ON t.id = pt.tag_id 
        WHERE pt.post_id = ?
    ");
    $tags_stmt->execute([$post_id]);
    $tags = $tags_stmt->fetchAll(PDO::FETCH_COLUMN);

    // جلب التعليقات المقبولة
    $comments_stmt = $pdo->prepare("
        SELECT * FROM comments 
        WHERE post_id = ? AND status = 'approved' 
        ORDER BY created_at DESC
    ");
    $comments_stmt->execute([$post_id]);
    $comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // في حالة حدوث خطأ في قاعدة البيانات
    header('Location: index.php');
    exit;
}

// إضافة تعليق جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $user_name = sanitize($_POST['user_name']);
    $user_email = sanitize($_POST['user_email']);
    $content = sanitize($_POST['content']);

    // تحقق من أن الحقول المطلوبة غير فارغة
    if (empty($user_name) || empty($content)) {
        $error = "الرجاء ملء جميع الحقول المطلوبة";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_name, user_email, content, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$post_id, $user_name, $user_email, $content]);
            $success = "شكراً لك! تم إرسال تعليقك وسيظهر بعد المراجعة.";
        } catch (PDOException $e) {
            $error = "حدث خطأ أثناء إضافة التعليق";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post['title']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* إضافة الأنماط الأساسية */
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --text-color: #1f2937;
            --light-text: #6b7280;
            --background-color: #ffffff;
            --light-background: #f9fafb;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --error-color: #ef4444;
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
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .back-to-blog {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--primary-color);
            text-decoration: none;
            margin-bottom: 20px;
        }

        .single-post {
            background: var(--background-color);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .post-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: var(--light-text);
        }

        .post-category {
            padding: 3px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.8rem;
        }

        .post-title {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--text-color);
        }

        .post-tags {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .tag {
            background: var(--light-background);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            color: var(--light-text);
        }

        .post-content {
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .comments-section {
            background: var(--background-color);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .comment {
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .comment-author {
            font-weight: 600;
            color: var(--primary-color);
        }

        .comment-date {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .comment-form {
            background: var(--light-background);
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .post-meta {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-to-blog"><i class="fas fa-arrow-right"></i> العودة إلى المدونة</a>

        <div class="single-post">
            <?php if (isset($post['image_url']) && !empty($post['image_url'])): ?>
                <img src="<?php echo $post['image_url']; ?>" alt="<?php echo $post['title']; ?>" style="width:100%; border-radius:8px; margin-bottom:20px;">
            <?php endif; ?>

            <div class="post-meta">
                <span class="post-date"><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></span>
                <span class="post-category" style="background-color: <?php echo $post['color'] ?? '#2563eb'; ?>">
                    <?php echo $post['category_name'] ?? 'بدون تصنيف'; ?>
                </span>
                <span><i class="fas fa-eye"></i> <?php echo $post['views']; ?> مشاهدة</span>
                <span><i class="fas fa-user"></i> <?php echo $post['username']; ?></span>
            </div>

            <h1 class="post-title"><?php echo $post['title']; ?></h1>

            <?php if (!empty($tags)): ?>
                <div class="post-tags">
                    <?php foreach ($tags as $tag): ?>
                        <span class="tag"><?php echo $tag; ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="post-content">
                <?php echo nl2br($post['content']); ?>
            </div>
        </div>

        <!-- قسم التعليقات -->
        <div class="comments-section">
            <h3><i class="fas fa-comments"></i> التعليقات (<?php echo count($comments); ?>)</h3>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($comments)): ?>
                <p class="text-center">لا توجد تعليقات بعد. كن أول من يعلق!</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <span class="comment-author"><?php echo $comment['user_name']; ?></span>
                            <span class="comment-date"><?php echo date('Y-m-d', strtotime($comment['created_at'])); ?></span>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br($comment['content']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- نموذج إضافة تعليق -->
            <div class="comment-form">
                <h4><i class="fas fa-edit"></i> أضف تعليقاً</h4>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user_name" class="form-label">الاسم *</label>
                            <input type="text" id="user_name" name="user_name" class="form-control" required value="<?php echo isset($_POST['user_name']) ? $_POST['user_name'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="user_email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" id="user_email" name="user_email" class="form-control" value="<?php echo isset($_POST['user_email']) ? $_POST['user_email'] : ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="content" class="form-label">التعليق *</label>
                        <textarea id="content" name="content" class="form-control" rows="5" required><?php echo isset($_POST['content']) ? $_POST['content'] : ''; ?></textarea>
                    </div>
                    <button type="submit" name="add_comment" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> إرسال التعليق
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>