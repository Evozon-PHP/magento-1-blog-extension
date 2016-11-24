<?php

/**
 * Blog search types
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Adminhtml_System_Config_Source_Search_Engine
{
    public function toOptionArray()
    {
        $engines = $this->_getEngines();
        $options = array();
        foreach ($engines as $engine) {
            $options[] = array(
                'value' => $engine['model'],
                'label' => $engine['label']
            );
        }
        return $options;
    }

    /**
     * Get available engines
     *
     * @return array
     */
    protected function _getEngines()
    {
        $available = array();
        $engines = Mage::getModel('evozon_blog/search')->getConfig()->getEngines();

        foreach ($engines as $engine) {
            $available[] = array(
                'label' => $engine['engine']['label'],
                'model' => $engine['engine']['resource']
            );
        }

        return $available;
    }
}
