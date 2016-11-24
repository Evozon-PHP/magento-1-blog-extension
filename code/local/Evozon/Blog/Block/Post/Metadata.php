<?php

/**
 * Metadata: author, published date, number of comments
 * This block is shown in single and list blocks.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_Metadata extends Evozon_Blog_Block_Post_Abstract
{        
    /**
     * built-in constructor. Set the template, if not already set.
     */
    public function __construct() 
    {
        $template = $this->getTemplate();
        if (empty($template)) {
            $this->setTemplate('evozon/blog/post/metadata.phtml');
        }
    }      
}
