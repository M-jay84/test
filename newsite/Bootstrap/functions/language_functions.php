<?php
declare(strict_types=1);

/**
 * Load the correct language file.
 * Priority: cookie → DEFAULTLANG constant
 */
function load_language(): void
{
    // 1. Try cookie
    $lang = $_COOKIE['lang'] ?? null;

    // 2. Fallback to DEFAULTLANG from config
    if (empty($lang) || !is_string($lang)) {
        $lang = defined('DEFAULTLANG') ? DEFAULTLANG : 'english';
    }

    // 3. Sanitize (only letters)
    $lang = preg_replace('/[^a-z]/i', '', $lang) ?? 'english';
    $lang = strtolower($lang);

    $file = BASEROOT . '/Bootstrap/language/' . $lang . '.php';

    // 4. If the chosen language file does not exist → fall back to english
    if (!is_file($file)) {
        $file = BASEROOT . '/Bootstrap/language/english.php';
        $lang = 'english';
    }

    // 5. Load it (this defines the LANG constant)
    require $file;

}


/**
 * Translate a language key.
 *
 * Examples:
 *   __('HOME')
 *   __('NO_FILE', $filename)
 *   __('USERNAME_TOO_LONG', 20)
 */
function __(string $key, mixed ...$args): string
{
    if (!defined('LANG') || !is_array(LANG)) {
        return $key;
    }

    $text = LANG[$key] ?? $key;

    if ($args !== []) {
        $args = array_map(static fn($v) => is_scalar($v) || $v === null ? $v : '', $args);
        $text = vsprintf($text, $args);
    }

    return $text;
}