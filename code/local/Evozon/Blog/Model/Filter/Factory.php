<?php

/**
 * Input presentation layer validator/filter for blog needs (tags, comments, etc)
 * It has to filter and sanitize the input according to the received rules
 * It receives the required filter/validation parameters from classes of type Evozon_Blog_Model_Filter_Interface
 * The user can also mention a new filter namespace (aka location of the config files) by using the propper key in the transfered data
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Filter_Factory extends Zend_Filter_Input
{
    /**
     * Key by which the filter type will be identified in construct data
     * @const
     */
    const EVOZON_BLOG_MODEL_FILTER_TYPE = 'type';

    /**
     * Key by which the user input will be identified in construct data
     * @const
     */
    const EVOZON_BLOG_MODEL_FILTER_INPUT = 'input';

    /**
     * In case the user submitted a different path for filter config files, it will store it
     * @var string | null
     */
    protected $_filterPath = null;

    /**
     * Setting needed filters, validators, etc in order to create the input filter
     * Process any filter namespaces mentioned
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $data array with 3 keys: type, input and optional - a new filter namespace where to look for filter config files
     */
    public function __construct(array $data)
    {
        $config = $this->_createConfig($data[self::EVOZON_BLOG_MODEL_FILTER_TYPE]);
        
        if (!isset($data[self::EVOZON_BLOG_MODEL_FILTER_INPUT])){
            throw new Evozon_Blog_Model_Exception_Validate("Input data is missing. Please set the required key with the input as value");
        }
        $input = array_diff($data[self::EVOZON_BLOG_MODEL_FILTER_INPUT], array(""));

        parent::__construct($config->getFilters(), $config->getValidators(), $input, $config->getOptions());
    }

    /**
     * Requesting the model and retrieving it
     * 
     * @param string $type
     * @return Evozon_Blog_Model_Filter this
     * @throws Evozon_Blog_Model_Exception_Validate
     */
    protected function _createConfig($type)
    {
        $modelName = Mage::getSingleton('evozon_blog/config')->getValidationModel($type);
        $model = Mage::getModel($modelName);
        
        if ($model === null) {
            throw new Evozon_Blog_Model_Exception_Validate('The filter model has not been defined or is missing.');
        }

        return $model;
    }

    /**
     * Check if there are any messages from the validation/filtering
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return boolean
     */
    public function hasMessages()
    {
        $messages = $this->getMessages();

        if (empty($messages)) {
            return false;
        }

        return true;
    }

}
