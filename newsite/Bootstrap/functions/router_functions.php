<?php
declare(strict_types=1);

/**
 * File-based router.
 *
 * How URLs map to files:
 *   /                 → page/authorize/home.php
 *   /login            → page/authorize/login.php
 *   /account/view/3   → page/authorize/account/view.php   + param(0) = "3"
 *   /account-signup   → optional: treat as account/signup
 *
 * Search order: page
 * Leftover URL segments become $GLOBALS['params'] (use param(0), param(1), …).
 */

/**
 * Entry point — called from index.php after bootstrap.
 */
function router_dispatch(): void
{
    $segments = router_get_url();

    // Allow a single hyphenated segment: account-signup → account/signup
    if (count($segments) === 1 && str_contains($segments[0], '-')) {
        $segments = explode('-', $segments[0]);
    }

    // Empty URL → home
    if ($segments === []) {
        $segments = ['home'];
    }

    $params = [];
    $file   = router_resolve_file($segments, $params);

    if ($file === null) {
        error(__('NOTHING_FOUND'), 404);
    }

    // Available in the page as param(0), param(1), …
    $GLOBALS['params'] = $params;

    require $file;
}

/**
 * Walk URL segments and find the first matching .php file.
 * Anything left after the file becomes $params.
 *
 * Example:
 *   segments = ['account', 'view', '3']
 *   file     = page/authorize/view.php
 *   params   = ['3']
 */
function router_resolve_file(array $segments, array &$params): ?string
{
    $bases = [
        BASEROOT . '/page',
    ];

    foreach ($bases as $base) {
        $left  = $segments;
        $built = [];

        while ($left !== []) {
            $part = router_sanitize($left[0]);
            if ($part === '') {
                break;
            }

            $built[] = $part;
            array_shift($left);

            // Try: …/account/view.php
            $candidate = $base . '/' . implode('/', $built) . '.php';
            if (is_file($candidate)) {
                $params = array_values($left);
                return $candidate;
            }
        }

        // Try folder index: …/account/view/index.php
        if ($built !== []) {
            $index = $base . '/' . implode('/', $built) . '/index.php';
            if (is_file($index)) {
                $params = array_values($left);
                return $index;
            }
        }
    }

    return null;
}

/**
 * Read ?url= from the front-controller rewrite and split into segments.
 */
function router_get_url(): array
{
    if (empty($_GET['url']) || !is_string($_GET['url'])) {
        return [];
    }

    $url = rtrim($_GET['url'], '/');
    $url = filter_var($url, FILTER_SANITIZE_URL);

    if ($url === '' || $url === false) {
        return [];
    }

    return explode('/', $url);
}

/**
 * Keep only safe path characters; force lowercase.
 */
function router_sanitize(string $value): string
{
    $value = preg_replace('/[^a-z0-9_-]/i', '', $value) ?? '';
    return strtolower($value);
}

/**
 * Read a leftover URL segment by index.
 * param(0) is the first segment after the matched file.
 */
function param(int $index, mixed $default = null): mixed
{
    return $GLOBALS['params'][$index] ?? $default;
}