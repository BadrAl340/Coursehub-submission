<?php
require_once __DIR__ . '/_guard.php';
$pageTitle = 'التقارير';
$pdo = db();
$byCourse = $pdo->query("SELECT c.course_code, c.course_name, d.department_name, COUNT(e.enrollment_id) AS enrollment_count FROM courses c INNER JOIN departments d ON d.department_id = c.department_id LEFT JOIN enrollments e ON e.course_id = c.course_id AND e.status = 'active' GROUP BY c.course_id, c.course_code, c.course_name, d.department_name ORDER BY enrollment_count DESC, c.course_code")->fetchAll();
$byDepartment = $pdo->query('SELECT d.department_name, COUNT(DISTINCT c.course_id) AS course_count, COUNT(e.enrollment_id) AS enrollment_count FROM departments d LEFT JOIN courses c ON c.department_id = d.department_id LEFT JOIN enrollments e ON e.course_id = c.course_id AND e.status = \'active\' GROUP BY d.department_id, d.department_name ORDER BY enrollment_count DESC, d.department_name')->fetchAll();
page_header($pageTitle);
?>
<section class="page-heading"><p class="eyebrow">مؤشرات قاعدة البيانات</p><h1>التقارير والإحصاءات</h1><p>عدد التسجيلات النشطة لكل مقرر وقسم.</p></section><nav class="admin-links"><a href="/admin/index.php">لوحة المسؤول</a><a href="/admin/enrollments.php">التسجيلات</a></nav>
<h2>الإحصاءات حسب المقرر</h2><div class="table-wrap"><table><thead><tr><th>الرمز</th><th>المقرر</th><th>القسم</th><th>عدد التسجيلات</th></tr></thead><tbody><?php foreach ($byCourse as $row): ?><tr><td><?= h($row['course_code']) ?></td><td><?= h($row['course_name']) ?></td><td><?= h($row['department_name']) ?></td><td><?= (int) $row['enrollment_count'] ?></td></tr><?php endforeach; ?></tbody></table></div>
<h2>ملخص الأقسام</h2><div class="table-wrap"><table><thead><tr><th>القسم</th><th>عدد المقررات</th><th>عدد التسجيلات</th></tr></thead><tbody><?php foreach ($byDepartment as $row): ?><tr><td><?= h($row['department_name']) ?></td><td><?= (int) $row['course_count'] ?></td><td><?= (int) $row['enrollment_count'] ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php page_footer(); ?>
