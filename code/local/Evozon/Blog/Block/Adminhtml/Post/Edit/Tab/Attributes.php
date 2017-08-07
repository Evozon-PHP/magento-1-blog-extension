<?php

/**
 * Gets groups` attributes and puts them in the form
 * It will generate existing groups (General, Content, Meta Information, Restrictions)
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Catalog_Form
{

    /**
     * constant to access the form
     * 
     * @var Mage_Adminhtml_Block_Widget_Form 
     */
    protected $_form = null;

    /**
     * constant to access the fieldset
     * 
     * @var Mage_Adminhtml_Block_Widget_Form 
     */
    protected $_fieldset = null;

    /**
     * Load Wysiwyg on demand and prepare layout
     * 
     * @access protected
     * @return void
     * @see Mage_Adminhtml_Block_Widget_Form::_prepareLayout()
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    /**
     * Prepare the attributes for the form
     *
     * @access protected
     * @return void
     * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
     */
    protected function _prepareForm()
    {
        $group = $this->getGroup();

        //seting defaults
        $_helper = Mage::helper('evozon_blog');
        $post = $this->getPost();

        if ($group) {
            $form = new Varien_Data_Form();
            $form->setDataObject($post);

            $fieldset = $form->addFieldset('group_fields' . $group->getId(),
                array(
                'legend' => $_helper->__($group->getAttributeGroupName()),
                'class' => 'fieldset-wide'
            ));

            //setting globals
            $this->_form = $form;
            $this->_fieldset = $fieldset;

            $attributes = $this->getAttributes();
            $this->_setFieldset($attributes, $fieldset);

            if ($form->getElement('created_at')) {
                $this->getTimeFieldset();
            }

            //creating the authorArea after the date-time fieldset
            $authorArea = $form->getElement('admin_id');
            if ($authorArea) {
                $this->getAdminFieldset();
            }

            //setting publish date dependencies to publish time
            $publishDateArea = $form->getElement('publish_date');
            if ($publishDateArea) {
                $this->getPublishDateDetails();
            }

            foreach ($attributes as $attribute) {
                $attribute->setEntity(Mage::getResourceModel('evozon_blog/post'));
            }

            $formValues = $post->getData();

            if (!$post->getId()) {
                foreach ($attributes as $attribute) {
                    if (!isset($formValues[$attribute->getAttributeCode()])) {
                        $formValues[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                    }
                }
            }

            if ($this->getPost()->getCustomUseDefaultSettings()) {
                foreach ($this->getPost()->getDesignAttributes() as $attributeCode) {
                    if (($element = $form->getElement($attributeCode)) 
                        && ($attributeCode!='custom_use_default_settings')
                    ){
                        $element->setDisabled(true);
                    }
                }
            }

            if ($element = $form->getElement('custom_use_default_settings')) {
                $element->setData('onchange', 'onCustomUseDefaultChanged(this)');
            }

            if ($this->getPost()->hasLockedAttributes()) {
                foreach ($this->getPost()->getLockedAttributes() as $attribute) {
                    if ($element = $form->getElement($attribute)) {
                        $element->setReadonly(true, true);
                    }
                }
            }

            $form->addValues($formValues);
            $form->setFieldNameSuffix('post');

            Mage::dispatchEvent('adminhtml_evozon_blog_post_edit_prepare_form', array('form' => $form));

            $this->setForm($form);
        }
    }

    /**
     * get current entity
     *
     * @access protected
     * @return Evozon_Blog_Model_Posts
     */
    protected function getPost()
    {
        return Mage::registry('evozon_blog');
    }

    /**
     * Creates Author and Owner fieldsets in the General tab
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Attributes
     */
    protected function getAdminFieldset()
    {
        $this->getOwnerFieldset();
        $this->getAuthorFieldset();

        return $this;
    }

    /**
     * Creates a fieldset to display owner information
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return $this
     */
    protected function getOwnerFieldset()
    {
        $post = $this->getPost();
        if ($post->getId())
        {
            $owner = $post->getOwner();
            $fieldset = $this->_form->addFieldset('owner_fieldset',
                array('legend' => Mage::helper('evozon_blog')->__('Created by')));

            $fieldset->addField('owner', 'label',
                array(
                    'label' => Mage::helper('evozon_blog')->__('Owner'),
                    'value' => $owner->getFirstName() . ' ' . $owner->getLastName() . ' (' . $owner->getEmail(). ')',
                    'readonly' => true
                ));

        }

        return $this;
    }

    /**
     * Selecting author fields and creating a fieldset containing them
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return $this
     */
    protected function getAuthorFieldset()
    {
        $email = $this->_form->getElement('author_email');
        $firstname = $this->_form->getElement('author_firstname');
        $lastname = $this->_form->getElement('author_lastname');

        $fieldset = $this->_form->addFieldset('author_fieldset',
            array('legend' => $this->__('Author details')));

        $this->_fieldset->removeField($email->getId());
        $this->_fieldset->removeField($lastname->getId());
        $this->_fieldset->removeField($firstname->getId());

        $fieldset->addField('warning', 'label', array(
                'label' => '',
                'value' => $this->__('If you decide to set a different author than yourself, complete these fields.')
            )
        );

        $fieldset->addField($firstname->getId(), 'text', $firstname->getData());
        $fieldset->addField($lastname->getId(), 'text', $lastname->getData());
        $fieldset->addField($email->getId(), 'text', $email->getData());

        return $this;
    }

    /**
     * Setting dependencies on the status and publish date
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Attributes
     */
    protected function getPublishDateDetails()
    {
        // prepare default values for publish time
        $publishTime = implode(',',
            $this->getPost()->getProperPublishTime());
        $this->getPost()->setPublishTime($publishTime);

        // remove published date field
        $this->_fieldset->removeField('publish_date');

        // add scheduled date      
        $this->_fieldset->addField('scheduled_date',
            'date',
            array(
            'name' => 'scheduled_date',
            'title' => Mage::helper('evozon_blog')->__('Scheduled Date'),
            'label' => Mage::helper('evozon_blog')->__('Scheduled Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'value' => $this->getPost()->getPublishDate(),
            'disabled' => false,
            'required' => true,
            'tabindex' => 1,
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));

        // add scheduled time      
        $this->_fieldset->addField('scheduled_time',
            'time',
            array(
            'name' => 'scheduled_time',
            'label' => Mage::helper('evozon_blog')->__('Scheduled Time'),
            'after_element_html' =>
            '<br /><small>' .
            Mage::helper('evozon_blog')->__('In addition to scheduled date, you can add the time when you want the article to be visible in frontend.') .
            '</small>',
            'value' => $publishTime,
            'disabled' => false,
            'readonly' => false
        ));

        // append dependency javascript to show schedule date and time only for pending status
        $this->setChild('form_after',
            $this->getLayout()
                ->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap('status',
                    'status')
                ->addFieldMap('scheduled_date',
                    'scheduled_date')
                ->addFieldMap('scheduled_time',
                    'scheduled_time')
                ->addFieldDependence('scheduled_date',
                    'status',
                    Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PENDING)
                ->addFieldDependence('scheduled_time',
                    'status',
                    Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PENDING)
        );

        return $this;
    }

    /**
     * Creating a new fieldset (Time) in General tab to display info about the post 
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Attributes
     */
    protected function getTimeFieldset()
    {
        $createDate = clone $this->_form->getElement('created_at');
        $this->_fieldset->removeField('created_at');

        $updateDate = clone $this->_form->getElement('updated_at');
        $this->_fieldset->removeField('updated_at');

        if ($this->getPost()->getId()) {
            // fieldset data details
            $fieldset = $this->_form->addFieldset('time_fieldset',
                array('legend' => Mage::helper('evozon_blog')->__('Time')));

            $fieldset->addElement($createDate);
            $fieldset->addElement($updateDate);

            $hasPublishDate = (bool) $this->getPost()->getPublishDate();
            $publishDateFieldConfig = array(
                'label' => Mage::helper('evozon_blog')->__('Publishing date'),
                'title' => Mage::helper('evozon_blog')->__('Publishing date'),
                'value' => ($this->getPost()->getPublishDate() ? $this->getPost()->getPublishDate() : '-')
            );

            if ($hasPublishDate) {
                $publishDateFieldConfig = array_merge($publishDateFieldConfig, array(
                    'name' => 'publish_date',
                    'format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
                    'time' => true,
                    'image' => $this->getSkinUrl('images/grid-cal.gif')
                ));
            }

            $fieldset->addField('publish_date',
                ($hasPublishDate) ? 'date' : 'label',
                $publishDateFieldConfig
            );
        }

        return $this;
    }

    /**
     * Retrieve additional element types
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'gallery' => Mage::getConfig()->getBlockClassName('evozon_blog/adminhtml_post_helper_form_gallery'),
            'image' => Mage::getConfig()->getBlockClassName('evozon_blog/adminhtml_post_helper_form_image'),
        );

        return $result;
    }

}
