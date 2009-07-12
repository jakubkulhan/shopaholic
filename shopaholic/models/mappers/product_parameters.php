<?php
final class product_parameters extends mapper
{
    /**
     * @var string Base query
     */
    private $query =
            'SELECT [names].[id] AS [id],
                [names].[name] AS [name],
                [names].[title] AS [title],
                [parameters].[value] AS [value],
                [parameters].[product_id] AS [product_id]
             FROM [:prefix:products_parameters] AS [parameters]
             LEFT JOIN [:prefix:product_parameters] AS [names]
                ON [parameters].[product_parameter_id] = [names].[id]';

    /**
     * Preload by product id(s)
     * @param int|array
     */
    public function preloadByProductIds($product_id)
    {
        if (!is_array($product_id)) {
            $product_id = func_get_args();
        }

        if (empty($product_id)) {
            return ;
        }

        // build WHERE
        $where = array();
        foreach ($product_id as $id) {
            if (!empty($where)) {
                $where[] = 'OR';
            }
            $where[] = '[parameters].[product_id] =';
            $where[] = $id;

            $this->pool[$id] = array();
        }

        // query
        $this->poolResults(dibi::query($this->query, 'WHERE %ex', $where));
    }

    /**
     * Find by product id
     * @param int
     * @return array
     */
    public function findByProductId($product_id)
    {
        if (!isset($this->pool[$product_id])) {
            $this->pool[$product_id] = array();
            $this->poolResults(dibi::query($this->query,
                'WHERE [parameters].[product_id] = %i', $product_id));
        }

        return $this->pool[$product_id];
    }

    /**
     * Build and save in pool
     * @param DibiResult
     */
    private function poolResults($result)
    {
        // create tmp pool
        $tmp_pool = array();

        foreach ($result as $row) {
            $row = (array) $row;
            $product_id = $row['product_id'];
            unset($row['product_id']);

            if (!isset($tmp_pool[$product_id])) {
                $tmp_pool[$product_id] = array();
            }

            $tmp_pool[$product_id][] = new product_parameter($row);
        }

        // copy from tmp to pool
        foreach ($tmp_pool as $product_id => $parameters) {
            $this->pool[$product_id] = $parameters;
        }
    }

    /**
     * Remove from pool
     * @param int
     */
    public function resetByProductId($product_id)
    {
        unset($this->pool[$product_id]);
    }
}
