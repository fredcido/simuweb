<?php

class App_View_Helper_Accordion extends Zend_View_Helper_Abstract
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
     * @var boolean
     */
    protected $_active = false;
    
    /**
     *
     * @var string
     */
    protected $_defaultColor = 'purple';
    
    /**
     *
     * @var string
     */
    protected $_defaultIcon = 'icon-reorder';
    
    /**
     *
     * @return \App_View_Helper_Accordion 
     */
    public function accordion()
    {
        return $this;
    }
    
   
    /**
     *
     * @return \App_View_Helper_Accordion 
     */
    public function clear()
    {
	$this->_steps = array();
	return $this;
    }
    
    /**
     *
     * @param array $step
     * @return \App_View_Helper_Accordion 
     */
    public function addStep( array $step )
    {
	$this->_steps[] = $step;
	return $this;
    }
    
    /**
     *
     * @param string $color
     * @return \App_View_Helper_Accordion 
     */
    public function setDefaultColor( $color )
    {
	$this->_defaultColor = $color;
	return $this;
    }
    
    /**
     *
     * @param string $icon
     * @return \App_View_Helper_Accordion 
     */
    public function setDefaultIcon( $icon )
    {
	$this->_defaultIcon = $icon;
	return $this;
    }
    
    /**
     *
     * @param array $steps
     * @return \App_View_Helper_Accordion 
     */
    public function setSteps( array $steps )
    {
	$this->_steps = $steps;
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
	if ( empty( $this->_steps ) )
	    return '';
	
	$this->_dom = new DOMDocument();
	
	foreach ( $this->_steps as $key => $step )
	    $this->_addStepContainer( $step, ++$key );
	
	return $this->_dom->saveHTML();
    }
    
    /**
     *
     * @param array $step
     * @return boolean 
     */
    public function _addStepContainer( $step, $index )
    {
	// If has access
	if ( !empty( $step['id'] ) && !$this->view->access( $step['id'] ) )
	    return false;
	
	// Create the rows
	$row = $this->_dom->createElement( 'div' );
	$span = $this->_dom->createElement( 'span12' );
	
	$row->setAttribute( 'class', 'row-fluid' );
	$row->appendChild( $span );
	
	// Create the portlets
	$portlet = $this->_dom->createElement( 'div' );
	$color = empty( $step['color'] ) ? $this->_defaultColor : $step['color'];
	
	$portletClass = 'portlet dynamic-portlet box ' . $color;
	
	$portlet->setAttribute( 'id', $step['ref'] );
	$span->appendChild( $portlet );
	
	// Create the title
	$title = $this->_dom->createElement( 'div' );
	$title->setAttribute( 'class', 'portlet-title' );
	
	$portlet->appendChild( $title );
	
	// Create the caption
	$caption = $this->_dom->createElement( 'div' );
	$caption->setAttribute( 'class', 'caption' );
	
	// Create the icon
	$icon = $this->_dom->createElement( 'div' );
	$iconClass = empty( $step['icon'] ) ? $this->_defaultIcon : $step['icon'];
	$icon->setAttribute( 'class', $iconClass );
	
	$caption->appendChild( $icon );
	$caption->appendChild( $this->_dom->createTextNode( ' ' . $index . ' - ' . $step['label'] ) );
	
	$title->appendChild( $caption );
	
	// Create the tools
	$tools = $this->_dom->createElement( 'div' );
	$tools->setAttribute( 'class', 'tools' );
	
	$title->appendChild( $tools );
	
	// Check is the step is released
	if ( !empty( $step['released'] ) ) {
	    
	    $aCollapse = $this->_dom->createElement( 'a' );
	    $aCollapse->setAttribute( 'class', ( !empty( $step['content'] ) ? 'collapse' : 'expand' ) );
	    $aCollapse->setAttribute( 'href', 'javascript:;' );
	    
	    $aReload = $this->_dom->createElement( 'a' );
	    $aReload->setAttribute( 'class', 'reload' );
	    $aReload->setAttribute( 'href', 'javascript:;' );
	    
	    $tools->appendChild( $aReload );
	    $tools->appendChild( $aCollapse );
	    
	} else {
	    
	    $portletClass .= ' disabled';
	}
	
	// if the portlet can be reloaded
	if ( !empty( $step['url'] ) )
	    $portlet->setAttribute( 'data-url', $step['url'] );
	
	// if the portlet has a callback
	if ( !empty( $step['callback'] ) )
	    $portlet->setAttribute( 'data-callback', $step['callback'] );
	
	// Create the portlet body
	$body = $this->_dom->createElement( 'div' );
	$body->setAttribute( 'class', 'portlet-body ' . ( empty( $step['content'] ) ? 'hide' : '' ) );
	
	// If the step has a content
	if ( !empty( $step['content'] ) ) {
	    
	    $fragment = $this->_dom->createDocumentFragment();
	    $fragment->appendXML( $step['content'] );
	    $body->appendChild( $fragment );
	    
	} else
	    $portletClass .= ' ajax-loaded';
	
	$portlet->appendChild( $body );
	$portlet->setAttribute( 'class', $portletClass );
	
	$this->_dom->appendChild( $row );
    }
}