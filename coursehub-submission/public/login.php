<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$pageTitle = 'تسجيل الدخول';
$error = '';
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = (string) ($_POST['password'] ?? '');
    try {
        $stmt = db()->prepare('SELECT user_id, full_name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            flash('success', 'أهلًا ' . $user['full_name'] . '، تم تسجيل الدخول.');
            redirect($user['role'] === 'admin' ? '/admin/index.php' : '/courses.php');
        }
        $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
    } catch (Throwable $exception) {
        $error = database_error_message();
    }
}
page_header($pageTitle);
?>
<section class="form-card"><p class="eyebrow">أهلًا بعودتك</p><h1>تسجيل الدخول</h1>
<?php if ($error): ?><div class="notice notice-error"><?= h($error) ?></div><?php endif; ?>
<form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><label>البريد الإلكتروني<input type="email" name="email" required value="<?= h($email) ?>" autocomplete="username"></label><label>كلمة المرور<input type="password" name="password" required autocomplete="current-password"></label><button class="button" type="submit">دخول</button></form><p class="form-help">لا تملك حسابًا؟ <a href="/register.php">أنشئ حساب طالب</a></p></section>
<?php page_footer(); ?>
