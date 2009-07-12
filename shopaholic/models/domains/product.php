<?php
final class product extends /*Nette\*/Object
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
    public function dirty($action = product::IS, $col = NULL)
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
     * @return product
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
     * @return product
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
    private $code;

    /**
     * code getter
     * @return string
     */
    public function &getCode()
    {
        return $this->code;
    }

    /**
     * code setter
     * @param string
     * @return product
     */
    public function setCode($code)
    {
        $this->code = $code;
        $this->dirty["code"] = TRUE;
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
     * @return product
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
     * @return product
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
     * @return product
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
     * @return product
     */
    public function setMetaDescription($meta_description)
    {
        $this->meta_description = $meta_description;
        $this->dirty["meta_description"] = TRUE;
        return $this;
    }

    /**
     * @var int
     */
    private $price;

    /**
     * price getter
     * @return int
     */
    public function &getPrice()
    {
        return $this->price;
    }

    /**
     * price setter
     * @param int
     * @return product
     */
    public function setPrice($price)
    {
        $this->price = $price;
        $this->dirty["price"] = TRUE;
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
     * @return product
     */
    public function setPicture(picture $picture)
    {
        $this->picture = $picture;
        $this->dirty["picture"] = TRUE;
        return $this;
    }

    /**
     * @var product_availability
     */
    private $availability;

    /**
     * availability getter
     * @return product_availability
     */
    public function &getAvailability()
    {
        return $this->availability;
    }

    /**
     * availability setter
     * @param product_availability
     * @return product
     */
    public function setAvailability(product_availability $availability)
    {
        $this->availability = $availability;
        $this->dirty["availability"] = TRUE;
        return $this;
    }

    /**
     * Constructor
     * @param int id
     * @param string name
     * @param string code
     * @param string nice_name
     * @param string description
     * @param string meta_keywords
     * @param string meta_description
     * @param int price
     * @param picture picture
     * @param product_availability availability
     */
    public function __construct($id = NULL, $name = NULL, $code = NULL, $nice_name = NULL, $description = NULL, $meta_keywords = NULL, $meta_description = NULL, $price = NULL, picture $picture = NULL, product_availability $availability = NULL)
    {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->id = $id;
            $this->name = $name;
            $this->code = $code;
            $this->nice_name = $nice_name;
            $this->description = $description;
            $this->meta_keywords = $meta_keywords;
            $this->meta_description = $meta_description;
            $this->price = $price;
            $this->picture = $picture;
            $this->availability = $availability;
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
        return array('id' => $this->id, 'name' => $this->name, 'code' => $this->code, 'nice_name' => $this->nice_name, 'description' => $this->description, 'meta_keywords' => $this->meta_keywords, 'meta_description' => $this->meta_description, 'price' => $this->price, 'picture' => $this->picture, 'availability' => $this->availability);
    }
}
