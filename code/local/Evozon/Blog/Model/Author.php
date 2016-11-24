<?php
/**
 * Author model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Author extends Mage_Core_Model_Abstract
{
    const EVOZON_BLOG_AUTHOR_GUEST = 0;
    const EVOZON_BLOG_AUTHOR_CUSTOMER = 1;
    const EVOZON_BLOG_AUTHOR_ADMIN = 2;

    /**
     * Author
     * 
     * @var Evozon_Blog_Model_Author_Interface_IAuthor
     */
    protected $_author;

    /**
     * array with the path for all author models
     * 
     * @var array
     */
    protected $_authorMap = array(
        self::EVOZON_BLOG_AUTHOR_GUEST => 'evozon_blog/author_guestAuthor',
        self::EVOZON_BLOG_AUTHOR_CUSTOMER => 'evozon_blog/author_customerAuthor',
        self::EVOZON_BLOG_AUTHOR_ADMIN => 'evozon_blog/author_adminAuthor'
    );

    /**
     * user id
     * 
     * @var int
     */
    protected $_user;
    
    /**
     * admin id to be used in later calls 
     * 
     * @var int 
     */
    protected $_admin;

    /**
     * Constructor
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function __construct(array $data)
    {
        parent::__construct();

        if (!empty($data))
        {
            $data['user_id'] = (isset($data['user_id']) ? $data['user_id'] : null);
            $this->setAuthorData($data);
            $this->_user = $data['user_id'];
            $this->_admin = $data['admin_id'];
        }
    }

    /**
     * Return author model (if guest => model for guest, admin_id != 0 => model 
     * for admin, if customer => model for customer)
     * 
     * @return Evozon_Blog_Model_Author_Interface_IAuthor
     */
    public function getAuthor()
    {
        if (!$this->_author) {
            $author = Mage::getModel($this->getAuthorClass());
            $author->setData($this->getData());

            $this->_author = $author;
        }

        return $this->_author;
    }

    /**
     * Setting data for the model
     *
     * @param array $data
     * @return $this
     */
    public function setAuthorData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Get the author class
     *
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function getAuthorClass()
    {
        return $this->_authorMap[$this->getAuthorGroup()];
    }

    /**
     * Get author group
     *
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function getAuthorGroup()
    {
        // when it is used from the Post model, default value - admin
        $authorGroup = self::EVOZON_BLOG_AUTHOR_ADMIN;

        if (!is_null($this->_user)) {
            // default value - guest
            $authorGroup = self::EVOZON_BLOG_AUTHOR_GUEST;

            // if admin id is bigger than customer id => the author is an admin
            if ($this->_admin > $this->_user) {
                $authorGroup = self::EVOZON_BLOG_AUTHOR_ADMIN;
            }

            // if customer id is bigger than admin id => the author is a customer
            if ($this->_admin < $this->_user) {
                $authorGroup = self::EVOZON_BLOG_AUTHOR_CUSTOMER;
            }
        }

        return $authorGroup;
    }

}
