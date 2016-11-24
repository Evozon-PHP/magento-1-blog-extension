<?php

/**
 * Magento Helper, mainly used for translation issues.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Use this function in order to format and convert to locale the displayed date in the front end    
     * If format is not given, then take the default date and time format from system config
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @param  int|String $time - can accept to types: unix timestamp or mysql timestamp 
     * @param  null|string $format [d.m.Y], [d.M, Y], ...
     * @return String 
     */
    public function getTimeByFormatAndLocale($time, $format = null)
    {
        if ($format === null) {
            $format = $this->getConfigModel()->getGeneralConfig(Evozon_Blog_Model_Config_General::DATETIME_DEFAULT_FORMAT);
        }
        
        return $this->getLocaleDate($time, $format);
    }
    
    /**
     * This function is used in back end to return the date converted in the locale date
     * If the $time variable is not unixtime, but mysql timestamp, convert it to unix timestamp
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @param  int|string $time - can accept to types: unix timestamp or mysql timestamp
     * @param  null|string $format [d.m.Y], [d.M, Y], ...
     * @return string
     */
    public function getLocaleDate($time, $format = 'Y-m-d H:i:s')
    {           
        // data is provided as string, as mysql format: 2015-01-01 01:01:01. Convert to unix timestamp
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $dateObj = Mage::app()->getLocale()->date($time);

        return $dateObj->get(
                Zend_Locale_Format::convertPhpToIsoFormat($format), Mage::app()->getLocale()->getDefaultLocale()
        );
    }

    /**
     * Return the text truncated by words, add ... to the end
     * Eplode the text by space, verify if the length of the array is bigger than the maximum words number
     * and then slice the array and transform it in a string
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param string $text
     * @param int $wordsNr
     * @return string
     */
    public function truncateTextByWords($text, $wordsNr)
    {
        $textArray = explode(' ',$text);

        if (count($textArray) > $wordsNr && $wordsNr > 0) {
            $text = implode(' ', array_slice($textArray, 0, $wordsNr)) . ' ...';
        }

        return $text;
    }

    /**
     * Use this function to clean a string keeping only allowed characters. It will take two parameters
     * a array of allwed chars and a string that will be pharsed and cleaned
     * 
     * @author  Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @param   array $allowedCharsArray
     * @param   string $pharsedString
     * @return  string
     */
    public function keepOnlyAllowedChararacters($allowedCharsArray, $pharsedString)
    {
        $changedFlag = false;
        $characters = str_split($pharsedString);
        foreach ($characters as $char) {
            if ((ctype_digit($char) || ctype_alpha($char)) && !in_array($char, $allowedCharsArray)) {
                $changedFlag = true;
                continue;
            }
            $newCharsArray[] = $char;
        }

        // if wrong characters added: save after clean. 
        if ($changedFlag && isset($newCharsArray)) {
            $characters = $newCharsArray;
        }
        return trim(implode('', $characters));
    }

    /**
     * Getting the sorting order
     * Adding filter with the text "All" with id = -1, to know if to apply selected filter or not
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getAvailableCommentFilters()
    {
        return  Mage::getSingleton('evozon_blog/adminhtml_comment_status')->getUserCommentFilterArray();;
    }

    /**
     * Substitute for array_column function which is available only from php 5 >= 5.5.0
     * Creates an array by using one array for keys and another for its values
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @param array $array - A multi-dimensional array (record set) from which to pull a column of values.
     * @param mixed $columnKey - The column of values to return
     * @return type
     */
    public function arrayColumn(Array $array, $columnKey)
    {
        return array_map(function($element) use($columnKey) {
            return $element[$columnKey];
        }, $array);
    }
    
    /**
     * Return the content without widgets, html code and special characters
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param string $content
     * @return string
     */
    public function getContentPlainText($content)
    {
        // get the content without the widgets
        $content = preg_replace('#({{widget).*?(}})#', '', $content);
        
        // decode the special characters and replace the &nbsp into space for counting the words
        $decodedContent = html_entity_decode(str_replace('&nbsp;', ' ', $content), ENT_QUOTES);
        
        // strip html code
        $stripContent = strip_tags($decodedContent);
        
        // return the modified content
        return $stripContent;
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

    /**
     * Check if exist Flash Uploader
     *
     * @author Lilian Codreanu <lilian.codreanu@evozon.com>
     * @return bool
     */
    public function isNoFlashUploader()
    {
        try {
            return class_exists("Mage_Uploader_Block_Abstract");
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *
     * @author Lilian Codreanu <lilian.codreanu@evozon.com>
     * @return null|string
     */
    public function getFileGalleryJs()
    {
        return $this->isNoFlashUploader() ? "js/evozon/blog/post/gallery190.js" : "js/evozon/blog/post/gallery.js";

    }

    /**
     *
     * @author Lilian Codreanu <lilian.codreanu@evozon.com>
     * @return null|string
     */
    public function getFlowMin()
    {
        return $this->isNoFlashUploader() ? "lib/uploader/flow.min.js" : null;
    }

    /**
     *
     * @author Lilian Codreanu <lilian.codreanu@evozon.com>
     * @return null|string
     */
    public function getFustyFlow()
    {
        return $this->isNoFlashUploader() ? "lib/uploader/fusty-flow.js" : null;
    }

    /**
     *
     * @author Lilian Codreanu <lilian.codreanu@evozon.com>
     * @return null|string
     */
    public function getFustyFlowFactory()
    {
        return $this->isNoFlashUploader() ? "lib/uploader/fusty-flow-factory.js" : null;
    }

    /**
     *
     * @author Lilian Codreanu <lilian.codreanu@evozon.com>
     * @return null|string
     */
    public function getAdminhtmlUploaderInstance()
    {
        return $this->isNoFlashUploader() ? "mage/adminhtml/uploader/instance.js" : null;
    }
}
