<?php
declare(strict_types=1);

/**
 * Get current theme name (cookie → DEFAULTTHEME → default)
 */
function get_theme(): string
{
    $theme = $_COOKIE['theme'] ?? null;

    if (empty($theme) || !is_string($theme)) {
        $theme = defined('DEFAULTTHEME') ? DEFAULTTHEME : 'default';
    }

    // only allow safe characters
    $theme = preg_replace('/[^a-z0-9_-]/i', '', $theme) ?? 'default';
    $theme = strtolower($theme);

    $path = BASEROOT . '/page/theme/' . $theme;

    if (!is_dir($path)) {
        $theme = 'default';
    }

    return $theme;
}

/**
 * Include a file from the current theme (with fallback to default)
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

function theme_header(?string $title = null): void
{
    $GLOBALS['page_title'] = $title ?? (CONFIG['SITENAME'] ?? 'Site');
    if (!theme_include('header.php')) {
        throw new RuntimeException('Theme header.php not found');
    }
}

function theme_footer(): void
{
    if (!theme_include('footer.php')) {
        throw new RuntimeException('Theme footer.php not found');
    }
}

function theme_admin_header(string $title = 'Admin CP'): void
{
    $GLOBALS['page_title'] = $title;
    if (!theme_include('admin_header.php')) {
        theme_header($title);
    }
}

function theme_admin_footer(): void
{
    if (!theme_include('admin_footer.php')) {
        theme_footer();
    }
}

/* ---------- Simple UI helpers (no style_ prefix) ---------- */

function block_begin(string $caption = '-', int $padding = 3): void
{
    echo '<div class="tt_block rounded"><div class="tt_blockhead text-center">' . e($caption) . '</div><div class="p-' . max(0, $padding) . '">';
}

function block_end(): void
{
    echo '</div></div>';
}

function alert(string $message, string $type = 'info'): void
{
    echo '<div class="alert alert-' . e($type) . '">' . e($message) . '</div>';
}

function alert_success(string $message): void { alert($message, 'success'); }
function alert_error(string $message): void   { alert($message, 'error'); }
function alert_warning(string $message): void { alert($message, 'warning'); }
function alert_info(string $message): void    { alert($message, 'info'); }

function form_start(string $action = '', string $method = 'post', bool $enctype = false, string $id = '', string $class = ''): void
{
    echo '<form action="' . e($action) . '" method="' . e($method) . '"'
       . ($enctype ? ' enctype="multipart/form-data"' : '')
       . ($id !== '' ? ' id="' . e($id) . '"' : '')
       . ($class !== '' ? ' class="' . e($class) . '"' : '') . '>';
}

function form_end(): void
{
    echo '</form>';
}

function form_input(string $name, string $label = '', string $value = '', string $type = 'text', string $placeholder = '', bool $required = false, string $extra = ''): void
{
    echo '<div class="form-group">';
    if ($label !== '') echo '<label for="' . e($name) . '">' . e($label) . '</label>';
    echo '<input type="' . e($type) . '" name="' . e($name) . '" id="' . e($name) . '" value="' . e($value)
       . '" placeholder="' . e($placeholder) . '" class="form-control"' . ($required ? ' required' : '') . ' ' . $extra . '>';
    echo '</div>';
}

function form_textarea(string $name, string $label = '', string $value = '', int $rows = 5, string $placeholder = '', bool $required = false): void
{
    echo '<div class="form-group">';
    if ($label !== '') echo '<label for="' . e($name) . '">' . e($label) . '</label>';
    echo '<textarea name="' . e($name) . '" id="' . e($name) . '" rows="' . max(1, $rows)
       . '" placeholder="' . e($placeholder) . '" class="form-control"' . ($required ? ' required' : '') . '>'
       . e($value) . '</textarea></div>';
}

function form_select(string $name, string $label = '', array $options = [], string $selected = '', bool $required = false): void
{
    echo '<div class="form-group">';
    if ($label !== '') echo '<label for="' . e($name) . '">' . e($label) . '</label>';
    echo '<select name="' . e($name) . '" id="' . e($name) . '" class="form-control"' . ($required ? ' required' : '') . '>';
    foreach ($options as $value => $text) {
        echo '<option value="' . e((string)$value) . '"' . ((string)$value === $selected ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    echo '</select></div>';
}

function form_checkbox(string $name, string $label = '', string $value = '1', bool $checked = false): void
{
    echo '<div class="form-group"><label><input type="checkbox" name="' . e($name) . '" value="' . e($value) . '"'
       . ($checked ? ' checked' : '') . '> ' . e($label) . '</label></div>';
}

function form_hidden(string $name, string $value = ''): void
{
    echo '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
}

function button(string $text, string $type = 'submit', string $class = 'ttbtn', string $name = '', string $value = ''): void
{
    echo '<button type="' . e($type) . '" class="' . e($class) . '"'
       . ($name !== '' ? ' name="' . e($name) . '"' : '')
       . ($value !== '' ? ' value="' . e($value) . '"' : '') . '>' . e($text) . '</button>';
}

function button_link(string $text, string $url, string $class = 'ttbtn'): void
{
    echo '<a href="' . e($url) . '" class="' . e($class) . '">' . e($text) . '</a>';
}

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
    foreach ($columns as $column) echo '<th>' . e($column) . '</th>';
    echo '</tr></thead>';
}

/** Cells can contain trusted HTML – escape user data with e() before passing */
function table_row(array $cells = []): void
{
    echo '<tr>';
    foreach ($cells as $cell) echo '<td>' . $cell . '</td>';
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
       . ($label !== '' ? e($label) : $percent . '%') . '</div></div>';
}

function divider(): void
{
    echo '<hr class="divider">';
}

function spacer(int $size = 15): void
{
    echo '<div style="height:' . max(0, $size) . 'px;"></div>';
}

function text_center(string $content): void
{
    echo '<div class="text-center">' . $content . '</div>';
}

function card_start(string $title = ''): void
{
    echo '<div class="tt_block rounded">';
    if ($title !== '') echo '<div class="tt_blockhead text-center">' . e($title) . '</div>';
    echo '<div class="p-3">';
}

function card_end(): void
{
    echo '</div></div>';
}

/* ---------- View renderer (simplified) ---------- */

function render(string $file, array $data = [], string $layout = 'user'): void
{
    $file = str_replace('\\', '/', $file);
    if ($file === '' || str_contains($file, '..') || !preg_match('~^[a-z0-9_/-]+$~i', $file)) {
        not_found();
    }

    $viewFile = match ($layout) {
        'admin' => BASEROOT . '/page/admin/' . $file . '.php',
        'raw'   => BASEROOT . '/page/user/' . $file . '.php',
        default => BASEROOT . '/page/user/' . $file . '.php',
    };

    if (!is_file($viewFile)) {
        not_found(__('NO_FILE', $file));
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

    // normal user layout
    theme_header($data['TITLE'] ?? null);
    render_flash();
    require $viewFile;
    theme_footer();
}

function render_flash(): void
{
    $flash = flash_get();
    if ($flash === null) return;

    $message = (string)($flash['message'] ?? '');
    match ($flash['type'] ?? 'info') {
        'success' => alert_success($message),
        'error'   => alert_error($message),
        'warning' => alert_warning($message),
        default   => alert_info($message),
    };
}

function not_found(string $message = ''): never
{
    http_response_code(404);
    $message = $message !== '' ? $message : __('NOTHING_FOUND');
    $view = BASEROOT . '/page/user/error_404.php';

    if (is_file($view)) {
        render('error_404', ['TITLE' => '404', 'MESSAGE' => $message], 'user');
        exit;
    }

    echo '<!doctype html><html><meta charset="utf-8"><title>404</title><h1>404</h1><p>' . e($message) . '</p>';
    exit;
}