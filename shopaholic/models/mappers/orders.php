<?php
final class orders extends mapper
{
    /**
     * @var string Base query
     */
    private $query = '
        SELECT
            [orders].[id] AS [id],
            [order_delivery_types].[id] AS [delivery_type_id],
            [order_delivery_types].[name] AS [delivery_type_name],
            [order_delivery_types].[price] AS [delivery_type_price],
            [order_payment_types].[id] AS [payment_type_id],
            [order_payment_types].[name] AS [payment_type_name],
            [order_payment_types].[price] AS [payment_type_price],
            [order_statuses].[id] AS [status_id],
            [order_statuses].[name] AS [status_name],
            [order_statuses].[initial] AS [status_initial],
            [orders].[payer_name] AS [payer_name],
            [orders].[payer_lastname] AS [payer_lastname],
            [orders].[payer_company] AS [payer_company],
            [orders].[payer_street] AS [payer_street],
            [orders].[payer_city] AS [payer_city],
            [orders].[payer_postcode] AS [payer_postcode],
            [orders].[delivery_name] AS [delivery_name],
            [orders].[delivery_lastname] AS [delivery_lastname],
            [orders].[delivery_company] AS [delivery_company],
            [orders].[delivery_street] AS [delivery_street],
            [orders].[delivery_city] AS [delivery_city],
            [orders].[delivery_postcode] AS [delivery_postcode],
            [orders].[email] AS [email],
            [orders].[phone] AS [phone],
            [orders].[comment] AS [comment],
            [orders].[at] AS [at]
        FROM [:prefix:orders] AS [orders]
        LEFT JOIN [:prefix:order_delivery_types] AS [order_delivery_types]
            ON [orders].[delivery_type_id] = [order_delivery_types].[id]
        LEFT JOIN [:prefix:order_payment_types] AS [order_payment_types]
            ON [orders].[payment_type_id] = [order_payment_types].[id]
        LEFT JOIN [:prefix:order_statuses] AS [order_statuses]
            ON [orders].[status_id] = [order_statuses].[id]';

    /**
     * Find by id
     * @param int
     * @return order
     */
    public function findById($id)
    {
        if (!isset($this->pool[$id])) {
            $ret = $this->poolResults(dibi::query($this->query,
                'WHERE [orders].[id] = %i', $id));
            $this->pool[$id] = isset($ret[0]) ? $ret[0] : NULL;
        }

        return $this->pool[$id];
    }

    /**
     * Find by status id
     * @param int
     * @return order[]
     */
    public function findByStatusId($id)
    {
        try {
            return $this->poolResults(dibi::query($this->query,
                'WHERE [order_statuses].[id] = %i', $id,
                'ORDER BY [orders].[at]'));
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Find products with prices and amounts
     * @param order
     * @return array
     */
    public function findProducts(order $order)
    {
        try {
            $result = dibi::query('SELECT [product_id], [price], [amount]',
                'FROM [:prefix:orders_products]',
                'WHERE [order_id] = %i', $order->getId());
            $ids = array();
            $ret = array();
            foreach ($result as $row) {
                $ids[] = $row->product_id;
                $arr = (array) $row; unset($arr['product_id']);
                $ret[$row->product_id] = $arr;
            }

            foreach (mapper::products()->findByIds($ids) as $product) {
                $ret[$product->getId()]['product'] = $product;
                $ret[$product->getId()] = (object) $ret[$product->getId()];
            }

            return $ret;

        } catch (Exception $e) {
            var_dump($e);
            exit();
            return FALSE;
        }
    }

    /**
     * Count all
     * @return int
     */
    public function countAll()
    {
        try {
            return dibi::query('SELECT COUNT(*) FROM [:prefix:orders]')->fetchSingle();
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * Count with initial status
     * @return int
     */
    public function countWithInitialStatus()
    {
        try {
            $status_id = dibi::query('SELECT [id] FROM [:prefix:order_statuses] WHERE [initial] = %b', TRUE)->fetchSingle();
            return dibi::query('SELECT COUNT(*) FROM [:prefix:orders] WHERE [status_id] = %i', $status_id)->fetchSingle();
        } catch (Exception $e) {
            var_dump($e);
            return NULL;
        }
    }

    /**
     * Save given order
     * @param order
     * @param array
     * @return bool
     */
    public function save(order $order, array $products)
    {
        // order data
        $data = $order->__toArray();
        $data['at'] = date('Y-m-d H:i:s', time());
        $data['delivery_type_id'] = $data['delivery_type']->getId();
        unset($data['delivery_type']);
        $data['payment_type_id'] = $data['payment_type']->getId();
        unset($data['payment_type']);
        $data['status_id'] = $data['status']->getId();
        unset($data['status']);

        // start transaction
        dibi::begin();
        try {
            $this->insert($data);
            $order_id = dibi::query('SELECT LAST_INSERT_ID()')->fetchSingle();
            $order->setId($order_id);
            $order->dirty(order::UNDIRT);
            foreach (mapper::products()->findByIds(array_keys($products)) as $product) {
                dibi::query('INSERT INTO [:prefix:orders_products]', array(
                    'order_id' => $order_id,
                    'product_id' => $product->getId(),
                    'price' => $product->getPrice(),
                    'amount' => $products[$product->getId()]
                ));
            }

            $mail = new Mail;
            $mail->setFrom(Environment::expand('%shopEmail%'));
            $mail->addTo($data['email']);
            $mail->setSubject(__('Your order at %s has been accepted', Environment::expand('%shopName%')));
            $mail->setBody(str_replace('\n', "\n", __('Hello, your order has been accepted.')));
            $mail->send();
        } catch (Exception $e) {
            dibi::rollback();
            return FALSE;
        }

        dibi::commit();
        return TRUE;
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
            // delivery type
            $delivery_type = array(
                'id' => $arr['delivery_type_id'],
                'name' => $arr['delivery_type_name'],
                'price' => $arr['delivery_type_price']
            );
            unset($arr['delivery_type_id'], $arr['delivery_type_name'], $arr['delivery_type_price']);
            $arr['delivery_type'] = new order_delivery_type($delivery_type);

            // payment type
            $payment_type = array(
                'id' => $arr['payment_type_id'],
                'name' => $arr['payment_type_name'],
                'price' => $arr['payment_type_price']
            );
            unset($arr['payment_type_id'], $arr['payment_type_name'], $arr['payment_type_price']);
            $arr['payment_type'] = new order_payment_type($payment_type);

            // status
            $status = array(
                'id' => $arr['status_id'],
                'name' => $arr['status_name'],
                'initial' => $arr['status_initial']
            );
            unset($arr['status_id'], $arr['status_name'], $arr['status_initial']);
            $arr['status'] = new order_status($status);

            $this->pool[$row->id] = new order($arr);
            $ret[] = $this->pool[$row->id];
        }

        return $ret;
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
            dibi::query('UPDATE [:prefix:orders] SET', $values,
                'WHERE [id] = %i', $id);
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }
}
