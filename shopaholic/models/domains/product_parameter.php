<?php
final class product_parameter extends /*Nette\*/Object
{
    /**
     * Is dirty?
     */
    const IS = 1;

    /**
     * Put dirt
     */
    const DIRT = 2;

    /**
     * Remove dirt
     */
    const UNDIRT = 3;

    /**
     * @var bool Dirty
     */
    private $dirty = array();

    /**
     * Is dirty?
     */
    public function dirty($action = product_parameter::IS, $col = NULL)
    {
        switch ($action) {
            case self::IS:
                return !empty($this->dirty);
            case self::DIRT:
                $this->dirty[$col] = TRUE;
                return ;
            case self::UNDIRT:
                $this->dirty = array();
                return ;
        }

        return ;        
    }

    /**
     * @var int
     */
    private $id;

    /**
     * id getter
     * @return int
     */
    public function &getId()
    {
        return $this->id;
    }

    /**
     * id setter
     * @param int
     * @return product_parameter
     */
    public function setId($id)
    {
        $this->id = $id;
        $this->dirty["id"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $name;

    /**
     * name getter
     * @return string
     */
    public function &getName()
    {
        return $this->name;
    }

    /**
     * name setter
     * @param string
     * @return product_parameter
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->dirty["name"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $title;

    /**
     * title getter
     * @return string
     */
    public function &getTitle()
    {
        return $this->title;
    }

    /**
     * title setter
     * @param string
     * @return product_parameter
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->dirty["title"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $value;

    /**
     * value getter
     * @return string
     */
    public function &getValue()
    {
        return $this->value;
    }

    /**
     * value setter
     * @param string
     * @return product_parameter
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->dirty["value"] = TRUE;
        return $this;
    }

    /**
     * Constructor
     * @param int id
     * @param string name
     * @param string title
     * @param string value
     */
    public function __construct($id = NULL, $name = NULL, $title = NULL, $value = NULL)
    {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->id = $id;
            $this->name = $name;
            $this->title = $title;
            $this->value = $value;
        }

        if ($id === NULL) {
            $this->dirty = TRUE;
        }
    }

    /**
     * Converts data to array
     */
    public function __toArray()
    {
        return array('id' => $this->id, 'name' => $this->name, 'title' => $this->title, 'value' => $this->value);
    }
}
