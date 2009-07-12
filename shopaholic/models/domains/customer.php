<?php
final class customer extends /*Nette\*/Object
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
    public function dirty($action = customer::IS, $col = NULL)
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
     * @return customer
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
    private $email;

    /**
     * email getter
     * @return string
     */
    public function &getEmail()
    {
        return $this->email;
    }

    /**
     * email setter
     * @param string
     * @return customer
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->dirty["email"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $password;

    /**
     * password getter
     * @return string
     */
    public function &getPassword()
    {
        return $this->password;
    }

    /**
     * password setter
     * @param string
     * @return customer
     */
    public function setPassword($password)
    {
        $this->password = $password;
        $this->dirty["password"] = TRUE;
        return $this;
    }

    /**
     * Constructor
     * @param int id
     * @param string email
     * @param string password
     */
    public function __construct($id = NULL, $email = NULL, $password = NULL)
    {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->id = $id;
            $this->email = $email;
            $this->password = $password;
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
        return array('id' => $this->id, 'email' => $this->email, 'password' => $this->password);
    }
}
