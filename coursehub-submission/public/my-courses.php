<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pageTitle = 'مقرراتي';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $enrollmentId = filter_input(INPUT_POST, 'enrollment_id', FILTER_VALIDATE_INT);
    if ($enrollmentId) {
        $delete = $pdo->prepare('DELETE FROM enrollments WHERE enrollment_id = :id AND user_id = :user_id');
        $delete->execute(['id' => $enrollmentId, 'user_id' => current_user()['user_id']]);
        flash('success', 'تم إلغاء التسجيل.');
    }
    redirect('/my-courses.php');
}
$sql = "SELECT e.enrollment_id, e.enrollment_date, e.status, c.course_code, c.course_name, c.credit_hours, d.department_name FROM enrollments e INNER JOIN courses c ON c.course_id = e.course_id INNER JOIN departments d ON d.department_id = c.department_id WHERE e.user_id = :user_id AND e.status = 'active' ORDER BY e.enrollment_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => current_user()['user_id']]);
$courses = $stmt->fetchAll();
page_header($pageTitle);
?>
<section class="page-heading"><p class="eyebrow">مساحة الطالب</p><h1>مقرراتي</h1><p>هنا تظهر المقررات التي سجلت فيها.</p></section>
<?php if ($courses): ?><div class="table-wrap"><table><thead><tr><th>الرمز</th><th>المقرر</th><th>القسم</th><th>الساعات</th><th>تاريخ التسجيل</th><th>إجراء</th></tr></thead><tbody>
<?php foreach ($courses as $course): ?><tr><td><?= h($course['course_code']) ?></td><td><?= h($course['course_name']) ?></td><td><?= h($course['department_name']) ?></td><td><?= (int) $course['credit_hours'] ?></td><td><?= h(date('Y-m-d', strtotime($course['enrollment_date']))) ?></td><td><form method="post" onsubmit="return confirm('هل تريد إلغاء هذا التسجيل؟')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="enrollment_id" value="<?= (int) $course['enrollment_id'] ?>"><button class="button button-danger button-small" type="submit">إلغاء</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><div class="empty-state"><h2>لم تسجل في أي مقرر بعد</h2><p>استعرض القائمة واختر المقرر المناسب.</p><a class="button" href="/courses.php">تصفح المقررات</a></div><?php endif; ?>
<?php page_footer(); ?>
