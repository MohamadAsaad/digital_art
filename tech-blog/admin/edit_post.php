<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$post_id = intval($_GET['id']);

try {
    // جلب بيانات المقال مع التحقق من الوجود
    $stmt = $pdo->prepare("
        SELECT p.*, GROUP_CONCAT(pt.tag_id) as tag_ids 
        FROM posts p 
        LEFT JOIN post_tags pt ON p.id = pt.post_id 
        WHERE p.id = ? 
        GROUP BY p.id
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        $_SESSION['error'] = "المقال غير موجود";
        header('Location: posts.php');
        exit;
    }

    // جلب التصنيفات والوسوم
    $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $tags = $pdo->query("SELECT * FROM tags")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = handlePDOException($e, "حدث خطأ في جلب البيانات");
    $_SESSION['error'] = $error;
    header('Location: posts.php');
    exit;
}

// معالجة التعديل
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = $_POST['content'];
    $excerpt = sanitize($_POST['excerpt']);
    $category_id = intval($_POST['category_id']);
    $status = sanitize($_POST['status']);
    $selected_tags = isset($_POST['tags']) ? array_map('intval', $_POST['tags']) : [];

    // رفع الصورة إذا تم اختيار جديدة
    $image_url = $post['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_result = handleImageUpload($_FILES['image']);
        if (isset($upload_result['url'])) {
            $image_url = $upload_result['url'];
            
            // حذف الصورة القديمة إذا كانت موجودة
            if ($post['image_url'] && file_exists('../' . $post['image_url'])) {
                unlink('../' . $post['image_url']);
            }
        } else {
            $error = $upload_result['error'] ?? "حدث خطأ في رفع الصورة";
        }
    }

    if (!isset($error)) {
        try {
            // تحديث المقال
            $stmt = $pdo->prepare("
                UPDATE posts SET 
                title = ?, content = ?, excerpt = ?, image_url = ?, 
                category_id = ?, status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $excerpt, $image_url, $category_id, $status, $post_id]);

            // تحديث الوسوم
            $pdo->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$post_id]);
            
            if (!empty($selected_tags)) {
                $tag_stmt = $pdo->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                foreach ($selected_tags as $tag_id) {
                    if ($tag_id > 0) {
                        $tag_stmt->execute([$post_id, $tag_id]);
                    }
                }
            }

            $_SESSION['success'] = "تم تحديث المقال بنجاح";
            header('Location: posts.php');
            exit;

        } catch (PDOException $e) {
            $error = handlePDOException($e, "حدث خطأ أثناء تحديث المقال");
        }
    }
}

// تحويل الوسوم الحالية إلى مصفوفة
$current_tags = $post['tag_ids'] ? explode(',', $post['tag_ids']) : [];

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
    <title>تعديل مقال - <?php echo SITE_NAME; ?></title>
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
            overflow-y: auto;
        }

        .admin-logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid #374151;
            margin-bottom: 20px;
        }

        .admin-logo h2 {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
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
            border-right: 3px solid transparent;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: var(--primary-color);
            color: white;
            border-right-color: var(--secondary-color);
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #374151;
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            font-size: 1rem;
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
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
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 10px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .tag-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: white;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tag-checkbox:hover {
            border-color: var(--primary-color);
            background: #f0f9ff;
        }

        .tag-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid transparent;
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border-color: #bbf7d0;
        }

        .current-image {
            max-width: 300px;
            margin-top: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 2px solid #e5e7eb;
        }

        .current-image-container {
            margin-top: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .current-image-container p {
            margin-bottom: 10px;
            font-weight: 500;
            color: #374151;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .page-header h1 {
            color: var(--text-color);
            font-size: 1.8rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .file-input-container {
            position: relative;
        }

        .file-input-container input[type="file"] {
            padding: 12px 15px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            width: 100%;
        }

        .required::after {
            content: " *";
            color: #dc2626;
        }

        @media (max-width: 1024px) {
            .admin-layout {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .tags-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .admin-content {
                padding: 15px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .tags-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .btn {
                padding: 10px 16px;
                font-size: 0.9rem;
            }
            
            .form-control,
            .form-select {
                padding: 10px 12px;
            }
        }

        .ck-editor {
            border-radius: 6px;
            overflow: hidden;
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
                <h1>تعديل مقال</h1>
                <a href="posts.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> رجوع لقائمة المقالات
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title" class="form-label required">عنوان المقال</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($post['title']); ?>" 
                               required maxlength="255" placeholder="أدخل عنوان المقال">
                    </div>

                    <div class="form-group">
                        <label for="excerpt" class="form-label">ملخص المقال</label>
                        <textarea id="excerpt" name="excerpt" class="form-control" rows="3" 
                                  maxlength="500" placeholder="ملخص مختصر عن المقال..."><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                        <small style="color: #6b7280; margin-top: 5px; display: block;">الحد الأقصى 500 حرف</small>
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label required">محتويات المقال</label>
                        <textarea id="content" name="content" class="form-control" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label">صورة المقال</label>
                        <div class="file-input-container">
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        </div>
                        <small style="color: #6b7280; margin-top: 5px; display: block;">الحد الأقصى 5MB - المسموح: JPG, PNG, GIF, WebP</small>
                        
                        <?php if ($post['image_url']): ?>
                            <div class="current-image-container">
                                <p>الصورة الحالية:</p>
                                <img src="../<?php echo htmlspecialchars($post['image_url']); ?>" 
                                     alt="صورة المقال الحالية" class="current-image"
                                     onerror="this.src='../<?php echo getDefaultImage(); ?>'">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id" class="form-label">التصنيف</label>
                            <select id="category_id" name="category_id" class="form-select">
                                <option value="">اختر التصنيف</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category['id'] == $post['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">حالة المقال</label>
                            <select id="status" name="status" class="form-select">
                                <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>مسودة</option>
                                <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>منشور</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">الوسوم</label>
                        <div class="tags-container">
                            <?php if (!empty($tags)): ?>
                                <?php foreach ($tags as $tag): ?>
                                    <label class="tag-checkbox">
                                        <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" 
                                            <?php echo in_array($tag['id'], $current_tags) ? 'checked' : ''; ?>>
                                        <span><?php echo htmlspecialchars($tag['name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #6b7280; text-align: center; grid-column: 1 / -1;">لا توجد وسوم متاحة</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; gap: 15px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> تحديث المقال
                        </button>
                        <a href="posts.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                        <a href="../post.php?id=<?php echo $post_id; ?>" class="btn" style="background: #10b981; color: white;" target="_blank">
                            <i class="fas fa-eye"></i> معاينة
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        CKEDITOR.replace('content', {
            language: 'ar',
            contentsLangDirection: 'rtl',
            toolbar: [
                { name: 'document', items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'] },
                { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] },
                { name: 'forms', items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'] },
                '/',
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl'] },
                { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
                { name: 'insert', items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'] },
                '/',
                { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'tools', items: ['Maximize', 'ShowBlocks'] }
            ],
            height: 400
        });

        // إضافة تأكيد قبل المغادرة إذا كان هناك تغييرات
        let formChanged = false;
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                formChanged = true;
            });
            
            input.addEventListener('change', () => {
                formChanged = true;
            });
        });

        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        form.addEventListener('submit', () => {
            formChanged = false;
        });
    </script>
</body>
</html>