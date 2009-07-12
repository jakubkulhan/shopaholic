<?php
final class category extends /*Nette\*/Object
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
    public function dirty($action = category::IS, $col = NULL)
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
     * @return category
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
     * @return category
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
    private $nice_name;

    /**
     * nice_name getter
     * @return string
     */
    public function &getNiceName()
    {
        return $this->nice_name;
    }

    /**
     * nice_name setter
     * @param string
     * @return category
     */
    public function setNiceName($nice_name)
    {
        $this->nice_name = $nice_name;
        $this->dirty["nice_name"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $description;

    /**
     * description getter
     * @return string
     */
    public function &getDescription()
    {
        return $this->description;
    }

    /**
     * description setter
     * @param string
     * @return category
     */
    public function setDescription($description)
    {
        $this->description = $description;
        $this->dirty["description"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $meta_keywords;

    /**
     * meta_keywords getter
     * @return string
     */
    public function &getMetaKeywords()
    {
        return $this->meta_keywords;
    }

    /**
     * meta_keywords setter
     * @param string
     * @return category
     */
    public function setMetaKeywords($meta_keywords)
    {
        $this->meta_keywords = $meta_keywords;
        $this->dirty["meta_keywords"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $meta_description;

    /**
     * meta_description getter
     * @return string
     */
    public function &getMetaDescription()
    {
        return $this->meta_description;
    }

    /**
     * meta_description setter
     * @param string
     * @return category
     */
    public function setMetaDescription($meta_description)
    {
        $this->meta_description = $meta_description;
        $this->dirty["meta_description"] = TRUE;
        return $this;
    }

    /**
     * @var picture
     */
    private $picture;

    /**
     * picture getter
     * @return picture
     */
    public function &getPicture()
    {
        return $this->picture;
    }

    /**
     * picture setter
     * @param picture
     * @return category
     */
    public function setPicture(picture $picture)
    {
        $this->picture = $picture;
        $this->dirty["picture"] = TRUE;
        return $this;
    }

    /**
     * Constructor
     * @param int id
     * @param string name
     * @param string nice_name
     * @param string description
     * @param string meta_keywords
     * @param string meta_description
     * @param picture picture
     */
    public function __construct($id = NULL, $name = NULL, $nice_name = NULL, $description = NULL, $meta_keywords = NULL, $meta_description = NULL, picture $picture = NULL)
    {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->id = $id;
            $this->name = $name;
            $this->nice_name = $nice_name;
            $this->description = $description;
            $this->meta_keywords = $meta_keywords;
            $this->meta_description = $meta_description;
            $this->picture = $picture;
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
        return array('id' => $this->id, 'name' => $this->name, 'nice_name' => $this->nice_name, 'description' => $this->description, 'meta_keywords' => $this->meta_keywords, 'meta_description' => $this->meta_description, 'picture' => $this->picture);
    }
}
