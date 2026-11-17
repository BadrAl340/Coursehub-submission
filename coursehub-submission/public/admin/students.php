<?php
require_once __DIR__ . '/_guard.php';
$pageTitle = 'إدارة الطلاب';
$pdo = db();
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$form = ['full_name' => '', 'email' => ''];
if ($editId) {
    $stmt = $pdo->prepare("SELECT user_id, full_name, email FROM users WHERE user_id = :id AND role = 'student'");
    $stmt->execute(['id' => $editId]);
    $form = $stmt->fetch() ?: $form;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?: 0;
    $action = (string) ($_POST['action'] ?? 'save');
    if ($action === 'delete' && $id) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id AND role = 'student'");
        $stmt->execute(['id' => $id]);
        flash('success', 'تم حذف حساب الطالب وتسجيلاته المرتبطة.');
        redirect('/admin/students.php');
    }
    $form['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $form['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($form['full_name'] === '' || text_length($form['full_name']) > 120) $errors[] = 'أدخل اسمًا صحيحًا (حتى 120 حرفًا).';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'أدخل بريدًا إلكترونيًا صحيحًا.';
    if (!$id && strlen($password) < 8) $errors[] = 'كلمة مرور الطالب الجديد يجب أن تكون 8 أحرف على الأقل.';
    if ($id && $password !== '' && strlen($password) < 8) $errors[] = 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.';
    if (!$errors) {
        try {
            if ($id) {
                if ($password !== '') {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = :name, email = :email, password_hash = :hash WHERE user_id = :id AND role = 'student'");
                    $stmt->execute(['name' => $form['full_name'], 'email' => $form['email'], 'hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = :name, email = :email WHERE user_id = :id AND role = 'student'");
                    $stmt->execute(['name' => $form['full_name'], 'email' => $form['email'], 'id' => $id]);
                }
                flash('success', 'تم تحديث حساب الطالب.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (:name, :email, :hash, 'student')");
                $stmt->execute(['name' => $form['full_name'], 'email' => $form['email'], 'hash' => password_hash($password, PASSWORD_DEFAULT)]);
                flash('success', 'تمت إضافة حساب الطالب.');
            }
            redirect('/admin/students.php');
        } catch (PDOException $exception) {
            $errors[] = 'تعذر حفظ الحساب. ربما البريد الإلكتروني مستخدم مسبقًا.';
        }
    }
}
$students = $pdo->query("SELECT user_id, full_name, email, created_at FROM users WHERE role = 'student' ORDER BY full_name")->fetchAll();
page_header($pageTitle);
?>
<section class="page-heading"><p class="eyebrow">إدارة الحسابات</p><h1>الطلاب</h1></section><nav class="admin-links"><a href="/admin/index.php">لوحة المسؤول</a><a href="/admin/enrollments.php">التسجيلات</a></nav>
<div class="form-card" style="margin:0 0 18px;width:100%"><h2><?= $editId ? 'تعديل بيانات طالب' : 'إضافة طالب' ?></h2><?php foreach ($errors as $error): ?><div class="notice notice-error"><?= h($error) ?></div><?php endforeach; ?><form method="post" class="inline-edit"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="user_id" value="<?= (int) $editId ?>"><label>الاسم<input name="full_name" maxlength="120" required value="<?= h($form['full_name']) ?>"></label><label>البريد الإلكتروني<input type="email" name="email" maxlength="190" required value="<?= h($form['email']) ?>"></label><label>كلمة المرور<?= $editId ? ' (اتركها فارغة للإبقاء عليها)' : '' ?><input type="password" name="password" <?= $editId ? '' : 'required' ?> minlength="8" autocomplete="new-password"></label><button class="button" name="action" value="save" type="submit">حفظ</button></form></div>
<div class="table-wrap"><table><thead><tr><th>الاسم</th><th>البريد</th><th>تاريخ الإنشاء</th><th>تعديل</th><th>حذف</th></tr></thead><tbody><?php foreach ($students as $student): ?><tr><td><?= h($student['full_name']) ?></td><td><?= h($student['email']) ?></td><td><?= h(date('Y-m-d', strtotime($student['created_at']))) ?></td><td><a href="/admin/students.php?edit=<?= (int) $student['user_id'] ?>">تعديل</a></td><td><form method="post" onsubmit="return confirm('سيؤدي الحذف إلى حذف تسجيلات الطالب أيضًا. هل تريد المتابعة؟')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="user_id" value="<?= (int) $student['user_id'] ?>"><button class="button button-danger button-small" name="action" value="delete" type="submit">حذف</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php page_footer(); ?>
