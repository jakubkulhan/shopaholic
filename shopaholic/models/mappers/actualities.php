<?php
final class actualities extends mapper
{
    /**
     * @var string Base query
     */
    private $query;
   
    public function __construct()
    {
        $this->query = '
            SELECT 
                [actualities].[id] AS [id],
                [actualities].[added_at] AS [added_at],
                [pages].[nice_name] AS [nice_name],
                [pages].[name] AS [name],
                [pages].[content] AS [content],
                [pages].[meta_keywords] AS [meta_keywords],
                [pages].[meta_description] AS [meta_description],
                [pictures].[id] AS [picture_id],
                [pictures].[file] AS [picture_file],
                [pictures].[description] AS [picture_description],
                [pictures].[thumbnail_id] AS [thumbnail_id],
                [thumbnails].[file] AS [thumbnail_file],
                [thumbnails].[description] AS [thumbnail_description]
            FROM [:prefix:actualities] AS [actualities]
            LEFT JOIN [:prefix:pages] AS [pages]
                ON [pages].[ref_id] = [actualities].[id]
                AND [pages].[ref_type] = \'' . pages::ACTUALITY . '\'
            LEFT JOIN [:prefix:pictures] AS [pictures]
                ON [pages].[picture_id] = [pictures].[id]
            LEFT JOIN [:prefix:pictures] AS [thumbnails]
                ON [pictures].[thumbnail_id] = [thumbnails].[id]
            ORDER BY [actualities].[added_at] DESC';
    }

    /**
     * Find by id
     * @param int
     * @return actuality
     */
    public function findById($id)
    {
        $ret = $this->poolResults(dibi::query('
            SELECT 
                [actualities].[id] AS [id],
                [actualities].[added_at] AS [added_at],
                [pages].[nice_name] AS [nice_name],
                [pages].[name] AS [name],
                [pages].[content] AS [content],
                [pages].[meta_keywords] AS [meta_keywords],
                [pages].[meta_description] AS [meta_description],
                [pictures].[id] AS [picture_id],
                [pictures].[file] AS [picture_file],
                [pictures].[description] AS [picture_description],
                [pictures].[thumbnail_id] AS [thumbnail_id],
                [thumbnails].[file] AS [thumbnail_file],
                [thumbnails].[description] AS [thumbnail_description]
            FROM [:prefix:actualities] AS [actualities]
            LEFT JOIN [:prefix:pages] AS [pages]
                ON [pages].[ref_id] = [actualities].[id]
                AND [pages].[ref_type] = \'' . pages::ACTUALITY . '\'
            LEFT JOIN [:prefix:pictures] AS [pictures]
                ON [pages].[picture_id] = [pictures].[id]
            LEFT JOIN [:prefix:pictures] AS [thumbnails]
            ON [pictures].[thumbnail_id] = [thumbnails].[id]
            WHERE [actualities].[id] = %i', $id, '
            ORDER BY [actualities].[added_at] DESC'));

        if (!isset($ret[0])) return NULL;
        return $ret[0];
    }

    /**
     * Find all
     * @return actuality[]
     */
    public function findAll()
    {
        return $this->poolResults(dibi::query($this->query));
    }

    /**
     * Latest
     * @param int limit, NULL means no limit
     * @return actuality[]
     */
    public function findLatest($limit = NULL)
    {
        if ($limit === NULL) return $this->findAll();
        return $this->poolResults(dibi::query(array(
            $this->query,
            'LIMIT', $limit
        )));
    }

    /**
     * Pool results
     * @param DibiResult
     * @return array
     */
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
            $this->pool[$row->nice_name] = new actuality($arr);
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
        try {
            dibi::begin();

            $id = dibi::query('SELECT [ref_id] FROM [:prefix:pages]',
                'WHERE [nice_name] = %s', $nice_name)->fetchSingle();
            dibi::query('DELETE FROM [:prefix:pages]',
                'WHERE [nice_name] = %s', $nice_name);
            dibi::query('DELETE FROM [:prefix:actualities]',
                'WHERE [id] = %i', $id);

            dibi::commit();
            return TRUE;

        } catch (Exception $e) {
            dibi::rollback();
            return FALSE;
        }
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

        try {
            dibi::begin();

            dibi::query('INSERT INTO [:prefix:actualities]', array(
                'added_at' => new DibiVariable('NOW()', 'sql')
            ));

            $id = intval(dibi::query('SELECT LAST_INSERT_ID()')->fetchSingle());
            $values['ref_id'] = $id;
            $values['ref_type'] = pages::ACTUALITY;

            dibi::query('INSERT INTO [:prefix:pages]', $values);

            dibi::commit();
            return TRUE;

        } catch (Exception $e) {
            dibi::rollback();
            return FALSE;
        }
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
        unset($values['id'], $values['nice_name'], $values['added_at']);

        try {
            dibi::query('UPDATE [:prefix:pages] SET', $values,
                'WHERE [nice_name] = %s', $nice_name,
                'LIMIT 1');

            return TRUE;

        } catch (Exception $e) {
            return FALSE;
        }
    }
}
