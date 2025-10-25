<?php
session_start(); // إضافة هذا السطر
include 'config.php';

$search_query = isset($_GET['q']) ? sanitize($_GET['q']) : '';

if (!empty($search_query)) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, c.name as category_name, c.color 
            FROM posts p 
            LEFT JOIN users u ON p.author_id = u.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'published' AND (
                p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?
            )
            ORDER BY p.created_at DESC
        ");

        $search_term = "%$search_query%";
        $stmt->execute([$search_term, $search_term, $search_term]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results_count = count($posts);
    } catch (PDOException $e) {
        $posts = [];
        $results_count = 0;
    }
} else {
    $posts = [];
    $results_count = 0;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتائج البحث - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* نفس التصميم السابق */
        .search-results-header {
            background: var(--background-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <!-- نفس الهيدر السابق -->

    <div class="container">
        <div class="search-results-header">
            <h2><i class="fas fa-search"></i> نتائج البحث</h2>
            <p>
                <?php if (!empty($search_query)): ?>
                    عثرنا على <strong><?php echo $results_count; ?></strong> نتيجة لبحثك عن "<strong><?php echo $search_query; ?></strong>"
                <?php else: ?>
                    يرجى إدخال كلمة للبحث
                <?php endif; ?>
            </p>
        </div>

        <?php if (empty($search_query)): ?>
            <div class="text-center">
                <p>استخدم نموذج البحث في الأعلى للبحث في المقالات</p>
            </div>
        <?php elseif (empty($posts)): ?>
            <div class="text-center">
                <h3>لم نعثر على نتائج</h3>
                <p>جرب استخدام كلمات بحث أخرى أو تصفح جميع المقالات</p>
                <a href="blog.php" class="btn btn-primary">عرض جميع المقالات</a>
            </div>
        <?php else: ?>
            <div class="posts-container">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <?php if ($post['image_url']): ?>
                            <img src="<?php echo $post['image_url']; ?>" alt="<?php echo $post['title']; ?>" class="post-image">
                        <?php endif; ?>
                        <div class="post-content">
                            <div class="post-meta">
                                <span class="post-date"><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></span>
                                <span class="post-category" style="background-color: <?php echo $post['color']; ?>">
                                    <?php echo $post['category_name']; ?>
                                </span>
                            </div>
                            <h2 class="post-title">
                                <a href="post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a>
                            </h2>
                            <p class="post-excerpt"><?php echo $post['excerpt'] ?: substr(strip_tags($post['content']), 0, 150) . '...'; ?></p>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more">
                                <i class="fas fa-book-reader"></i> قراءة المزيد
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- نفس الفوتر السابق -->
</body>

</html>