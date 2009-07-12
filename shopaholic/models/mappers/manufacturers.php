<?php
final class manufacturers extends mapper
{
    /**
     * @var string Base query
     */
    private $query;

    /**
     * @var array All manufacturers
     */
    private $all = NULL;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->query = '
            SELECT
                [manufacturers].[id] AS [id],
                [pages].[name] AS [name],
                [pages].[nice_name] AS [nice_name],
                [pages].[content] AS [description],
                [pages].[meta_keywords] AS [meta_keywords],
                [pages].[meta_description] AS [meta_description],
                [pictures].[id] AS [picture_id],
                [pictures].[file] AS [picture_file],
                [pictures].[description] AS [picture_description],
                [pictures].[thumbnail_id] AS [thumbnail_id],
                [thumbnails].[file] AS [thumbnail_file],
                [thumbnails].[description] AS [thumbnail_description]
            FROM [:prefix:manufacturers] AS [manufacturers]
            LEFT JOIN [:prefix:pages] AS [pages]
                ON [pages].[ref_id] = [manufacturers].[id] AND
                   [pages].[ref_type] = \'' . pages::MANUFACTURER . '\'
            LEFT JOIN [:prefix:pictures] AS [pictures]
                ON [pages].[picture_id] = [pictures].[id]
            LEFT JOIN [:prefix:pictures] AS [thumbnails]
                ON [pictures].[thumbnail_id] = [thumbnails].[id]';
    }

    /**
     * Find all
     * @return array
     */
    public function findAll()
    {
        if ($this->all === NULL) {
            $this->all = $this->poolResults(dibi::query($this->query,
                'ORDER BY [pages].[nice_name]'));
        }
        return $this->all;
    }

    /**
     * Find by id
     * @param int
     * @return manufacturer
     */
    public function findById($id)
    {
        if (!isset($this->pool[$id])) {
            $ret = $this->poolResults(dibi::query($this->query,
                'WHERE [manufacturers].[id] = %i', $id));
            $this->pool[$id] = isset($ret[0]) ? $ret[0] : NULL;
        }

        return $this->pool[$id];
    }

    /**
     * Find by nice name
     * @param int
     * @return manufacturer
     */
    public function findByNiceName($nice_name)
    {
        $ret = $this->poolResults(dibi::query($this->query,
            'WHERE [pages].[nice_name] = %s', $nice_name));
        return isset($ret[0]) ? $ret[0] : NULL;
    }

    /**
     * Count all
     * @return int
     */
    public function countAll()
    {
        try {
            return dibi::query('SELECT COUNT(*) FROM [:prefix:manufacturers]')->fetchSingle();
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * Add results to pool
     * @param DibiResult
     * @return array
     */
    public function poolResults($result)
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

            $this->pool[$row->id] = new manufacturer($arr);
            $ret[] = $this->pool[$row->id];
        }

        return $ret;
    }

    /**
     * Insert manufacturer
     * @param array
     */
    public function insertOne(array $values)
    {
        try {
            dibi::begin();
            dibi::query('INSERT INTO [:prefix:manufacturers] ([id]) VALUES (NULL)');
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
            $values['ref_id'] = dibi::query('SELECT LAST_INSERT_ID()')->fetchSingle();
            $values['ref_type'] = pages::MANUFACTURER;
            dibi::query('INSERT INTO [:prefix:pages]', $values);
            dibi::commit();
            return TRUE;
        } catch (Exception $e) {
            dibi::rollback();
            return FALSE;
        }
    }

    /**
     * Update manufacturer
     * @param array
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
        $ref_id = $values['id'];
        unset($values['id']);
        unset($values['nice_name']);

        return dibi::query('UPDATE [:prefix:pages] SET', $values,
            'WHERE [ref_id] = %i AND [ref_type] = %s', $ref_id, pages::MANUFACTURER
        );
    }

    /**
     * Delete one
     */
    public function deleteOne(manufacturer $m)
    {
        try {
            dibi::query('DELETE FROM [:prefix:manufacturers]',
               'WHERE [id] = %i', $m->getId(),
               'LIMIT 1');
            dibi::query('DELETE FROM [:prefix:pages]',
                'WHERE [nice_name] = %s', $m->getNiceName(),
                'LIMIT 1');
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }
}
