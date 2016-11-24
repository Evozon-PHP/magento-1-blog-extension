<?php

/**
 * Implement Form for Edit/Add new items
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Setup form fields for inserts/updates
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id' => 'edit_form',
            'action' => $this->getUrl(
                '*/*/save',
                array(
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
                )
            ),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
