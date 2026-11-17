<?php
require_once __DIR__ . '/_guard.php';
$pageTitle = 'تسجيلات الطلاب';
$pdo = db();
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$form = ['user_id' => '', 'course_id' => '', 'status' => 'active'];
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM enrollments WHERE enrollment_id = :id');
    $stmt->execute(['id' => $editId]);
    $form = $stmt->fetch() ?: $form;
}
$students = $pdo->query("SELECT user_id, full_name, email FROM users WHERE role = 'student' ORDER BY full_name")->fetchAll();
$courses = $pdo->query('SELECT course_id, course_code, course_name FROM courses ORDER BY course_code')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'save');
    $enrollmentId = filter_input(INPUT_POST, 'enrollment_id', FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'delete' && $enrollmentId) {
        $delete = $pdo->prepare('DELETE FROM enrollments WHERE enrollment_id = :id');
        $delete->execute(['id' => $enrollmentId]);
        flash('success', 'تم حذف التسجيل.');
        redirect('/admin/enrollments.php');
    }
    $userId = (int) ($_POST['user_id'] ?? 0);
    $courseId = (int) ($_POST['course_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? 'active');
    $form = ['user_id' => $userId, 'course_id' => $courseId, 'status' => $status];
    $studentIds = array_map(static fn($row) => (int) $row['user_id'], $students);
    $courseIds = array_map(static fn($row) => (int) $row['course_id'], $courses);
    if (!in_array($userId, $studentIds, true)) $error = 'اختر طالبًا صحيحًا.';
    elseif (!in_array($courseId, $courseIds, true)) $error = 'اختر مقررًا صحيحًا.';
    elseif (!in_array($status, ['active', 'cancelled'], true)) $error = 'حالة التسجيل غير صحيحة.';
    else {
        try {
            if ($enrollmentId) {
                $stmt = $pdo->prepare('UPDATE enrollments SET user_id = :user_id, course_id = :course_id, status = :status WHERE enrollment_id = :id');
                $stmt->execute(['user_id' => $userId, 'course_id' => $courseId, 'status' => $status, 'id' => $enrollmentId]);
                flash('success', 'تم تحديث التسجيل.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO enrollments (user_id, course_id, status) VALUES (:user_id, :course_id, :status)');
                $stmt->execute(['user_id' => $userId, 'course_id' => $courseId, 'status' => $status]);
                flash('success', 'تمت إضافة التسجيل.');
            }
            redirect('/admin/enrollments.php');
        } catch (PDOException $exception) {
            $error = 'تعذر حفظ التسجيل؛ ربما الطالب مسجل في المقرر مسبقًا.';
        }
    }
}
$rows = $pdo->query('SELECT e.enrollment_id, e.enrollment_date, e.status, u.full_name, u.email, c.course_code, c.course_name FROM enrollments e INNER JOIN users u ON u.user_id = e.user_id INNER JOIN courses c ON c.course_id = e.course_id ORDER BY e.enrollment_date DESC')->fetchAll();
page_header($pageTitle);
?>
<section class="page-heading"><p class="eyebrow">إدارة التسجيل</p><h1>تسجيلات الطلاب</h1></section><nav class="admin-links"><a href="/admin/index.php">لوحة المسؤول</a><a href="/admin/reports.php">التقارير</a></nav>
<div class="form-card" style="margin:0 0 18px;width:100%"><h2><?= $editId ? 'تعديل تسجيل' : 'إضافة تسجيل' ?></h2><?php if (!empty($error)): ?><div class="notice notice-error"><?= h($error) ?></div><?php endif; ?><form method="post" class="inline-edit"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="enrollment_id" value="<?= (int) $editId ?>"><label>الطالب<select name="user_id" required><option value="">اختر طالبًا</option><?php foreach ($students as $student): ?><option value="<?= (int) $student['user_id'] ?>" <?= (string) $form['user_id'] === (string) $student['user_id'] ? 'selected' : '' ?>><?= h($student['full_name']) ?> (<?= h($student['email']) ?>)</option><?php endforeach; ?></select></label><label>المقرر<select name="course_id" required><option value="">اختر مقررًا</option><?php foreach ($courses as $course): ?><option value="<?= (int) $course['course_id'] ?>" <?= (string) $form['course_id'] === (string) $course['course_id'] ? 'selected' : '' ?>><?= h($course['course_code']) ?> — <?= h($course['course_name']) ?></option><?php endforeach; ?></select></label><label>الحالة<select name="status"><option value="active" <?= $form['status'] === 'active' ? 'selected' : '' ?>>نشط</option><option value="cancelled" <?= $form['status'] === 'cancelled' ? 'selected' : '' ?>>ملغى</option></select></label><button class="button" name="action" value="save" type="submit">حفظ التسجيل</button></form></div>
<div class="table-wrap"><table><thead><tr><th>الطالب</th><th>البريد</th><th>المقرر</th><th>تاريخ التسجيل</th><th>الحالة</th><th>تعديل/حذف</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= h($row['full_name']) ?></td><td><?= h($row['email']) ?></td><td><?= h($row['course_code']) ?> — <?= h($row['course_name']) ?></td><td><?= h(date('Y-m-d', strtotime($row['enrollment_date']))) ?></td><td><?= $row['status'] === 'active' ? 'نشط' : 'ملغى' ?></td><td><a href="/admin/enrollments.php?edit=<?= (int) $row['enrollment_id'] ?>">تعديل</a><form method="post" class="inline-form" onsubmit="return confirm('حذف التسجيل؟')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="enrollment_id" value="<?= (int) $row['enrollment_id'] ?>"><button class="button button-danger button-small" name="action" value="delete" type="submit">حذف</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php page_footer(); ?>
