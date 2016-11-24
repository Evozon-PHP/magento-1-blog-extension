<?php

/**
 * Blog Validations Configuration model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2016 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Config_Validation
{
    /**
     * Validations tags validation model
     */
    const VALIDATION_TAGS_MODEL = 'model';
    
    /**
     * Maxlength on tags validation
     */
    const VALIDATION_TAGS_MAXLENGTH = 'maxlength';
    
    /**
     * Minlength on tags validation
     */
    const VALIDATION_TAGS_MINLENGTH = 'minlength';
    
    /**
     * Comments validation model
     */
    const VALIDATION_COMMENTS_MODEL = 'model';
    
    /**
     * Comments author maxlength
     */
    const VALIDATION_COMMENTS_AUTHOR_MAXLENGTH = 'author_maxlength';
    
    /**
     * Comments subject status (enabled, disabled)
     */
    const VALIDATION_COMMENTS_SUBJECT_STATUS = 'subject';
    
    /**
     * Comments subject maxlength
     */
    const VALIDATION_COMMENTS_SUBJECT_MAXLENGTH = 'subject_maxlength';
    
    /**
     * Comments content maxlength
     */
    const VALIDATION_COMMENTS_CONTENT_MAXLENGTH = 'content_maxlength';
}
