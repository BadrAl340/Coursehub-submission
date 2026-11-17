<?php
require_once __DIR__ . '/_guard.php';
$pdo = db();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$course = ['course_code' => '', 'course_name' => '', 'description' => '', 'credit_hours' => 3, 'department_id' => ''];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE course_id = :id');
    $stmt->execute(['id' => $id]);
    $course = $stmt->fetch() ?: null;
}
if ($id && !$course) {
    flash('error', 'المقرر المطلوب غير موجود.');
    redirect('/admin/courses.php');
}
$departments = $pdo->query('SELECT department_id, department_name FROM departments ORDER BY department_name')->fetchAll();
$errors = [];
$pageTitle = $id ? 'تعديل مقرر' : 'إضافة مقرر';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $course['course_code'] = strtoupper(trim((string) ($_POST['course_code'] ?? '')));
    $course['course_name'] = trim((string) ($_POST['course_name'] ?? ''));
    $course['description'] = trim((string) ($_POST['description'] ?? ''));
    $course['credit_hours'] = (int) ($_POST['credit_hours'] ?? 0);
    $course['department_id'] = (int) ($_POST['department_id'] ?? 0);
    if ($course['course_code'] === '' || strlen($course['course_code']) > 20) $errors[] = 'أدخل رمزًا صحيحًا لا يتجاوز 20 حرفًا.';
    if ($course['course_name'] === '' || text_length($course['course_name']) > 160) $errors[] = 'أدخل اسم المقرر (حتى 160 حرفًا).';
    if ($course['description'] === '') $errors[] = 'أدخل وصفًا للمقرر.';
    if ($course['credit_hours'] < 1 || $course['credit_hours'] > 12) $errors[] = 'الساعات المعتمدة يجب أن تكون بين 1 و12.';
    if (!in_array($course['department_id'], array_map(static fn($row) => (int) $row['department_id'], $departments), true)) $errors[] = 'اختر قسمًا صحيحًا.';
    if (!$errors) {
        try {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE courses SET course_code = :code, course_name = :name, description = :description, credit_hours = :hours, department_id = :department_id WHERE course_id = :id');
                $stmt->execute(['code' => $course['course_code'], 'name' => $course['course_name'], 'description' => $course['description'], 'hours' => $course['credit_hours'], 'department_id' => $course['department_id'], 'id' => $id]);
                flash('success', 'تم تحديث المقرر.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO courses (course_code, course_name, description, credit_hours, department_id) VALUES (:code, :name, :description, :hours, :department_id)');
                $stmt->execute(['code' => $course['course_code'], 'name' => $course['course_name'], 'description' => $course['description'], 'hours' => $course['credit_hours'], 'department_id' => $course['department_id']]);
                flash('success', 'تمت إضافة المقرر.');
            }
            redirect('/admin/courses.php');
        } catch (PDOException $exception) {
            $errors[] = 'تعذر الحفظ. تأكد أن رمز المقرر غير مستخدم وأن القسم ما زال موجودًا.';
        }
    }
}
page_header($pageTitle);
?>
<section class="form-card"><p class="eyebrow">إدارة المقررات</p><h1><?= h($pageTitle) ?></h1>
<?php foreach ($errors as $error): ?><div class="notice notice-error"><?= h($error) ?></div><?php endforeach; ?>
<form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><label>رمز المقرر<input name="course_code" maxlength="20" required value="<?= h($course['course_code']) ?>"></label><label>اسم المقرر<input name="course_name" maxlength="160" required value="<?= h($course['course_name']) ?>"></label><label>الوصف<textarea name="description" rows="4" required><?= h($course['description']) ?></textarea></label><label>الساعات المعتمدة<input type="number" name="credit_hours" min="1" max="12" required value="<?= (int) $course['credit_hours'] ?>"></label><label>القسم<select name="department_id" required><option value="">اختر القسم</option><?php foreach ($departments as $department): ?><option value="<?= (int) $department['department_id'] ?>" <?= (string) $course['department_id'] === (string) $department['department_id'] ? 'selected' : '' ?>><?= h($department['department_name']) ?></option><?php endforeach; ?></select></label><button class="button" type="submit">حفظ</button><a href="/admin/courses.php">إلغاء</a></form></section>
<?php page_footer(); ?>
