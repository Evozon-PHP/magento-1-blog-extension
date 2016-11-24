<?php

/**
 * Validation class for comment model
 * It is called whenever the user introduces a comment from the FE
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Filter_Comment
    implements Evozon_Blog_Model_Filter_Interface
{

    /**
     * Holds name regexp validation
     * @var NULL |  Zend_Validate_Regex
     */
    protected $_regExp = NULL;

    /**
     * Filter rules for each comment input to be filtered/sanitzied by
     * If the user has used more spaces than needed between words, it will be replaced
     * 
     * @return \Evozon_Blog_Model_Filter_Comment
     */
    public function getFilters()
    {
        return array(
            '*' => array(
                'StringTrim',
                'StripTags',
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
     * General options for all of the inputs
     * 
     * @return \Evozon_Blog_Model_Filter_Comment
     */
    public function getOptions()
    {
        return array(
            Zend_Filter_Input::ALLOW_EMPTY => false,
            Zend_Filter_Input::NOT_EMPTY_MESSAGE => 'This field can not be empty. Try again!',
            Zend_Filter_Input::ESCAPE_FILTER => 'HtmlEntities',
            Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_REQUIRED,
            Zend_Filter_Input::MISSING_MESSAGE => "This is a required field and it can not be empty!"
        );
    }

    /**
     * Each field has to be validated according to specific rules
     * Author and author_email depends on the context
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Filter_Comment
     */
    public function getValidators()
    {
        return array(
            'form_key' => array(
                array('Identical', array('token' => Mage::getSingleton('core/session')->getFormKey())),
                Zend_Filter_Input::BREAK_CHAIN => true,
                'messages' => array('The comment could not be added. Please try again or refresh the form!')
            ),
            'author' => array(
                $this->getAuthorNameRegExValidator(),
                array('StringLength', array('max' => $this->getConfig('author_maxlength'))),
                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
            ),
            'author_email' => array(
                array(
                    'EmailAddress', 
                    array(
                        'messages'=> array(
                            'emailAddressInvalidFormat' => 'Your e-mail does not have a valid format of e-mail@hostname',
                            'emailAddressInvalidHostname' => "Please check again on the '%hostname%' value",
                            'emailAddressInvalidLocalPart'=> "Please chekc your local value from '%hostname%'",
                            'emailAddressDotAtom' => "There has been found no dot in the '%hostname'"
                        )
                    )
                ),
                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
            ),
            'subject' => array(
                array('StringLength', array('max' => $this->getConfig('subject_maxlength'))),
                Zend_Filter_Input::PRESENCE => $this->getConfig('subject')? Zend_Filter_Input::PRESENCE_REQUIRED : Zend_Filter_Input::PRESENCE_OPTIONAL,
                Zend_Filter_Input::ALLOW_EMPTY => $this->getConfig('subject')? false : true
            ),
            'content' => array(
                'StringLength',
                array('stringLength', array('max' => $this->getConfig('content_maxlength')))
            ),
            'post_id' => array(
                'Int'
            ),
            'parent_id' => array(
                'Int',
                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
            ),
            'status' => array(
                'Int',
                Zend_Filter_Input::PRESENCE => Zend_Filter_Input::PRESENCE_OPTIONAL
            )
        );
    }

    /**
     * The user name has to be validated according to a regexp
     * Any unicode character is allowed, spaces and other specific symbols (like -`, etc)
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Zend_Validate_Regex
     */
    protected function getAuthorNameRegExValidator()
    {
        if (is_null($this->_regExp)) {
            $regExp = new Zend_Validate_Regex("/^[\p{L}\s'.-]+$/");
            $regExp->setMessages(
                array(
                    Zend_Validate_Regex::NOT_MATCH => "The name '%value%' contains invalid characters. Please change!"
                )
            );

            $this->_regExp = $regExp;
        }

        return $this->_regExp;
    }

    /**
     * Accessing validation config parameters
     * 
     * @param string $key
     * @return string | int
     */
    protected function getConfig($key)
    {
        return Mage::getSingleton('evozon_blog/config')->getCommentsValidationConfig($key);
    }
}
