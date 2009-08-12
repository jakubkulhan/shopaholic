<?php
/**
 * It is metatable or something like that ;-)
 */
interface metatableable
{
    /**
     * Get value(s)
     * @param string
     * @param string
     * @return array
     */
    public function get($row, $col);

    /**
     * Set value
     * @param string
     * @param string
     * @param mixed
     * @param bool
     */
    public function set($row, $col, $value);
}

/**
 * Simple way how to store and retrieve data
 */
final class metatable implements metatableable
{
    /**
     * Magic integer for string type
     */
    const MAGIC_TYPE_STRING = -2147483648;

    /**
     * Magic integer for integer type
     */
    const MAGIC_TYPE_INTEGER = 1073741824;

    /**
     * Magic integer for true value
     */
    const MAGIC_TYPE_TRUE = 536870912;

    /**
     * Magic integer for false value
     */
    const MAGIC_TYPE_FALSE = 268435456;

    /**
     * Magic string indcating it's metatable file
     */
    const MAGIC_STRING = 'metatable';

    /**
     * Version
     */
    const VERSION = 0;

    /**
     * Prefix for temporary created files
     */
    const TEMPORARY_PREFIX = 'mtb';

    /**
     * Metatable will be opened readonly
     */
    const READONLY = 1;

    /**
     * Metatable will be opened writeable
     */
    const READWRITE = 2;

    /**
     * Strings will be garbage collected
     */
    const STRINGS_GC = 4;

    /**
     * File will be saved automatically when instance is being destroyed (discouraged)
     */
    const AUTOCLOSE = 8;

    /**
     * Size of integer
     */
    const SIZEOF_INT = 4;

    /**
     * Name of data frame
     */
    const FRAME_DATA = 'data';

    /**
     * pack() string for data record
     */
    const DATA_RECORD_PACK = 'a124a124NN';

    /**
     * unpack() string for data record
     */
    const DATA_RECORD_UNPACK = 'a124row/a124col/Ntype/Nvalue';

    /**
     * Type string
     */
    const STRING = 'string';

    /**
     * Type integer
     */
    const INTEGER = 'integer';

    /**
     * Type boolean
     */
    const BOOLEAN = 'boolean';

    /**
     * Size of data record
     */
    const SIZEOF_DATA_RECORD = 256;

    /**
     * Size of data row
     */
    const SIZEOF_DATA_ROW = 124;

    /**
     * unpack() string for data row
     */
    const DATA_ROW_UNPACK = 'a124row';

    /**
     * pack() string for data row
     */
    const DATA_ROW_PACK = 'a124';

    /**
     * Size of data column
     */
    const SIZEOF_DATA_COL = 124;

    /**
     * unpack() string for data column
     */
    const DATA_COL_UNPACK = 'a124col';

    /**
     * pack() string for data column
     */
    const DATA_COL_PACK = 'a124';

    /**
     * Size of data type
     */
    const SIZEOF_DATA_TYPE = 4;

    /**
     * unpack() string for data type
     */
    const DATA_TYPE_UNPACK = 'Ntype';

    /**
     * pack() string for data type
     */
    const DATA_TYPE_PACK = 'N';

    /**
     * Size of data value
     */
    const SIZEOF_DATA_VALUE = 4;

    /**
     * unpack() string for data value
     */
    const DATA_VALUE_UNPACK = 'Nvalue';

    /**
     * pack() string for data value
     */
    const DATA_VALUE_PACK = 'N';

    /**
     * Name of strings frame
     */
    const FRAME_STRINGS = 'strs';

    /**
     * Name of indexes frames
     */
    const FRAME_INDEXES = 'indx';

    /**
     * pack() string for index record
     */
    const INDEX_RECORD_PACK = 'a120NN';

    /**
     * unpack() string for index record
     */
    const INDEX_RECORD_UNPACK = 'a120start/Nlower/Nupper';

    /**
     * Size of index record
     */
    const SIZEOF_INDEX_RECORD = 128;

    /**
     * Minimal size of one frame
     */
    const FRAME_MINIMAL_SIZE = 2048; // 2KiB

    /**
     * Number of tries to lock file
     */
    const LOCK_TRIES = 10;

    /**
     * @var int Flags
     */
    private $flags;

    /**
     * @var string Metatable file name
     */
    private $filename;

    /**
     * @var string Handle file name
     */
    private $handle_filename;

    /**
     * @var string Temporary file name
     */
    private $tmp_filename;

    /**
     * @var resource Metatable file stream
     */
    private $handle;

    /**
     * @var resource Locking file stream
     */
    private $locking;

    /**
     * @var resource Temporary file stream
     */
    private $tmp;

    /**
     * @var array File structure
     */
    private $structure;

    /**
     * @var array Indexes
     */
    private $indexes = array();

    /**
     * Private constructor, use metatable::open() to create new metatable instance
     */
    private function __construct()
    {
    }

    /**
     * Free all resources
     */
    public function __destruct()
    {
        if (($this->flags & self::AUTOCLOSE) === self::AUTOCLOSE) {
            $this->close();
        }

        if ($this->locking) {
            fclose($this->locking);
        }

        if ($this->handle) {
            fclose($this->handle);
        }

        if ($this->handle_filename) {
            unlink($this->handle_filename);
        }

        if ($this->tmp) {
            fclose($this->tmp);
        }

        if ($this->tmp_filename) {
            unlink($this->tmp_filename);
        }
    }

    /**
     * Closes metatable and saves contents if opened in READWRITE mode
     * @return bool
     */
    public function close()
    {
        if (($this->flags & self::READONLY) === self::READONLY) {
            fclose($this->handle);
            $this->handle = NULL;
            return TRUE;
        }

        $data = $this->structure['frames_indexes'][self::FRAME_DATA];
        $strings = $this->structure['frames_indexes'][self::FRAME_STRINGS];
        $indexes = $this->structure['frames_indexes'][self::FRAME_INDEXES];

        // strings GC
        if (($this->flags & self::STRINGS_GC) === self::STRINGS_GC) {
            fseek($this->tmp, 0, SEEK_SET);
            fseek($this->handle, $this->structure['frames'][$data]['offset'], SEEK_SET);
            $new_offset = 0;
            
            for ($i = 0, $N = $this->structure['frames'][$data]['used'] /
                self::SIZEOF_DATA_RECORD, $unpack = self::DATA_TYPE_UNPACK . '/' .
                self::DATA_VALUE_UNPACK; $i < $N; $i++)
            {
                $read = unpack($unpack, substr(fread($this->handle,
                    self::SIZEOF_DATA_RECORD), self::SIZEOF_DATA_ROW +
                    self::SIZEOF_DATA_COL));

                list($type, $offset, $size) = $this->data_get_type($read['type'],
                    $read['value']);

                if ($type === self::STRING) {
                    if ($this->locking && $offset < $this->structure['frames']
                        [$strings]['used_at_start'])
                    {
                        fseek($this->locking, $this->structure['frames'][$strings]
                            ['offset_at_start'] + $offset, SEEK_SET);
                        stream_copy_to_stream($this->locking, $this->tmp, $size);

                    } else {
                        fseek($this->handle, $this->structure['frames'][$strings]
                            ['offset'] + $offset, SEEK_SET);
                        stream_copy_to_stream($this->handle, $this->tmp, $size);
                    }

                    $this->frame_write(self::FRAME_DATA, $i * self::SIZEOF_DATA_RECORD
                        + self::SIZEOF_DATA_ROW + self::SIZEOF_DATA_COL, $this->data_type(
                        self::STRING, $new_offset, $size));

                    $new_offset += $size;
                }
            }

            fseek($this->handle, $this->structure['frames'][$strings]['offset'],
                SEEK_SET);
            fseek($this->tmp, 0, SEEK_SET);
            stream_copy_to_stream($this->tmp, $this->handle, $new_offset);
            $this->structure['frames'][$strings]['used'] = $new_offset;
        }

        // write indexes
        ksort($this->indexes);
        $count = count($this->indexes);
        while ($count * self::SIZEOF_INDEX_RECORD > $this->structure['frames']
            [$indexes])
        {
            $this->frame_grow(self::FRAME_INDEXES);
        }

        $i = 0;
        foreach ($this->indexes as $index => $values) {
            list($lower, $upper) = $values;
            $this->frame_write(self::FRAME_INDEXES, $i * self::SIZEOF_INDEX_RECORD,
                pack(self::INDEX_RECORD_PACK, $index, $lower, $upper),
                self::SIZEOF_INDEX_RECORD);
            $i++;
        }

        $this->structure['frames'][$indexes]['used'] = $i * self::SIZEOF_INDEX_RECORD;

        // write structure
        $last = max(array_keys($this->structure['frames']));
        fseek($this->handle, $this->structure['frames'][$last]['offset'] +
            $this->structure['frames'][$last]['size'], SEEK_SET);
        self::structure_write($this->handle, $this->structure);

        // rename
        if (!fflush($this->handle)) {
            return FALSE;
        }
        fclose($this->handle);
        $this->handle = NULL;

        // WIN rename() workaround - unfotunately breaks atomicity :-(
        if (substr(PHP_OS, 0, 3) === 'WIN' && file_exists($this->filename)) {
            fclose($this->locking);
            $this->locking = NULL;
            @unlink($this->filename);
        }

        $ret = @rename($this->handle_filename, $this->filename);
        if ($ret) {
            $this->handle_filename = NULL;
        }

        if ($this->locking) {
            fclose($this->locking);
            $this->locking = NULL;
        }

        return $ret;
    }

    /**
     * Create index
     * @param string
     * @return bool
     */
    public function index($start)
    {
        $lower = 0;
        $upper = $this->structure['frames'][$this->structure['frames_indexes']
            [self::FRAME_DATA]]['used'] / self::SIZEOF_DATA_RECORD - 1;

        list($lower, $upper, $found) = $this->data_find($start);

        if (!$found) {
            return FALSE;
        }

        $this->indexes[$start] = array($lower, $upper);

        return TRUE;
    }

    /**
     * Remove index
     * @param string
     */
    public function unindex($start)
    {
        unset($this->indexes[$start]);
    }

    /**
     * Get values by (row, col)
     * @param string
     * @param string
     * @return array
     */
    public function get($row, $col)
    {
        // get bounds
        $lower = 0;
        $upper = $this->structure['frames'][$this->structure['frames_indexes']
            [self::FRAME_DATA]]['used'] / self::SIZEOF_DATA_RECORD - 1;

        if ($row[0] !== '*') {
            $start = $row;
            $star_pos = strpos($row, '*');
            if ($star_pos !== false) {
                $start = substr($row, 0, $star_pos);
            }

            if (isset($this->indexes[$start])) {
                list($lower, $upper) = $this->indexes[$start];

            } else {
                list($lower, $upper, $found) = $this->data_find($start);
                if (!$found) {
                    return array();
                }
            }
        }

        // get data itself
        if ($row !== '*') {
            $row = '~^' . str_replace('\*', '.+', preg_quote($row, '~')) . '$~';
        }
        if ($col !== '*') {
            $col = '~^' . str_replace('\*', '.+', preg_quote($col, '~')) . '$~';
        }

        $ret = array();
        fseek($this->handle, $this->structure['frames'][$this->structure
            ['frames_indexes'][self::FRAME_DATA]]['offset'] + $lower *
            self::SIZEOF_DATA_RECORD, SEEK_SET); // single seek

        for ($i = $lower; $i <= $upper; $i++) {
            $data = fread($this->handle, self::SIZEOF_DATA_RECORD);
            if (strlen($data) < self::SIZEOF_DATA_RECORD) {
                return array();
            }

            $data = unpack(self::DATA_RECORD_UNPACK, $data);

            if (!($row !== '*' && !preg_match($row, $data['row']))) {
                if (!($col !== '*' && !preg_match($col, $data['col']))) {
                    if (!isset($ret[$data['row']])) {
                        $ret[$data['row']] = array();
                    }

                    $ret[$data['row']][$data['col']] = $this->data_get_value(
                        $data['type'], $data['value']);

                    if (is_string($ret[$data['row']][$data['col']])) {
                        fseek($this->handle, $this->structure['frames'][
                            $this->structure['frames_indexes']
                            [self::FRAME_DATA]]['offset'] + ($i + 1) *
                            self::SIZEOF_DATA_RECORD, SEEK_SET);
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Set or unset (pass NULL as value) value
     * @param string
     * @param string
     * @param mixed
     * @return bool
     */
    public function set($row, $col, $value)
    {
        // is writeable?
        if (($this->flags & self::READONLY) === self::READONLY) {
            return FALSE;
        }

        // data frame index
        $index = $this->structure['frames_indexes'][self::FRAME_DATA];

        // check value
        if (!(is_string($value) || is_int($value) || is_bool($value) ||
                is_null($value)))
        {
            return FALSE;
        }

        // find where to put record
        list($place, , $found) = $this->data_find($row, $col);

        // already done?
        if (!$found && is_null($value)) {
            return TRUE;
        }

        // exapand frame if necessary
        if (!$found) {
            while ($this->structure['frames'][$index]['used'] +
                self::SIZEOF_DATA_RECORD > $this->structure['frames'][$index]
                ['size'])
            {
                $this->frame_grow(self::FRAME_DATA);
            }
        }

        // move records
        if ((!$found || is_null($value)) && $this->structure['frames'][$index]
            ['used'] - ($place + (is_null($value) ? 1 : 0)) *
            self::SIZEOF_DATA_RECORD > 0)
        {
            $offset = $this->structure['frames'][$index]['offset'];
            $size = $this->structure['frames'][$index]['used']
                - ($place + (is_null($value) ? 1 : 0)) * self::SIZEOF_DATA_RECORD;

            fseek($this->handle, $offset + ($place + (is_null($value) ? 1 : 0))
                * self::SIZEOF_DATA_RECORD, SEEK_SET);
            fseek($this->tmp, 0, SEEK_SET);

            stream_copy_to_stream($this->handle, $this->tmp, $size);

            fseek($this->handle, $offset + ($place + (is_null($value) ? 0 : 1))
                * self::SIZEOF_DATA_RECORD, SEEK_SET);
            fseek($this->tmp, 0, SEEK_SET);

            stream_copy_to_stream($this->tmp, $this->handle, $size);
        }

        // write record
        if (is_null($value)) { // unset
            $this->structure['frames'][$index]['used'] -= self::SIZEOF_DATA_RECORD;

        } else { // real set
            $this->frame_write(self::FRAME_DATA, $place * self::SIZEOF_DATA_RECORD,
                $this->data_value($row, $col, $value), self::SIZEOF_DATA_RECORD);

            if (!$found) {
                $this->structure['frames'][$index]['used'] += self::SIZEOF_DATA_RECORD;
            }
        }

        // update indexes
        $k = is_null($value) ? -1 : 1;
        foreach ($this->indexes as $index => $values) {
            list($lower, $upper) = $values;
            $cmp = strncmp($row, $index, strlen($index));
            if ($cmp < 0) {
                $this->indexes[$index] = array($lower + $k, $upper + $k);
            } else if ($cmp === 0) {
                $this->indexes[$index] = array($lower, $upper + $k);
            }
        }

        return TRUE;
    }

    /**
     * Get real value
     * @param int
     * @param int
     * @return mixed
     */
    private function data_get_value($type, $value)
    {
        $val = NULL;

        if (($type & (self::MAGIC_TYPE_STRING)) !== 0) {
            $offset = $value;
            $size = $type & (~(self::MAGIC_TYPE_STRING));
            if ($this->locking && $offset < $this->structure['frames'][$this->
                structure['frames_indexes'][self::FRAME_STRINGS]]['used_at_start'])
            {
                fseek($this->locking, $this->structure['frames'][$this->structure
                        ['frames_indexes'][self::FRAME_STRINGS]]['offset_at_start'] +
                    $offset, SEEK_SET);
                if ($size > 0) {
                    $val = fread($this->locking, $size);
                } else {
                    $val = '';
                }

            } else {
                if ($size > 0) {
                    $val = $this->frame_read(self::FRAME_STRINGS, $offset, $size);
                } else {
                    $val = '';
                }
            }

        } else if (($type & (self::MAGIC_TYPE_INTEGER)) !== 0) { // integer
            $val = intval($value);

        } else if (($type & (self::MAGIC_TYPE_TRUE)) !== 0) { // true
            $val = true;

        } else if (($type & (self::MAGIC_TYPE_FALSE)) !== 0) { // false
            $val = false;
        }

        return $val;
    }

    /**
     * Get string representing type of value
     * @param int
     * @param int
     * @return array
     */
    private function data_get_type($type, $value)
    {
        $ret = array(NULL, NULL, NULL);

        if (($type & (self::MAGIC_TYPE_STRING)) !== 0) {
            $ret = array(self::STRING, $value, $type & (~(self::MAGIC_TYPE_STRING)));

        } else if (($type & (self::MAGIC_TYPE_INTEGER)) !== 0) { // integer
            $ret = array(self::INTEGER, $value, NULL);

        } else if (($type & (self::MAGIC_TYPE_TRUE)) !== 0) { // true
            $ret = array(self::BOOLEAN, TRUE, NULL);

        } else if (($type & (self::MAGIC_TYPE_FALSE)) !== 0) { // false
            $ret = array(self::BOOLEAN, FALSE, NULL);
        }

        return $ret;
    }

    /**
     * Get representation of given value
     * @param mixed
     * @return string
     */
    private function data_value($row, $col, $value)
    {
        $type = NULL;
        switch (gettype($value)) {
            case 'string':
                $length = strlen($value);
                while ($this->structure['frames'][$this->structure
                    ['frames_indexes'][self::FRAME_STRINGS]]['size'] -
                    $this->structure['frames'][$this->structure['frames_indexes']
                    [self::FRAME_STRINGS]]['used'] < $length)
                {
                    $this->frame_grow(self::FRAME_STRINGS);
                }

                $offset = $this->structure['frames'][$this->structure
                    ['frames_indexes'][self::FRAME_STRINGS]]['used'];

                $this->frame_write(self::FRAME_STRINGS, $offset, $value, $length);

                $this->structure['frames'][$this->structure['frames_indexes']
                    [self::FRAME_STRINGS]]['used'] += $length;

                $type = self::MAGIC_TYPE_STRING | $length;
                $value = $offset;
            break;

            case 'integer':
                $type = self::MAGIC_TYPE_INTEGER;
            break;

            case 'boolean':
                $type = ($value ? self::MAGIC_TYPE_TRUE : self::MAGIC_TYPE_FALSE);
                $value = 0;
            break;
        }

        return pack(self::DATA_RECORD_PACK, $row, $col, $type, $value);
    }

    /**
     * Get binary representation of type
     * @param string
     * @param mixed
     * @param mixed
     * @return string
     */
    private function data_type($type, $offset, $size = NULL)
    {
        switch ($type) {
            case self::STRING:
                return pack('NN', self::MAGIC_TYPE_STRING | $size, $offset);
            case self::INTEGER:
                return pack('NN', self::MAGIC_TYPE_INTEGER, $offset);
            case self::BOOLEAN:
                return pack('NN', ($offset ? self::MAGIC_TYPE_TRUE : self::MAGIC_TYPE_FALSE), 0);
        }

        return pack('NN', 0, 0);
    }

    /**
     * Find index of some data
     * @param string
     * @param string
     * @return array
     */
    private function data_find($row, $col = NULL)
    {
        // init
        $row_len = strlen($row);
        $col_len = 0;
        if ($col !== NULL) {
            $col_len = strlen($col);
        }

        $max = ($this->structure['frames'][$this->structure['frames_indexes']
            [self::FRAME_DATA]]['used'] / self::SIZEOF_DATA_RECORD) - 1;

        // find lower
        $l = 0;
        $r = $max;
        $found = FALSE;
        $cmp = 0;

        while ($l <= $r) {
            $center = intval(($l + $r) / 2);
            if ($col === NULL) {
                $data = unpack(self::DATA_ROW_UNPACK, $this->frame_read(
                    self::FRAME_DATA, $center * self::SIZEOF_DATA_RECORD,
                    self::SIZEOF_DATA_ROW));
                $cmp = strncmp($data['row'], $row, $row_len);
            } else {
                $data = unpack(self::DATA_ROW_UNPACK . '/' .
                    self::DATA_COL_UNPACK, $this->frame_read(self::FRAME_DATA,
                    $center * self::SIZEOF_DATA_RECORD, self::SIZEOF_DATA_ROW +
                    self::SIZEOF_DATA_COL));
                $cmp = strcmp($data['row'], $row);
                $col_cmp = strcmp($data['col'], $col);
            }

            if ($cmp < 0) {
                $l = $center + 1;
            } else {
                if ($col === NULL || $cmp > 0) {
                    $r = $center - 1;
                } else {
                    if ($col_cmp < 0) {
                        $l = $center + 1;
                    } else if ($col_cmp > 0) {
                        $r = $center - 1;
                    } else {
                        $l = $center;
                        $found = TRUE;
                        $r = $l - 1;
                    }
                }
            }
        }

        $lower = $l;

        // already done?
        if ($col !== NULL) {
            return array($lower, 0, $found);
        }

        if ($cmp > 0 || $lower > $max) {
            return array(0, 0, FALSE);
        }

        // find upper
        $l = $lower;
        $r = $max;

        while ($l <= $r) {
            $center = intval(($l + $r) / 2);
            $data = unpack(self::DATA_ROW_UNPACK, $this->frame_read(
                self::FRAME_DATA, $center * self::SIZEOF_DATA_RECORD,
                self::SIZEOF_DATA_ROW));
            $cmp = strncmp($row, $data['row'], $row_len);

            if (!($cmp < 0)) {
                $l = $center + 1;
            } else {
                $r = $center - 1;
            }
        }

        $upper = $l - 1;

        // return
        return array($lower, $upper, TRUE);
    }

    /**
     * Create new frame
     * @param string
     * @param int negative numbers mean from the end
     * @return bool FALSE on failure
     */
    private function frame_create($name, $index = -1)
    {
        $frames_count = count($this->structure['frames']);
        // check index
        if ($index < -($frames_count + 1) || $index > $frames_count) {
            return FALSE;
        }

        if ($index < 0) {
            $index += count($this->structure['frames']) + 1;
        }

        // move frames
        for ($i = count($this->structure['frames']) - 1; $i >= $index; $i--) {
            fseek($this->handle, $this->structure['frames'][$i]['offset'],
                SEEK_SET);
            fseek($this->tmp, 0, SEEK_SET);
            stream_copy_to_stream($this->handle, $this->tmp,
                $this->structure['frames'][$i]['used']);

            fseek($this->handle, $this->structure['frames'][$i]['offset'] +
                self::FRAME_MINIMAL_SIZE, SEEK_SET);
            fseek($this->tmp, 0, SEEK_SET);
            stream_copy_to_stream($this->tmp, $this->handle,
                $this->structure['frames'][$i]['used']);

            $this->structure['frames_indexes'][$this->structure['frames'][$i]
                ['name']] = $i + 1;

            $this->structure['frames'][$i]['offset'] += self::FRAME_MINIMAL_SIZE;
            $this->structure['frames'][$i + 1] = $this->structure['frames'][$i];
            unset($this->structure['frames'][$i]);
        }

        // add new frame
        $this->structure['frames'][$index] = array(
            'name' => $name,
            'size' => self::FRAME_MINIMAL_SIZE,
            'used' => 0,
            'offset' => isset($this->structure['frames'][$index - 1]) ?
                $this->structure['frames'][$index - 1]['offset'] +
                $this->structure['frames'][$index - 1]['size'] :
                0
        );

        $this->structure['frames_indexes'][$name] = $index;

        // clean and return

        return TRUE;
    }

    /**
     * Delete frame
     * @param string
     * @return bool FALSE on failure; if frame does not exist, returns TRUE
     */
    private function frame_delete($name)
    {
        // check
        if (!isset($this->structure['frames_indexes'][$name])) {
            return TRUE;
        }

        $index = $this->structure['frames_indexes'][$name];
        $size = $this->structure['frames'][$index]['size'];

        // move frames
        $frames_count = count($this->structure['frames']);

        if ($index + 1 >= $frames_count) {
            unset($this->structure['frames'][$index]);
        } else {
            for ($i = $index + 1; $i < $frames_count; $i++) {
                fseek($this->handle, $this->structure['frames'][$i]['offset'],
                    SEEK_SET);
                fseek($this->tmp, 0, SEEK_SET);
                stream_copy_to_stream($this->handle, $this->tmp,
                    $this->structure['frames'][$i]['used']);

                fseek($this->handle, $this->structure['frames'][$i]['offset'] -
                    $size, SEEK_SET);
                fseek($this->tmp, 0, SEEK_SET);
                stream_copy_to_stream($this->tmp, $this->handle,
                    $this->structure['frames'][$i]['used']);

                $this->structure['frames_indexes']
                    [$this->structure['frames'][$i]['name']]--;

                $this->structure['frames'][$i]['offset'] -= $size;
                $this->structure['frames'][$i - 1] = $this->structure['frames'][$i];
                unset($this->structure['frames'][$i]);
            }
        }

        // clean and return
        unset($this->structure['frames_indexes'][$name]);

        return TRUE;
    }

    /**
     * Grow frame (frame will be 2 times larger than before)
     * @param string
     * @return bool FALSE on failure
     */
    private function frame_grow($name)
    {
        // check
        if (!isset($this->structure['frames_indexes'][$name])) {
            return FALSE;
        }

        $index = $this->structure['frames_indexes'][$name];
        $new_size = $this->structure['frames'][$index]['size'] * 2;
        $grow = $new_size - $this->structure['frames'][$index]['size'];

        // move frames
        for ($i = count($this->structure['frames']) - 1; $i > $index; $i--) {
            fseek($this->handle, $this->structure['frames'][$i]['offset'],
                SEEK_SET);
            fseek($this->tmp, 0, SEEK_SET);
            stream_copy_to_stream($this->handle, $this->tmp,
                $this->structure['frames'][$i]['used']);

            fseek($this->handle, $this->structure['frames'][$i]['offset'] +
                $grow, SEEK_SET);
            fseek($this->tmp, 0, SEEK_SET);
            stream_copy_to_stream($this->tmp, $this->handle,
                $this->structure['frames'][$i]['used']);

            $this->structure['frames'][$i]['offset'] += $grow;
        }

        $this->structure['frames'][$index]['size'] = $new_size;

        // clean and return
        return TRUE;
    }

    /**
     * Read data from frame
     * @param string
     * @param int offset in frame
     * @param int bytes to read
     * @return string|bool read data; FALSE on failure (e.g. frame does not
     * exist, out of bounds)
     */
    private function frame_read($name, $offset, $size)
    {
        if (!isset($this->structure['frames_indexes'][$name])) {
            return FALSE;
        }

        $index = $this->structure['frames_indexes'][$name];

        fseek($this->handle, $this->structure['frames'][$index]['offset'] +
            $offset, SEEK_SET);
        return fread($this->handle, $size);
    }

    /**
     * Write data into frame
     * @param string
     * @param int offset in frame
     * @param int bytes to write
     * @param string string of bytes
     * @return int|bool bytes written; FALSE on failure (e.g. frame does not
     * exist, out of bounds)
     */
    private function frame_write($name, $offset, $value, $size = -1)
    {
        if (!isset($this->structure['frames_indexes'][$name])) {
            return FALSE;
        }

        $index = $this->structure['frames_indexes'][$name];

        fseek($this->handle, $this->structure['frames'][$index]['offset'] +
            $offset, SEEK_SET);
        return fwrite($this->handle, $value, (($size === -1) ? strlen($value) : $size));
    }

    /**
     * Open metatable file
     * @param string
     * @param int defaults to self::READWRITE | self::STRINGS_GC
     * @return metatable|bool
     */
    public static function open($filename, $flags = 6 /* self::READWRITE | self::STRINGS_GC */)
    {
        // check flags
        if (($flags & self::READONLY) === self::READONLY &&
            ($flags & self::READWRITE) === self::READWRITE)
        {
            return FALSE;
        }

        // initialize
        $tries_left = self::LOCK_TRIES;
        $inode = @fileinode($filename);
        $handle = @fopen($filename, 'rb' .
            (($flags & self::READWRITE) === self::READWRITE ? '+' : ''));
        $locking = NULL;
        $tmp = NULL;
        $structure = array();
        $handle_filename = NULL;
        $tmp_filename = NULL;

        // open metatable file
        if ($handle) { // file exists
            while (true) {
                // try to lock
                if (!flock($handle, (($flags & self::READONLY) === self::READONLY) ?
                        LOCK_SH : LOCK_EX))
                {
                    fclose($handle);
                    return FALSE;
                }

                // check inode
                clearstatcache();
                $current_inode = fileinode($filename);
                if ($current_inode === $inode) {
                    break;
                }

                if ($tries_left <= 0) {
                    fclose($handle);
                    return FALSE;
                }

                // try to open again
                fclose($handle);
                $tries_left--;
                $inode = $current_inode;
                $handle = @fopen($filename, 'rb' .
                    (($flags & self::READWRITE) === self::READWRITE ? '+' : ''));

                if (!$handle) {
                    return FALSE;
                }
            }

        } else { // file does not exist
            if (($flags & self::READONLY) === self::READONLY) {
                return FALSE;
            }

            $handle = NULL;
        }

        // read structure
        $structure = array(
            'signature'         => self::MAGIC_STRING,
            'version'           => self::VERSION,
            'frames'            => array(),
            'frames_indexes'    => array()
        );
        $frames = array();
        if ($handle) {
            $structure = self::structure_read($handle);
        }

        // open write and temporary file
        if (($flags & self::READWRITE) === self::READWRITE) {
            $locking = $handle;

            list($tmp_filename, $tmp) = self::tmp();
            if (!$tmp_filename || !$tmp) {
                if ($locking) {
                    fclose($locking);
                }
                return FALSE;
            }

            list($handle_filename, $handle) = array($filename . '~handle', 
                @fopen($filename . '~handle', 'w+'));
            if (!$handle_filename || !$handle) {
                fclose($tmp);
                unlink($tmp_filename);
                if ($locking) {
                    fclose($locking);
                }
                return FALSE;
            }

            // copy data to temporary
            if ($locking) {
                foreach ($structure['frames'] as $i => $frame) {
                    if ($frame['name'] === self::FRAME_STRINGS)
                    {
                        $structure['frames'][$i]['used_at_start'] =
                            $structure['frames'][$i]['used'];
                        $structure['frames'][$i]['offset_at_start'] =
                            $structure['frames'][$i]['offset'];

                    }
                    if (!(($flags & self::STRINGS_GC) === self::STRINGS_GC &&
                        $frame['name'] === self::FRAME_STRINGS))
                    {
                        if (!(fseek($locking, $frame['offset'], SEEK_SET) !== -1 &&
                            fseek($handle, $frame['offset'], SEEK_SET) !== -1 &&
                            stream_copy_to_stream($locking, $handle, $frame['used'])
                                === $frame['used']))
                        {
                            fclose($locking);
                            fclose($handle);
                            unlink($handle_filename);
                            fclose($tmp);
                            unlink($tmp_filename);
                            return FALSE;
                        }
                    }
                }
            }
        }

        // create instance
        $that = new self;
        $that->flags = $flags;
        $that->filename = $filename;
        $that->handle_filename = $handle_filename;
        $that->tmp_filename = $tmp_filename;
        $that->locking = $locking;
        $that->handle = $handle;
        $that->tmp = $tmp;
        $that->structure = $structure;

        if (!isset($structure['frames_indexes'][self::FRAME_INDEXES])) {
            $that->frame_create(self::FRAME_INDEXES, 0);
        }

        if (!isset($structure['frames_indexes'][self::FRAME_STRINGS])) {
            $that->frame_create(self::FRAME_STRINGS, 1);
        }

        if (!isset($structure['frames_indexes'][self::FRAME_DATA])) {
            $that->frame_create(self::FRAME_DATA, 2);
        }

        // get indexes
        fseek($that->handle, $that->structure['frames'][$that->structure
                ['frames_indexes'][self::FRAME_INDEXES]]['offset'], SEEK_SET);

        for ($i = 0, $N = $that->structure['frames'][$that->structure
            ['frames_indexes'][self::FRAME_INDEXES]]['used'] / self::SIZEOF_INDEX_RECORD;
            $i < $N; $i++)
        {
            $data = fread($that->handle, self::SIZEOF_INDEX_RECORD);
            if (strlen($data) < self::SIZEOF_INDEX_RECORD) {
                continue;
            }

            $data = unpack(self::INDEX_RECORD_UNPACK, $data);

            $that->indexes[$data['start']] = array($data['lower'], $data['upper']);
        }

        // return
        return $that;
    }

    /**
     * Create temporary file
     * @return array 0 => filename, 1 => handle
     */
    private static function tmp()
    {
        if (!is_string(sys_get_temp_dir())) {
            return array(NULL, NULL);
        }

        $filename = tempnam(sys_get_temp_dir(), self::TEMPORARY_PREFIX);
        if (!$filename) {
            return array(NULL, NULL);
        }

        $handle = @fopen($filename, 'rb+');
        if (!$handle) {
            unlink($filename);
            return array(NULL, NULL);
        }

        return array($filename, $handle);
    }

    /**
     * Read structure data
     * @param resource
     * @return array|bool FALSE on failure
     */
    private static function structure_read($handle)
    {
        $signature_size = strlen(self::MAGIC_STRING) + self::SIZEOF_INT;

        // seek to footer
        if (fseek($handle, -$signature_size, SEEK_END) === -1) {
            return FALSE;
        }

        // try to read signature and version
        $data = fread($handle, $signature_size);
        if (strlen($data) < $signature_size) {
            return FALSE;
        }
        extract(unpack('Nversion/a' . strlen(self::MAGIC_STRING) . 'signature',
                $data));

        // check signature
        if ($signature !== self::MAGIC_STRING) {
            return FALSE;
        }

        // data
        $ret = array(
            'signature' => $signature,
            'version' => $version
        );

        switch ($version) {
            case 0:
                // get frames count
                if (fseek($handle, -($signature_size +
                            self::SIZEOF_INT), SEEK_END) === -1)
                {
                    return FALSE;
                }

                $data = fread($handle, self::SIZEOF_INT);
                if (strlen($data) < self::SIZEOF_INT) {
                    return FALSE;
                }
                extract(unpack('Nframes_count', $data));

                // read frames
                if (fseek($handle, -($signature_size + self::SIZEOF_INT *
                            ($frames_count * 3 + 1)), SEEK_END) === -1)
                {
                    return FALSE;
                }

                $ret['frames'] = array();
                $ret['frames_indexes'] = array();
                $record_size = self::SIZEOF_INT * 3;
                $offset = 0;

                for ($i = 0; $i < $frames_count; $i++) {
                    $data = fread($handle, $record_size);
                    if (strlen($data) < $record_size) {
                        return FALSE;
                    }
                    $frame = unpack('a4name/Nsize/Nused', $data);

                    $ret['frames'][$i] = $frame;
                    $ret['frames'][$i]['offset'] = $offset;
                    $offset += $frame['size'];

                    $ret['frames_indexes'][$frame['name']] = $i;
                }
            break;

            default:
                return FALSE;
        }

        return $ret;
    }

    /**
     * Write structure data to actual position in handle
     * @param resource
     * @param array
     * @return string
     */
    private static function structure_write($handle, $data = array())
    {
        $structure = '';

        switch ($data['version']) {
            case 0:
                // frames data
                foreach ($data['frames'] as $frame) {
                    $structure .= pack('a4NN', $frame['name'], $frame['size'],
                        $frame['used']);
                }

                $structure .= pack('N', count($data['frames']));
            break;

            default:
                return FALSE;
        }

        // version and signature
        $structure .= pack('Na' . strlen($data['signature']), $data['version'],
            $data['signature']);

        // write into file
        return fwrite($handle, $structure) === strlen($structure);
    }
}

/**
 * sys_get_temp_dir() for PHP < 5.2.1
 */
if (!function_exists('sys_get_temp_dir')) {
    function sys_get_temp_dir()
    {
        static $temp_dir = NULL; // precomputed temporary directory

        if ($temp_dir === NULL) {
            // try some of environment variables
            if (!empty($_ENV['TMP'])) {
                return $temp_dir = realpath($_ENV['TMP']);
            }

            if (!empty($_ENV['TMPDIR'])) {
                return $temp_dir = realpath($_ENV['TMPDIR']);
            }

            if (!empty($_ENV['TEMP'])) {
                return $temp_dir = realpath($_ENV['TEMP']);
            }

            // try tempnam()
            $tempnam = tempnam(uniqid(metatable::MAGIC_STRING, TRUE), '');
            if (file_exists($tempnam)) {
                unlink($tempnam);
                return $temp_dir = realpath(dirname($tempnam));
            }

            return $temp_dir = FALSE;
        }

        return $temp_dir;
    }
}
