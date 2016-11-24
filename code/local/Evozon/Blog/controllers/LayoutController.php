<?php

/**
 * Layout controller that will handle blog layout rendering
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_LayoutController extends Mage_Core_Controller_Front_Action
{

    /**
     * Sets the propper layout handles
     * When it is selected an action from the post listing [tag, archive, etc]
     * The selected layout has to be maintained
     * First we remove the unneeded blocks, set the one column view and then check with template has been set
     * It is called instead of $this->loadLayout();
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function getBlogLayout()
    {
        $update = $this->getLayout()->getUpdate();

        $update->addHandle('default');
        $this->addActionLayoutHandles();

        // add default layout if is not one_column which is already added.
        $update->addHandle('evozon_blog_removable_blocks');
        $update->addHandle('evozon_blog_category_view_one_column');

        $defaultLayout = Mage::getSingleton('evozon_blog/config')->getGeneralConfig(Evozon_Blog_Model_Config_General::LAYOUT_DEFAULT);
        if ($defaultLayout != 'one_column') {
            $update->addHandle('evozon_blog_' . $defaultLayout);
        }

        $this->loadLayoutUpdates();
        $this->generateLayoutXml();
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
    }
    
    /**
     * If a post has a customized layout, it should be rendered in case of the default one
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Varien_Object $settings
     */
    public function getPostLayout($settings, $id)
    {
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        $update->addHandle('EVOZON_BLOG_POST_' . $id);
        $this->loadLayoutUpdates();

        // Apply custom layout update once layout is loaded
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates) {
            if (is_array($layoutUpdates)) {
                foreach($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }
        }

        $this->generateLayoutXml()
            ->generateLayoutBlocks();

        // Apply custom layout (page) template once the blocks are generated
        if ($settings->getPageLayout()) {
            $this->getLayout()->helper('page/layout')->applyTemplate($settings->getPageLayout());
        }
        
        $this->_isLayoutLoaded = true;
        
        return $this;
    }

}
