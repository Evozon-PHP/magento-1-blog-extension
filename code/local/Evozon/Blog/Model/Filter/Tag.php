<?php

/**
 * Validation class for tag model
 * It will filter the input and use validator
 * The user input will have several filters, escapers and validators applied
 * FILTERS:
 * -StringTrim
 * -HtmlEntities
 * VALIDATORS:
 * -Length - maxim 32
 * -Required - only for the default value (for store = 0)
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Filter_Tag implements Evozon_Blog_Model_Filter_Interface
{
    /**
     * Encoding type of a tag
     * @const string
     */
    const EVOZON_BLOG_FILTER_TAG_DEFAULT_ENCODING = 'UTF-8';

    /**
     * Options for the entire group of data
     * The input is allowed to be empty
     * 
     * @return \Evozon_Blog_Model_Filter_Tag
     */
    public function getOptions()
    {
        return array(
            Zend_Filter_Input::ALLOW_EMPTY => true,
            Zend_Filter_Input::MISSING_MESSAGE => 'The default value can not be empty. Try again!',
            Zend_Filter_Input::NOT_EMPTY_MESSAGE => 'Please set the default tag name without HTML tags.',
            Zend_Filter_Input::ESCAPE_FILTER => 'HtmlEntities'
        );
    }

    /**
     * Default tag rules
     * All the input shall be trimmed, removed of new lines and of too many whitespaces between words
     * 
     * @return \Evozon_Blog_Model_Filter_Tag
     */
    public function getFilters()
    {
        return array(
            '*' => array(
                'StringTrim',
                'StripTags',
                'StripNewlines',
                array(
                    'PregReplace',
                    array(
                        'match' => array("/\s+/", "/\s([?.!])/"),
                        'replace' => array(" ", "$1")
                    )
                )
            ),
        );
    }

    /**
     * Default tag validators
     * Default value is required
     * All the tags have a limit on length
     * 
     * @return \Evozon_Blog_Model_Filter_Tag
     */
    public function getValidators()
    {
        return array(
            'default' => array(
                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
                Zend_Filter_Input::BREAK_CHAIN => true,
                Zend_Filter_Input::ALLOW_EMPTY => false
            ),
            '*' => array(
                'StringLength',
                array('stringLength',
                    array(
                        'min' => $this->getConfig('minlength'),
                        'max' => $this->getConfig('maxlength'),
                        'encoding' => $this->validateEncoding($this->getConfig('encoding'))
                    )
                )
            )
        );
    }
    
    /**
     * Check if the encoding value given by the user, is valid
     * If not - the default UTF-8 will be set 
     * 
     * @param string $value
     * @return string
     */
    protected function validateEncoding($value)
    {
        $encodings = Mage::getSingleton('evozon_blog/adminhtml_tag_encodings')->getAllOptions();
        if (in_array($value, $encodings))
        {
            return $value;
        }
        
        return self::EVOZON_BLOG_FILTER_TAG_DEFAULT_ENCODING;
    }
    
    /**
     * Accessing validation config parameters
     * 
     * @param string $key
     * @return string | int
     */
    protected function getConfig($key)
    {
        return Mage::getSingleton('evozon_blog/config')->getTagsValidationConfig($key);
    }

}
