<?php

/**
 * Abstract router. All custom blog routes will extend from this abstract. 
 * Main method match is implemented here.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Controller_AbstractRouter extends Mage_Core_Controller_Varien_Router_Standard
{
    /**
     * Frontend router defined in config for blog module
     */
    const EVOZON_BLOG_FRONTEND_ROUTER = 'blog';
    
    /**
     * Module name
     */
    const EVOZON_BLOG_MODULE = 'Evozon_Blog';
    
    /**
     * Request path: from base url to query string
     *
     * @var null|string
     */
    protected $_requestPath = null;
    
    /**
     * entity record id
     *
     * @var int|null
     */
    protected $_entityId = null;
    
    /**
     * Match the request
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {        
        // checking before even try to find out that current module
        // if forward to noroute ... no match should take place
        if (!$this->_beforeModuleMatch() || $request->getActionName() == 'noroute') {
            return false;
        }

        // set defaults
        $this->fetchDefault();
        $realModule = self::EVOZON_BLOG_MODULE;         
        $module     = self::EVOZON_BLOG_FRONTEND_ROUTER;   
        $controller = $this->getController();
        $action     = $this->getAction();                
        
        // get the path form base to query string
        $path = trim($request->getPathInfo(), '/');
        $path = ($path) ? $path : $this->_getDefaultPath();
        $p = $p = explode('/', $path);
        
        $this->_requestPath = array_filter($p);
        
        // if path is empty, there's no need to continue
        if (empty($this->_requestPath) || false === $this->matchPath()) {
            return false;
        }      
          
        // get controller class name
        $controllerClassName = $this->_validateControllerClassName($realModule, $controller);
        if (!$controllerClassName) {
            return false;
        }

        // instantiate controller class
        $front = $this->getFront();
        $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());

        if (!$controllerInstance->hasAction($action)) {
            return false;
        }
        
        // set values only after all the checks are done
        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);
        $request->setControllerModule($realModule);
        
        // set params
        foreach ($this->getParams() as $key => $value) {
            $request->setParam($key, $value);
        }
        
        $request->setRouteName($realModule);
      
        // dispatch action
        $request->setDispatched(true);
        $controllerInstance->dispatch($action);

        return true;
    }
       
     /**
     * Implement abstract method that will return the controller name that
     * will be dispatched
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return string
     */
    protected function getController()
    {
        $callingClass = get_called_class();
        
        return $callingClass::EVOZON_BLOG_ROUTER_CONTROLLER;
    }
    
    /**
     * Implement abstract method that will return the controller action that 
     * will be dispatched.
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return string
     */
    protected function getAction()
    {
        $callingClass = get_called_class();
        
        return $callingClass::EVOZON_BLOG_ROUTER_ACTION;
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
     * Return the params that has to be set on the request
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return array
     */
    abstract protected function getParams();
    
    /**
     * Match the router path against the logic the url are being generated
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>     
     * @param int|null $id
     * @return boolean
     */    
    abstract protected function matchPath();
}
