<?php

/**
 * Create checkboxes for post_url.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_System_Config_Form_PostUrl extends Mage_Adminhtml_Block_System_Config_Form_Field
{    
    /**
     * Overwrite parent method in order to add onclick for element
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {           
        $patterns = $this->getOptionArray();

        foreach ($patterns as $key => $pattern) {
            if (strstr($element->getHtmlId(), $key)) {
                break;
            }
        }

        $html = '<input id="' . $element->getHtmlId() . '" name="' . $element->getName()
            . '" value="' . $element->getEscapedValue() . '" ' . $element->serialize($element->getHtmlAttributes()) .
            ' data-pattern="' . $pattern . '" rel="post-url" onclick="postUrlSelect(this)"/>' . "\n";
        $html.= $this->getAfterElementHtml($element);
        
        return $html;
    }   
    
    /**
     * Add javascript method to make clicked checkbox checked
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return String
     */
    protected function getAfterElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $afterHtmlElement = $element->getData('after_element_html');
               
        return $afterHtmlElement;
    }
    
    /**
     * Create an array with option-values from url model constants
     * 
     * @return array
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getOptionArray()
    {    
       $options = array(
            'posturl_day_name'   => Evozon_Blog_Model_Post_Url::EVOZON_BLOG_CONFIG_FORMAT_POSTURL_DAY_NAME,
            'posturl_month_name' => Evozon_Blog_Model_Post_Url::EVOZON_BLOG_CONFIG_FORMAT_POSTURL_MONTH_NAME,
            'posturl_name'       => Evozon_Blog_Model_Post_Url::EVOZON_BLOG_CONFIG_FORMAT_POSTURL_NAME,
            'posturl_numeric'    => Evozon_Blog_Model_Post_Url::EVOZON_BLOG_CONFIG_FORMAT_POSTURL_NUMERIC
        );
       
        return $options;
    }
}
