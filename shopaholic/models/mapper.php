<?php
/**
 * Mapper
 */
abstract class mapper extends /*Nette\*/Object
{
    /**
     * @var array Mapper objects
     */
    private static $mappers = array();

    /**
     * @var array Mappers' domain objects
     */
    private static $domains = array();

    /**
     * @var array Pool of fetched objects
     */
    protected $pool = array();

    /**
     * Get mapper
     * @param string
     * @return mixed
     */
    public static function object($name)
    {
        if (!isset(self::$mappers[$name])) {
            self::$mappers[$name] = new $name;
        }

        return self::$mappers[$name];
    }

    /**
     * Convert mapper to domain name
     * @param string
     * @return string
     */
    private static function domainize($mapper)
    {
        if (isset(self::$domains[$mapper])) {
            return self::$domains[$mapper];
        }

        if ($mapper[strlen($mapper) - 1] === 's') {
            return substr($mapper, 0, strlen($mapper) - 1);
        }

        throw new /*\*/Exception("Cannot find domain for $mapper.");
    }

    /**
     * Add/change mappers' domain
     * @param string
     * @param string
     */
    private static function mapperDomain($mapper, $domain)
    {
        self::$domains[$mapper] = $domain;
    }

    /**
     * Call to undefined static method
     * @param string
     * @param array
     * @return mapper
     */
    public static function __callStatic($name, $args)
    {
        return self::object($name);
    }

    /**
     * product_availabilities mapper
     * @return product_availabilities
     */
    public static function product_availabilities()
    {
        return self::object('product_availabilities');
    }

    /**
     * categories mapper
     * @return categories
     */
    public static function categories()
    {
        return self::object('categories');
    }

    /**
     * manufacturers mapper
     * @return manufacturers
     */
    public static function manufacturers()
    {
        return self::object('manufacturers');
    }

    /**
     * order_delivery_types mapper
     * @return order_delivery_types
     */
    public static function order_delivery_types()
    {
        return self::object('order_delivery_types');
    }


    /**
     * order_payment_types mapper
     * @return order_payment_types
     */
    public static function order_payment_types()
    {
        return self::object('order_payment_types');
    }

    /**
     * order_statuses mapper
     * @return order_statuses
     */
    public static function order_statuses()
    {
        return self::object('order_statuses');
    }

    /**
     * orders mapper
     * @return orders
     */
    public static function orders()
    {
        return self::object('orders');
    }

    /**
     * pages mapper
     * @return pages
     */
    public static function pages()
    {
        return self::object('pages');
    }

    /**
     * pictures mapper
     * @return pictures
     */
    public static function pictures()
    {
        return self::object('pictures');
    }

    /**
     * products mapper
     * @return products
     */
    public static function products()
    {
        return self::object('products');
    }


    /**
     * Should not be constructed outside of mapper
     */
    protected function __construct()
    {
    }

    /**
     * Primary key getter
     * @return string
     */
    public function getPK()
    {
        return 'id';
    }

    /**
     * Table name getter
     * @return string
     */
    public function getTable()
    {
        return get_class($this);
    }

    /**
     * Domain object class getter
     * @return string
     */
    public function getDomain()
    {
        return self::domainize(get_class($this));
    }

    /**
     * Reset pool
     */
    public function resetPool()
    {
        $this->pool = array();
    }

    /**
     * Update data in DB
     * @param array
     */
    protected function update(array $data)
    {
        $pk = $data[$this->getPK()];
        unset($data[$this->getPK()]);

        return dibi::query(
            'UPDATE %n', ':prefix:' . $this->getTable(),
            'SET', $data,
            'WHERE %n=%i', $this->getPK(), $pk
        );
    }

    /**
     * Insert data into DB
     * @param array
     */
    protected function insert(array $data)
    {
        return dibi::query(
            'INSERT INTO %n', ':prefix:' . $this->getTable(), $data
        );
    }

    /**
     * Delete from DB
     * @param array
     */
    protected function delete(array $where)
    {
        return dibi::query(
            'DELETE FROM %n', ':prefix:' . $this->getTable(),
            'WHERE', $where
        );
    }
}
