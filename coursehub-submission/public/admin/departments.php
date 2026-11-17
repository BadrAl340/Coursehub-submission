<?php
require_once __DIR__ . '/_guard.php';
$pageTitle = 'إدارة الأقسام';
$pdo = db();
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT) ?: 0;
$form = ['department_name' => '', 'description' => ''];
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM departments WHERE department_id = :id');
    $stmt->execute(['id' => $editId]);
    $form = $stmt->fetch() ?: $form;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'save');
    $id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'delete' && $id) {
        try {
            $stmt = $pdo->prepare('DELETE FROM departments WHERE department_id = :id');
            $stmt->execute(['id' => $id]);
            flash('success', 'تم حذف القسم.');
        } catch (PDOException $exception) {
            flash('error', 'تعذر حذف القسم؛ قد توجد مقررات مرتبطة به.');
        }
        redirect('/admin/departments.php');
    }
    $form['department_name'] = trim((string) ($_POST['department_name'] ?? ''));
    $form['description'] = trim((string) ($_POST['description'] ?? ''));
    if ($form['department_name'] === '' || text_length($form['department_name']) > 120) $errors[] = 'أدخل اسم القسم (حتى 120 حرفًا).';
    if (text_length($form['description']) > 500) $errors[] = 'الوصف طويل جدًا.';
    if (!$errors) {
        try {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE departments SET department_name = :name, description = :description WHERE department_id = :id');
                $stmt->execute(['name' => $form['department_name'], 'description' => $form['description'], 'id' => $id]);
                flash('success', 'تم تحديث القسم.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO departments (department_name, description) VALUES (:name, :description)');
                $stmt->execute(['name' => $form['department_name'], 'description' => $form['description']]);
                flash('success', 'تمت إضافة القسم.');
            }
            redirect('/admin/departments.php');
        } catch (PDOException $exception) {
            $errors[] = 'تعذر الحفظ؛ قد يكون اسم القسم مستخدمًا مسبقًا.';
        }
    }
}
$departments = $pdo->query('SELECT d.department_id, d.department_name, d.description, COUNT(c.course_id) AS course_count FROM departments d LEFT JOIN courses c ON c.department_id = d.department_id GROUP BY d.department_id ORDER BY d.department_name')->fetchAll();
page_header($pageTitle);
?>
<section class="page-heading"><p class="eyebrow">إدارة البيانات</p><h1>الأقسام</h1></section>
<nav class="admin-links"><a href="/admin/index.php">لوحة المسؤول</a><a href="/admin/courses.php">المقررات</a></nav>
<div class="form-card" style="margin:0 0 18px;width:100%"><h2><?= $editId ? 'تعديل قسم' : 'إضافة قسم' ?></h2><?php foreach ($errors as $error): ?><div class="notice notice-error"><?= h($error) ?></div><?php endforeach; ?><form method="post" class="inline-edit"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="department_id" value="<?= (int) ($editId ?: 0) ?>"><label>اسم القسم<input name="department_name" maxlength="120" required value="<?= h($form['department_name']) ?>"></label><label>وصف مختصر<input name="description" maxlength="500" value="<?= h($form['description']) ?>"></label><button class="button" name="action" value="save" type="submit">حفظ القسم</button></form></div>
<div class="table-wrap"><table><thead><tr><th>القسم</th><th>الوصف</th><th>عدد المقررات</th><th>تعديل</th><th>حذف</th></tr></thead><tbody><?php foreach ($departments as $department): ?><tr><td><?= h($department['department_name']) ?></td><td><?= h($department['description']) ?></td><td><?= (int) $department['course_count'] ?></td><td><a href="/admin/departments.php?edit=<?= (int) $department['department_id'] ?>">تعديل</a></td><td><form method="post" onsubmit="return confirm('حذف القسم؟')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="department_id" value="<?= (int) $department['department_id'] ?>"><button class="button button-danger button-small" name="action" value="delete" type="submit">حذف</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php page_footer(); ?>
