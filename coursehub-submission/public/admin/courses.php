<?php
require_once __DIR__ . '/_guard.php';
$pageTitle = 'إدارة المقررات';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $courseId = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    if ($courseId) {
        try {
            $stmt = $pdo->prepare('DELETE FROM courses WHERE course_id = :id');
            $stmt->execute(['id' => $courseId]);
            flash('success', 'تم حذف المقرر.');
        } catch (PDOException $exception) {
            flash('error', 'تعذر حذف المقرر؛ قد توجد تسجيلات مرتبطة به.');
        }
    }
    redirect('/admin/courses.php');
}
$courses = $pdo->query('SELECT c.course_id, c.course_code, c.course_name, c.credit_hours, d.department_name FROM courses c INNER JOIN departments d ON d.department_id = c.department_id ORDER BY c.course_code')->fetchAll();
page_header($pageTitle);
?>
<section class="page-heading"><p class="eyebrow">إدارة البيانات</p><h1>إدارة المقررات</h1></section>
<nav class="admin-links"><a class="button" href="/admin/course-form.php">إضافة مقرر</a><a href="/admin/index.php">لوحة المسؤول</a><a href="/admin/departments.php">الأقسام</a></nav>
<div class="table-wrap"><table><thead><tr><th>الرمز</th><th>المقرر</th><th>القسم</th><th>الساعات</th><th>تعديل</th><th>حذف</th></tr></thead><tbody>
<?php foreach ($courses as $course): ?><tr><td><?= h($course['course_code']) ?></td><td><?= h($course['course_name']) ?></td><td><?= h($course['department_name']) ?></td><td><?= (int) $course['credit_hours'] ?></td><td><a href="/admin/course-form.php?id=<?= (int) $course['course_id'] ?>">تعديل</a></td><td><form method="post" onsubmit="return confirm('هل تريد حذف هذا المقرر؟')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="course_id" value="<?= (int) $course['course_id'] ?>"><button type="submit" class="button button-danger button-small">حذف</button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php page_footer(); ?>
