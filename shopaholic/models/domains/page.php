<?php
final class page extends /*Nette\*/Object
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
    public function dirty($action = page::IS, $col = NULL)
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
     * @return page
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
     * @return page
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
    private $content;

    /**
     * content getter
     * @return string
     */
    public function &getContent()
    {
        return $this->content;
    }

    /**
     * content setter
     * @param string
     * @return page
     */
    public function setContent($content)
    {
        $this->content = $content;
        $this->dirty["content"] = TRUE;
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
     * @return page
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
     * @return page
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
     * @return page
     */
    public function setPicture(picture $picture)
    {
        $this->picture = $picture;
        $this->dirty["picture"] = TRUE;
        return $this;
    }

    /**
     * @var mixed
     */
    private $ref;

    /**
     * ref getter
     * @return mixed
     */
    public function &getRef()
    {
        return $this->ref;
    }

    /**
     * ref setter
     * @param mixed
     * @return page
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
        $this->dirty["ref"] = TRUE;
        return $this;
    }

    /**
     * Constructor
     * @param string nice_name
     * @param string name
     * @param string content
     * @param string meta_keywords
     * @param string meta_description
     * @param picture picture
     * @param mixed ref
     */
    public function __construct($nice_name = NULL, $name = NULL, $content = NULL, $meta_keywords = NULL, $meta_description = NULL, picture $picture = NULL, $ref = NULL)
    {
        if (is_array($nice_name)) {
            foreach ($nice_name as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->nice_name = $nice_name;
            $this->name = $name;
            $this->content = $content;
            $this->meta_keywords = $meta_keywords;
            $this->meta_description = $meta_description;
            $this->picture = $picture;
            $this->ref = $ref;
        }

        if ($nice_name === NULL) {
            $this->dirty = TRUE;
        }
    }

    /**
     * Converts data to array
     */
    public function __toArray()
    {
        return array('nice_name' => $this->nice_name, 'name' => $this->name, 'content' => $this->content, 'meta_keywords' => $this->meta_keywords, 'meta_description' => $this->meta_description, 'picture' => $this->picture, 'ref' => $this->ref);
    }
}
