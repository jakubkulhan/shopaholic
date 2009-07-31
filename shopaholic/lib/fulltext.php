<?php
final class fulltext
{
    /**
     * Filename of dirty ids bitmap
     */
    const DIRTY_BITMAP_FILENAME = 'dirty';

    /**
     * Size of byte (in bits)
     */
    const SIZEOF_BYTE = 8;

    /**
     * @var string Directory with fulltext
     */
    private static $dir = NULL;

    /**
     * @var Zend_Search_Lucene Index
     */
    private static $index = NULL;

    /**
     * Init fulltext
     * @param string fulltext directory
     */
    public static function init($dir)
    {
        static $done = FALSE;

        if (!$done) {
            self::$dir = rtrim($dir, DIRECTORY_SEPARATOR);
            $index_dir = self::$dir . DIRECTORY_SEPARATOR . 'index';
            if (!is_dir($index_dir)) self::$index = Zend_Search_Lucene::create($index_dir);
            else self::$index = Zend_Search_Lucene::open($index_dir);

            if (!file_exists(self::$dir . DIRECTORY_SEPARATOR . self::DIRTY_BITMAP_FILENAME)) 
                if (!@touch(self::$dir . DIRECTORY_SEPARATOR . self::DIRTY_BITMAP_FILENAME))
                    return FALSE;

            $done = TRUE;
        }
    }

    /**
     * Dirty documents handling
     * @param int id
     * @param bool set
     * @return mixed depends on number of given arguments
     */
    public static function dirty()
    {
        if (func_num_args() === 0) { // get all
            // read data
            if (($data = file_get_contents('safe://' . self::$dir . DIRECTORY_SEPARATOR . 
                self::DIRTY_BITMAP_FILENAME)) === FALSE) return NULL;

            // build ids array
            $ids = array();
            for ($i = 0, $len = strlen($data); $i < $len; $i++) {
                $byte = decbin(ord($data{$i}));
                $byte = str_pad($byte, self::SIZEOF_BYTE, '0', STR_PAD_LEFT);
                for ($j = 0; $j < self::SIZEOF_BYTE; $j++)
                    if ($byte{$j}) $ids[] = $i * self::SIZEOF_BYTE + $j;
            }

            return $ids;

        } else {
            $id = intval(func_get_arg(0));
            $set = NULL;
            if (func_num_args() > 1) $set = func_get_arg(1);
                
            // open file
            if (!($handle = fopen('safe://' . self::$dir . DIRECTORY_SEPARATOR . 
                self::DIRTY_BITMAP_FILENAME, 'r+b'))) return NULL;

            // read byte with id
            $offset = intval(floor($id / self::SIZEOF_BYTE));
            if (fseek($handle, $offset, SEEK_SET) === -1) {
                fclose($handle); 
                return NULL;
            }
            $byte = decbin(ord(fread($handle, 1)));
            $byte = str_pad($byte, self::SIZEOF_BYTE, '0', STR_PAD_LEFT);

            // get
            if ($set === NULL) {
                fclose($handle);
                return $byte{$id - $offset * self::SIZEOF_BYTE} === '1';
            }

            // set
            $byte{$id - $offset * self::SIZEOF_BYTE} = (string) intval($set);
            if (fseek($handle, $offset, SEEK_SET) === -1) {
                fclose($handle);
                return NULL;
            }

            if (fwrite($handle, chr(bindec($byte)), 1) !== 1) {
                fclose($handle);
                return NULL;
            }

            fclose($handle);
            return TRUE;
        }
    }

    /**
     * Index getter
     * @return Zend_Search_Lucene
     */
    public static function index()
    {
        return self::$index;
    }
}
