<?php

/**
 * Implement checkboxes switcher in System Config for post url
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Backend_PostUrl extends Mage_Core_Model_Config_Data
{
    /**
     * path to the fields in system.xml
     */
    const POST_URL_STRING_PATH = 'sections/evozon_blog_post/groups/post_url/fields';    
    
    /**
     *  this is how data should look like:
     * 
     *  [post_url] => Array
           (
               [fields] => Array
                   (
                       [use_html_sufix] => Array
                           (
                               [value] => 1
                           )

                       [posturl_month_name] => Array
                           (
                               [value] => 1
                           )

                       [posturl_day_name] => Array
                           (
                               [value] => 0
                           )

                       [posturl_name] => Array
                           (
                               [value] => 0
                           )

                       [posturl_numeric] => Array
                           (
                               [value] => 0
                           )

                   )

           )
     * without this _afterSave and individual save of each record, the values with 0 (unchecked) will not be saved.
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return \Evozon_Blog_Model_Adminhtml_System_Config_Backend_PostUrl
    */
    protected function _afterSave() 
    {
        parent::_afterSave();
        $config = Mage::getConfig()
               ->loadModulesConfiguration('system.xml')        
               ->getNode(self::POST_URL_STRING_PATH)
               ->asArray();         
                 
        // get only the keys from path sections/evozon_blog_post/groups/post_url/fields
        $options = array_keys($config);       
        foreach ($options as $option) {
            
            // if the option does not begin with 'posturl' or is the field checked skip.
            if (substr($option, 0, 7) != 'posturl' || $this->getField() == $option) {
                continue;
            }
            
            try {
                Mage::getModel('core/config_data')
                        ->load(Evozon_Blog_Model_Config::XML_PATH_BLOG_POST_URL.'/'.$option, 'path')
                        ->setValue(0)
                        ->setPath(Evozon_Blog_Model_Config::XML_PATH_BLOG_POST_URL.'/'.$option)
                        ->save();
            } catch (Exception $e) {
                throw new Exception('Unable to save the post url values: %s', $e->getMessage());
            }
        }                                    
        
        return $this;
    }
}
