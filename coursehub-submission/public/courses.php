<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$pageTitle = 'المقررات';
$pdo = db();
$search = trim((string) ($_GET['q'] ?? ''));
$departmentId = (string) ($_GET['department'] ?? $_COOKIE['preferred_department'] ?? '');
$sortOptions = ['name' => 'c.course_name ASC', 'code' => 'c.course_code ASC', 'hours' => 'c.credit_hours DESC'];
$sort = (string) ($_GET['sort'] ?? 'name');
$orderBy = $sortOptions[$sort] ?? $sortOptions['name'];
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 5;
$departments = $pdo->query('SELECT department_id, department_name FROM departments ORDER BY department_name')->fetchAll();
if ($departmentId !== '' && ctype_digit($departmentId)) {
    set_preferred_department($departmentId);
} elseif ($departmentId !== '') {
    $departmentId = '';
}
$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(c.course_name LIKE :search_name OR c.course_code LIKE :search_code OR c.description LIKE :search_description)';
    $like = '%' . $search . '%';
    $params['search_name'] = $like;
    $params['search_code'] = $like;
    $params['search_description'] = $like;
}
if ($departmentId !== '') {
    $where[] = 'c.department_id = :department_id';
    $params['department_id'] = (int) $departmentId;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$count = $pdo->prepare('SELECT COUNT(*) FROM courses c' . $whereSql);
$count->execute($params);
$total = (int) $count->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$sql = 'SELECT c.course_id, c.course_code, c.course_name, c.description, c.credit_hours, d.department_name FROM courses c INNER JOIN departments d ON d.department_id = c.department_id' . $whereSql . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset';
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$courses = $stmt->fetchAll();
function course_query_url(int $pageNumber): string
{
    $query = $_GET;
    $query['page'] = $pageNumber;
    return '?' . http_build_query($query);
}
page_header($pageTitle);
?>
<section class="page-heading"><p class="eyebrow">دليل الدراسة</p><h1>قائمة المقررات</h1><p>ابحث باسم المقرر أو رمزه، ثم صفِّ النتائج حسب القسم.</p></section>
<form class="filter-panel" method="get" action="/courses.php">
    <label>كلمة البحث<input type="search" name="q" value="<?= h($search) ?>" placeholder="مثال: قواعد البيانات"></label>
    <label>القسم<select name="department"><option value="">كل الأقسام</option><?php foreach ($departments as $department): ?><option value="<?= (int) $department['department_id'] ?>" <?= $departmentId === (string) $department['department_id'] ? 'selected' : '' ?>><?= h($department['department_name']) ?></option><?php endforeach; ?></select></label>
    <label>الترتيب<select name="sort"><option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>الاسم</option><option value="code" <?= $sort === 'code' ? 'selected' : '' ?>>رمز المقرر</option><option value="hours" <?= $sort === 'hours' ? 'selected' : '' ?>>الساعات</option></select></label>
    <button class="button" type="submit">تطبيق</button>
</form>
<p class="result-count">عدد النتائج: <?= $total ?></p>
<?php if ($courses): ?>
<div class="table-wrap"><table><thead><tr><th>الرمز</th><th>اسم المقرر</th><th>القسم</th><th>الساعات</th><th>التفاصيل</th></tr></thead><tbody>
<?php foreach ($courses as $course): ?><tr><td><span class="course-code"><?= h($course['course_code']) ?></span></td><td><?= h($course['course_name']) ?></td><td><?= h($course['department_name']) ?></td><td><?= (int) $course['credit_hours'] ?></td><td><a href="/course.php?id=<?= (int) $course['course_id'] ?>">عرض</a></td></tr><?php endforeach; ?>
</tbody></table></div>
<nav class="pagination" aria-label="صفحات النتائج"><?php for ($number = 1; $number <= $totalPages; $number++): ?><a class="<?= $number === $page ? 'active' : '' ?>" href="<?= h(course_query_url($number)) ?>"><?= $number ?></a><?php endfor; ?></nav>
<?php else: ?><p class="empty-state">لم نجد مقررات مطابقة. جرّب تغيير كلمة البحث أو القسم.</p><?php endif; ?>
<?php page_footer(); ?>
