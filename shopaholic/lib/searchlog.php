<?php
final class searchlog extends baselog
{
    /**
     * @var metatable Statistics table
     */
    private static $stats;

    /**
     * Initialize
     * @var string
     */
    public static function init($dir)
    {
        parent::init($dir);

        self::$stats = metatable::open($dir . DIRECTORY_SEPARATOR . '.stats', metatable::READWRITE | metatable::AUTOCLOSE);
    }

    /**
     * Log
     * @param string query
     */
    public static function log($q)
    {
        if (self::$stats) {
            $current = self::$stats->get(str_replace('*', '', $q), 'count');
            $current_count = 0;
            if (!empty($current)) {
                $current_count = $current[str_replace('*', '', $q)]['count'];
            }

            self::$stats->set(str_replace('*', '', $q), 'count', ++$current_count);
        }

        parent::log(str_replace('%', '%%', $q));
    }

    /**
     * Searchlog statistics
     * @return array
     */
    public static function stats()
    {
        if (!self::$stats) return array();

        $ret = array();
        foreach (self::$stats->get('*', 'count') as $q => $_) {
            $ret[$q] = $_['count'];
        }
        arsort($ret);

        return $ret;
    }
}
