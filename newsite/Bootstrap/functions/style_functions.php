<?php
declare(strict_types=1);

/**
 * Theme + simple HTML UI helpers.
 *
 * Theme resolution: cookie "theme" → DEFAULTTHEME → "default"
 * Files live in: page/theme/{name}/header.php, footer.php, …
 *
 * Message colours (via alert_*):
 *   success = green | warning = yellow | info = blue | error = red
 */

// -----------------------------------------------------------------
// Theme selection + include
// -----------------------------------------------------------------

/**
 * Safe current theme folder name.
 */
function get_theme(): string
{
    $theme = $_COOKIE['theme'] ?? null;

    if (empty($theme) || !is_string($theme)) {
        $theme = defined('DEFAULTTHEME') ? DEFAULTTHEME : 'default';
    }

    // Letters, numbers, underscore, hyphen only
    $theme = preg_replace('/[^a-z0-9_-]/i', '', $theme) ?? 'default';
    $theme = strtolower($theme);

    $path = BASEROOT . '/page/theme/' . $theme;
    if (!is_dir($path)) {
        $theme = 'default';
    }

    return $theme;
}

/**
 * Require a file from the active theme, falling back to default.
 *
 * @return bool  true if a file was included
 */
function theme_include(string $file): bool
{
    $theme = get_theme();
    $full  = BASEROOT . '/page/theme/' . $theme . '/' . $file;

    if (!is_file($full)) {
        $full = BASEROOT . '/page/theme/default/' . $file;
    }

    if (!is_file($full)) {
        return false;
    }

    require $full;
    return true;
}

/** Open HTML document + navbar (theme header.php). */
function theme_header(?string $title = null): void
{
    $GLOBALS['page_title'] = $title ?? (CONFIG['SITENAME'] ?? 'Site');

    if (!theme_include('header.php')) {
        throw new RuntimeException('Theme header.php not found');
    }
}

/** Close layout + footer scripts (theme footer.php). */
function theme_footer(): void
{
    if (!theme_include('footer.php')) {
        throw new RuntimeException('Theme footer.php not found');
    }
}

/** Admin layout header (falls back to normal header). */
function theme_admin_header(string $title = 'Admin CP'): void
{
    $GLOBALS['page_title'] = $title;

    if (!theme_include('admin_header.php')) {
        theme_header($title);
    }
}

/** Admin layout footer (falls back to normal footer). */
function theme_admin_footer(): void
{
    if (!theme_include('admin_footer.php')) {
        theme_footer();
    }
}

// -----------------------------------------------------------------
// Blocks / cards
// -----------------------------------------------------------------

function block_begin(string $caption = '-', int $padding = 3): void
{
    echo '<div class="tt_block rounded">'
       . '<div class="tt_blockhead text-center">' . e($caption) . '</div>'
       . '<div class="p-' . max(0, $padding) . '">';
}

function block_end(): void
{
    echo '</div></div>';
}

function card_start(string $title = ''): void
{
    echo '<div class="tt_block rounded">';
    if ($title !== '') {
        echo '<div class="tt_blockhead text-center">' . e($title) . '</div>';
    }
    echo '<div class="p-3">';
}

function card_end(): void
{
    echo '</div></div>';
}

// -----------------------------------------------------------------
// Alerts (colour boxes)
// -----------------------------------------------------------------

/**
 * Generic alert. Prefer alert_success / warning / info / error.
 * Uses alert_type() when available so aliases map to CSS classes.
 */
function alert(string $message, string $type = 'info'): void
{
    if (function_exists('alert_type')) {
        $type = alert_type($type);
    }
    echo '<div class="alert alert-' . e($type) . '">' . e($message) . '</div>';
}

function alert_success(string $message): void { alert($message, 'success'); } // green
function alert_warning(string $message): void { alert($message, 'warning'); } // yellow
function alert_info(string $message): void    { alert($message, 'info'); }    // blue
function alert_error(string $message): void   { alert($message, 'error'); }   // red

/**
 * Show one-time flash from a previous redirect (then clear it).
 * Call once after theme_header() on normal pages.
 */
function render_flash(): void
{
    if (!function_exists('flash_get')) {
        return;
    }

    $flash = flash_get();
    if ($flash === null) {
        return;
    }

    $message = (string) ($flash['message'] ?? '');
    $type    = (string) ($flash['type'] ?? 'info');

    alert($message, $type);
}

// -----------------------------------------------------------------
// Forms
// -----------------------------------------------------------------

function form_start(
    string $action = '',
    string $method = 'post',
    bool $enctype = false,
    string $id = '',
    string $class = ''
): void {
    echo '<form action="' . e($action) . '" method="' . e($method) . '"'
       . ($enctype ? ' enctype="multipart/form-data"' : '')
       . ($id !== '' ? ' id="' . e($id) . '"' : '')
       . ($class !== '' ? ' class="' . e($class) . '"' : '')
       . '>';
}

function form_end(): void
{
    echo '</form>';
}

function form_input(
    string $name,
    string $label = '',
    string $value = '',
    string $type = 'text',
    string $placeholder = '',
    bool $required = false,
    string $extra = ''
): void {
    echo '<div class="form-group">';
    if ($label !== '') {
        echo '<label for="' . e($name) . '">' . e($label) . '</label>';
    }
    echo '<input type="' . e($type) . '" name="' . e($name) . '" id="' . e($name) . '"'
       . ' value="' . e($value) . '" placeholder="' . e($placeholder) . '"'
       . ' class="form-control"' . ($required ? ' required' : '') . ' ' . $extra . '>';
    echo '</div>';
}

function form_textarea(
    string $name,
    string $label = '',
    string $value = '',
    int $rows = 5,
    string $placeholder = '',
    bool $required = false
): void {
    echo '<div class="form-group">';
    if ($label !== '') {
        echo '<label for="' . e($name) . '">' . e($label) . '</label>';
    }
    echo '<textarea name="' . e($name) . '" id="' . e($name) . '" rows="' . max(1, $rows) . '"'
       . ' placeholder="' . e($placeholder) . '" class="form-control"'
       . ($required ? ' required' : '') . '>' . e($value) . '</textarea>';
    echo '</div>';
}

function form_select(
    string $name,
    string $label = '',
    array $options = [],
    string $selected = '',
    bool $required = false
): void {
    echo '<div class="form-group">';
    if ($label !== '') {
        echo '<label for="' . e($name) . '">' . e($label) . '</label>';
    }
    echo '<select name="' . e($name) . '" id="' . e($name) . '" class="form-control"'
       . ($required ? ' required' : '') . '>';
    foreach ($options as $value => $text) {
        $sel = ((string) $value === $selected) ? ' selected' : '';
        echo '<option value="' . e((string) $value) . '"' . $sel . '>' . e($text) . '</option>';
    }
    echo '</select></div>';
}

function form_checkbox(string $name, string $label = '', string $value = '1', bool $checked = false): void
{
    echo '<div class="form-group"><label>'
       . '<input type="checkbox" name="' . e($name) . '" value="' . e($value) . '"'
       . ($checked ? ' checked' : '') . '> ' . e($label)
       . '</label></div>';
}

function form_hidden(string $name, string $value = ''): void
{
    echo '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
}

function button(
    string $text,
    string $type = 'submit',
    string $class = 'ttbtn',
    string $name = '',
    string $value = ''
): void {
    echo '<button type="' . e($type) . '" class="' . e($class) . '"'
       . ($name !== '' ? ' name="' . e($name) . '"' : '')
       . ($value !== '' ? ' value="' . e($value) . '"' : '')
       . '>' . e($text) . '</button>';
}

function button_link(string $text, string $url, string $class = 'ttbtn'): void
{
    echo '<a href="' . e($url) . '" class="' . e($class) . '">' . e($text) . '</a>';
}

// -----------------------------------------------------------------
// Tables / misc UI
// -----------------------------------------------------------------

function table_start(string $class = 'table'): void
{
    echo '<div class="table-responsive"><table class="' . e($class) . '">';
}

function table_end(): void
{
    echo '</table></div>';
}

function table_head(array $columns = []): void
{
    echo '<thead><tr>';
    foreach ($columns as $column) {
        echo '<th>' . e($column) . '</th>';
    }
    echo '</tr></thead>';
}

/** Cells may contain trusted HTML — escape user text with e() first. */
function table_row(array $cells = []): void
{
    echo '<tr>';
    foreach ($cells as $cell) {
        echo '<td>' . $cell . '</td>';
    }
    echo '</tr>';
}

function badge(string $text, string $type = 'primary'): void
{
    echo '<span class="badge badge-' . e($type) . '">' . e($text) . '</span>';
}

function progress_bar(int $percent, string $label = ''): void
{
    $percent = max(0, min(100, $percent));
    echo '<div class="progress"><div class="progress-bar" style="width:' . $percent . '%">'
       . ($label !== '' ? e($label) : $percent . '%')
       . '</div></div>';
}

function divider(): void
{
    echo '<hr class="divider">';
}

function spacer(int $size = 15): void
{
    echo '<div style="height:' . max(0, $size) . 'px;"></div>';
}

/** Content may include trusted HTML. */
function text_center(string $content): void
{
    echo '<div class="text-center">' . $content . '</div>';
}

// -----------------------------------------------------------------
// Optional view renderer (file-based pages often skip this)
// -----------------------------------------------------------------

/**
 * Load a view file with optional layout.
 * Prefer theme_header / content / theme_footer on simple pages.
 */
function render(string $file, array $data = [], string $layout = 'user'): void
{
    $file = str_replace('\\', '/', $file);

    if ($file === '' || str_contains($file, '..') || !preg_match('~^[a-z0-9_/-]+$~i', $file)) {
        error(__('NOTHING_FOUND'), 404);
    }

    $viewFile = match ($layout) {
        default => BASEROOT . '/page/' . $file . '.php',
    };

    if (!is_file($viewFile)) {
        error(__('NO_FILE', $file), 404);
    }

    extract($data, EXTR_SKIP);

    if ($layout === 'raw') {
        require $viewFile;
        return;
    }

    if ($layout === 'admin') {
        theme_admin_header($data['TITLE'] ?? 'Admin CP');
        render_flash();
        require $viewFile;
        theme_admin_footer();
        return;
    }

    theme_header($data['TITLE'] ?? null);
    render_flash();
    require $viewFile;
    theme_footer();
}

/** Simple 2–4 column row wrapper (your .row / .col-* or rename to your classes) */
function layout_row_start(): void
{
    echo '<div class="row p-4">';
}

function layout_row_end(): void
{
    echo '</div>';
}

function layout_col(string $content, string $class = 'col-md-3'): void
{
    echo '<div class="' . e($class) . '">' . $content . '</div>';
}

/** Label: value line (profile stats) */
function dl_line(string $label, string $valueHtml): void
{
    echo e($label) . ': ' . $valueHtml . '<br>';
}

/**
 * Modal shell — body is HTML you pass in (or capture with ob).
 * Replaces long Bootstrap modal boilerplate with your classes later.
 */
function modal_start(string $id, string $maxWidth = '700px'): void
{
    echo '<div class="modal fade" id="' . e($id) . '" tabindex="-1" aria-hidden="true">';
    echo '<div class="modal-dialog modal-dialog-centered modal-md" style="max-width:' . e($maxWidth) . ';">';
    echo '<div class="modal-content"><div class="modal-body">';
}

function modal_footer_start(): void
{
    echo '</div><div class="modal-footer">';
}

function modal_end(): void
{
    echo '</div></div></div></div>';
}