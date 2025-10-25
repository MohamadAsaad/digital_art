htdocs/
└── tech-blog/
    ├── index.php
    ├── config.php
    ├── login.php
    ├── register.php
    ├── post.php
    ├── blog.php
    ├── categories.php
    ├── search.php
    ├── contact.php
    ├── upload.php
    ├── logout.php
    ├── uploads/ (مجلد)
    └── admin/
        ├── index.php
        ├── posts.php
        ├── categories.php
        ├── tags.php
        ├── comments.php
        ├── users.php
        └── upload.php






        -- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS tech_blog 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE tech_blog;

-- جدول المستخدمين
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول التصنيفات
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#2563eb',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول المقالات
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    image_url VARCHAR(500),
    author_id INT NOT NULL,
    category_id INT,
    status ENUM('published', 'draft') DEFAULT 'draft',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- جدول الوسوم
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول العلاقة بين المقالات والوسوم
CREATE TABLE post_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_tag (post_id, tag_id)
);

-- جدول التعليقات
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(100),
    content TEXT NOT NULL,
    status ENUM('approved', 'pending', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- إدراج بيانات أولية

-- إضافة مستخدم مدير
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@techblog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- كلمة المرور: password

-- إضافة تصنيفات أساسية
INSERT INTO categories (name, color, description) VALUES 
('برمجة', '#2563eb', 'مقالات عن لغات البرمجة وتطوير البرمجيات'),
('تطوير الويب', '#dc2626', 'مقالات عن تطوير مواقع وتطبيقات الويب'),
('قواعد البيانات', '#16a34a', 'مقالات عن قواعد البيانات وإدارتها'),
('أمن المعلومات', '#ea580c', 'مقالات عن الأمن السيبراني وحماية البيانات'),
('شبكات', '#9333ea', 'مقالات عن الشبكات واتصالات البيانات');

-- إضافة وسوم أساسية
INSERT INTO tags (name) VALUES 
('PHP'), ('MySQL'), ('JavaScript'), ('HTML'), ('CSS'),
('Laravel'), ('React'), ('Vue'), ('Node.js'), ('Python'),
('أمن'), ('أداء'), ('نصائح'), ('شروحات'), ('أخبار');

-- إضافة مقالات نموذجية
INSERT INTO posts (title, content, excerpt, author_id, category_id, status, views) VALUES 
(
    'مقدمة في لغة PHP',
    'PHP هي لغة برمجة نصية صُممت أساساً من أجل تطوير الويب. يمكن تضمينها في HTML وهي لغة مفتوحة المصدر وشائعة الاستخدام.

مميزات PHP:
- سهلة التعلم للمبتدئين
- مفتوحة المصدر ومجانية
- تدعم العديد من قواعد البيانات
- مجتمع كبير وداعم
- متوافقة مع مختلف أنظمة التشغيل

يمكن استخدام PHP لإنشاء:
- مواقع ديناميكية
- أنظمة إدارة المحتوى
- تطبيقات الويب
- واجهات برمجة التطبيقات (APIs)

لبدء تعلم PHP، تحتاج إلى:
1. خادم ويب محلي (مثل XAMPP أو WAMP)
2. محرر نصوص (مثل VS Code)
3. معرفة أساسية بـ HTML',
    
    'مقدمة شاملة عن لغة PHP واستخداماتها في تطوير الويب',
    1, 1, 'published', 150
),

(
    'أساسيات قواعد البيانات مع MySQL',
    'MySQL هي نظام إدارة قواعد البيانات العلائقية مفتوحة المصدر. تُستخدم على نطاق واسع في تطبيقات الويب.

مفهوم قواعد البيانات:
- تخزين البيانات بشكل منظم
- إمكانية استرجاع البيانات بسرعة
- الحفاظ على سلامة البيانات
- إدارة العلاقات بين البيانات

أوامر SQL الأساسية:
- CREATE: إنشاء قاعدة بيانات أو جدول
- SELECT: استعلام البيانات
- INSERT: إضافة بيانات جديدة
- UPDATE: تعديل البيانات الموجودة
- DELETE: حذف البيانات

مثال على إنشاء جدول:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100)
);
```',
    
    'تعلم أساسيات قواعد البيانات واستخدام MySQL في مشاريعك',
    1, 3, 'published', 120
),

(
    'مقدمة في إطار عمل Laravel',
    'Laravel هو إطار عمل PHP أنيق وسريع التطوير. يتبع نمط MVC ويوفر العديد من المميزات الجاهزة.

مميزات Laravel:
- نظام توجيه مرن
- نظام قوالب Blade
- هجرة قواعد البيانات
- نظام المصادقة المدمج
- مكتبة Eloquent ORM

هيكل مشروع Laravel:
- app/: منطق التطبيق
- database/: ملفات قواعد البيانات
- resources/: views و assets
- routes/: تعريف المسارات
- config/: إعدادات التطبيق

مثال على نموذج:
```php
class User extends Model {
    protected $fillable = [''name'', ''email''];
}
```',
    
    'تعرف على إطار عمل Laravel ومميزاته في تطوير الويب',
    1, 1, 'published', 95
);

-- إضافة علاقات بين المقالات والوسوم
INSERT INTO post_tags (post_id, tag_id) VALUES 
(1, 1), (1, 6), (1, 13),
(2, 2), (2, 13), (2, 14),
(3, 1), (3, 6), (3, 13);

-- إضافة تعليقات نموذجية
INSERT INTO comments (post_id, user_name, user_email, content, status) VALUES 
(1, 'أحمد محمد', 'ahmed@example.com', 'مقال رائع ومفيد للمبتدئين في PHP', 'approved'),
(1, 'سارة عبدالله', 'sara@example.com', 'شكراً على الشرح الوافي، أتمنى المزيد من الأمثلة', 'approved'),
(2, 'خالد العتيبي', 'khaled@example.com', 'MySQL من أهم قواعد البيانات التي يجب على المطور تعلمها', 'approved'),
(3, 'فاطمة القحطاني', 'fatima@example.com', 'Laravel سهل التعلم وقوي في نفس الوقت', 'pending');

-- إنشاء الفهرس لتحسين الأداء
CREATE INDEX idx_posts_status ON posts(status);
CREATE INDEX idx_posts_author ON posts(author_id);
CREATE INDEX idx_posts_category ON posts(category_id);
CREATE INDEX idx_posts_created ON posts(created_at);
CREATE INDEX idx_comments_post ON comments(post_id);
CREATE INDEX idx_comments_status ON comments(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);

-- عرض رسالة نجاح
SELECT 'تم إنشاء قاعدة البيانات والجداول بنجاح!' AS message;











📋 ملاحظات هامة:
1. كلمة مرور المدير الافتراضية:
اسم المستخدم: admin

البريد الإلكتروني: admin@techblog.com

كلمة المرور: password

2. لتنفيذ ملف SQL:
bash
# الطريقة 1: استخدام سطر الأوامر
mysql -u root -p < database.sql

# الطريقة 2: استخدام phpMyAdmin
# - ادخل إلى phpMyAdmin
# - أنشئ قاعدة بيانات جديدة باسم tech_blog
# - اختر Import وقم برفع ملف SQL
3. هيكل الجداول:
users: إدارة المستخدمين والصلاحيات

categories: تصنيفات المقالات

posts: المقالات والمحتوى

tags: الوسوم

post_tags: العلاقة بين المقالات والوسوم

comments: نظام التعليقات

4. الصلاحيات:
admin: صلاحيات كاملة

user: صلاحيات محدودة (إضافة تعليقات فقط)

5. حالات المقالات:
published: منشور ومتاح للعموم

draft: مسودة غير منشورة

6. حالات التعليقات:
approved: مقبول ومعروض

pending: قيد المراجعة

rejected: مرفوض

هذا الملف سيُنشئ قاعدة بيانات كاملة وجاهزة للاستخدام مع نظام المدونة الخاص بك!