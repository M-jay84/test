<?php
declare(strict_types=1);

/* ---------- URL & Redirect ---------- */

function url(string $path = ''): string
{
    $path = trim($path);
    return $path === '' ? URLROOT : URLROOT . '/' . ltrim($path, '/');
}

function redirect(string $path = '', ?string $message = null, string $type = 'info'): never
{
    if ($message !== null) {
        flash_set($message, $type);
    }

    $target = url($path);

    if (!headers_sent()) {
        header('Location: ' . $target, true, 302);
        exit;
    }

    echo '<script>window.location.href=' . json_encode($target, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ';</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . e($target) . '"></noscript>';
    exit;
}
function message(string $text, string $path = '', int $refresh = 3, string $type = 'info'): never
{
    $target = url($path);

    theme_header('Message');

    match ($type) {
        'success' => alert_success($text),
        'error'   => alert_error($text),
        'warning' => alert_warning($text),
        default   => alert_info($text),
    };

    echo '<p class="text-center">Redirecting in ' . (int)$refresh . ' seconds...</p>';
    echo '<p class="text-center"><a href="' . e($target) . '">Click here if you are not redirected</a></p>';
    echo '<meta http-equiv="refresh" content="' . (int)$refresh . ';url=' . e($target) . '">';

    theme_footer();
    exit;
}

/**
 * Show a themed error page (exceptions, missing files, 404, etc.)
 *
 * error('Something went wrong');
 * error('Page not found', 404);
 * error('Access denied', 403);
 */
function error(string $text, int $code = 500): never
{
    http_response_code($code);

    $title = match ($code) {
        404 => 'Page Not Found',
        403 => 'Access Denied',
        default => 'Error',
    };

    if (function_exists('theme_header') && function_exists('theme_footer') && function_exists('alert_error')) {
        theme_header($title);
        alert_error($text); // red
        echo '<p class="text-center"><a href="' . url() . '">Return to homepage</a></p>';
        theme_footer();
        exit;
    }

    // Fallback if theme not loaded
    echo '<!doctype html><html><meta charset="utf-8"><title>' . e($title) . '</title>';
    echo '<h1>' . e($title) . '</h1><p>' . e($text) . '</p>';
    echo '<p><a href="' . e(URLROOT) . '">Home</a></p></html>';
    exit;
}
/* ---------- Flash messages ---------- */

function flash_set(string $message, string $type = 'info'): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['_flash'] = ['message' => $message, 'type' => $type];
    }
}

function flash_get(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['_flash'])) {
        return null;
    }
    $flash = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
    return $flash;
}

function flash_has(): bool
{
    return session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['_flash']);
}

/* ---------- Escaping ---------- */

function e(mixed $value): string
{
    if ($value === null || (!is_scalar($value) && !$value instanceof Stringable)) {
        $value = '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ---------- Request helpers ---------- */

function is_get(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET';
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

function is_ajax(): bool
{
    return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
}

function get(string $key, mixed $default = null): mixed
{
    return $_GET[$key] ?? $default;
}

function post(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $default;
}

function req(string $key, mixed $default = null): mixed
{
    return $_REQUEST[$key] ?? $default;
}


/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('Session required for CSRF token');
    }
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $submitted = post('_token');
    $stored    = $_SESSION['_csrf_token'] ?? null;
    return is_string($submitted) && is_string($stored) && $submitted !== '' && hash_equals($stored, $submitted);
}