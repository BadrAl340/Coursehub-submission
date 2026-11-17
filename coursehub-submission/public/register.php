<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$pageTitle = 'إنشاء حساب';
$errors = [];
$name = trim((string) ($_POST['full_name'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = (string) ($_POST['password'] ?? '');
    if ($name === '' || text_length($name) > 120) $errors[] = 'اكتب الاسم الكامل (حتى 120 حرفًا).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'اكتب بريدًا إلكترونيًا صحيحًا.';
    if (strlen($password) < 8) $errors[] = 'يجب ألا تقل كلمة المرور عن 8 أحرف.';
    if (!$errors) {
        try {
            $stmt = db()->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (:name, :email, :password_hash, 'student')");
            $stmt->execute(['name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'تم إنشاء الحساب. يمكنك الآن تسجيل الدخول.');
            redirect('/login.php');
        } catch (PDOException $exception) {
            $errors[] = 'تعذر إنشاء الحساب. قد يكون البريد مستخدمًا من قبل.';
        }
    }
}
page_header($pageTitle);
?>
<section class="form-card"><p class="eyebrow">حساب طالب جديد</p><h1>إنشاء حساب</h1>
<?php foreach ($errors as $error): ?><div class="notice notice-error"><?= h($error) ?></div><?php endforeach; ?>
<form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><label>الاسم الكامل<input name="full_name" required maxlength="120" value="<?= h($name) ?>"></label><label>البريد الإلكتروني<input type="email" name="email" required maxlength="190" value="<?= h($email) ?>"></label><label>كلمة المرور<input type="password" name="password" required minlength="8" autocomplete="new-password"></label><button class="button" type="submit">إنشاء الحساب</button></form><p class="form-help">لديك حساب؟ <a href="/login.php">تسجيل الدخول</a></p></section>
<?php page_footer(); ?>
