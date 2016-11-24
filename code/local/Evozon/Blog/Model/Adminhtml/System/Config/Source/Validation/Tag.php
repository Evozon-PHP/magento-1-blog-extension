<?php

/**
 * Source model for tag validator file
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2016 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Source_Validation_Tag
{

    /**
     * Options getter
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function toOptionArray()
    {
        $options = Mage::getSingleton('evozon_blog/filter_config')->getDataByValidatorType(Evozon_Blog_Model_Filter_Config::XML_PATH_BLOG_VALIDATION_TYPE_TAGS);

        $tagValidatorsArray = array();
        foreach ($options as $tagValidators) {
            if (isset($tagValidators['model']) && isset($tagValidators['label'])) {
                $tagValidatorsArray[] = array(
                    'value' => $tagValidators['model'],
                    'label' => $tagValidators['label']
                );
            }
        }

        return $tagValidatorsArray;
    }

}
