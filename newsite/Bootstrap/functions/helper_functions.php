<?php
declare(strict_types=1);

/**
 * Shared helpers: URLs, flash messages, redirects, CSRF, input.
 *
 * Message colours:
 *   success → green   (action worked)
 *   warning → yellow  (bad input / login failed)
 *   info    → blue    (soft notices / DB messages later)
 *   error   → red     (system / 404 / exceptions)
 */

// -----------------------------------------------------------------
// Alert type aliases → canonical name
// -----------------------------------------------------------------

function alert_type(string $type): string
{
    $type = strtolower(trim($type));

    return match ($type) {
        'success', 'ok', 'green'              => 'success',
        'warning', 'yellow', 'input', 'valid' => 'warning',
        'info', 'blue', 'db', 'notice'        => 'info',
        'error', 'danger', 'red', 'fatal'     => 'error',
        default                               => 'info',
    };
}

// -----------------------------------------------------------------
// URL builder
// -----------------------------------------------------------------

/**
 * Build a site URL from a path.
 *
 * url()              → http://localhost/newsite
 * url('login')       → http://localhost/newsite/login
 * url('/login')      → http://localhost/newsite/login
 *
 * If $path is already absolute (http/https) or already starts with URLROOT,
 * it is returned unchanged (no doubling).
 */
function url(string $path = ''): string
{
    $path = trim($path);

    // Already a full URL
    if ($path !== '' && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))) {
        return $path;
    }

    $root = rtrim(URLROOT, '/');

    // Empty → site root
    if ($path === '' || $path === '/') {
        return $root;
    }

    // Already includes URLROOT as a prefix (e.g. path = URLROOT or URLROOT/login)
    if (str_starts_with($path, $root)) {
        return $path;
    }

    return $root . '/' . ltrim($path, '/');
}

// -----------------------------------------------------------------
// Flash (one request only)
// -----------------------------------------------------------------

function flash_set(string $message, string $type = 'info'): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['_flash'] = [
            'message' => $message,
            'type'    => alert_type($type),
        ];
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

// -----------------------------------------------------------------
// Redirect (+ optional flash on next page)
// -----------------------------------------------------------------

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

    // Headers already sent — JS / meta fallback
    echo '<script>window.location.href=' . json_encode($target, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ';</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . e($target) . '"></noscript>';
    exit;
}

// -----------------------------------------------------------------
// Themed intermediate message, then redirect
// -----------------------------------------------------------------

function message(string $text, string $path = '', int $refresh = 3, string $type = 'info'): never
{
    $target = url($path);
    $type   = alert_type($type);

    theme_header('Message');
    alert($text, $type);

    echo '<p class="text-center">Redirecting in ' . (int) $refresh . ' seconds...</p>';
    echo '<p class="text-center"><a href="' . e($target) . '">Click here if you are not redirected</a></p>';
    echo '<meta http-equiv="refresh" content="' . (int) $refresh . ';url=' . e($target) . '">';

    theme_footer();
    exit;
}

// -----------------------------------------------------------------
// Themed system error page (always red)
// -----------------------------------------------------------------

function error(string $text, int $code = 500): never
{
    http_response_code($code);

    $title = match ($code) {
        404     => 'Page Not Found',
        403     => 'Access Denied',
        400     => 'Bad Request',
        default => 'Error',
    };

    if (function_exists('theme_header') && function_exists('theme_footer') && function_exists('alert_error')) {
        theme_header($title);
        alert_error($text);
        echo '<p class="text-center"><a href="' . url() . '">Return to homepage</a></p>';
        theme_footer();
        exit;
    }

    echo '<!doctype html><html><meta charset="utf-8"><title>' . e($title) . '</title>';
    echo '<h1>' . e($title) . '</h1><p>' . e($text) . '</p>';
    echo '<p><a href="' . e(URLROOT) . '">Home</a></p></html>';
    exit;
}

// -----------------------------------------------------------------
// Escape output for HTML
// -----------------------------------------------------------------

function e(mixed $value): string
{
    if ($value === null || (!is_scalar($value) && !$value instanceof Stringable)) {
        $value = '';
    }
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// -----------------------------------------------------------------
// Request helpers
// -----------------------------------------------------------------

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

function req(string $key, mixed $default = null): mixed
{
    return $_REQUEST[$key] ?? $default;
}

/* ---------- Input (POST) ---------- */

/**
 * Raw POST value (no redirect, no forced type).
 * post('username', '')
 */
function post(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $default;
}

/**
 * Trimmed string from POST (tags stripped).
 */
function input_string(string $key, string $default = ''): string
{
    if (!isset($_POST[$key])) {
        return $default;
    }
    return trim(strip_tags((string) $_POST[$key]));
}

/**
 * Integer from POST (invalid → $default).
 */
function input_int(string $key, int $default = 0): int
{
    if (!isset($_POST[$key])) {
        return $default;
    }
    $v = filter_var($_POST[$key], FILTER_VALIDATE_INT);
    return $v === false ? $default : (int) $v;
}

/**
 * Email from POST.
 * If invalid and $redirectOnFail is set, redirect with yellow flash.
 * If empty string allowed, pass $allowEmpty = true.
 */
function input_email(
    string $key = 'email',
    string $redirectOnFail = 'home',
    bool $allowEmpty = false
): string {
    $email = input_string($key);

    if ($email === '' && $allowEmpty) {
        return '';
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        redirect($redirectOnFail, __('INVAILD_EMAIL_ADDRESS'), 'warning');
    }

    return $email;
}

/**
 * Legacy name used all over old code.
 *
 * Input1('wantusername')
 * Input1('invite', 'int')
 * Input1('email', 'email')  — redirects home if invalid
 *
 * Prefer input_string / input_int / input_email in new pages.
 */
function Input1(string $value, ?string $type = null): mixed
{
    return match ($type) {
        'int'   => input_int($value, 0),
        'email' => input_email($value, 'home', false),
        default => input_string($value, ''),
    };
}

// -----------------------------------------------------------------
// CSRF
// -----------------------------------------------------------------

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

    return is_string($submitted)
        && is_string($stored)
        && $submitted !== ''
        && hash_equals($stored, $submitted);
}