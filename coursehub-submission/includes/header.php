<?php
$user = current_user();
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | مقرراتي</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="/" aria-label="مقرراتي - الرئيسية"><img class="brand-image" src="/brand-mark.svg" alt="" width="34" height="34"><span class="brand-copy"><strong>مقرراتي | جامعة اب</strong><small>كلية الحاسبات والمعلومات · منطقة الباحة، المملكة العربية السعودية</small></span></a>
        <nav class="main-nav" aria-label="التنقل الرئيسي">
            <a href="/">الرئيسية</a>
            <a href="/coursehub-submission/public/courses.php">المقررات</a>
            <?php if ($user): ?>
                <?php if ($user['role'] === 'student'): ?><a href="/my-courses.php">مقرراتي</a><?php endif; ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="/admin/index.php">الإدارة</a>
                    <a href="/admin/students.php">الطلاب</a>
                    <a href="/admin/courses.php">المقررات</a>
                    <a href="/admin/departments.php">الأقسام</a>
                    <a href="/admin/enrollments.php">التسجيلات</a>
                    <a href="/admin/reports.php">التقارير</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
        <div class="nav-user">
            <?php if ($user): ?>
                <span class="user-name"><?= h($user['full_name']) ?></span>
                <form action="/logout.php" method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button class="button button-small button-light" type="submit">خروج</button></form>
            <?php else: ?>
                <a href="/login.php">دخول</a>
                <a class="button button-small" href="/register.php">حساب جديد</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="container">
    <?php if ($flashMessage): ?><div class="notice notice-<?= h($flashMessage['type']) ?>" role="status"><?= h($flashMessage['message']) ?></div><?php endif; ?>
