<?php
final class order_payment_types extends mapper
{
    /**
     * @var string Base query
     */
    private $query = '
        SELECT [order_payment_types].[id] AS [id],
            [order_payment_types].[name] AS [name],
            [order_payment_types].[price] AS [price]
        FROM [:prefix:order_payment_types] AS [order_payment_types]';

    /**
     * Find by id
     * @param int
     * @return order_payment_type
     */
    public function findById($id)
    {
        $ret = $this->poolResults(dibi::query($this->query,
            'WHERE [id] = %i', $id));
        return isset($ret[0]) ? $ret[0] : 0;
    }

    /**
     * Find all
     * @return array
     */
    public function findAll()
    {
        return $this->poolResults(dibi::query($this->query));
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
            $this->pool[$row->id] = new order_payment_type((array) $row);
            $ret[] = $this->pool[$row->id];
        }

        return $ret;
    }

    /**
     * Insert
     * @param array
     * @return bool
     */
    public function insertOne(array $values)
    {
        try {
            $values['price'] = intval($values['price']);
            dibi::query('INSERT INTO [:prefix:order_payment_types]', $values);
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
            $values['price'] = intval($values['price']);
            dibi::query('UPDATE [:prefix:order_payment_types] SET', $values,
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
            $id = intval($id);
            dibi::query('DELETE FROM [:prefix:order_payment_types]',
                'WHERE [id] = %i', $id);
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }
}
