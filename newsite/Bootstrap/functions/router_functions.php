<?php
declare(strict_types=1);

function router_dispatch(): void
{
    $segments = router_get_url();

    // Optional: allow account-signup → account/signup
    if (count($segments) === 1 && str_contains($segments[0], '-')) {
        $segments = explode('-', $segments[0]);
    }

    // Default page
    if ($segments === []) {
        $segments = ['home'];
    }

    $params = [];
    $file   = router_resolve_file($segments, $params);

    if ($file === null) {
        error(__('NOTHING_FOUND'), 404);
    }

    // Make params available to the included page
    $GLOBALS['params'] = $params;

    require $file;
}

/**
 * Walk URL segments and find the first matching .php file.
 * Leftover segments become $params.
 *
 * Tries: page/user/... then page/admin/...
 */
function router_resolve_file(array $segments, array &$params): ?string
{
    $bases = [
        BASEROOT . '/page/user',
        BASEROOT . '/page/admin',
    ];

    foreach ($bases as $base) {
        $path = $base;
        $left = $segments;

        // Build path segment by segment; stop when a file matches
        $built = [];
        while ($left !== []) {
            $part = router_sanitize($left[0]);
            if ($part === '') {
                break;
            }

            $built[] = $part;
            array_shift($left);

            $candidate = $path . '/' . implode('/', $built) . '.php';
            if (is_file($candidate)) {
                $params = array_values($left);
                return $candidate;
            }
        }

        // Also try: .../index.php inside a folder (optional)
        if ($built !== []) {
            $index = $path . '/' . implode('/', $built) . '/index.php';
            if (is_file($index)) {
                $params = array_values($left);
                return $index;
            }
        }
    }

    return null;
}

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

function router_sanitize(string $value): string
{
    $value = preg_replace('/[^a-z0-9_-]/i', '', $value) ?? '';
    return strtolower($value);
}

/** Helper for pages: read a param by index */
function param(int $index, mixed $default = null): mixed
{
    return $GLOBALS['params'][$index] ?? $default;
}