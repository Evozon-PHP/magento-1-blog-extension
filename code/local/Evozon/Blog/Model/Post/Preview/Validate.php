<?php
/**
 * File used to hold class for Validations
 *
 * @package     Evozon
 * @author      Murgocea Victorita <victorita.murgocea@evozon.com>
 * @copyright   @copyright   Copyright (c) 2015, Evozon
 */

/**
 * Class for Validate Preview keys
 *
 * @author   Murgocea Victorita <victorita.murgocea@evozon.com>
 */
class Evozon_Blog_Model_Post_Preview_Validate
{
    /* @var $_postId */
    protected $postId;

    /**
     * Evozon_Blog_Model_Post_Preview_Validate constructor.
     * @param $postId
     */
    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    /**
     * Get post Id
     *
     * @return mixed
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * Get preview key from admin session
     *
     * @return string|null
     */
    protected function getPreviewKeyForValidate()
    {
        $previewKey = null;

        //remember current session data
        $frontEndSessionId = Mage::getSingleton('core/session')->getSessionId();
        $frontEndSessionName = Mage::getSingleton('core/session')->getSessionName();

        if(filter_input(INPUT_COOKIE, 'adminhtml')){
            //switch to admin session
            $this->switchSession('adminhtml', filter_input(INPUT_COOKIE, 'adminhtml'));

            // Get admin session
            $adminSession = Mage::getModel('admin/session');
            $previewKey = $this->getPreviewKeyFromAdminSession($adminSession);

            //switch back to original session
            $this->switchSession($frontEndSessionName, $frontEndSessionId);
        }

        return $previewKey;
    }

    /**
     * Get preview key from admin session
     *
     * @param $adminSession
     * @return null
     */
    public function getPreviewKeyFromAdminSession($adminSession)
    {
        $previewKey = null;

        if($adminSession->hasData('preview_keys')) {

            $savedKeys = $adminSession->getData('preview_keys');
            $previewKey = isset($savedKeys[$this->getPostId()])
                ? $savedKeys[$this->getPostId()] : null;
        }

        return $previewKey;
    }

    /**
     * Switch session
     *
     * @param string $namespace (adminhtml|frontend)
     * @param string $id
     */
    private function switchSession($namespace, $id = null)
    {
        session_write_close();
        $GLOBALS['_SESSION'] = null;
        $session = Mage::getSingleton('core/session');
        if ($id) {
            $session->setSessionId($id);
        }
        $session->start($namespace);
    }

    /**
     * Validate preview key, throw exception if invalid
     *
     * @throws Exception
     * @param string $previewKey
     * @return bool
     */
    public function validatePreviewKeyWithException($previewKey)
    {
        if($previewKey !== $this->getPreviewKeyForValidate()) {
            Mage::throwException('Preview key is not valid');
        }
    }
}