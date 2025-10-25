<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

try {
    // جلب التصنيفات والوسوم
    $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $tags = $pdo->query("SELECT * FROM tags")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = handlePDOException($e, "حدث خطأ في جلب البيانات");
}

// معالجة إضافة المقال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = $_POST['content'];
    $excerpt = sanitize($_POST['excerpt']);
    $category_id = intval($_POST['category_id']);
    $status = sanitize($_POST['status']);
    $selected_tags = isset($_POST['tags']) ? array_map('intval', $_POST['tags']) : [];

    // رفع الصورة
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_result = handleImageUpload($_FILES['image']);
        if (isset($upload_result['url'])) {
            $image_url = $upload_result['url'];
        } else {
            $error = $upload_result['error'] ?? "حدث خطأ في رفع الصورة";
        }
    }

    if (!isset($error)) {
        try {
            // إضافة المقال
            $stmt = $pdo->prepare("
                INSERT INTO posts (title, content, excerpt, image_url, author_id, category_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, 
                $content, 
                $excerpt, 
                $image_url, 
                $_SESSION['user_id'], 
                $category_id, 
                $status
            ]);

            $post_id = $pdo->lastInsertId();

            // إضافة الوسوم
            if (!empty($selected_tags)) {
                $tag_stmt = $pdo->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                foreach ($selected_tags as $tag_id) {
                    $tag_stmt->execute([$post_id, $tag_id]);
                }
            }

            $_SESSION['success'] = "تم إضافة المقال بنجاح";
            header('Location: posts.php');
            exit;

        } catch (PDOException $e) {
            $error = handlePDOException($e, "حدث خطأ أثناء إضافة المقال");
        }
    }
}

// عرض رسائل النجاح أو الخطأ
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? $error ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مقال جديد - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
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

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            font-size: 1rem;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .tags-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .tag-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
                    <li><a href="users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                    <li><a href="../index.php"><i class="fas fa-home"></i> العودة للمدونة</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل خروج</a></li>
                </ul>
            </nav>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="admin-content">
            <div class="page-header">
                <h1>إضافة مقال جديد</h1>
                <a href="posts.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> رجوع
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title" class="form-label">عنوان المقال *</label>
                        <input type="text" id="title" name="title" class="form-control" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="excerpt" class="form-label">ملخص المقال</label>
                        <textarea id="excerpt" name="excerpt" class="form-control" rows="3" placeholder="ملخص مختصر عن المقال..." maxlength="500"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label">محتويات المقال *</label>
                        <textarea id="content" name="content" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label">صورة المقال</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="category_id" class="form-label">التصنيف</label>
                            <select id="category_id" name="category_id" class="form-select">
                                <option value="">اختر التصنيف</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">حالة المقال</label>
                            <select id="status" name="status" class="form-select">
                                <option value="draft">مسودة</option>
                                <option value="published">منشور</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">الوسوم</label>
                        <div class="tags-container">
                            <?php foreach ($tags as $tag): ?>
                                <label class="tag-checkbox">
                                    <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; gap: 10px; margin-top: 30px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ المقال
                        </button>
                        <a href="posts.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        CKEDITOR.replace('content', {
            language: 'ar',
            contentsLangDirection: 'rtl'
        });
    </script>
</body>
</html>