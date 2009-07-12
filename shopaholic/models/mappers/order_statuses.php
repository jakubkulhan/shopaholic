<?php
final class order_statuses extends mapper
{
    /**
     * @var string Base query
     */
    private $query = '
        SELECT [order_statuses].[id] AS [id],
            [order_statuses].[name] AS [name],
            [order_statuses].[initial] AS [initial]
        FROM [:prefix:order_statuses] AS [order_statuses]';

    /**
     * Find by id
     * @param int
     * @return order_status
     */
    public function findById($id)
    {
        $ret = $this->poolResults(dibi::query($this->query,
            'WHERE [id] = %i', $id));
        return isset($ret[0]) ? $ret[0] : 0;
    }

    /**
     * Find initial
     * @return order_status
     */
    public function findInitial()
    {
        $ret = $this->poolResults(dibi::query($this->query,
            'WHERE [initial] = %b', TRUE));
        return isset($ret[0]) ? $ret[0] : 0;
    }

    /**
     * Find all
     * @return array
     */
    public function findAll()
    {
        static $all = NULL;
        if ($all === NULL) {
            return $all = $this->poolResults(dibi::query($this->query,
                    'ORDER BY [initial] DESC, [name]'));
        }
        return $all;
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
            $row->initial = (bool) $row->initial;
            $this->pool[$row->id] = new order_status((array) $row);
            $ret[] = $this->pool[$row->id];
        }

        return $ret;
    }

    /**
     * Insert new
     * @param array
     * @return bool
     */
    public function insertOne(array $values)
    {
        try {
            $values['initial'] = (bool) $values['initial'];
            dibi::begin();
            if ($values['initial']) {
                dibi::query('UPDATE [:prefix:order_statuses] SET [initial] = %b', FALSE);
            }
            dibi::query('INSERT INTO [:prefix:order_statuses]', $values);
            dibi::commit();
            return TRUE;
        } catch (Excetion $e) {
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
            $values['initial'] = (bool) $values['initial'];
            dibi::begin();
            if ($values['initial']) {
                dibi::query('UPDATE [:prefix:order_statuses] SET [initial] = %b', FALSE);
            }
            $id = intval($values['id']); unset($values['id']);
            dibi::query('UPDATE [:prefix:order_statuses] SET', $values,
                'WHERE [id] = %i', $id);
            dibi::commit();
            return TRUE;
        } catch (Excetion $e) {
            return FALSE;
        }
    }
}