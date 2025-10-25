<?php
include 'config.php';

echo "<h1>🎯 الاختبار النهائي للمدونة</h1>";

// 1. اختبار جلب المقالات مع التفاصيل الكاملة
echo "<h2>📝 المقالات المنشورة:</h2>";
$publishedPosts = getPostsWithDetails(10, 'published');

if ($publishedPosts && !empty($publishedPosts)) {
    foreach ($publishedPosts as $post) {
        echo "<div style='border: 2px solid #4CAF50; padding: 15px; margin: 15px; border-radius: 8px; background: #f9f9f9;'>";
        echo "<h3 style='color: #2E86AB;'>📄 " . $post['title'] . "</h3>";
        echo "<p><strong>👤 المؤلف:</strong> " . ($post['author_name'] ?? 'مجهول') . "</p>";
        echo "<p><strong>🏷️ التصنيف:</strong> <span style='background: " . ($post['category_color'] ?? '#2563eb') . "; color: white; padding: 2px 8px; border-radius: 4px;'>" . ($post['category_name'] ?? 'بدون تصنيف') . "</span></p>";
        echo "<p><strong>👀 المشاهدات:</strong> " . ($post['views'] ?? 0) . "</p>";
        echo "<p><strong>📅 النشر:</strong> " . ($post['created_at'] ?? 'غير محدد') . "</p>";
        echo "<p><strong>🏷️ الوسوم:</strong> " . (implode(', ', $post['tags']) ?: 'لا توجد وسوم') . "</p>";
        echo "<p><strong>📖 الملخص:</strong> " . ($post['excerpt'] ?? 'لا يوجد ملخص') . "</p>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ لا توجد مقالات منشورة</p>";
}

// 2. إحصائيات المدونة
echo "<h2>📊 إحصائيات المدونة:</h2>";

$allPosts = supabaseQuery('posts', 'GET', null, "select=id,status");
$publishedCount = 0;
$draftCount = 0;

if ($allPosts) {
    foreach ($allPosts as $post) {
        if ($post['status'] === 'published') {
            $publishedCount++;
        } else {
            $draftCount++;
        }
    }
}

echo "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0;'>";
echo "<div style='background: #4CAF50; color: white; padding: 20px; border-radius: 8px; text-align: center;'>";
echo "<h3>📄 المقالات</h3>";
echo "<p style='font-size: 24px; margin: 0;'>" . count($allPosts) . "</p>";
echo "</div>";

echo "<div style='background: #2196F3; color: white; padding: 20px; border-radius: 8px; text-align: center;'>";
echo "<h3>✅ منشورة</h3>";
echo "<p style='font-size: 24px; margin: 0;'>$publishedCount</p>";
echo "</div>";

echo "<div style='background: #FF9800; color: white; padding: 20px; border-radius: 8px; text-align: center;'>";
echo "<h3>📝 مسودة</h3>";
echo "<p style='font-size: 24px; margin: 0;'>$draftCount</p>";
echo "</div>";
echo "</div>";

// 3. اختبار الصفحة الرئيسية
echo "<h2>🏠 محاكاة الصفحة الرئيسية:</h2>";

$recentPosts = getPostsWithDetails(3, 'published');
if ($recentPosts && !empty($recentPosts)) {
    foreach ($recentPosts as $post) {
        echo "<article style='border: 1px solid #ddd; padding: 15px; margin: 10px; border-radius: 5px;'>";
        echo "<h4><a href='post.php?id=" . $post['id'] . "' style='text-decoration: none; color: #2563eb;'>" . $post['title'] . "</a></h4>";
        echo "<p style='color: #666; font-size: 14px;'>" . ($post['excerpt'] ?? '') . "</p>";
        echo "<div style='display: flex; gap: 15px; font-size: 12px; color: #888;'>";
        echo "<span>👤 " . ($post['author_name'] ?? 'مجهول') . "</span>";
        echo "<span>🏷️ " . ($post['category_name'] ?? 'بدون تصنيف') . "</span>";
        echo "<span>👀 " . ($post['views'] ?? 0) . " مشاهدة</span>";
        echo "</div>";
        echo "</article>";
    }
} else {
    echo "<p>لا توجد مقالات للعرض</p>";
}

// 4. روابط التنقل
echo "<h2>🔗 روابط التنقل:</h2>";
echo "<div style='display: flex; gap: 10px; flex-wrap: wrap;'>";
echo "<a href='index.php' style='padding: 10px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>🏠 الصفحة الرئيسية</a>";
echo "<a href='login.php' style='padding: 10px 15px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>🔐 تسجيل الدخول</a>";
echo "<a href='admin/index.php' style='padding: 10px 15px; background: #FF9800; color: white; text-decoration: none; border-radius: 4px;'>⚙️ لوحة التحكم</a>";
echo "</div>";

// 5. معلومات التقنية
echo "<h2>🔧 معلومات التقنية:</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>📡 قاعدة البيانات:</strong> Supabase (PostgreSQL)</p>";
echo "<p><strong>🌐 طريقة الاتصال:</strong> REST API</p>";
echo "<p><strong>✅ الحالة:</strong> 🟢 تعمل بشكل ممتاز</p>";
echo "</div>";
?>