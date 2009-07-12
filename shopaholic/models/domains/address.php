<?php
final class address extends /*Nette\*/Object
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
    public function dirty($action = address::IS, $col = NULL)
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
     * @return address
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
     * @return address
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
    private $street;

    /**
     * street getter
     * @return string
     */
    public function &getStreet()
    {
        return $this->street;
    }

    /**
     * street setter
     * @param string
     * @return address
     */
    public function setStreet($street)
    {
        $this->street = $street;
        $this->dirty["street"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $city;

    /**
     * city getter
     * @return string
     */
    public function &getCity()
    {
        return $this->city;
    }

    /**
     * city setter
     * @param string
     * @return address
     */
    public function setCity($city)
    {
        $this->city = $city;
        $this->dirty["city"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $postcode;

    /**
     * postcode getter
     * @return string
     */
    public function &getPostcode()
    {
        return $this->postcode;
    }

    /**
     * postcode setter
     * @param string
     * @return address
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
        $this->dirty["postcode"] = TRUE;
        return $this;
    }

    /**
     * Constructor
     * @param int id
     * @param string name
     * @param string street
     * @param string city
     * @param string postcode
     */
    public function __construct($id = NULL, $name = NULL, $street = NULL, $city = NULL, $postcode = NULL)
    {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->id = $id;
            $this->name = $name;
            $this->street = $street;
            $this->city = $city;
            $this->postcode = $postcode;
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
        return array('id' => $this->id, 'name' => $this->name, 'street' => $this->street, 'city' => $this->city, 'postcode' => $this->postcode);
    }
}
