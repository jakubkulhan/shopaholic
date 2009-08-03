<?php
final class pages extends mapper
{
    /**
     * It is product
     */
    const PRODUCT = 'P';

    /**
     * It is category
     */
    const CATEGORY = 'C';

    /**
     * It is manufacturer
     */
    const MANUFACTURER = 'M';

    /**
     * It is actuality
     */
    const ACTUALITY = 'A';

    /**
     * @var array Not refs
     */
    private $not_ref = NULL;

    /**
     * @var array Convert types to mapper
     */
    private static $type2mapper = array(
        self::PRODUCT => 'products',
        self::CATEGORY => 'categories',
        self::MANUFACTURER => 'manufacturers',
        self::ACTUALITY => 'actualities'
    );

    /**
     * @var string Base query
     */
    private $query = '
        SELECT [pages].[nice_name] AS [nice_name],
            [pages].[name] AS [name],
            [pages].[content] AS [content],
            [pages].[meta_keywords] AS [meta_keywords],
            [pages].[meta_description] AS [meta_description],
            [pictures].[id] AS [picture_id],
            [pictures].[file] AS [picture_file],
            [pictures].[description] AS [picture_description],
            [pictures].[thumbnail_id] AS [thumbnail_id],
            [thumbnails].[file] AS [thumbnail_file],
            [thumbnails].[description] AS [thumbnail_description],
            ([pages].[ref_id] IS NOT NULL) AS [is_reference],
            [pages].[ref_id] AS [ref_id],
            [pages].[ref_type] AS [ref_type]
        FROM [:prefix:pages] AS [pages]
        LEFT JOIN [:prefix:pictures] AS [pictures]
            ON [pages].[picture_id] = [pictures].[id]
        LEFT JOIN [:prefix:pictures] AS [thumbnails]
            ON [pictures].[thumbnail_id] = [thumbnails].[id]';

    /**
     * Find all
     * @return page[]
     */
    public function findAll()
    {
        return $this->poolResults(dibi::query($this->query));
    }

    /**
     * Find not referencing
     */
    public function findNotRef()
    {
        if ($this->not_ref === NULL) {
            $this->not_ref = $this->poolResults(dibi::query($this->query,
                'WHERE [pages].[ref_id] IS NULL',
                'ORDER BY [pages].[nice_name]'));
        }

        return $this->not_ref;
    }

    /**
     *
     * @param string
     * @return page
     */
    public function findByNiceName($nice_name)
    {
        if (!isset($this->pool[$nice_name])) {
            $ret = $this->poolResults(dibi::query($this->query,
                'WHERE [pages].[nice_name] = %s', $nice_name,
                'LIMIT 1'));
            $this->pool[$nice_name] = isset($ret[0]) ? $ret[0] : NULL;
        }

        return $this->pool[$nice_name];
    }

    /**
     * Count not ref
     * @return int
     */
    public function countNotRef()
    {
        try {
            return dibi::query('SELECT COUNT(*) FROM [:prefix:pages] WHERE [ref_id] IS NULL')->fetchSingle();
        } catch (Exception $e) {
            return NULL;
        }
    }

    private function poolResults($result)
    {
        if (!$result) {
            return array();
        }

        $ret = array();

        foreach ($result as $row) {
            $arr = (array) $row;
            $thumbnail = array();
            $thumbnail['id'] = $row->thumbnail_id;
            $thumbnail['file'] = $row->thumbnail_file;
            $thumbnail['description'] = $row->thumbnail_description;
            $thumbnail['thumbnail'] = NULL;
            unset($arr['thumbnail_id'], $arr['thumbnail_file'], $arr['thumbnail_description']);

            $picture = array();
            $picture['id'] = $row->picture_id;
            $picture['file'] = $row->picture_file;
            $picture['description'] = $row->picture_description;
            $picture['thumbnail'] = is_null($row->thumbnail_id) ? NULL :
                new picture($thumbnail);
            unset($arr['picture_id'], $arr['picture_file'], $arr['picture_description']);

            $arr['picture'] = is_null($row->picture_id) ? NULL :
                new picture($picture);
            $arr['ref'] = ($row->is_reference && isset(self::$type2mapper[$row->ref_type])) ?
                self::object(self::$type2mapper[$row->ref_type])->findById($row->ref_id) :
                NULL;
            unset($arr['is_reference'], $arr['ref_id'], $arr['ref_type']);
            $this->pool[$row->nice_name] = new page($arr);
            $ret[] = $this->pool[$row->nice_name];
        }

        return $ret;
    }

    /**
     * Primary key
     * @return string
     */
    public function getPK()
    {
        return 'nice_name';
    }

    /**
     * Deletes page by given nice name
     * @param string
     * @return something
     */
    public function deleteOne($nice_name)
    {
        return parent::delete(array('nice_name' => $nice_name));
    }

    /**
     * Inserts page
     * @param array
     * @return someting
     */
    public function insertOne(array $values)
    {
        if (empty($values['meta_keywords'])) {
            $values['meta_keywords'] = NULL;
        }

        if (empty($values['meta_description'])) {
            $values['meta_description'] = NULL;
        }
        if (empty($values['content'])) {
            $values['content'] = NULL;
        }
        if (empty($values['picture_id'])) {
            $values['picture_id'] = NULL;
        }

        return $this->insert($values);
    }

    /**
     * Updates page
     * @param array
     * @return someting
     */
    public function updateOne(array $values)
    {
        if (empty($values['meta_keywords'])) {
            $values['meta_keywords'] = NULL;
        }

        if (empty($values['meta_description'])) {
            $values['meta_description'] = NULL;
        }
        if (empty($values['content'])) {
            $values['content'] = NULL;
        }
        if (empty($values['picture_id'])) {
            $values['picture_id'] = NULL;
        }

        $nice_name = $values['nice_name'];
        unset($values['nice_name']);
        return dibi::query('UPDATE [:prefix:pages] SET', $values,
            'WHERE [nice_name] = %s', $nice_name,
            'LIMIT 1');
    }
}
