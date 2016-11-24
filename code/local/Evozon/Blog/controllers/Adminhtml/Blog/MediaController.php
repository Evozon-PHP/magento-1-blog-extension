<?php

/**
 * Media controller
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Adminhtml_Blog_MediaController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Upload image action
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function uploadAction()
    {
        try {
            $uploader = new Mage_Core_Model_File_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            
            $uploader->addValidateCallback('evozon_blog_post_image',
                Mage::helper('evozon_blog/post_image'), 'validateUploadFile');
            
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            
            $result = $uploader->save(
                Mage::getSingleton('evozon_blog/post_media_config')->getBaseTmpMediaPath()
            );

            Mage::dispatchEvent('evozon_blog_post_gallery_upload_image_after', array(
                'result' => $result,
                'action' => $this
            ));

            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            $result['url'] = Mage::getSingleton('evozon_blog/post_media_config')->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );

        } catch (Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
