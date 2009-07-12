<?php
final class picture extends /*Nette\*/Object
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
    public function dirty($action = picture::IS, $col = NULL)
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
     * @return picture
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
    private $file;

    /**
     * file getter
     * @return string
     */
    public function &getFile()
    {
        return $this->file;
    }

    /**
     * file setter
     * @param string
     * @return picture
     */
    public function setFile($file)
    {
        $this->file = $file;
        $this->dirty["file"] = TRUE;
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
     * @return picture
     */
    public function setDescription($description)
    {
        $this->description = $description;
        $this->dirty["description"] = TRUE;
        return $this;
    }

    /**
     * @var picture
     */
    private $thumbnail;

    /**
     * thumbnail getter
     * @return picture
     */
    public function &getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * thumbnail setter
     * @param picture
     * @return picture
     */
    public function setThumbnail(picture $thumbnail)
    {
        $this->thumbnail = $thumbnail;
        $this->dirty["thumbnail"] = TRUE;
        return $this;
    }

    /**
     * Constructor
     * @param int id
     * @param string file
     * @param string description
     * @param picture thumbnail
     */
    public function __construct($id = NULL, $file = NULL, $description = NULL, picture $thumbnail = NULL)
    {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $this->id = $id;
            $this->file = $file;
            $this->description = $description;
            $this->thumbnail = $thumbnail;
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
        return array('id' => $this->id, 'file' => $this->file, 'description' => $this->description, 'thumbnail' => $this->thumbnail);
    }
}
