<?php
require_once __DIR__ . '/../includes/bootstrap.php';
try {
    $pdo = db();
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    if ($sql === false) throw new RuntimeException('Could not read schema.sql.');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if ($statement === '') continue;
        $normalized = ltrim($statement);
        if (preg_match('/^(?:CREATE\s+DATABASE\b|USE\s+)/i', $normalized)) continue;
        $pdo->exec($statement);
    }
    echo "تم تجهيز الجداول وإضافة البيانات التجريبية بنجاح.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "فشل تجهيز قاعدة البيانات. تحقق من إعداد الاتصال ورسالة الخطأ في بيئة التطوير.\n");
    exit(1);
}
