<?php

class App_View_Helper_Portlet extends Zend_View_Helper_Abstract
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
    protected $_steps = array();
    
    /**
     *
     * @var string
     */
    protected $_title;
    
    /**
     *
     * @var string
     */
    protected $_color = 'blue';
    
    /**
     * 
     */
    protected $_itens = array();
    
    /**
     * 
     */
    public function portlet()
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
	$this->_dom = new DOMDocument();
	
	$portlet = $this->_dom->createElement( 'div' );
	$portlet->setAttribute( 'class', 'portlet box ' . $this->_color );
	
	$title = $this->_dom->createElement( 'div' );
	$title->setAttribute( 'class', 'portlet-title' );

	$caption = $this->_dom->createElement( 'div' );
	$caption->setAttribute( 'class', 'caption' );
	
	$i = $this->_dom->createElement( 'i' );
	$i->setAttribute( 'class', 'icon-reorder' );
	
	$caption->appendChild( $i );
	$caption->appendChild( $this->_dom->createTextNode( $this->_title ) );
	$title->appendChild( $caption );
	$portlet->appendChild( $title );
	
	$portletBody = $this->_dom->createElement( 'div' );
	$portletBody->setAttribute( 'class', 'portlet-body' );
	
	$portlet->appendChild( $portletBody );
	
	$ul = $this->_dom->createElement( 'ul' );
	$ul->setAttribute( 'class', 'ver-inline-menu tabbable' );
	
	$portletBody->appendChild( $ul );
	
	foreach ( $this->_itens as $item ) {
	    
	    if ( !empty( $item['id'] ) && !$this->view->access( $item['id'] ) )
		continue;
	    
	    $li = $this->_dom->createElement( 'li' );
	    $a = $this->_dom->createElement( 'a' );
	    $i = $this->_dom->createElement( 'i' );
	    
	    $i->setAttribute( 'class', 'icon-tasks' );
	    $a->setAttribute( 'href', $this->view->baseUrl( $item['url'] ) );
	    
	    $a->appendChild( $i );
	    $a->appendChild( $this->_dom->createTextNode( ' ' ) );
	    $a->appendChild( $this->_dom->createTextNode( $item['label'] ) );
	    
	    $li->appendChild( $a );
	    $ul->appendChild( $li );
	}
	
	if ( $ul->childNodes->length > 0 )
	    $this->_dom->appendChild( $portlet );
	
	return $this->_dom->saveHTML();
    }
    
    /**
     *
     * @param string $title
     * @return \App_View_Helper_Portlet 
     */
    public function setTitle( $title )
    {
	$this->_title = $title;
	$this->_resetPortlet();
	
	return $this;
    }
    
    /**
     * 
     */
    protected function _resetPortlet()
    {
	$this->_itens = array();
	$this->_color = 'blue';
    }
    
    /**
     * 
     */
    public function addItem( $item )
    {
	$this->_itens[] = $item;
	return $this;
    }
    
    /**
     *
     * @param string $color
     * @return \App_View_Helper_Portlet 
     */
    public function setColor( $color )
    {
	$this->_color = $color;
	return $this;
    }
}