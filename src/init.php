<?php
define('NCURSES_DEBUG', true);
define('LOG_FILE', '/tmp/ncurses_widgets.log');

if (!defined('ROOT_NCURSES_WIDGETS'))
    define('ROOT_NCURSES_WIDGETS', __DIR__);

if (defined('NCURSES_IS_INITIALIZED'))
    return;

define('NCURSES_IS_INITIALIZED', true);
define('WIDGET_AUTO_SIZE', PHP_INT_MAX);
define('WIDGET_FILL', PHP_INT_MAX);
define('MSG_KEY_EVENT', 'MSG_KEY_EVENT');
define('MSG_MOUSE_EVENT', 'MSG_MOUSE_EVENT');
define('EVENT_HANDLED', PHP_INT_MAX);

// Initialize ncurses built-in constants before our own
// definitions, because some of them exists both places
ncurses_init();
ncurses_noecho();
ncurses_mousemask(NCURSES_ALL_MOUSE_EVENTS, $oldMask);

// Most if these constants are defined in ncurses_functions.c (ncurses ext.)
// but this helps PhpStorm to believe that the constants actually exists.
if (!defined('NCURSES_KEY_TAB'))
    define('NCURSES_KEY_TAB', 9);
if (!defined('NCURSES_KEY_ALT_ENTER'))
    define('NCURSES_KEY_ALT_ENTER', 13);
if (!defined('NCURSES_KEY_ESCAPE'))
    define('NCURSES_KEY_ESCAPE', 27);
if (!defined('NCURSES_KEY_SPACEBAR'))
    define('NCURSES_KEY_SPACEBAR', 32);
if (!defined('NCURSES_KEY_ALT_BACKSPACE'))
    define('NCURSES_KEY_ALT_BACKSPACE', 127);
if (!defined('NCURSES_ACS_RARROW'))
    define('NCURSES_ACS_RARROW', 4194347); // →
if (!defined('NCURSES_ACS_LARROW'))
    define('NCURSES_ACS_LARROW', 4194348); // ←
if (!defined('NCURSES_ACS_UARROW'))
    define('NCURSES_ACS_UARROW', 4194349); // ↑
if (!defined('NCURSES_ACS_DARROW'))
    define('NCURSES_ACS_DARROW', 4194350); // ↓
if (!defined('NCURSES_ACS_DIAMOND'))
    define('NCURSES_ACS_DIAMOND', 4194400); // ♦
if (!defined('NCURSES_ACS_CKBOARD'))
    define('NCURSES_ACS_CKBOARD', 4194401); // ▒
if (!defined('NCURSES_ACS_LRCORNER'))
    define('NCURSES_ACS_LRCORNER', 4194410); // ┘
if (!defined('NCURSES_ACS_URCORNER'))
    define('NCURSES_ACS_URCORNER', 4194411); // ┐
if (!defined('NCURSES_ACS_ULCORNER'))
    define('NCURSES_ACS_ULCORNER', 4194412); // ┌
if (!defined('NCURSES_ACS_LLCORNER'))
    define('NCURSES_ACS_LLCORNER', 4194413); // └
if (!defined('NCURSES_ACS_PLUS'))
    define('NCURSES_ACS_PLUS', 4194414); // ┼
if (!defined('NCURSES_ACS_S1'))
    define('NCURSES_ACS_S1', 4194415); // ⎺
if (!defined('NCURSES_ACS_S3'))
    define('NCURSES_ACS_S3', 4194416); // ⎻
if (!defined('NCURSES_ACS_HLINE'))
    define('NCURSES_ACS_HLINE', 4194417); // ─
if (!defined('NCURSES_ACS_S7'))
    define('NCURSES_ACS_S7', 4194418); // ⎼
if (!defined('NCURSES_ACS_S9'))
    define('NCURSES_ACS_S9', 4194419); // ⎽
if (!defined('NCURSES_ACS_LTEE'))
    define('NCURSES_ACS_LTEE', 4194420); // ├
if (!defined('NCURSES_ACS_RTEE'))
    define('NCURSES_ACS_RTEE', 4194421); // ┤
if (!defined('NCURSES_ACS_BTEE'))
    define('NCURSES_ACS_BTEE', 4194422); // ┴
if (!defined('NCURSES_ACS_TTEE'))
    define('NCURSES_ACS_TTEE', 4194423); // ┬
if (!defined('NCURSES_ACS_VLINE'))
    define('NCURSES_ACS_VLINE', 4194424); // │

/**
 * Define an autoload function. This function is executed
 * every time a new class is required which hasn't been
 * included into the current run yet.
 * */
spl_autoload_register(function ($class)
{
    // All files should be lower-cased
    $class = strtolower($class);

    // Replace \ char in PHP namespace to the separator
    // used in the current operating system
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    $class = ROOT_NCURSES_WIDGETS . '/' . $class . '.php';

    if (file_exists($class))
    {
        /** @noinspection PhpIncludeInspection */
        require_once $class;
    }
}, true, true);

/**
 * Dump any throwable to a DUMP file and to the log
 *
 * @param Throwable $t The throwable to dump
 */
function errorDump(Throwable $t)
{
    ncurses_end();
    $errorStr = '!! ' . get_class($t) . ' !! ' . $t->getMessage() . "\n" . getTrace($t) . "\n";
    file_put_contents('DUMP', $errorStr);
    logstr($errorStr);
    trigger_error($errorStr, E_USER_ERROR);
}

function getTrace(\Throwable $e, array $seen = [])
{
    $starter = count($seen) === 0 ? '' : 'Caused by: ';
    $result = [];
    $trace = $e->getTrace();
    $prev = $e->getPrevious();
    $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
    $file = $e->getFile();
    $line = $e->getLine();
    while (true)
    {
        $current = "$file:$line";
        if (in_array($current, $seen, true))
        {
            $result[] = sprintf(' ... %d more', count($trace) + 1);
            break;
        }
        $result[] = sprintf(' at %s%s%s(%s%s%s)', count($trace) && array_key_exists('class', $trace[0]) ? $trace[0]['class'] : '', count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '\\' : '', count($trace) && array_key_exists('function', $trace[0]) ? $trace[0]['function'] : '(main)', $line === null ? $file : basename($file), $line === null ? '' : ':', $line === null ? '' : $line);
        if (is_array($seen))
            $seen[] = "$file:$line";
        if (!count($trace))
            break;
        $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
        $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
        array_shift($trace);
    }
    $result = implode("\n", $result);
    if ($prev)
        $result .= "\n" . getTrace($prev, $seen);

    return $result;
}

/**
 * @param array ...$o Variable number of data to write to log file
 */
function logstr(... $o)
{
    if (!NCURSES_DEBUG)
        return;

    list($usec, $sec) = explode(' ', microtime());
    $buffer = '[' . date('H:i:s', $sec) . '.' . str_pad((int)($usec * 1000), 3, '0') . '] ';
    foreach ($o as $e)
        $buffer .= is_array($e) ? print_r($e, true) : '' . $e;

    file_put_contents(LOG_FILE, $buffer . "\n", FILE_APPEND);
}

// setlocale is required in order to use ncursesw (extended char map)
// e.g. NCURSES_ACS_RARROW would actually be a pretty arrow
setlocale(LC_ALL, '');

set_exception_handler(function (\Throwable $t)
{
    errorDump($t);
});

set_error_handler(function ($errno, $message, $file, $line)
{
    errorDump(new \ErrorException($message, $errno, 1, $file, $line));
});

register_shutdown_function(function ()
{
    ncurses_curs_set(1);
    ncurses_end();
    $lastError = error_get_last();
    if ($lastError !== null)
        errorDump(new ErrorException($lastError['message'], $lastError['type'], 1, $lastError['file'], $lastError['line']));
});

logstr("\n\n********** STARTING **********\n\n");