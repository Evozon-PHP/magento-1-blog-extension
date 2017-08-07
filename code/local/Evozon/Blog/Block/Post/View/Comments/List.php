<?php

/**
 * Comments block for post single page
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Block_Post_View_Comments_List extends Evozon_Blog_Block_Post_Abstract
{
    /**
     * Comments are allowed
     */
    const EVOZON_BLOG_POST_ALLOW_COMMENTS = 1;
    
    /**
     * The user have bo logged in to add a new comment
     */
    const EVOZON_BLOG_POST_LOGGED_IN_TO_ADD_COMMENTS = 0;
    
    /**
     * Comments are disabled for 
     */
    const EVOZON_BLOG_POST_DISABLE_COMMENTS = -1;
    
    /**
     * Set an array with the confid data
     * 
     * @var array 
     */
    protected $_config = array();

    /**
     * Comments collection by which the pagination will be set
     * 
     * @var Evozon_Blog_Model_Comment
     */
    protected $_commentsCollection = null;

    /**
     * Subcomments array
     * 
     * @var array
     */
    protected $_commentsThread = null;

    /**
     * @var string
     */
    private $infoMessage = '';

    /**
     * Built-in constructor, set the template, if not already set.
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function __construct()
    {
        $this->getConfigParams();
    }

    public function getTemplate()
    {
        return 'evozon/blog/post/view/comments/list.phtml';
    }

    /**
     * Getting comments from the post
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Comment
     */
    protected function _getCommentsCollection()
    {
        if (is_null($this->_commentsCollection)) {
            $postCommentsCollection = $this->getPostInstance()->getComments();
            $postCommentsCollection->setProperDateFormat();
            
            $this->_commentsCollection = $postCommentsCollection;
        }

        return $this->_commentsCollection;
    }

    /**
     * Accesing the loaded comments collection to display in the list
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getLoadedCommentsCollection()
    {
        $this->_getCommentsCollection();

        if ($this->getComments() == null) {
            return $this->_getThreadComments();
        }

        return $this->getComments();
    }

    /**
     * Loads children comments for all the level 0 comments
     * Sets comment data to be retrieved for display in the view
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function _getThreadComments()
    {
        if (null === $this->_commentsThread) {
            $multidimensionalCommentsArray = array();

            // get the comments from the first level
            $parentComments = $this->_commentsCollection->getItems();
            if (!empty($parentComments)) {
                $parentIds = array();
                foreach ($parentComments as $comment) {
                    $parentIds[] = $comment->getId();
                }

                // get children for the parent comments
                $childCommentsCollection = Mage::getModel('evozon_blog/comment')->getCollection();
                $childCommentsCollection
                    ->getChildren($parentIds)
                    ->setProperDateFormat();

                // merge children array with the parents array && create the multidimentional array with all the comments
                $parentAndChildrenComments = array_merge($parentComments, $childCommentsCollection->getItems());
                $multidimensionalCommentsArray = $this->getMultidimensionalCommentsArray($parentAndChildrenComments);
            }

            $this->_commentsThread = $multidimensionalCommentsArray;
            $this->setData('comments', $this->_commentsThread);
        }

        return $this->_commentsThread;
    }

    /**
     * Return a multidimensional array for comments
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param array $allComments
     * @return array
     */
    protected function getMultidimensionalCommentsArray($allComments)
    {        
        $comments = array();

        // go through all the comments
        foreach ($allComments as $comment) {
            $row = $comment->getData();
            $row['children'] = array();
            $vn = "row" . $row['id'];
            ${$vn} = $row;
            // verify if the parent is not null
            if (!is_null($row['parent_id'])) {
                $vp = "parent" . $row['parent_id'];
                if (isset($data[$row['parent_id']])) {
                    ${$vp} = $data[$row['parent_id']];
                } else {
                    ${$vp} = array('id' => $row['parent_id'], 'parent_id' => null, 'children' => array());
                    $data[$row['parent_id']] = &${$vp};
                }
                ${$vp}['children'][] = &${$vn};
                $data[$row['parent_id']] = ${$vp};
            }
            $data[$row['id']] = &${$vn};
        }
        
        // verify if the data is not empty
        if (!empty($data)) {
            $result = array_filter($data, function($elem) {
                return is_null($elem['parent_id']);
            });
            $comments = $result[0]['children'];
        }

        return $comments;
    }

    /**
     * Get parameters from config and set the value in the _config array
     * Verify if the value is set or the value is bigger than the maximum level for reply
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getConfigParams()
    {
        if (empty($this->_config)) {
            $configModel = $this->getConfigModel();
            $this->_config['allow_guest_comments'] = $configModel->getCommentsConfig(Evozon_Blog_Model_Config_Comment::ALLOW_GUEST_COMMENTS);

            $maxReplyLevel = $configModel->getCommentsConfig(Evozon_Blog_Model_Config_Comment::SINGLE_PAGE_REPLY_LEVEL);
            $systemMaxLevel = (int) Evozon_Blog_Model_Config::getCommentMaxReplyLevelConfig();
            
            $this->_config['max_reply_level'] = $maxReplyLevel;
            if (!$maxReplyLevel || $maxReplyLevel > $systemMaxLevel) {
                $this->_config['max_reply_level'] = $systemMaxLevel;
            }
        }
    }

    /**
     * Accessing config params
     * 
     * @param string $param
     * @return array
     */
    public function getConfigParam($param)
    {
        if (!is_null($param)) {
            return $this->_config[$param];
        }

        return $this->_config;
    }

    /**
     * Verify if the reply link should be shown by
     * checking first if the user can add comments and
     * the level of the comment is smaller than the maximum reply level
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param int $level
     * @return boolean
     */
    public function canReply($level)
    {
        if ($this->haveAccessToAddComment() == self::EVOZON_BLOG_POST_ALLOW_COMMENTS && $level < $this->getConfigParam('max_reply_level')) {
            return true;
        }

        return false;
    }

    /**
     * Verify if the comments are disabled or not, if the user has to be logged in to add a comment
     * Set the message with the proper text depending on the post configurations
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return int
     */
    public function haveAccessToAddComment()
    {
        // comments for the current post are disabled
        if (!$this->getPostInstance()->getCommentStatus()) {
            $this->infoMessage =  $this->__('Comments for this post are disabled.');
            
            return self::EVOZON_BLOG_POST_DISABLE_COMMENTS;
        }

        // the user has to be logged in to add a new comment
        if ((!Mage::getSingleton('customer/session')->isLoggedIn() && !(bool) $this->getConfigParam('allow_guest_comments'))) {
            $this->infoMessage = $this->__('You have to <a href="%s">log in</a> in order to leave a comment.', Mage::getUrl('customer/account/login'));


            return self::EVOZON_BLOG_POST_LOGGED_IN_TO_ADD_COMMENTS;
        }

        // the add new comment form should be displayed
        return self::EVOZON_BLOG_POST_ALLOW_COMMENTS;
    }


    /**
     * Return the message to inform the user if the comments are disabled or if
     * the user has to be logged in to add a comment
     * 
     * @return string
     */
    public function getInfoMessage()
    {
        return $this->infoMessage;
    }
    
    /**
     * Depending if the block has been created with post instance or by setting the post id,
     * the call has to be of Evozon_Blog_Model_Post type
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Post
     */
    protected function getPostInstance()
    {
        if ($this->hasPostId())
        {
            return Mage::getSingleton('evozon_blog/post')->load($this->getPostId());
        }
        
        return $this->getPost();
    }
}
