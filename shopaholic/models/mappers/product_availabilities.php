<?php
final class product_availabilities extends mapper
{
    /**
     * Base query
     */
    private $query = '
        SELECT [product_availabilities].[id] AS [id],
            [product_availabilities].[name] AS [name]
        FROM [:prefix:product_availabilities] AS [product_availabilities]';

    /**
     * Find all
     * @return array
     */
    public function findAll()
    {
        return $this->poolResults(dibi::query($this->query, 'ORDER BY [name]'));
    }

    /**
     * Find by id
     * @param int
     * @return product_availability
     */
    public function findById($id)
    {
        if (!isset($this->pool[$id])) {
            $ret = $this->poolResults(dibi::query($this->query,
                'WHERE [id] = %i', $id));
            $this->pool[$id] = isset($ret[0]) ? $ret[0] : NULL;
        }
        return $this->pool[$id];
    }

    /**
     * Pool results
     * @param DibiResult
     * @return array
     */
    public function poolResults(DibiResult $results)
    {
        $ret = array();
        foreach ($results as $row) {
            $this->pool[$row->id] = new product_availability((array) $row);
            $ret[] = $this->pool[$row->id];
        }
        return $ret;
    }

    /**
     * Insert new availavility
     * @param array
     * @return bool
     */
    public function insertOne(array $values)
    {
        try {
            dibi::query('INSERT INTO [:prefix:product_availabilities]', $values);
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Update
     * @param array
     * @return bool
     */
    public function updateOne(array $values)
    {
        try {
            $id = intval($values['id']); unset($values['id']);
            dibi::query('UPDATE [:prefix:product_availabilities] SET', $values,
                'WHERE [id] = %i', $id);
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Delete
     * @param int
     * @return bool
     */
    public function deleteOne($id)
    {
        try {
            dibi::query('DELETE FROM [:prefix:product_availabilities]',
                'WHERE [id] = %i', $id);
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }
}