<?php
// tech_blog/config.php
define('SUPABASE_URL', 'https://kmhnndhrhjsrtflvwfgx.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImttaG5uZGhyaGpzcnRmbHZ3Zmd4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjEzMDI0NTYsImV4cCI6MjA3Njg3ODQ1Nn0.qWhDOFDrv4sbO1L4fggskMoaeJ1E_cIlpZ_0LlxRpjs');
define('SITE_URL', 'https://www.di9ital.site/tech_blog');
define('SITE_NAME', 'مدونة التقنية - di9ital');

// دالة للاتصال بـ Supabase
function supabaseQuery($table, $method = 'GET', $data = null, $filters = '') {
    $url = SUPABASE_URL . "/rest/v1/" . $table;
    
    if ($filters) {
        $url .= '?' . $filters;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY,
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($response, true) ?: [];
    } else {
        error_log("Supabase API Error: $http_code - $response");
        return false;
    }
}

// دالات متقدمة للبيانات
function getPostsWithDetails($limit = 10, $status = 'published') {
    // جلب المقالات مع تفاصيل المستخدم والتصنيف
    $posts = supabaseQuery('posts', 'GET', null, "select=*&status=eq.$status&order=created_at.desc&limit=$limit");
    
    if (!$posts) return [];
    
    // إضافة تفاصيل المستخدمين والتصنيفات
    foreach ($posts as &$post) {
        if ($post['author_id']) {
            $user = supabaseQuery('users', 'GET', null, "select=username&id=eq.{$post['author_id']}");
            $post['author_name'] = $user ? $user[0]['username'] : 'مجهول';
        }
        
        if ($post['category_id']) {
            $category = supabaseQuery('categories', 'GET', null, "select=name,color&id=eq.{$post['category_id']}");
            $post['category_name'] = $category ? $category[0]['name'] : 'بدون تصنيف';
            $post['category_color'] = $category ? $category[0]['color'] : '#2563eb';
        }
        
        // جلب وسوم المقال
        $postTags = supabaseQuery('post_tags', 'GET', null, "select=tags(name)&post_id=eq.{$post['id']}");
        $post['tags'] = [];
        if ($postTags) {
            foreach ($postTags as $postTag) {
                if (isset($postTag['tags']['name'])) {
                    $post['tags'][] = $postTag['tags']['name'];
                }
            }
        }
    }
    
    return $posts;
}

function getPostWithDetails($id) {
    $posts = supabaseQuery('posts', 'GET', null, "select=*&id=eq.$id");
    
    if (!$posts || empty($posts)) return null;
    
    $post = $posts[0];
    
    // إضافة تفاصيل المؤلف
    if ($post['author_id']) {
        $user = supabaseQuery('users', 'GET', null, "select=username&id=eq.{$post['author_id']}");
        $post['author_name'] = $user ? $user[0]['username'] : 'مجهول';
    }
    
    // إضافة تفاصيل التصنيف
    if ($post['category_id']) {
        $category = supabaseQuery('categories', 'GET', null, "select=name,color&id=eq.{$post['category_id']}");
        $post['category_name'] = $category ? $category[0]['name'] : 'بدون تصنيف';
        $post['category_color'] = $category ? $category[0]['color'] : '#2563eb';
    }
    
    // جلب الوسوم
    $postTags = supabaseQuery('post_tags', 'GET', null, "select=tags(name)&post_id=eq.{$post['id']}");
    $post['tags'] = [];
    if ($postTags) {
        foreach ($postTags as $postTag) {
            if (isset($postTag['tags']['name'])) {
                $post['tags'][] = $postTag['tags']['name'];
            }
        }
    }
    
    return $post;
}

// دالة تسجيل الدخول
function getUserByCredentials($username, $password) {
    $users = supabaseQuery('users', 'GET', null, "username=eq.$username");
    
    if ($users && !empty($users) && password_verify($password, $users[0]['password'])) {
        return $users[0];
    }
    return false;
}

// دالة للتحقق من وجود بيانات أولية
function initializeSampleData() {
    // التحقق من وجود مقالات
    $posts = supabaseQuery('posts', 'GET', null, "select=id&limit=1");
    
    if (!$posts || empty($posts)) {
        echo "⚠️ لا توجد مقالات، جرب إضافة بيانات نموذجية...<br>";
        addSamplePosts();
    }
}

// دالة لإضافة مقالات نموذجية
function addSamplePosts() {
    // أولاً نحتاج معرف author_id من المستخدم الموجود
    $users = supabaseQuery('users', 'GET', null, "select=id&username=eq.admin");
    $categories = supabaseQuery('categories', 'GET', null, "select=id&limit=1");
    
    if (!$users || !$categories) return;
    
    $author_id = $users[0]['id'];
    $category_id = $categories[0]['id'];
    
    $samplePosts = [
        [
            'title' => 'مرحباً بكم في المدونة',
            'content' => 'هذه هي المقالة الأولى في مدونتنا. نحن سعداء بوجودكم معنا.',
            'excerpt' => 'مقالة ترحيبية بالزوار',
            'author_id' => $author_id,
            'category_id' => $category_id,
            'status' => 'published'
        ],
        [
            'title' => 'مقدمة في تطوير الويب',
            'content' => 'تطوير الويب هو مجال مثير يحتوي على العديد من التقنيات والأدوات.',
            'excerpt' => 'مقدمة شاملة عن تطوير الويب',
            'author_id' => $author_id,
            'category_id' => $category_id,
            'status' => 'published'
        ]
    ];
    
    foreach ($samplePosts as $post) {
        $result = supabaseQuery('posts', 'POST', $post);
        if ($result) {
            echo "✅ تم إضافة: " . $post['title'] . "<br>";
        }
    }
}

// باقي الدوال...
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تهيئة البيانات عند التشغيل (للاختبار فقط)
// initializeSampleData();
?>