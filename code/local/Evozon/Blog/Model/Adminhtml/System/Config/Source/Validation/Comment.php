<?php

/**
 * Source model for comment validator file
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2016 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Source_Validation_Comment
{

    /**
     * Options getter
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function toOptionArray()
    {
        $options = Mage::getSingleton('evozon_blog/filter_config')->getDataByValidatorType(Evozon_Blog_Model_Filter_Config::XML_PATH_BLOG_VALIDATION_TYPE_COMMENTS);

        $commentValidatorsArray = array();
        foreach ($options as $commentValidators) {
            if (isset($commentValidators['model']) && isset($commentValidators['label'])) {
                $commentValidatorsArray[] = array(
                    'value' => $commentValidators['model'],
                    'label' => $commentValidators['label']
                );
            }
        }

        return $commentValidatorsArray;
    }

}
