<?php
final class products extends mapper
{
    /**
     * @var string Base query
     */
    private $query;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->query ='
            SELECT [products].[id] AS [id],
                [products].[code] AS [code],
                [pages].[name] AS [name],
                [pages].[nice_name] AS [nice_name],
                [pages].[content] AS [description],
                [pages].[meta_keywords] AS [meta_keywords],
                [pages].[meta_description] AS [meta_description],
                [products].[price] AS [price],
                [pictures].[id] AS [picture_id],
                [pictures].[file] AS [picture_file],
                [pictures].[description] AS [picture_description],
                [pictures].[thumbnail_id] AS [thumbnail_id],
                [thumbnails].[file] AS [thumbnail_file],
                [thumbnails].[description] AS [thumbnail_description],
                [product_availabilities].[id] AS [availability_id],
                [product_availabilities].[name] AS [availability_name]
            FROM [:prefix:products] AS [products]
            LEFT JOIN [:prefix:pages] AS [pages]
                ON [pages].[ref_id] = [products].[id] AND
                   [pages].[ref_type] = \'' . pages::PRODUCT . '\'
            LEFT JOIN [:prefix:product_availabilities] AS [product_availabilities]
                ON [products].[availability_id] = [product_availabilities].[id]
            LEFT JOIN [:prefix:pictures] AS [pictures]
                ON [pages].[picture_id] = [pictures].[id]
            LEFT JOIN [:prefix:pictures] AS [thumbnails]
                ON [pictures].[thumbnail_id] = [thumbnails].[id]';
    }

    /**
     * Find all
     * @return array
     */
    public function findAll($limit = NULL, $offset = NULL)
    {
        $query = array(
            $this->query,
            'ORDER BY [pages].[nice_name]'
        );

        if ($limit !== NULL) {
            $query[] = 'LIMIT %i';
            $query[] = intval($limit);
        }

        if ($offset !== NULL) {
            $query[] = 'OFFSET %i';
            $query[] = intval($offset);
        }

        return $this->poolResults(dibi::query($query));
    }

    /**
     * Find by ids
     * @param int
     * @return product[]
     */
    public function findByIds($ids = array())
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        if (empty($ids)) {
            return array();
        }

        $ret = $this->poolResults(dibi::query($this->query,
            'WHERE [products].[id] IN %l', $ids));
        return $ret;
    }

    /**
     * Find by codes
     * @param int
     * @return products[]
     */
    public function findByCodes($codes = array())
    {
        if (!is_array($codes)) {
            $codes = func_get_args();
        }

        if (empty($codes)) {
            return array();
        }

        return $this->poolResults(dibi::query($this->query,
            'WHERE [products].[code] IN %l', $codes));
    }

    /**
     * Find by id
     * @param int
     * @return product
     */
    public function findById($id)
    {
        $ret = $this->poolResults(dibi::query($this->query,
            'WHERE [products].[id] = %i', $id,
            'LIMIT 1'));
        return isset($ret[0]) ? $ret[0] : NULL;
    }

    /**
     * Find by nice name
     * @param string
     * @return product
     */
    public function findByNiceName($nice_name)
    {
        $ret = $this->poolResults(dibi::query($this->query,
            'WHERE [pages].[nice_name] = %s', $nice_name,
            'LIMIT 1'));
        return isset($ret[0]) ? $ret[0] : NULL;
    }

    /**
     * Find by category
     * @param category
     * @return array
     */
    public function findByCategory(category $category, $limit = NULL, $offset = NULL)
    {
        try {
            $cat = dibi::query('SELECT [lft], [rgt]',
                'FROM [:prefix:categories]',
                'WHERE [id] = %i', $category->getId())->fetch();
            $query = array(
                $this->query,
                'LEFT JOIN [:prefix:categories] AS [categories]
                    ON [categories].[id] = [products].[category_id]',
                'WHERE [categories].[lft] >= %i', $cat->lft,
                    'AND [categories].[rgt] <= %i', $cat->rgt,
                'ORDER BY [pages].[nice_name]'
            );

            if ($limit !== NULL) {
                $query[] = 'LIMIT %i';
                $query[] = intval($limit);
            }

            if ($offset !== NULL) {
                $query[] = 'OFFSET %i';
                $query[] = intval($offset);
            }
            $ret = $this->poolResults(dibi::query($query));
            return $ret;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Find by manufacturer
     * @param manufacturer
     * @return array
     */
    public function findByManufacturer(manufacturer $manufacturer, $limit = NULL, $offset = NULL)
    {
        $query = array($this->query,
            'LEFT JOIN [:prefix:manufacturers] AS [manufacturers]
                ON [manufacturers].[id] = [products].[manufacturer_id]',
            'WHERE [manufacturers].[id] = %i', $manufacturer->getId(),
            'ORDER BY [pages].[nice_name]');

        if ($limit !== NULL) {
            $query[] = 'LIMIT %i';
            $query[] = intval($limit);
        }

        if ($offset !== NULL) {
            $query[] = 'OFFSET %i';
            $query[] = intval($offset);
        }
        $ret = $this->poolResults(dibi::query($query));
        return $ret;
    }

    /**
     * Find category of some product
     * @param int
     * @return category
     */
    public function findCategoryOf($id)
    {
        return self::categories()->findById(
            dibi::query('SELECT [category_id] FROM [:prefix:products] WHERE [id] = %i',
                $id)->fetchSingle());
    }

    /**
     * Find manufacturer of some product
     * @param int
     * @return manufacturer
     */
    public function findManufacturerOf($id)
    {
        return self::manufacturers()->findById(
            dibi::query('SELECT [manufacturer_id] FROM [:prefix:products] WHERE [id] = %i',
                $id)->fetchSingle());
    }

    /**
     * Find random
     * @param int
     * @return products[]
     */
    public function findRandom($count)
    {
        return $this->poolResults(dibi::query($this->query,
                'ORDER BY RAND()',
                'LIMIT %i', $count));
    }

    /**
     * Find price changes by product id
     * @param int
     * @return array
     */
    public function findPriceChanges($id)
    {
        try {
            return dibi::query('SELECT [price], [changed_at]',
                'FROM [:prefix:price_changes]',
                'WHERE [product_id] = %i', $id,
                'ORDER BY [changed_at] ASC');
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Count all
     * @return int
     */
    public function countAll()
    {
        try {
            return dibi::query('SELECT COUNT(*) FROM [:prefix:products]')->fetchSingle();
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * Count by manufacturer
     * @return int
     */
    public function countByManufacturer(manufacturer $manufacturer)
    {
        try {
            return dibi::query('SELECT COUNT(*) FROM [:prefix:products] AS [products]
                LEFT JOIN [:prefix:manufacturers] AS [manufacturers]
                    ON [products].[manufacturer_id] = [manufacturers].[id]',
                'WHERE [manufacturers].[id] = %i', $manufacturer->getId())->fetchSingle();
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * Count by category
     * @return int
     */
    public function countByCategory(category $category)
    {
        try {
            return dibi::query('SELECT COUNT(*) FROM [:prefix:products] AS [products]
                LEFT JOIN [:prefix:categories] AS [categories]
                    ON [products].[category_id] = [categories].[id]',
                'WHERE [categories].[id] = %i', $category->getId())->fetchSingle();
        } catch (Exception $e) {
            var_dump($e);
            return NULL;
        }
    }

    /**
     * Build result and insert it into the pool
     * @param DibiResult
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

            $availability = array();
            $availability['id'] = $row->availability_id;
            $availability['name'] = $row->availability_name;
            unset($arr['availability_id'], $arr['availability_name']);
            $arr['availability'] = is_null($row->availability_id) ? NULL :
                new product_availability($availability);

            $this->pool[$row->id] = new product($arr);
            $ret[] = $this->pool[$row->id];
        }

        return $ret;
    }

    /**
     * Delete
     */
    public function deleteOne(product $p)
    {
        try {
            dibi::query('DELETE FROM [:prefix:products]',
               'WHERE [id] = %i', $p->getId(),
               'LIMIT 1');
            dibi::query('DELETE FROM [:prefix:pages]',
                'WHERE [nice_name] = %s', $p->getNiceName(),
                'LIMIT 1');
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Insert
     */
    public function insertOne(array $values)
    {
        try {
            dibi::begin();
            $product = array();
            $product['price'] =
                !isset($values['price']) || (isset($values['price']) && $values['price'] == 0)
                    ? NULL
                    : intval($values['price']);
            $product['manufacturer_id'] =
                !isset($values['manufacturer_id']) || (isset($values['manufacturer_id']) && $values['manufacturer_id'] == 0)
                    ? NULL
                    : intval($values['manufacturer_id']);
            $product['category_id'] =
                !isset($values['category_id']) || (isset($values['category_id']) && $values['category_id'] == 0)
                    ? NULL
                    : intval($values['category_id']);
            $product['availability_id'] =
                !isset($values['availability_id']) || (isset($values['availability_id']) && $values['availability_id'] == 0)
                    ? NULL
                    : intval($values['availability_id']);
            $product['code'] =
                !isset($values['code'])
                    ? NULL
                    : $values['code'];
            unset($values['price'], $values['manufacturer_id'], $values['category_id'], $values['availability_id'], $values['code']);

            dibi::query('INSERT INTO [:prefix:products]', $product);
            $product_id = intval(dibi::query('SELECT LAST_INSERT_ID()')->fetchSingle());

            $change = array(
                'product_id' => $product_id,
                'price' => $product['price'],
                'changed_at' => new DibiVariable('NOW()', 'sql')
            );
            dibi::query('INSERT INTO [:prefix:price_changes]', $change);

            if (isset($values['picture_id'])) {
                $values['picture_id'] = $values['picture_id'] == 0 ? NULL : intval($values['picture_id']);
            } else {
                $values['picture_id'] = NULL;
            }
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
            $values['ref_id'] = $product_id;
            $values['ref_type'] = pages::PRODUCT;
            dibi::query('INSERT INTO [:prefix:pages]', $values);

            dibi::commit();
            return TRUE;

        } catch (Exception $e) {
            dibi::rollback();
            return FALSE;
        }
    }

    /**
     * Update
     */
    public function updateOne(array $values)
    {
        try {
            dibi::begin();
            $product = array();
            $product['price'] = intval($values['price']);
            if (isset($values['manufacturer_id'])) {
                $product['manufacturer_id'] = $values['manufacturer_id'] == 0 ? NULL : intval($values['manufacturer_id']);
            }
            if (isset($values['category_id'])) {
                $product['category_id'] = $values['category_id'] == 0 ? NULL : intval($values['category_id']);
            }
            $product_id = intval($values['id']);
            if (isset($values['availability_id'])) {
                $product['availability_id'] = $values['availability_id'] == 0 ? NULL : intval($values['availability_id']);
            }
            if (isset($values['code'])) {
                $product['code'] = empty($values['code']) ? NULL : $values['code'];
            }
            unset($values['price'], $values['manufacturer_id'], $values['category_id'], $values['availability_id'], $values['id'], $values['code']);

            dibi::query('UPDATE [:prefix:products] SET', $product,
                'WHERE [id] = %i', $product_id
            );

            $change = array(
                'product_id' => $product_id,
                'price' => $product['price'],
                'changed_at' => new DibiVariable('NOW()', 'sql')
            );
            dibi::query('INSERT INTO [:prefix:price_changes]', $change);

            if (isset($values['picture_id'])) {
                $values['picture_id'] = $values['picture_id'] == 0 ? NULL : intval($values['picture_id']);
            }
            if (isset($values['meta_keywords']) && empty($values['meta_keywords'])) {
                $values['meta_keywords'] = NULL;
            }

            if (isset($values['meta_description']) && empty($values['meta_description'])) {
                $values['meta_description'] = NULL;
            }
            if (isset($values['content']) && empty($values['content'])) {
                $values['content'] = NULL;
            }

            if (!empty($values)) {
                $where = array();
                $where['ref_id'] = $product_id;
                $where['ref_type'] = pages::PRODUCT;
                dibi::query('UPDATE [:prefix:pages] SET', $values, 'WHERE %and', $where);
            }

            dibi::commit();
            return TRUE;

        } catch (Exception $e) {
            var_dump($e);
            exit();
            dibi::rollback();
            return FALSE;
        }
    }
}
