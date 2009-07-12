<?php
final class categories extends mapper
{
    /**
     * @var string Base query
     */
    private $query;

    /**
     * @var array Main categories (depth = 0)
     */
    private $main = NULL;

    /**
     * @var array All categories
     */
    private $all = NULL;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->query = '
            SELECT
                [categories].[id] AS [id],
                [pages].[name] AS [name],
                [pages].[nice_name] AS [nice_name],
                [pages].[content] AS [description],
                [pages].[meta_keywords] AS [meta_keywords],
                [pages].[meta_description] AS [meta_description],
                [pictures].[id] AS [picture_id],
                [pictures].[file] AS [picture_file],
                [pictures].[description] AS [picture_description],
                [pictures].[thumbnail_id] AS [thumbnail_id],
                [thumbnails].[file] AS [thumbnail_file],
                [thumbnails].[description] AS [thumbnail_description]
            FROM [:prefix:categories] AS [categories]
            LEFT JOIN [:prefix:pages] AS [pages]
                ON [pages].[ref_id] = [categories].[id] AND
                   [pages].[ref_type] = \'' . pages::CATEGORY . '\'
            LEFT JOIN [:prefix:pictures] AS [pictures]
                ON [pages].[picture_id] = [pictures].[id]
            LEFT JOIN [:prefix:pictures] AS [thumbnails]
                ON [pictures].[thumbnail_id] = [thumbnails].[id]';
    }

    /**
     * Converts list of categories with depths to tree
     * @param array
     * @return array
     */
    public function treeize(array $categories)
    {
        $root = array(NULL, array());
        $indexes = array(0 => 0);
        foreach ($categories as $_) {
            list($depth, $category) = $_;
            $indexes[$depth]++;
            $add =& $root;
            for ($i = 0; $i < $depth; $i++) $add =& $add[1][$indexes[$i] - 1];
            $add[1][] = array($category, array());
            $indexes[$depth + 1] = 0;
        }
        return $root[1];
    }

    /**
     * Find all categories
     * @return array
     */
    public function findAll()
    {
        if ($this->all === NULL) {
            $this->all = array();
            $res = dibi::query('
                SELECT
                    [node].[id] AS [id],
                    [pages].[name] AS [name],
                    [pages].[nice_name] AS [nice_name],
                    [pages].[content] AS [description],
                    [pages].[meta_keywords] AS [meta_keywords],
                    [pages].[meta_description] AS [meta_description],
                    [pictures].[id] AS [picture_id],
                    [pictures].[file] AS [picture_file],
                    [pictures].[description] AS [picture_description],
                    [pictures].[thumbnail_id] AS [thumbnail_id],
                    [thumbnails].[file] AS [thumbnail_file],
                    [thumbnails].[description] AS [thumbnail_description],
                    (COUNT([parent].[id]) - 1) AS [depth]
                FROM [:prefix:categories] AS [node], [:prefix:categories] AS [parent],
                    [:prefix:pages] AS [pages]
                LEFT JOIN [:prefix:pictures] AS [pictures]
                    ON [pages].[picture_id] = [pictures].[id]
                LEFT JOIN [:prefix:pictures] AS [thumbnails]
                    ON [pictures].[thumbnail_id] = [thumbnails].[id]
                WHERE ([node].[lft] BETWEEN [parent].[lft] AND [parent].[rgt]) AND
                    [pages].[ref_id] = [node].[id] AND [pages].[ref_type] = %s', pages::CATEGORY, '
                GROUP BY [node].[id]
                ORDER BY [node].[lft]');

            foreach ($res as $row) {
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

                $depth = intval($arr['depth']);
                unset($arr['depth']); // for finding subcategories

                $this->pool[$row->id] = new category($arr);
                $this->all[] = array($row->depth, $this->pool[$row->id]);
            }
        }

        return $this->all;
    }

    /**
     * Find main categories
     * @return array
     */
    public function findMain()
    {
        if ($this->main === NULL) {
            $this->main = $this->poolResults(dibi::query('
                SELECT
                    [node].[id] AS [id],
                    [pages].[name] AS [name],
                    [pages].[nice_name] AS [nice_name],
                    [pages].[content] AS [description],
                    [pages].[meta_keywords] AS [meta_keywords],
                    [pages].[meta_description] AS [meta_description],
                    [pictures].[id] AS [picture_id],
                    [pictures].[file] AS [picture_file],
                    [pictures].[description] AS [picture_description],
                    [pictures].[thumbnail_id] AS [thumbnail_id],
                    [thumbnails].[file] AS [thumbnail_file],
                    [thumbnails].[description] AS [thumbnail_description]
                FROM [:prefix:categories] AS [node], [:prefix:categories] AS [parent]
                LEFT JOIN [:prefix:pages] AS [pages]
                    ON [pages].[ref_id] = [id] AND
                       [pages].[ref_type] = %s', pages::CATEGORY, '
                LEFT JOIN [:prefix:pictures] AS [pictures]
                    ON [pages].[picture_id] = [pictures].[id]
                LEFT JOIN [:prefix:pictures] AS [thumbnails]
                    ON [pictures].[thumbnail_id] = [thumbnails].[id]
                WHERE [node].[lft] BETWEEN [parent].[lft] AND [parent].[rgt]
                GROUP BY [node].[id]
                HAVING (COUNT([parent].[id]) - 1) = 0
                ORDER BY [node].[lft]'));
        }

        return $this->main;
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
                'WHERE [categories].[id] = %i', $id));
            $this->pool[$id] = isset($ret[0]) ? $ret[0] : NULL;
        }

        return $this->pool[$id];
    }

    /**
     * Find subcategories
     * @param category
     * @return array
     */
    public function findSubcategories(category $category)
    {
        $ret = $this->poolResults(dibi::query('
            SELECT
                [categories].[id] AS [id],
                [pages].[name] AS [name],
                [pages].[nice_name] AS [nice_name],
                [pages].[content] AS [description],
                [pages].[meta_keywords] AS [meta_keywords],
                [pages].[meta_description] AS [meta_description],
                [pictures].[id] AS [picture_id],
                [pictures].[file] AS [picture_file],
                [pictures].[description] AS [picture_description],
                [pictures].[thumbnail_id] AS [thumbnail_id],
                [thumbnails].[file] AS [thumbnail_file],
                [thumbnails].[description] AS [thumbnail_description]
            FROM (SELECT
                    [node].[id] AS [id],
                    (COUNT([parent].[id]) - ([sub_tree].[depth] + 1)) AS [depth]
                FROM [:prefix:categories] AS [node], [:prefix:categories] AS [parent],
                    [:prefix:categories] AS [sub_parent], (
                        SELECT [node].[id], (COUNT([parent].[id]) - 1) AS [depth]
                        FROM [:prefix:categories] AS [node], [:prefix:categories] AS [parent]
                        WHERE ([node].[lft] BETWEEN [parent].[lft] AND [parent].[rgt]) AND
                            [node].[id] = %i', $category->getId(), '
                        GROUP BY [node].[id]
                    ) AS [sub_tree]
                WHERE ([node].[lft] BETWEEN [parent].[lft] AND [parent].[rgt]) AND
                    ([node].[lft] BETWEEN [sub_parent].[lft] AND [sub_parent].[rgt]) AND
                    [sub_parent].[id] = [sub_tree].[id]
                GROUP BY [node].[id]
                HAVING [depth] = 1) AS [categories]
            LEFT JOIN [:prefix:pages] AS [pages]
                ON [pages].[ref_id] = [categories].[id] AND
                   [pages].[ref_type] = %s', pages::CATEGORY, '
            LEFT JOIN [:prefix:pictures] AS [pictures]
                ON [pages].[picture_id] = [pictures].[id]
            LEFT JOIN [:prefix:pictures] AS [thumbnails]
                ON [pictures].[thumbnail_id] = [thumbnails].[id]
            ORDER BY [name]'));
        return $ret;
    }

    /**
     * Find by nice name
     * @param string
     * @return category
     */
    public function findByNiceName($nice_name)
    {
        $ret = $this->poolResults(dibi::query($this->query,
            'WHERE [pages].[nice_name] = %s', $nice_name));
        return isset($ret[0]) ? $ret[0] : NULL;
    }

    /**
     * Find for nav by product ID
     * @param int
     * @return category[]
     */
    public function findForNavByProductId($product_id)
    {
        try {
            $category = dibi::query('
                SELECT
                    [categories].[lft] AS [lft],
                    [categories].[rgt]  AS [rgt]
                FROM [:prefix:categories] AS [categories]
                RIGHT JOIN [:prefix:products] AS [products]
                    ON [categories].[id] = [products].[category_id]
                WHERE [products].[id] = %i', $product_id, '
                LIMIT 1')->fetch();
            return $this->poolResults(dibi::query($this->query,
                'WHERE [categories].[lft] <= %i', $category->lft,
                'AND [categories].[rgt] >= %i', $category->rgt,
                'ORDER BY [categories].[lft]'));
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
            return dibi::query('SELECT COUNT(*) FROM [:prefix:categories]')->fetchSingle();
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * Reset main
     */
    public function resetMain()
    {
        $this->main = NULL;
    }

    /**
     * Reset all
     */
    public function resetAll()
    {
        $this->all = NULL;
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

            $picture = array();
            $picture['id'] = $row->picture_id;
            $picture['file'] = $row->picture_file;
            $picture['description'] = $row->picture_description;
            $picture['thumbnail'] = is_null($row->thumbnail_id) ? NULL :
                new picture($thumbnail);
            unset($arr['picture_id'], $arr['picture_file'], $arr['picture_description']);

            $arr['picture'] = is_null($row->picture_id) ? NULL :
                new picture($picture);

            unset($arr['depth']); // for finding subcategories

            $this->pool[$row->id] = new category($arr);
            $ret[] = $this->pool[$row->id];
        }

        return $ret;
    }

    /**
     * Edit category
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
        $ref_id = $values['id'];
        unset($values['id']);
        unset($values['nice_name']);

        $this->resetAll();

        return dibi::query('UPDATE [:prefix:pages] SET', $values,
            'WHERE [ref_id] = %i AND [ref_type] = %s', $ref_id, pages::CATEGORY
        );
    }

    /**
     * Add category
     */
    public function addOne(array $values, $parent_id = NULL)
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
            // build tree
            $this->resetAll();
            $categories = array(NULL, $this->treeize($this->findAll()));

            // sort and add
            $values['description'] = $values['content'];
            $picture_id = intval($values['picture_id']);
            unset($values['content'], $values['picture_id']);
            $new = new category($values);
            if ($parent_id === NULL) {
                $categories[1][] = array($new, array());
                $sorted = $this->sortTree($categories);
            } else {
                $sorted = $this->sortTree($categories, $new, $parent_id);
            }

            // score tree with lft and rgt
            $this->scoreTree($sorted[1], $scored);

            // update / insert into database
            foreach ($scored as $node) {
                list($obj, $lft, $rgt) = $node;
                if ($obj->getId() === NULL) { // new => insert
                    $values = $obj->__toArray();
                    $values['content'] = $values['description'];
                    unset($values['id'], $values['picture'], $values['description']);
                    $values['picture_id'] = $picture_id;
                    dibi::query('INSERT INTO [:prefix:categories]', 
                        array('lft' => $lft, 'rgt' => $rgt));
                    $id = dibi::query('SELECT LAST_INSERT_ID()')->fetchSingle();
                    $values['ref_id'] = intval($id);
                    $values['ref_type'] = pages::CATEGORY;
                    dibi::query('INSERT INTO [:prefix:pages]', $values);
                } else {
                    dibi::query('UPDATE [:prefix:categories]',
                        'SET', array('lft' => $lft, 'rgt' => $rgt),
                        'WHERE [id] = %i', intval($obj->getId()));
                }
            }
            
            dibi::commit();
            return TRUE;

        } catch (Exception $e) {
            dibi::rollback();
            return FALSE;
        }
    }

    /**
     * Delete
     */
    public function deleteOne($id)
    {
        $id = intval($id);
        try {
            dibi::begin();
            $one = dibi::query('SELECT [lft], [rgt] FROM [:prefix:categories] WHERE [id] = %i', $id)->fetch();
            $ids = array();

            foreach (dibi::query('SELECT [id] FROM [:prefix:categories] WHERE [lft] >= %i',
                $one->lft, ' AND [rgt] <= %i', $one->rgt) as $id)
            {
                $ids[] = intval($id->id);
            }

            dibi::query('DELETE FROM [:prefix:pages] WHERE [ref_id] IN %l', $ids,
                'AND [ref_type] = %s', pages::CATEGORY);
            dibi::query('DELETE FROM [:prefix:categories] WHERE [id] IN %l', $ids);
            dibi::query('UPDATE [:prefix:categories] SET',
                '[lft] = [lft] - %i', intval($one->lft) - intval($one->rgt) + 1,
                'WHERE [lft] > %i', $one->rgt);
            dibi::query('UPDATE [:prefix:categories] SET',
                '[rgt] = [rgt] - %i', intval($one->lft) - intval($one->rgt) + 1,
                'WHERE [rgt] > %i', $one->rgt);
            dibi::commit();

            return TRUE;
        } catch (Exception $e) {
            dibi::rollback();
            return FALSE;
        }
    }

    private function sortTree($node, $new = NULL, $parent_id = NULL)
    {
        if ($node[0] instanceof category) {
            if (intval($node[0]->getId()) === $parent_id) {
                $node[1][] = array($new, array());
            }
        }

        usort($node[1], array($this, 'nodeCmp'));
        foreach ($node[1] as &$_) {
            $_ = $this->sortTree($_, $new, $parent_id);
        }
        return $node;
    }

    private function nodeCmp($a, $b)
    {
        return strcmp($a[0]->getNiceName(), $b[0]->getNiceName());
    }

    private function scoreTree($nodes, &$scored = array(), $lft = 1)
    {
        foreach ($nodes as $node) {
            list($obj, $sub) = $node;
            if (empty($sub)) {
                $scored[] = array($obj, $lft, $lft + 1);
                $lft += 2;
            } else {
                $sub_scored = array();
                $rgt = $this->scoreTree($sub, $sub_scored, $lft + 1);
                $scored[] = array($obj, $lft, $rgt);
                $lft = $rgt + 1;
                $scored = array_merge($scored, $sub_scored);
            }
        }

        return $lft;
    }
}
