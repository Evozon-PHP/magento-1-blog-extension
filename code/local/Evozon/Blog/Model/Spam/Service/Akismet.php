<?php

/**
 * Akismet spam check service
 * 
 * @package     Evozon_Blog
 * @author      Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author      Lilian Codreanu <lilian.codreanu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Spam_Service_Akismet 
    extends Evozon_Blog_Model_Spam_Service_Abstract
    implements Evozon_Blog_Model_Spam_Service_Interface
{
    /**
     * Akismet check comment
     */
    const SPAM_CHECKER_SERVICE_PATH = '/1.1/comment-check';

    /**
     * Akismet host
     */
    const SPAM_CHECKER_SERVICE_HOST = 'rest.akismet.com';

    /**
     * Akismet key
     */
    const SPAM_CHECKER_KEY_PATH = '/1.1/verify-key';

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var array
     */
    protected $requestBody;

    /**
     * Set the uri
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param string $uri
     * @return \Evozon_Blog_Model_Comment_Spam_Service_Akismet
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Return the uri
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return type
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Return the data for request
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return type
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    /**
     * Set the data for request
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param array $requestBody
     * @return \Evozon_Blog_Model_Comment_Spam_Service_Akismet
     */
    public function setRequestBody($requestBody)
    {
        $this->requestBody = $requestBody;

        return $this;
    }

    /**
     * Return true is the comment is spam
     * 
     * @param Varien_Object $object
     * @return bool
     * @throws Exception
     */
    public function checkIsSpam(Varien_Object $object)
    {
        // verify if the spam checker is enabled
        if ($this->isEnabled()) {
            return $this->checkSpam($object);
        }
    }

    /**
     * Implement Akismet API checker
     *
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  Evozon_Blog_Model_Comment $comment
     * @throws Exception
     * @return boolean
     */
    protected function checkSpam(Evozon_Blog_Model_Comment $comment)
    {
        // set defaults
        $akismetApiKey = (string) $this->getConfigModel()->getCommentsSpamCheckerConfig(Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_AKISMET_API_KEY);
        // check if key is set, if not, throw exception
        if (empty($akismetApiKey)) {
            throw new Exception('No spam service API Key defined.');
        }
        
        $this->setUri('https://' . $akismetApiKey . '.' . self::SPAM_CHECKER_SERVICE_HOST . self::SPAM_CHECKER_SERVICE_PATH);
        $response = $this->setRequestBody(array(
            'blog' => Mage::getBaseUrl(),
            'user_ip' => Mage::helper('core/http')->getRemoteAddr(),
            'user_agent' => Mage::helper('core/http')->getHttpUserAgent(),
            'referrer' => Mage::helper('core/http')->getHttpReferer(),
            'permalink' => Mage::helper('core/url')->getHomeUrl(),
            'comment_type' => 'comment',
            'comment_author' => $comment->getData('author'),
            'comment_author_email' => $comment->getData('author_email'),
            'comment_author_url' => '',
            'comment_content' => $comment->getData('content')
        ))->getResponse();

        if (!$this->isKeyStillAvailable($response)) {
            // if Key are not available disable akismet and notice the admin
            $this->disableSpamChecker();
            throw new Exception('Invalid service API Key defined.');
        }

        if ($response->getBody() == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Verify if the Akismet key is valid
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @throws Exception
     * @return boolean
     */
    public function isKeyValid()
    {
        $akismetApiKey = (string) $this->getConfigModel()->getCommentsSpamCheckerConfig(Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_AKISMET_API_KEY);
        if (empty($akismetApiKey)) {
            throw new Exception('No spam service API Key defined.');
        }
        
        $this->setUri('https://' . self::SPAM_CHECKER_SERVICE_HOST . self::SPAM_CHECKER_KEY_PATH);
        // set the properly type of data for request
        $this->setRequestBody(array(
            'key' => $akismetApiKey,
            'user_agent' => Mage::helper('core/http')->getHttpUserAgent(),
            'blog' => urlencode(Mage::getBaseUrl())
        ));

        $response = $this->getResponse();
        if ($response->getBody() == 'valid') {
            return true;
        }

        return false;
    }

    /**
     * Gets the client and set the post parameters and the uri
     * Return the response from spam checker
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Zend_Http_Response
     * @throws Zend_Http_Client_Exception
     */
    private function getResponse()
    {
        $client = $this->getClient();
        $client->setParameterPost($this->getRequestBody());
        $client->setUri($this->getUri());
        
        $response = $client->request('POST');
        
        return $response;
    }

    /**
     * Verify if the response headers contain guid to know if the key is still available
     * 
     * @param Zend_Http_Response $response
     * @return bool
     */
    protected function isKeyStillAvailable(Zend_Http_Response $response)
    {
        if (!array_key_exists('X-akismet-guid', $response->getHeaders())) {
            return false;
        }
        
        return true;
    }
}
