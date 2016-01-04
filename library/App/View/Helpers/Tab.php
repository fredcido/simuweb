<?php

class App_View_Helper_Tab extends Zend_View_Helper_Abstract
{
    /**
     *
     * @var DOMDocument
     */
    protected $_dom;
    
    /**
     *
     * @var array
     */
    protected $_tabs = array();
    
    /**
     *
     * @var boolean
     */
    protected $_active = false;
    
    /**
     *
     * @return \App_View_Helper_Tab 
     */
    public function tab()
    {
        return $this;
    }
    
   
    /**
     *
     * @return \App_View_Helper_Tab 
     */
    public function clear()
    {
	$this->_tabs = array();
	return $this;
    }
    
    /**
     *
     * @param array $tab
     * @return \App_View_Helper_Tab 
     */
    public function addTab( array $tab )
    {
	$this->_tabs[] = $tab;
	return $this;
    }
    
    /**
     *
     * @param array $tabs
     * @return \App_View_Helper_Tab 
     */
    public function setTabs( array $tabs )
    {
	$this->_tabs = $tabs;
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
	if ( empty( $this->_tabs ) )
	    return '';
	
	$this->_dom = new DOMDocument();
	
	$ulTabs = $this->_dom->createElement( 'ul' );
	$ulTabs->setAttribute( 'class', 'nav nav-tabs' );

	$divContent = $this->_dom->createElement( 'div' );
	$divContent->setAttribute( 'class', 'tab-content' );
	
	foreach ( $this->_tabs as $tab )
	    $this->_addTabContainer( $tab, $ulTabs, $divContent );
	
	$this->_dom->appendChild( $ulTabs );
	$this->_dom->appendChild( $divContent );
	
	return $this->_dom->saveHTML();
    }
    
    /**
     *
     * @param array $tab
     * @param DOMElement $ul
     * @param DOMElement $div 
     */
    public function _addTabContainer( $tab, $ul, $div )
    {
	if ( !empty( $tab['content'] ) && empty( $this->_active ) ) {
	    
	    $class = 'active ';
	    $this->_active = true;
	    
	} else
	    $class = '';
	
	if ( !empty( $tab['id'] ) && !$this->view->access( $tab['id'] ) )
	    return false;
	
	$li = $this->_dom->createElement( 'li' );
	$a = $this->_dom->createElement( 'a' );
	
	$a->setAttribute( 'data-toggle', 'tab' );
	$a->setAttribute( 'href', '#' . $tab['ref'] );
	
	$a->appendChild( $this->_dom->createTextNode( $tab['label'] ) );
	
	$classLi = $class;
	if ( empty( $tab['released'] ) )
	    $classLi .= 'disabled';
	
	$li->setAttribute( 'class', $classLi );
	
	$li->appendChild( $a );
	$ul->appendChild( $li );
	
	$divPane = $this->_dom->createElement( 'div' );
	$divPane->setAttribute( 'id', $tab['ref'] );
	$divPane->setAttribute( 'class', $class . 'tab-pane' );
	
	$div->appendChild( $divPane );
	
	if ( empty( $tab['content'] ) ) {
	    
	    $a->setAttribute( 'data-href', $tab['url'] );
	    $a->setAttribute( 'class', 'ajax-tab' );
	    
	} else {
	    
	    /*
	    require_once APPLICATION_PATH . '/../library/HTMLPurifier/HTMLPurifier.auto.php';
	    $config = HTMLPurifier_Config::createDefault();
	    $purifier = new HTMLPurifier( $config );
	     * 
	     */
	    
	    $fragment = $this->_dom->createDocumentFragment();
	    $fragment->appendXML( $tab['content'] );//$purifier->purify( $tab['content'] ) );
	    $divPane->appendChild( $fragment );
	}
    }
}