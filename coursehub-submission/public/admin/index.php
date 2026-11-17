<?php
require_once __DIR__ . '/_guard.php';
$pageTitle = 'لوحة المسؤول';
$pdo = db();
$stats = [
    'courses' => (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn(),
    'departments' => (int) $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn(),
    'students' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
    'enrollments' => (int) $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'active'")->fetchColumn(),
];
page_header($pageTitle);
?>
<section class="page-heading"><p class="eyebrow">مساحة إدارة بسيطة</p><h1>لوحة المسؤول</h1><p>استخدم الروابط لإدارة بيانات النظام ومراجعة الإحصاءات.</p></section>
<nav class="admin-links"><a class="button" href="/admin/courses.php">إدارة المقررات</a><a class="button button-light" href="/admin/departments.php">إدارة الأقسام</a><a class="button button-light" href="/admin/students.php">إدارة الطلاب</a><a class="button button-light" href="/admin/enrollments.php">التسجيلات</a><a class="button button-light" href="/admin/reports.php">التقارير</a></nav>
<div class="admin-grid"><div class="admin-card"><strong><?= $stats['courses'] ?></strong><span>مقررًا</span></div><div class="admin-card"><strong><?= $stats['departments'] ?></strong><span>قسمًا</span></div><div class="admin-card"><strong><?= $stats['students'] ?></strong><span>طالبًا</span></div><div class="admin-card"><strong><?= $stats['enrollments'] ?></strong><span>تسجيلًا نشطًا</span></div></div>
<?php page_footer(); ?>
