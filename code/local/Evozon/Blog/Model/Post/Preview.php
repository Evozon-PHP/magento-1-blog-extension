<?php
/**
 * File used to hold class for post preview key
 *
 * @package     Evozon
 * @author      Murgocea Victorita <victorita.murgocea@evozon.com>
 * @copyright   Copyright (c) 2017, Evozon
 */

/**
 * Class for Post Preview Key
 *
 * @author   Murgocea Victorita <victorita.murgocea@evozon.com>
 */
class Evozon_Blog_Model_Post_Preview
{
    /* @var $_postId */
    protected $postId;

    /* @var $_source */
    protected $_source;

    /**
     * Evozon_Blog_Model_Post_Preview constructor
     * @param $postId
     * @param null $source
     */
    public function __construct($postId, $source = null)
    {
        $this->postId = $postId;

        if (!$source) {
            $this->_source =
                new Evozon_Blog_Model_Post_Preview_StoreKey(
                    new Evozon_Blog_Model_Post_Preview_StoreKeyAdaptor_Session(
                        $postId
                    )
                );
        }
    }

    /**
     * @return Evozon_Blog_Model_Post_Preview_StoreKey
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @param $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
     * Get post Id
     * @return mixed
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * Get preview key for post, if does not exist generate one
     *
     * @return string
     */
    public function getPreviewKey()
    {
        if (!$this->getSource()->hasKey()) {
            $postPreviewKey = $this->generatePreviewKey();
            $this->getSource()->setKey($postPreviewKey);
        }

        return $this->getSource()->getKey();
    }

    /**
     * Generate preview key
     *
     * @return string
     */
    public function generatePreviewKey()
    {
        return md5(uniqid(rand(), true));
    }
}