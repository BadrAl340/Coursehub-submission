<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$courseId || $courseId < 1) {
    http_response_code(404);
    $course = null;
} else {
    $pdo = db();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_login();
        verify_csrf();
        if (current_user()['role'] !== 'student') {
            flash('error', 'التسجيل في المقررات متاح لحسابات الطلاب.');
            redirect('/course.php?id=' . $courseId);
        }
        try {
            $insert = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, status) VALUES (:user_id, :course_id, 'active') ON DUPLICATE KEY UPDATE status = 'active'");
            $insert->execute(['user_id' => current_user()['user_id'], 'course_id' => $courseId]);
            flash('success', 'تم تسجيلك في المقرر.');
        } catch (PDOException $exception) {
            flash('error', 'تعذر إكمال التسجيل. حاول مرة أخرى.');
        }
        redirect('/course.php?id=' . $courseId);
    }
    $stmt = $pdo->prepare('SELECT c.*, d.department_name FROM courses c INNER JOIN departments d ON d.department_id = c.department_id WHERE c.course_id = :id');
    $stmt->execute(['id' => $courseId]);
    $course = $stmt->fetch();
}
$pageTitle = $course ? $course['course_name'] : 'المقرر غير موجود';
$alreadyEnrolled = false;
if ($course && is_logged_in() && current_user()['role'] === 'student') {
    $check = db()->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = :user_id AND course_id = :course_id AND status = 'active'");
    $check->execute(['user_id' => current_user()['user_id'], 'course_id' => $courseId]);
    $alreadyEnrolled = (bool) $check->fetchColumn();
}
page_header($pageTitle);
?>
<?php if (!$course): ?>
<section class="empty-state">
    <h1>المقرر غير موجود</h1>
    <p>قد يكون الرابط غير صحيح أو تم حذف المقرر.</p>
    <a href="/courses.php">العودة للمقررات</a>
</section>
<?php else: ?>
<section class="page-heading">
    <p class="eyebrow">تفاصيل المقرر</p>
    <span class="course-code large-code"><?= h($course['course_code']) ?></span>
    <h1><?= h($course['course_name']) ?></h1>
</section>
<div class="detail-card">
    <dl>
        <div><dt>رقم السجل</dt><dd><?= (int) $course['course_id'] ?></dd></div>
        <div><dt>رمز المقرر</dt><dd><?= h($course['course_code']) ?></dd></div>
        <div><dt>القسم</dt><dd><?= h($course['department_name']) ?></dd></div>
        <div><dt>الساعات المعتمدة</dt><dd><?= (int) $course['credit_hours'] ?></dd></div>
        <div><dt>تاريخ الإضافة</dt><dd><?= h(date('Y-m-d', strtotime($course['created_at']))) ?></dd></div>
    </dl>
    <h2>وصف المقرر</h2>
    <p><?= nl2br(h($course['description'])) ?></p>
    <?php if (!is_logged_in()): ?>
        <a class="button" href="/login.php">سجّل الدخول للتسجيل</a>
    <?php elseif (current_user()['role'] === 'student' && $alreadyEnrolled): ?>
        <a class="button button-light" href="/my-courses.php">أنت مسجل — اذهب إلى مقرراتي</a>
    <?php elseif (current_user()['role'] === 'student'): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <button class="button" type="submit">التسجيل في المقرر</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php page_footer(); ?>
