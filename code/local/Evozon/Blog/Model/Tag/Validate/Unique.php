<?php

/**
 * Unique value validator for the tag model
 * It needs the tag ID and the user input
 * 
 * We don`t have to check if the values are empty because it has been handled previously
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Tag_Validate_Unique extends Zend_Validate_Abstract
{

    const NOT_UNIQUE = 'notUnique';

     /**
     * Tag id
     * @var int 
     */
    protected $_tagId;

    /**
     * Message template to throw errors
     * @var array 
     */
    protected $_messageTemplates = array(
        self::NOT_UNIQUE => 'A tag named "%value%" already exists! Please rename.'
    );

    /**
     * Creating a validation instance 
     * 
     * @param array $data
     * @param int $id
     */
    public function __construct($options = array())
    {
        if (!array_key_exists('tag', $options)) {
            $options['tag'] = null;
        }

        $this->setTagId($options['tag']);
    }

    /**
     * Setting tag id
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Tag | int | NULL $tag
     * @return \Evozon_Blog_Model_Tag_Validate_Unique
     */
    public function setTagId($tagId)
    {
        $this->_tagId = $tagId;

        return $this;
    }

    /**
     * Looking for duplicates in the database
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $data
     */
    protected function validate($data)
    {
        return Mage::getResourceModel('evozon_blog/tag_validate_unique')->validate($data, $this->_tagId);
    }

    /**
     * Defined by Zend_Validate_Interface
     * Returns true if and only if $value only contains digit characters
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $data
     */
    public function isValid($data)
    {
        $this->_setValue($data);

        $matches = $this->validate($data);
        if (empty($matches)) {
            return true;
        }

        foreach ($matches as $row => $value) {
            $this->_messages[] = $this->_createMessage(self::NOT_UNIQUE, $value['value']);
        }

        return false;
    }

}
