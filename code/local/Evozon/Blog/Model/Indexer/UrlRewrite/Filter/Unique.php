<?php

/**
 * Unique path scanner and resolver container
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_UrlRewrite_Filter_Unique
    implements Evozon_Blog_Model_Indexer_UrlRewrite_Filter_Interface_Unique
{
    /**
     * Scanning engines collection
     * @var array
     */
    protected $_checks = array();

    /**
     * {@inheritdoc}
     * @param array|mixed $data
     */
    public function checkAndResolve(&$data)
    {
        foreach ($this->_checks as $checker) {
            $result = $checker->validate($data);

            if (true !== $result) {
                $checker->resolve($result, $data);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $class
     * @throws Exception
     */
    public function addScanner($class)
    {
        $checker = Mage::getModel($class);
        if (!$checker instanceof Evozon_Blog_Model_Indexer_UrlRewrite_Filter_Interface_Scanner) {
            throw Evozon_Blog_Model_Exception_IndexFactory::instance(10001);
        }

        $this->_checks[] = $checker;
    }
}