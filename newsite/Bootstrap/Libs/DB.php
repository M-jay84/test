<?php
declare(strict_types=1);

namespace Bootstrap\Libs;

use PDO;
use PDOException;

/**
 * Shared PDO connection + query helpers.
 *
 * Usage:
 *   $row  = DB::run('SELECT * FROM users WHERE id = ?', [$id])->fetch();
 *   $rows = DB::run('SELECT * FROM users')->fetchAll();
 *   DB::update('users', ['email' => $email], ['id' => $id]);
 *
 * Connection errors: logged and a short message is shown.
 * Query errors: logged, then re-thrown for the global exception handler (red page).
 */
class DB
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Single shared PDO instance (lazy connect).
     */
    public static function instance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                self::logError('Connection failed', $e->getMessage());
                http_response_code(500);

                // In testing, surface the real reason
                if (!empty($GLOBALS['MBITSY_ERROR_TESTING'])) {
                    exit('Database connection failed: ' . htmlspecialchars($e->getMessage()));
                }

                exit('Database temporarily unavailable. Please try again later.');
            }
        }

        return self::$instance;
    }

    /**
     * Run a SQL statement. Pass bound parameters as the second argument.
     */
    public static function run(string $sql, array $args = []): \PDOStatement
    {
        try {
            if ($args === []) {
                return self::instance()->query($sql);
            }

            $stmt = self::instance()->prepare($sql);
            $stmt->execute($args);
            return $stmt;
        } catch (PDOException $e) {
            self::logQueryError($sql, $args, $e);
            // Let the global exception handler show a themed red page
            throw $e;
        }
    }

    /**
     * Simple UPDATE helper.
     *
     * DB::update('users', ['email' => 'a@b.c'], ['id' => 5]);
     */
    public static function update(string $table, array $data, array $where): int
    {
        if ($data === [] || $where === []) {
            return 0;
        }

        $setParts = [];
        foreach (array_keys($data) as $col) {
            $setParts[] = '`' . str_replace('`', '``', $col) . '` = ?';
        }
        $set = implode(', ', $setParts);

        $whereParts = [];
        foreach (array_keys($where) as $col) {
            $whereParts[] = '`' . str_replace('`', '``', $col) . '` = ?';
        }
        $whereClause = implode(' AND ', $whereParts);

        $sql  = "UPDATE `{$table}` SET {$set} WHERE {$whereClause}";
        $stmt = self::run($sql, array_merge(array_values($data), array_values($where)));

        return $stmt->rowCount();
    }

    /**
     * Legacy alias kept for old code that called updateset().
     * Prefer update() for new code.
     */
    public static function updateset(string $table, array $data, array $where): int
    {
        return self::update($table, $data, $where);
    }

    /**
     * Proxy other PDO methods: lastInsertId(), beginTransaction(), etc.
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        return self::instance()->$method(...$args);
    }

    // -----------------------------------------------------------------
    // Logging
    // -----------------------------------------------------------------

    private static function logQueryError(string $sql, array $args, PDOException $e): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);

        $file = 'unknown';
        $line = 0;
        foreach ($trace as $t) {
            if (isset($t['file']) && !str_contains($t['file'], 'DB.php')) {
                $file = $t['file'];
                $line = $t['line'] ?? 0;
                break;
            }
        }

        $safeArgs = array_map(static function ($value) {
            if (is_string($value) && strlen($value) > 200) {
                return substr($value, 0, 200) . '... [truncated]';
            }
            if ($value === null) {
                return 'NULL';
            }
            return $value;
        }, $args);

        $logFile = (defined('LOG_PATH') ? LOG_PATH : sys_get_temp_dir()) . '/db_errors.log';

        $message = sprintf(
            "[%s] SQL ERROR\nMessage : %s\nFile    : %s:%d\nSQL     : %s\nParams  : %s\n----------------------------------------\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $file,
            $line,
            $sql,
            json_encode($safeArgs, JSON_UNESCAPED_UNICODE)
        );

        error_log($message, 3, $logFile);
    }

    private static function logError(string $title, string $details): void
    {
        $logFile = (defined('LOG_PATH') ? LOG_PATH : sys_get_temp_dir()) . '/db_errors.log';

        $message = sprintf(
            "[%s] %s\n%s\n----------------------------------------\n",
            date('Y-m-d H:i:s'),
            $title,
            $details
        );

        error_log($message, 3, $logFile);
    }
}