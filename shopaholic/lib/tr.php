<?php
//use Nette\ITranslator;

final class tr implements ITranslator
{
    /**
     * @var array Translations table
     */
    public static $table = array();

    /**
     * @var string Current locale
     */
    public static $locale;

    /**
     * @var array Plurals
     */
    public static $plurals = array();

    /**
     * Translates string
     * @param string
     * @param int
     * @return string
     */
    public static function _($msg, $count = NULL)
    {
        if (isset(self::$table[self::$locale][$msg])) {
            $msg = self::$table[self::$locale][$msg];
        }

        if ($count !== NULL) {
            if (is_array($msg)) {
                $msg = call_user_func(self::$plurals[self::$locale], $msg, $count);
            }
            $msg = sprintf($msg, $count);
        }

        return $msg;
    }

    /**
     * Translates string
     * @param string
     * @param int
     * @return string
     */
    public function translate($msg, $count = NULL)
    {
        return self::_($msg, $count);
    }
}

/**
 * Translates string; tr::_() wrapper
 * @param string
 * @param int
 * @return string
 */
function __($msg, $count = NULL)
{
    return tr::_($msg, $count);
}
