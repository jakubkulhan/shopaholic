<?php
final class order_email extends /*Nette\*/Object
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
    public function dirty($action = order_email::IS, $col = NULL)
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
     * @var string
     */
    private $sent_at;

    /**
     * sent_at getter
     * @return string
     */
    public function &getSentAt()
    {
        return $this->sent_at;
    }

    /**
     * sent_at setter
     * @param string
     * @return order_email
     */
    public function setSentAt($sent_at)
    {
        $this->sent_at = $sent_at;
        $this->dirty["sent_at"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $subject;

    /**
     * subject getter
     * @return string
     */
    public function &getSubject()
    {
        return $this->subject;
    }

    /**
     * subject setter
     * @param string
     * @return order_email
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        $this->dirty["subject"] = TRUE;
        return $this;
    }

    /**
     * @var string
     */
    private $body;

    /**
     * body getter
     * @return string
     */
    public function &getBody()
    {
        return $this->body;
    }

    /**
     * body setter
     * @param string
     * @return order_email
     */
    public function setBody($body)
    {
        $this->body = $body;
        $this->dirty["body"] = TRUE;
        return $this;
    }

    /**
     * Constructor
     * @param string sent_at
     * @param string subject
     * @param string body
     */
    public function __construct($sent_at = NULL, $subject = NULL, $body = NULL)
    {
        if (is_array($sent_at)) {
            foreach ($sent_at as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->sent_at = $sent_at;
            $this->subject = $subject;
            $this->body = $body;
        }

        if ($sent_at === NULL) {
            $this->dirty = TRUE;
        }
    }

    /**
     * Converts data to array
     */
    public function __toArray()
    {
        return array('sent_at' => $this->sent_at, 'subject' => $this->subject, 'body' => $this->body);
    }
}
