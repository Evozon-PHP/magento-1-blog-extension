<?php

/**
 * Interface that is required in order to make sure that the filter classes
 * have all the needed information
 * It contains the getters called by the Evozon_Blog_Model_Filter_Factory model
 * and that have to be implemented in the children
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
interface Evozon_Blog_Model_Filter_Interface
{

    public function getFilters();

    public function getValidators();

    public function getOptions();
}
