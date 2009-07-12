<?php
final class order extends /*Nette\*/Object
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
    public function dirty($action = order::IS, $col = NULL)
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
     * @return order
     */
    public function setId($id)
    {
        $this->id = $id;
        $this->dirty["id"] = TRUE;
        return $this;
    }

    /**
     * @var order_delivery_type
     */
    private $delivery_type;

    /**
     * delivery_type getter
     * @return order_delivery_type
     */
    public function &getDeliveryType()
    {
        return $this->delivery_type;
    }

    /**
     * delivery_type setter
     * @param order_delivery_type
     * @return order
     */
    public function setDeliveryType(order_delivery_type $delivery_type)
    {
        $this->delivery_type = $delivery_type;
        $this->dirty["delivery_type"] = TRUE;
        return $this;
    }

    /**
     * @var order_payment_type
     */
    private $payment_type;

    /**
     * payment_type getter
     * @return order_payment_type
     */
    public function &getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * payment_type setter
     * @param order_payment_type
     * @return order
     */
    public function setPaymentType(order_payment_type $payment_type)
    {
        $this->payment_type = $payment_type;
        $this->dirty["payment_type"] = TRUE;
        return $this;
    }

    /**
     * @var order_status
     */
    private $status;

    /**
     * status getter
     * @return order_status
     */
    public function &getStatus()
    {
        return $this->status;
    }

    /**
     * status setter
     * @param order_status
     * @return order
     */
    public function setStatus(order_status $status)
    {
        $this->status = $status;
        $this->dirty["status"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $payer_name;

    /**
     * payer_name getter
     * @return string
     */
    public function &getPayerName()
    {
        return $this->payer_name;
    }

    /**
     * payer_name setter
     * @param string
     * @return order
     */
    public function setPayerName($payer_name)
    {
        $this->payer_name = $payer_name;
        $this->dirty["payer_name"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $payer_lastname;

    /**
     * payer_lastname getter
     * @return string
     */
    public function &getPayerLastname()
    {
        return $this->payer_lastname;
    }

    /**
     * payer_lastname setter
     * @param string
     * @return order
     */
    public function setPayerLastname($payer_lastname)
    {
        $this->payer_lastname = $payer_lastname;
        $this->dirty["payer_lastname"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $payer_company;

    /**
     * payer_company getter
     * @return string
     */
    public function &getPayerCompany()
    {
        return $this->payer_company;
    }

    /**
     * payer_company setter
     * @param string
     * @return order
     */
    public function setPayerCompany($payer_company)
    {
        $this->payer_company = $payer_company;
        $this->dirty["payer_company"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $payer_street;

    /**
     * payer_street getter
     * @return string
     */
    public function &getPayerStreet()
    {
        return $this->payer_street;
    }

    /**
     * payer_street setter
     * @param string
     * @return order
     */
    public function setPayerStreet($payer_street)
    {
        $this->payer_street = $payer_street;
        $this->dirty["payer_street"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $payer_city;

    /**
     * payer_city getter
     * @return string
     */
    public function &getPayerCity()
    {
        return $this->payer_city;
    }

    /**
     * payer_city setter
     * @param string
     * @return order
     */
    public function setPayerCity($payer_city)
    {
        $this->payer_city = $payer_city;
        $this->dirty["payer_city"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $payer_postcode;

    /**
     * payer_postcode getter
     * @return string
     */
    public function &getPayerPostcode()
    {
        return $this->payer_postcode;
    }

    /**
     * payer_postcode setter
     * @param string
     * @return order
     */
    public function setPayerPostcode($payer_postcode)
    {
        $this->payer_postcode = $payer_postcode;
        $this->dirty["payer_postcode"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $delivery_name;

    /**
     * delivery_name getter
     * @return string
     */
    public function &getDeliveryName()
    {
        return $this->delivery_name;
    }

    /**
     * delivery_name setter
     * @param string
     * @return order
     */
    public function setDeliveryName($delivery_name)
    {
        $this->delivery_name = $delivery_name;
        $this->dirty["delivery_name"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $delivery_lastname;

    /**
     * delivery_lastname getter
     * @return string
     */
    public function &getDeliveryLastname()
    {
        return $this->delivery_lastname;
    }

    /**
     * delivery_lastname setter
     * @param string
     * @return order
     */
    public function setDeliveryLastname($delivery_lastname)
    {
        $this->delivery_lastname = $delivery_lastname;
        $this->dirty["delivery_lastname"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $delivery_company;

    /**
     * delivery_company getter
     * @return string
     */
    public function &getDeliveryCompany()
    {
        return $this->delivery_company;
    }

    /**
     * delivery_company setter
     * @param string
     * @return order
     */
    public function setDeliveryCompany($delivery_company)
    {
        $this->delivery_company = $delivery_company;
        $this->dirty["delivery_company"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $delivery_street;

    /**
     * delivery_street getter
     * @return string
     */
    public function &getDeliveryStreet()
    {
        return $this->delivery_street;
    }

    /**
     * delivery_street setter
     * @param string
     * @return order
     */
    public function setDeliveryStreet($delivery_street)
    {
        $this->delivery_street = $delivery_street;
        $this->dirty["delivery_street"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $delivery_city;

    /**
     * delivery_city getter
     * @return string
     */
    public function &getDeliveryCity()
    {
        return $this->delivery_city;
    }

    /**
     * delivery_city setter
     * @param string
     * @return order
     */
    public function setDeliveryCity($delivery_city)
    {
        $this->delivery_city = $delivery_city;
        $this->dirty["delivery_city"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $delivery_postcode;

    /**
     * delivery_postcode getter
     * @return string
     */
    public function &getDeliveryPostcode()
    {
        return $this->delivery_postcode;
    }

    /**
     * delivery_postcode setter
     * @param string
     * @return order
     */
    public function setDeliveryPostcode($delivery_postcode)
    {
        $this->delivery_postcode = $delivery_postcode;
        $this->dirty["delivery_postcode"] = TRUE;
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
     * @return order
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
    private $phone;

    /**
     * phone getter
     * @return string
     */
    public function &getPhone()
    {
        return $this->phone;
    }

    /**
     * phone setter
     * @param string
     * @return order
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        $this->dirty["phone"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $comment;

    /**
     * comment getter
     * @return string
     */
    public function &getComment()
    {
        return $this->comment;
    }

    /**
     * comment setter
     * @param string
     * @return order
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        $this->dirty["comment"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $at;

    /**
     * at getter
     * @return string
     */
    public function &getAt()
    {
        return $this->at;
    }

    /**
     * at setter
     * @param string
     * @return order
     */
    public function setAt($at)
    {
        $this->at = $at;
        $this->dirty["at"] = TRUE;
        return $this;
    }

    /**
     * Constructor
     * @param int id
     * @param order_delivery_type delivery_type
     * @param order_payment_type payment_type
     * @param order_status status
     * @param string payer_name
     * @param string payer_lastname
     * @param string payer_company
     * @param string payer_street
     * @param string payer_city
     * @param string payer_postcode
     * @param string delivery_name
     * @param string delivery_lastname
     * @param string delivery_company
     * @param string delivery_street
     * @param string delivery_city
     * @param string delivery_postcode
     * @param string email
     * @param string phone
     * @param string comment
     * @param string at
     */
    public function __construct($id = NULL, order_delivery_type $delivery_type = NULL, order_payment_type $payment_type = NULL, order_status $status = NULL, $payer_name = NULL, $payer_lastname = NULL, $payer_company = NULL, $payer_street = NULL, $payer_city = NULL, $payer_postcode = NULL, $delivery_name = NULL, $delivery_lastname = NULL, $delivery_company = NULL, $delivery_street = NULL, $delivery_city = NULL, $delivery_postcode = NULL, $email = NULL, $phone = NULL, $comment = NULL, $at = NULL)
    {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->id = $id;
            $this->delivery_type = $delivery_type;
            $this->payment_type = $payment_type;
            $this->status = $status;
            $this->payer_name = $payer_name;
            $this->payer_lastname = $payer_lastname;
            $this->payer_company = $payer_company;
            $this->payer_street = $payer_street;
            $this->payer_city = $payer_city;
            $this->payer_postcode = $payer_postcode;
            $this->delivery_name = $delivery_name;
            $this->delivery_lastname = $delivery_lastname;
            $this->delivery_company = $delivery_company;
            $this->delivery_street = $delivery_street;
            $this->delivery_city = $delivery_city;
            $this->delivery_postcode = $delivery_postcode;
            $this->email = $email;
            $this->phone = $phone;
            $this->comment = $comment;
            $this->at = $at;
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
        return array('id' => $this->id, 'delivery_type' => $this->delivery_type, 'payment_type' => $this->payment_type, 'status' => $this->status, 'payer_name' => $this->payer_name, 'payer_lastname' => $this->payer_lastname, 'payer_company' => $this->payer_company, 'payer_street' => $this->payer_street, 'payer_city' => $this->payer_city, 'payer_postcode' => $this->payer_postcode, 'delivery_name' => $this->delivery_name, 'delivery_lastname' => $this->delivery_lastname, 'delivery_company' => $this->delivery_company, 'delivery_street' => $this->delivery_street, 'delivery_city' => $this->delivery_city, 'delivery_postcode' => $this->delivery_postcode, 'email' => $this->email, 'phone' => $this->phone, 'comment' => $this->comment, 'at' => $this->at);
    }
}
