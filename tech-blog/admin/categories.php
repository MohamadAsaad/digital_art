<?php
session_start();
include '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

try {
    // جلب التصنيفات مع معالجة الأخطاء
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(p.id) as posts_count 
        FROM categories c 
        LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
        GROUP BY c.id 
        ORDER BY c.name
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = handlePDOException($e, "حدث خطأ في جلب البيانات");
}

// حذف تصنيف مع التحقق
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    try {
        // التحقق من وجود التصنيف
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "التصنيف غير موجود";
        } else {
            // التحقق من وجود مقالات مرتبطة بالتصنيف
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $posts_count = $stmt->fetchColumn();
            
            if ($posts_count > 0) {
                $_SESSION['error'] = "لا يمكن حذف التصنيف لأنه يحتوي على مقالات. الرجاء نقل المقالات أولاً";
            } else {
                $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$category_id]);
                $_SESSION['success'] = "تم حذف التصنيف بنجاح";
            }
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = handlePDOException($e, "حدث خطأ أثناء حذف التصنيف");
    }
    
    header('Location: categories.php');
    exit;
}

// إضافة/تعديل تصنيف
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $color = sanitize($_POST['color']);
    $description = sanitize($_POST['description']);
    
    try {
        if (isset($_POST['category_id'])) {
            // تعديل تصنيف موجود
            $category_id = intval($_POST['category_id']);
            
            // التحقق من وجود التصنيف
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);
            
            if ($stmt->fetch()) {
                $pdo->prepare("UPDATE categories SET name = ?, color = ?, description = ? WHERE id = ?")
                    ->execute([$name, $color, $description, $category_id]);
                $_SESSION['success'] = "تم تحديث التصنيف بنجاح";
            } else {
                $_SESSION['error'] = "التصنيف غير موجود";
            }
        } else {
            // إضافة تصنيف جديد
            // التحقق من عدم وجود تصنيف بنفس الاسم
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            
            if ($stmt->fetch()) {
                $_SESSION['error'] = "هناك تصنيف بنفس الاسم موجود مسبقاً";
            } else {
                $pdo->prepare("INSERT INTO categories (name, color, description) VALUES (?, ?, ?)")
                    ->execute([$name, $color, $description]);
                $_SESSION['success'] = "تم إضافة التصنيف بنجاح";
            }
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = handlePDOException($e, "حدث خطأ أثناء حفظ التصنيف");
    }
    
    header('Location: categories.php');
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
    <title>إدارة التصنيفات - <?php echo SITE_NAME; ?></title>
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

        .color-badge {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-left: 8px;
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
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
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
                    <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> إدارة التصنيفات</a></li>
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
                <h1>إدارة التصنيفات</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="content-section">
                <h3 style="margin-bottom: 20px;">إضافة تصنيف جديد</h3>
                <form method="POST" style="max-width: 500px;">
                    <div class="form-group">
                        <label for="name" class="form-label">اسم التصنيف *</label>
                        <input type="text" id="name" name="name" class="form-control" required maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="color" class="form-label">لون التصنيف</label>
                        <input type="color" id="color" name="color" class="form-control" value="#2563eb" style="height: 40px;">
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">الوصف</label>
                        <textarea id="description" name="description" class="form-control" rows="3" maxlength="255"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة التصنيف
                    </button>
                </form>
            </div>

            <?php if (isset($categories) && !empty($categories)): ?>
                <div class="content-section">
                    <h3 style="margin-bottom: 20px;">التصنيفات الحالية</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>اللون</th>
                                <th>عدد المقالات</th>
                                <th>الوصف</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo sanitize($category['name']); ?></td>
                                    <td>
                                        <span class="color-badge" style="background-color: <?php echo $category['color']; ?>"></span>
                                        <?php echo $category['color']; ?>
                                    </td>
                                    <td><?php echo intval($category['posts_count']); ?></td>
                                    <td><?php echo sanitize($category['description'] ?: '---'); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" class="btn btn-warning btn-sm" 
                                                    onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>', '<?php echo $category['color']; ?>', '<?php echo addslashes($category['description']); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" 
                                               onclick="return confirm('هل أنت متأكد من حذف هذا التصنيف؟')">
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
                    <p>لا توجد تصنيفات</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- نموذج التعديل -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 8px; width: 500px; max-width: 90%;">
            <h3 style="margin-bottom: 20px;">تعديل التصنيف</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="category_id" id="edit_category_id">
                <div class="form-group">
                    <label for="edit_name" class="form-label">اسم التصنيف *</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="edit_color" class="form-label">لون التصنيف</label>
                    <input type="color" id="edit_color" name="color" class="form-control" style="height: 40px;">
                </div>
                
                <div class="form-group">
                    <label for="edit_description" class="form-label">الوصف</label>
                    <textarea id="edit_description" name="description" class="form-control" rows="3" maxlength="255"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">تحديث التصنيف</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editCategory(id, name, color, description) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_color').value = color;
            document.getElementById('edit_description').value = description;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // إغلاق النافذة عند النقر خارجها
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>