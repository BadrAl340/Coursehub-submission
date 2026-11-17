<?php
declare(strict_types=1);

$secureCookie = filter_var(getenv('APP_COOKIE_SECURE') ?: 'true', FILTER_VALIDATE_BOOLEAN);
$sameSite = $secureCookie ? 'None' : 'Lax';
session_name('coursehub_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => $sameSite,
]);
session_start();

set_exception_handler(static function (Throwable $exception): void {
    error_log($exception->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><html lang="ar" dir="rtl"><meta charset="utf-8"><title>حدث خطأ</title><body><main><h1>تعذر إكمال الطلب</h1><p>حدث خطأ أثناء معالجة الصفحة. تحقق من إعدادات قاعدة البيانات وحاول مجددًا.</p></main></body></html>';
});

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $url = getenv('DATABASE_URL');
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if ($url) {
        $parts = parse_url($url);
        if (!$parts || empty($parts['host']) || empty($parts['path'])) {
            throw new RuntimeException('DATABASE_URL is not a valid MySQL connection URL.');
        }
        $host = $parts['host'];
        $port = $parts['port'] ?? 3306;
        $database = rawurldecode(ltrim($parts['path'], '/'));
        $username = rawurldecode($parts['user'] ?? '');
        $password = rawurldecode($parts['pass'] ?? '');
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        $sslCa = getenv('MYSQL_SSL_CA') ?: ($query['ssl-ca'] ?? '/etc/ssl/certs/ca-certificates.crt');
        if ($sslCa && is_readable($sslCa) && defined('PDO::MYSQL_ATTR_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
        }
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_NAME') ?: 'coursehub';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);
    return $pdo;
}

function text_length(string $value): int
{
    $count = preg_match_all('/./us', $value);
    return $count === false ? strlen($value) : $count;
}

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return is_logged_in() && current_user()['role'] === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'سجّل الدخول أولًا لاستخدام هذه الصفحة.');
        redirect('/login.php');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        flash('error', 'هذه الصفحة مخصصة للمسؤول.');
        redirect(is_logged_in() ? '/courses.php' : '/login.php');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($message) ? $message : null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $posted = (string) ($_POST['csrf_token'] ?? '');
    if ($posted === '' || !hash_equals(csrf_token(), $posted)) {
        http_response_code(419);
        exit('انتهت صلاحية النموذج. ارجع للصفحة وحاول مجددًا.');
    }
}

function set_preferred_department(string $departmentId): void
{
    $secureCookie = filter_var(getenv('APP_COOKIE_SECURE') ?: 'true', FILTER_VALIDATE_BOOLEAN);
    setcookie('preferred_department', $departmentId, [
        'expires' => time() + (60 * 60 * 24 * 30),
        'path' => '/',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => $secureCookie ? 'None' : 'Lax',
    ]);
}

function page_header(string $title): void
{
    $pageTitle = $title;
    $flashMessage = take_flash();
    require __DIR__ . '/header.php';
}

function page_footer(): void
{
    require __DIR__ . '/footer.php';
}

function database_error_message(): string
{
    return 'تعذر الاتصال بقاعدة البيانات. تأكد من تشغيل MySQL واستيراد database/schema.sql وإعداد متغيرات الاتصال في README.md.';
}
