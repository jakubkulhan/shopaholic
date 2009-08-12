<?php
abstract class baselog
{
    /**
     * Do not instantiate this class
     */
    public function __construct()
    {
        throw new Exception('Not a chance.');
    }

    /**
     * Directory with logs
     */
    private static $dir;

    /**
     * This day in Y-m-d format
     */
    private static $today;

    /**
     * Initialize
     * @param string dir for logs
     * @return bool
     */
    public static function init($dir)
    {
        self::$dir = $dir;
        self::$today = date('Y-m-d');
    }

    /**
     * Log
     * @param string message (printf()-like format)
     * @param mixed arg1 ...
     */
    public static function log($msg)
    {
        $args = func_get_args();
        file_put_contents(
            self::$dir . DIRECTORY_SEPARATOR . self::$today,
            '[' . date('Y-m-d H:i:s') . '] ' .
                vsprintf($args[0], array_slice($args, 1)) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }
}
