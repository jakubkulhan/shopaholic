<?php
final class pictures extends mapper
{
    /**
     * @var string Base query
     */
    private $query = '
            SELECT
                [pictures].[id] AS [id],
                [pictures].[file] AS [file],
                [pictures].[description] AS [description],
                [pictures].[thumbnail_id] AS [thumbnail_id],
                [thumbnails].[file] AS [thumbnail_file],
                [thumbnails].[description] AS [thumbnail_description]
            FROM [:prefix:pictures] AS [pictures]
            LEFT JOIN [:prefix:pictures] AS [thumbnails]
                ON [pictures].[thumbnail_id] = [thumbnails].[id]';

    /**
     * Find all
     * @return array
     */
     public function findAll()
     {
         return $this->poolResults(dibi::query($this->query,
            'WHERE [pictures].[thumbnail_id] IS NOT NULL',
            'ORDER BY [pictures].[file]'));
     }

    /**
     * Find by id
     * @param int
     * @return category
     */
    public function findById($id)
    {
        if (!isset($this->pool[$id])) {
            $ret = $this->poolResults(dibi::query($this->query,
                'WHERE [pictures].[id] = %i', $id));
            $this->pool[$id] = isset($ret[0]) ? $ret[0] : NULL;
        }

        return $this->pool[$id];
    }

    /**
     * Count all
     * @return int
     */
    public function countAll()
    {
        try {
            return dibi::query('SELECT COUNT(*) FROM [:prefix:pictures]')->fetchSingle();
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * Add results to pool
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

            $arr['thumbnail'] = is_null($row->thumbnail_id) ? NULL :
                new picture($thumbnail);

            $this->pool[$row->id] = new picture($arr);
            $ret[] = $this->pool[$row->id];
        }

        return $ret;
    }

    /**
     * Insert new picture
     */
     public function insertOne($file, $thumbnail_file, $description = NULL)
     {
         if (empty($description)) {
             $description = NULL;
         }

         try {
            dibi::query('INSERT INTO [:prefix:pictures] ([file], [description], [thumbnail_id])',
                 'VALUES (%s, NULL, NULL)', $thumbnail_file);
            $thumbnail_id = dibi::query('SELECT LAST_INSERT_ID()')->fetchSingle();
            dibi::query('INSERT INTO [:prefix:pictures] ([file], [description], [thumbnail_id])',
                 'VALUES (%s, %sn, %i)', $file, $description, $thumbnail_id);
             return TRUE;
         } catch (Exception $e) {
             return FALSE;
         }
     }

     /**
      * Delete by id
      */
     public function deleteOne($id)
     {
         try {
             dibi::query('DELETE FROM [:prefix:pictures]',
                 'WHERE [id] = %i', $id,
                 'LIMIT 1');
             return TRUE;
         } catch (Exception $e) {
             return FALSE;
         }
     }
}
