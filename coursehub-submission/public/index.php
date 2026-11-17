<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$pageTitle = 'الرئيسية';
$error = null;
$courses = [];
$stats = ['courses' => 0, 'departments' => 0, 'enrollments' => 0];
try {
    $pdo = db();
    $stats['courses'] = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
    $stats['departments'] = (int) $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn();
    $stats['enrollments'] = (int) $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'active'")->fetchColumn();
    $courses = $pdo->query('SELECT c.course_id, c.course_code, c.course_name, c.credit_hours, d.department_name FROM courses c INNER JOIN departments d ON d.department_id = c.department_id ORDER BY c.course_id DESC LIMIT 3')->fetchAll();
} catch (Throwable $exception) {
    $error = database_error_message();
}
page_header($pageTitle);
?>
<section class="hero">
    <div>
        <p class="eyebrow">بوابة المقررات الجامعية</p>
        <h1>اعثر على مقررك، وسجّل بخطوات بسيطة.</h1>
        <p class="hero-text">تصفح مقررات الأقسام الأكاديمية، اقرأ معلوماتها، وتابع تسجيلاتك من مكان واحد.</p>
        <a class="button" href="/courses.php">استعراض المقررات</a>
    </div>
    <div class="hero-note"><span class="brand-mark large">م</span><strong>مقرراتي</strong><span>مشروع طلابي لمقرر IT1515</span></div>
</section>
<?php if ($error): ?><div class="notice notice-error"><?= h($error) ?></div><?php endif; ?>
<section class="stats-row" aria-label="إحصاءات الموقع">
    <div class="stat-card"><strong><?= $stats['courses'] ?></strong><span>مقررًا</span></div>
    <div class="stat-card"><strong><?= $stats['departments'] ?></strong><span>قسمًا</span></div>
    <div class="stat-card"><strong><?= $stats['enrollments'] ?></strong><span>تسجيلًا نشطًا</span></div>
</section>
<section class="section-heading"><div><p class="eyebrow">تعرّف على الخيارات</p><h2>مقررات مضافة حديثًا</h2></div><a href="/courses.php">عرض الكل ←</a></section>
<?php if ($courses): ?>
<div class="course-grid">
    <?php foreach ($courses as $course): ?>
    <article class="course-card">
        <span class="course-code"><?= h($course['course_code']) ?></span>
        <h3><?= h($course['course_name']) ?></h3>
        <p><?= h($course['department_name']) ?> · <?= h($course['credit_hours']) ?> ساعات</p>
        <a href="/course.php?id=<?= (int) $course['course_id'] ?>">تفاصيل المقرر</a>
    </article>
    <?php endforeach; ?>
</div>
<?php elseif (!$error): ?><p class="empty-state">لا توجد مقررات حاليًا. أضفها من لوحة المسؤول.</p><?php endif; ?>
<?php page_footer(); ?>
