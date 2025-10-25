<?php
session_start();
include 'config.php';

// عرض رسالة تسجيل الخروج إذا وجدت
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $logout_message = "تم تسجيل الخروج بنجاح";
}

try {
    // جلب المقالات المنشورة
    $stmt = $pdo->query("
        SELECT p.*, u.username, c.name as category_name, c.color 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'published' 
        ORDER BY p.created_at DESC 
        LIMIT 10
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // جلب التصنيفات
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // جلب المقالات الشائعة
    $stmt = $pdo->query("
        SELECT p.*, u.username, c.name as category_name 
        FROM posts p 
        LEFT JOIN users u ON p.author_id = u.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'published' 
        ORDER BY p.views DESC 
        LIMIT 5
    ");
    $popular_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // في حالة حدوث خطأ، تعيين مصفوفات فارغة
    $posts = [];
    $categories = [];
    $popular_posts = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - الرئيسية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --text-color: #1f2937;
            --light-text: #6b7280;
            --background-color: #ffffff;
            --light-background: #f9fafb;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header Styles */
        header {
            background-color: var(--background-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
        }

        nav ul li {
            margin-left: 20px;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        nav ul li a:hover {
            color: var(--primary-color);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }

        .btn-outline:hover {
            background-color: var(--light-background);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            padding: 30px 0;
        }

        /* Blog Posts */
        .posts-container {
            display: grid;
            gap: 25px;
        }

        .post-card {
            background-color: var(--background-color);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .post-content {
            padding: 20px;
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.85rem;
            color: var(--light-text);
            flex-wrap: wrap;
            gap: 10px;
        }

        .post-category {
            background-color: var(--primary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .post-title {
            font-size: 1.25rem;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .post-title a {
            text-decoration: none;
            color: inherit;
        }

        .post-title a:hover {
            color: var(--primary-color);
        }

        .post-excerpt {
            color: var(--light-text);
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 15px;
        }

        .tag {
            background-color: var(--light-background);
            color: var(--light-text);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .read-more {
            display: inline-block;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }

        .read-more:hover {
            color: var(--secondary-color);
        }

        /* Search Box */
        .search-box {
            background: var(--background-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
        }

        /* Sidebar */
        .sidebar {
            background-color: var(--background-color);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .sidebar-section {
            margin-bottom: 30px;
        }

        .sidebar-title {
            font-size: 1.1rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .categories-list,
        .popular-posts-list {
            list-style: none;
        }

        .categories-list li,
        .popular-posts-list li {
            margin-bottom: 10px;
        }

        .categories-list a,
        .popular-posts-list a {
            text-decoration: none;
            color: var(--text-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            transition: color 0.3s;
        }

        .categories-list a:hover,
        .popular-posts-list a:hover {
            color: var(--primary-color);
        }

        .category-count {
            background-color: var(--light-background);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            color: var(--light-text);
        }

        /* Footer */
        footer {
            background-color: var(--text-color);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-section h3 {
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .footer-section p,
        .footer-section a {
            color: #d1d5db;
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
        }

        .footer-section a:hover {
            color: white;
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #374151;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            nav {
                width: 100%;
                margin-top: 15px;
                display: none;
            }

            nav.active {
                display: block;
            }

            nav ul {
                flex-direction: column;
                align-items: flex-start;
            }

            nav ul li {
                margin: 0;
                margin-bottom: 10px;
            }

            .user-menu {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                width: 100%;
            }

            .mobile-menu-btn {
                display: block;
                position: absolute;
                top: 15px;
                left: 15px;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }

            .search-form {
                flex-direction: column;
            }

            .post-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }

        /* Utility Classes */
        .hidden {
            display: none !important;
        }

        .text-center {
            text-align: center;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mb-20 {
            margin-bottom: 20px;
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
    </style>
</head>

<body>
    <!-- Header -->
    <!-- Header -->
<header>
    <div class="container">
        <div class="header-content">
            <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
            <a href="index.php" class="logo">
                <i class="fas fa-laptop-code"></i>
                <span><?php echo SITE_NAME; ?></span>
            </a>
            <nav id="main-nav">
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> الرئيسية</a></li>
                    <li><a href="blog.php"><i class="fas fa-blog"></i> المقالات</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> التصنيفات</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="admin/"><i class="fas fa-cogs"></i> لوحة التحكم</a></li>
                    <?php endif; ?>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> اتصل بنا</a></li>

                    <li class="user-menu">
                        <?php if (isLoggedIn() && isset($_SESSION['user_id'])): ?>
                            <span>مرحباً، <?php echo $_SESSION['username']; ?></span>
                            <a href="logout.php" class="btn btn-outline" onclick="return confirm('هل تريد تسجيل الخروج؟')">
                                <i class="fas fa-sign-out-alt"></i> تسجيل خروج
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> تسجيل دخول</a>
                            <a href="register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> إنشاء حساب</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>

    <!-- Main Content -->
    <div class="container">
        <!-- Search Box -->
        <!-- Search Box -->
        <div class="search-box">
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="ابحث في المقالات..." value="<?php echo isset($_GET['q']) ? sanitize($_GET['q']) : ''; ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> بحث</button>
            </form>
        </div>

        <!-- إضافة رسالة تسجيل الخروج هنا -->
        <?php if (isset($logout_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $logout_message; ?>
            </div>
        <?php endif; ?>

        <div class="main-content">
            <div class="posts-container">
                <?php if (empty($posts)): ?>
                    <div class="text-center">
                        <h3>لا توجد مقالات منشورة حالياً</h3>
                        <p>كن أول من يضيف مقالاً إلى المدونة</p>
                        <?php if (isLoggedIn() && isAdmin()): ?>
                            <a href="admin/posts.php" class="btn btn-primary mt-20">
                                <i class="fas fa-plus"></i> إضافة مقال جديد
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card">
                            <?php if (!empty($post['image_url'])): ?>
                                <img src="<?php echo $post['image_url']; ?>" alt="<?php echo $post['title']; ?>" class="post-image">
                            <?php endif; ?>
                            <div class="post-content">
                                <div class="post-meta">
                                    <span class="post-date"><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></span>
                                    <span class="post-category" style="background-color: <?php echo $post['color'] ?? '#2563eb'; ?>">
                                        <?php echo $post['category_name'] ?? 'بدون تصنيف'; ?>
                                    </span>
                                </div>
                                <h2 class="post-title">
                                    <a href="post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a>
                                </h2>
                                <p class="post-excerpt">
                                    <?php 
                                    if (!empty($post['excerpt'])) {
                                        echo $post['excerpt'];
                                    } else {
                                        $content = strip_tags($post['content']);
                                        echo strlen($content) > 150 ? substr($content, 0, 150) . '...' : $content;
                                    }
                                    ?>
                                </p>

                                <!-- وسوم المقال -->
                                <?php
                                try {
                                    $tags_stmt = $pdo->prepare("
                                        SELECT t.name FROM tags t 
                                        JOIN post_tags pt ON t.id = pt.tag_id 
                                        WHERE pt.post_id = ?
                                    ");
                                    $tags_stmt->execute([$post['id']]);
                                    $tags = $tags_stmt->fetchAll(PDO::FETCH_COLUMN);
                                } catch (PDOException $e) {
                                    $tags = [];
                                }
                                ?>
                                <?php if (!empty($tags)): ?>
                                    <div class="post-tags">
                                        <?php foreach ($tags as $tag): ?>
                                            <span class="tag"><?php echo $tag; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                    <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more">
                                        <i class="fas fa-book-reader"></i> قراءة المزيد
                                    </a>
                                    <div style="color: var(--light-text); font-size: 0.8rem;">
                                        <i class="fas fa-eye"></i> <?php echo $post['views']; ?> مشاهدة
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <aside class="sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><i class="fas fa-tags"></i> التصنيفات</h3>
                    <ul class="categories-list">
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="categories.php?id=<?php echo $category['id']; ?>">
                                    <?php echo $category['name']; ?>
                                    <span class="category-count">
                                        <?php
                                        try {
                                            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ? AND status = 'published'");
                                            $count_stmt->execute([$category['id']]);
                                            echo $count_stmt->fetchColumn();
                                        } catch (PDOException $e) {
                                            echo '0';
                                        }
                                        ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title"><i class="fas fa-fire"></i> المقالات الشائعة</h3>
                    <ul class="popular-posts-list">
                        <?php foreach ($popular_posts as $post): ?>
                            <li>
                                <a href="post.php?id=<?php echo $post['id']; ?>">
                                    <?php echo $post['title']; ?>
                                    <span><i class="fas fa-eye"></i> <?php echo $post['views']; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="sidebar-section">
                    <h3 class="sidebar-title"><i class="fas fa-info-circle"></i> عن المدونة</h3>
                    <p style="color: var(--light-text); line-height: 1.6;">
                        <?php echo SITE_NAME; ?> - مدونة تقنية تقدم أحدث المقالات والشروحات في مجال التكنولوجيا والبرمجة.
                    </p>
                </div>
            </aside>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>عن المدونة</h3>
                    <p><?php echo SITE_NAME; ?> - مدونة تقنية تقدم محتوى عالي الجودة في مجال التكنولوجيا والبرمجة.</p>
                </div>
                <div class="footer-section">
                    <h3>روابط سريعة</h3>
                    <a href="index.php"><i class="fas fa-home"></i> الرئيسية</a>
                    <a href="blog.php"><i class="fas fa-blog"></i> المقالات</a>
                    <a href="categories.php"><i class="fas fa-tags"></i> التصنيفات</a>
                    <a href="contact.php"><i class="fas fa-envelope"></i> اتصل بنا</a>
                </div>
                <div class="footer-section">
                    <h3>اتصل بنا</h3>
                    <p><i class="fas fa-envelope"></i> البريد الإلكتروني: info@techblog.com</p>
                    <p><i class="fas fa-phone"></i> الهاتف: +966 123 456 789</p>
                </div>
            </div>
            <div class="copyright">
                &copy; 2023 <?php echo SITE_NAME; ?>. جميع الحقوق محفوظة.
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('main-nav').classList.toggle('active');
        });

        // إغلاق القائمة عند النقر على رابط
        document.querySelectorAll('#main-nav a').forEach(link => {
            link.addEventListener('click', function() {
                document.getElementById('main-nav').classList.remove('active');
            });
        });
    </script>
</body>

</html>