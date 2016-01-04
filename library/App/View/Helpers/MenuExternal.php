<?php

class Zend_View_Helper_MenuExternal extends Zend_View_Helper_Abstract
{
    /**
     *
     * @var DOMDocument
     */
    protected $_dom;
    
    /**
     * 
     */
    protected $_requestedUri;
    
    /**
     *
     * @var DomElement
     */
    protected $_separator;
    
    /**
     *
     * @var boolean
     */
    protected $_active = false;
    
    /**
     *
     * @var string
     */
    protected $_activePath;
    
    /**
     *
     * @var array
     */
    protected $_menus = array(
	array(
	    'label' =>	'Home',
	    'icon'  =>	'icon-home',
	    'url'   =>	'/external'
	),
	array(
	    'label' =>	'PCE',
	    'children'	=>  array(
		array(
		    'label' => 'CEG',
		    'url'   => 'external/pce/ceg/'
		),
		array(
		    'label' => 'CEC',
		    'url'   => 'external/pce/cec'
		),
		array(
		    'label' => 'CED',
		    'url'   => 'external/pce/ced'
		),
	    )
	)
    );
    
    /**
     * 
     */
    public function __construct()
    {
	$this->_dom = new DOMDocument();
	
	$this->_initRoute();
    }
    
    /**
     *
     * @return \Zend_View_Helper_Menu 
     */
    public function menuExternal()
    {
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function __toString()
    {
	return $this->render();
    }
    
    /**
     *
     * @return string
     */
    public function render()
    {
	$ulMenu = $this->_dom->createElement( 'ul' );
	$ulMenu->setAttribute( 'class', 'nav' );
	
	// Create all the another itens
	foreach ( $this->_menus as $menu )
	    $this->_addChildMenu( $menu, $ulMenu, true );
	
	// Is there is no item active, define home
	$liHome = $ulMenu->childNodes->item( 0 );
	
	if ( !$this->_active )
	    $this->_setActive( $liHome, true );
	
	$this->_dom->appendChild( $ulMenu );
	return $this->_dom->saveHTML();
    }
    
    /**
     *
     * @param array $item
     * @param DomElement $parentContainer
     * @return bool 
     */
    protected function _addChildMenu( $item, $parentContainer, $root = false )
    {
	$active = false;
	
	$li = $this->_dom->createElement( 'li' );
	
	// Check if the item has an url and it is active
	if ( array_key_exists( 'url', $item ) && $this->_checkActive( $item['url'] ) )
	    $active = true;
	
	$a = $this->_dom->createElement( 'a' );
	
	if ( !empty( $item['url'] ) ) {
	    $a->setAttribute ( 'href', $this->view->baseUrl( $item['url'] ) );
	} else {
	 
	    $a->setAttribute( 'href', 'javascript:;' );
	}
	 
	$a->appendChild( $this->_dom->createTextNode( $item['label'] ) );
	$li->appendChild( $a );
	
	// If there are children in the item
	if ( !empty( $item['children'] ) ) {
	    
	    $li->setAttribute( 'class', 'dropdown' );
	    $a->setAttribute( 'class', 'dropdown-toggle' );
	    $a->setAttribute( 'data-toggle', 'dropdown' );
	    
	    $spanArrow = $this->_dom->createElement( 'span' );
	    $spanArrow->setAttribute( 'class', 'arrow' );
	    
	    $a->appendChild( $spanArrow );
	    
	    $ulContainer = $this->_dom->createElement( 'ul' );
	    $ulContainer->setAttribute( 'class', 'dropdown-menu' );
	    
	    // Attach all the children itens
	    foreach ( $item['children'] as $child ) {
		
		$activeTest = $this->_addChildMenu( $child, $ulContainer );
		if ( $activeTest )
		    $active = $activeTest;
	    }
	    
	    if ( $ulContainer->childNodes->length < 1 )
		return false;
	    
	    $li->appendChild( $ulContainer );
	}
	    
	// Check if it's active
	if ( $active )
	    $this->_setActive( $li, $root );
	
	$parentContainer->appendChild( $li );

	return $active;
    }
    
    /**
     *
     * @param DOMElement $li
     * @param bool $root 
     */
    protected function _setActive( $li, $root = false )
    {
	$currentClass = $li->getAttribute( 'class' );
	$li->setAttribute( 'class', 'active ' . $currentClass );
	
	if ( $root ) {
	    
	    $span = $this->_dom->createElement( 'span' );
	    $span->setAttribute( 'class', 'selected' );
	    $li->childNodes->item( 0 )->appendChild( $span );
	}

	$this->_active = true;
    }
    
    /**
     *
     * @param type $url
     * @return boolean 
     */
    protected function _checkActive( $url )
    {
	$url = trim( $url, '/' );
	
	if ( !empty( $this->_activePath ) )
	    return $this->_activePath == $url;
	else
	    return $this->_currentRoute == $url;
    }
    
    /**
     *
     * @param string $path 
     */
    public function setActivePath( $path )
    {
	$this->_activePath = $path;
    }
    
    /**
     * 
     */
    protected function _initRoute()
    {
	$request = Zend_Controller_Front::getInstance()->getRequest();
	
	$module = $request->getModuleName();
	if ( '' == $module )
	    $module = 'default';
	
	$controller = $request->getControllerName();
	if ( '' == $controller )
	    $controller = 'index';
	
	$route = array( $module, $controller );
	
	$this->_currentRoute = trim( implode( '/', $route ) , '/' );
    }
}