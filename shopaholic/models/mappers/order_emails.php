<?php
final class order_emails extends mapper
{
    /**
     * @var string Base query
     */
    private $query = '
        SELECT [order_emails].[sent_at] AS [sent_at],
            [order_emails].[subject] AS [subject],
            [order_emails].[body] AS [body]
        FROM [:prefix:order_emails] AS [order_emails]';

    /**
     * Find by order id
     * @return array
     */
    public function findByOrderId($order_id)
    {
        return $this->poolResults(dibi::query($this->query,
            'WHERE [order_emails].[order_id] = %i', intval($order_id)));
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
            $ret[] = new order_email((array) $row);
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
            $values['order_id'] = intval($values['order_id']);
            $values['sent_at'] = new DibiVariable('NOW()', 'sql');
            dibi::query('INSERT INTO [:prefix:order_emails]', $values);
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }
}
