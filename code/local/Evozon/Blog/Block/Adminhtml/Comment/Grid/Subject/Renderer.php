<?php

/**
 * Display also the content of the comment in the subject column
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Comment_Grid_Subject_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Display the subject and the content for comment
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param Evozon_Blog_Model_Comment $row
     * @return string
     */
    public function render(Varien_Object $row)
    {        
        // get the value from the grid
        $value = '<b>' . $row->getData($this->getColumn()->getIndex()) . '</b>';

        // get the id of the loaded model
        $commentId = $row->getData('id');               
        
        //get the number of words for content      
        $wordsNumber = $this->getConfigModel()->getCommentsConfig(Evozon_Blog_Model_Config_Comment::CONTENT_WORDS_NUMBER);
        
        // truncate the content after the words number from config
        $truncatedContent = Mage::helper('evozon_blog')->truncateTextByWords($row->getContent(), $wordsNumber);
        
        // explode content to find out the words number
        $contentWordsNumber = explode(' ',$row->getContent());
        
        // if the content was truncted add the read more link (words number of the content is bigger than the words number from config)
        $content = (count($contentWordsNumber) > $wordsNumber ? 
            $truncatedContent . ' <a onclick="toggleContent(' . $commentId . ')">'. Mage::helper('evozon_blog')->__('Read More') . '</a>' : 
            $truncatedContent
        );
        
        // add two divs, one for read more (dissapears when the admin clicks on it) 
        // and another for show less (it appears only when the admin clicks on the read more link)
        $value .= '<div class="comments-grid-content-links" id="comment_subject_read_more_' . $commentId . '">'. $content .'</div>'.
            '<div class="comments-grid-content-links" id="comment_show_less_' . $commentId . '" style="display:none;">' . $row->getContent() . 
                ' <a onclick="toggleContent(' . $commentId . ')">' . Mage::helper('evozon_blog')->__('Show less') . '</a>' . 
            '</div>';
        
        return $value;
    }
    
    /**
     * Return the config model
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Model_Config
     */
    public function getConfigModel()
    {
        return Mage::getSingleton('evozon_blog/config');
    }
}
