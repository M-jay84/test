<?php

declare(strict_types=1);

function error_register_handlers(bool $testing = false): void
{
    $GLOBALS['MBITSY_ERROR_TESTING'] = $testing;

    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
 
    if (defined('LOG_PATH')) {
        ini_set('error_log', LOG_PATH . '/error_php.log');
    }

    set_exception_handler('error_handle_exception');
    set_error_handler('error_handle_php');
    register_shutdown_function('error_handle_shutdown');
}

function error_log_message(string $message, string $fileName = 'error_php.log'): void
{
    $directory = defined('LOG_PATH') ? LOG_PATH : sys_get_temp_dir();
    if (!is_dir($directory) && !@mkdir($directory, 0770, true) && !is_dir($directory)) {
        error_log($message);
        return;
    }
    $target = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($fileName);
    if (@error_log($message, 3, $target) === false) error_log($message);
}

function error_log_throwable(Throwable $exception): void
{
    $file = $exception instanceof PDOException ? 'error_database.log' : 'error_exceptions.log';
    $type = $exception instanceof PDOException ? 'DATABASE' : 'APPLICATION';

    $message = sprintf(
        "[%s] [%s] %s\nFile: %s:%d\nTrace:\n%s\n\n",
        date('Y-m-d H:i:s'),
        $type,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    error_log_message($message, $file);
}

function error_handle_php(int $number, string $message, string $file, int $line): bool
{
    if (!(error_reporting() & $number)) {
        return false;
    }

    $fatalTypes = [E_USER_ERROR, E_RECOVERABLE_ERROR];
    if (in_array($number, $fatalTypes, true)) {
        throw new ErrorException($message, 0, $number, $file, $line);
    }

    $levels = [
        E_NOTICE => 'NOTICE',
        E_USER_NOTICE => 'NOTICE',
        E_WARNING => 'WARNING',
        E_USER_WARNING => 'WARNING',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'DEPRECATED',
        E_STRICT => 'STRICT',
    ];

    $level = $levels[$number] ?? 'PHP ERROR';
    error_log_message(sprintf("[%s] [%s] %s\nFile: %s:%d\n\n", date('Y-m-d H:i:s'), $level, $message, $file, $line));

    return true;
}

function error_handle_exception(Throwable $exception): void
{
    static $handling = false;

    if ($handling) {
        error_log_message('Recursive exception while rendering an error page: ' . $exception);
        error_display_fallback();
        exit(1);
    }

    $handling = true;
    error_log_throwable($exception);
    http_response_code(500);

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }

    $message = function_exists('__')
        ? __('OOPS_ERR')
        : 'Something went wrong. Please try again later.';

    $title = function_exists('__') ? __('ERRORS') : 'Error';

    try {
        if (function_exists('theme_header') && function_exists('theme_footer') && function_exists('alert_error')) {
            theme_header($title);
            if (function_exists('block_begin')) {
                block_begin($title);
            }

            alert_error($message); // always red

            // Testing: print exception details (still red box)
            if (!empty($GLOBALS['MBITSY_ERROR_TESTING'])) {
                echo '<pre class="alert alert-error">' . e((string) $exception) . '</pre>';
            }

            if (function_exists('block_end')) {
                block_end();
            }
            theme_footer();
            exit(1);
        }
    } catch (Throwable $renderException) {
        error_log_message('Error-page rendering failed: ' . $renderException);
    }

    error_display_fallback();
    exit(1);
}

function error_handle_shutdown(): void
{
    $last = error_get_last();
    if ($last === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($last['type'] ?? 0, $fatalTypes, true)) {
        return;
    }

    $exception = new ErrorException(
        (string) $last['message'],
        0,
        (int) $last['type'],
        (string) $last['file'],
        (int) $last['line']
    );

    error_handle_exception($exception);
}

function error_display_fallback(): void
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }

    $title = function_exists('__') ? __('ERRORS') : 'Error';
    $message = function_exists('__')
        ? __('OOPS_ERR')
        : 'Something went wrong. Please try again later.';

    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>'
       . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
       . '</title><h1>'
       . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
       . '</h1><p>'
       . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
       . '</p></html>';
}
