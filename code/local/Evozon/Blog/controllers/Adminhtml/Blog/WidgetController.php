<?php

/**
 * Widget controller for the gallery insertion used in WYSIWYG editor on Post Edit page, Content tab
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Adminhtml_Blog_WidgetController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Wisywyg widget plugin main page
     */
    public function indexAction()
    {
        $this->loadLayout('empty')->renderLayout();
    }

    /**
     * Ajax responder for loading widget parameters layout based on the widget type and the values received
     * If no parameters are received, render the block
     */
    public function loadFieldsAction()
    {
        try {
            $this->loadLayout('empty');
            $paramsJson = $this->getRequest()->getParam('widget');

            if (empty($paramsJson)) {
                $this->renderLayout();
            }

            $request = Mage::helper('core')->jsonDecode($paramsJson);
            if (is_array($request)) {
                $optionsBlock = $this->getLayout()->getBlock('gallery_widget.options');

                (isset($request['widget_type'])) ? $optionsBlock->setWidgetType($request['widget_type']) : '';
                (isset($request['values'])) ? $optionsBlock->setWidgetValues($request['values']) : '';
                
                $this->renderLayout();
            }
        } catch (Mage_Core_Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Format widget pseudo-code for inserting into wysiwyg editor
     * It is the widget form action which is called on "Insert Gallery" button click
     */
    public function buildWidgetAction()
    {
        $type = $this->getRequest()->getPost('widget_type');
        $params = $this->getRequest()->getPost('parameters', array());
        $asIs = $this->getRequest()->getPost('as_is');
        $html = Mage::getSingleton('widget/widget')->getWidgetDeclaration($type, $params, $asIs);
        $this->getResponse()->setBody($html);
    }

}
