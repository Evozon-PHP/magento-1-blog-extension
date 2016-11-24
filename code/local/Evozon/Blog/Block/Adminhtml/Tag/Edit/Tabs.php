<?php

/**
 * Tag tabs
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Tag_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('tag_options_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('evozon_blog')->__('Tag Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('values', array(
            'label' => Mage::helper('evozon_blog')->__('Manage Tag'),
            'title' => Mage::helper('evozon_blog')->__('Manage Tag'),
            'content' => $this->getLayout()->createBlock('evozon_blog/adminhtml_tag_edit_tab_options')
                ->setUseValidate(true)
                ->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
