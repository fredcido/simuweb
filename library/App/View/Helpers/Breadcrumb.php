<?php

class App_View_Helper_Breadcrumb extends Zend_View_Helper_Abstract
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
     */
    protected $_base = '/';
    
    /**
     * 
     */
    public function breadcrumb()
    {
        return $this;
    }
    
    /**
     *
     * @param string $base
     * @return \App_View_Helper_Breadcrumb 
     */
    public function setBase( $base )
    {
	$this->_base = $base;
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
	// Get the current request uri
	$this->_requestedUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
	
	$this->_dom = new DOMDocument();
	
	$mainDiv = $this->_dom->createElement( 'div' );
	$mainDiv->setAttribute( 'id', 'breadcrumb' );
	
	$ulBreadcrumb = $this->_dom->createElement( 'ul' );
	$ulBreadcrumb->setAttribute( 'class', 'breadcrumb' );
	
	// Prepare the home step
	$liHome = $this->_dom->createElement( 'li' );
	$aHome = $this->_dom->createElement( 'a' );
	
	$aHome->setAttribute( 'href', $this->view->baseUrl( $this->_base ) );
	
	$aHome->appendChild( $this->_dom->createTextNode( 'Home' ) );
	$icon = $this->_dom->createElement( 'i' );
	$icon->setAttribute( 'class', 'icon-home' );
	$liHome->appendChild( $icon );
	$liHome->appendChild( $aHome );
	
	$ulBreadcrumb->appendChild( $liHome );
	
	$previousLi = $liHome;
	foreach ( $this->_steps as $label => $url ) {
	    
	    $liStep = $this->_dom->createElement( 'li' );
	    $aStep = $this->_dom->createElement( 'a' );

	    $aStep->setAttribute( 'href', $this->view->baseUrl( $url ) );
	    $aStep->appendChild( $this->_dom->createTextNode( $label ) );
	    
	    $iIcon = $this->_dom->createElement( 'i' );
	    $iIcon->setAttribute( 'class', 'icon-angle-right' );
	    
	    $previousLi->appendChild( $iIcon );
	    $liStep->appendChild( $aStep );
	    
	    $ulBreadcrumb->appendChild( $liStep );
	    $previousLi = $liStep;
	}
	
	$mainDiv->appendChild( $ulBreadcrumb );
	$this->_dom->appendChild( $mainDiv );
	
	return $this->_dom->saveHTML();
    }
    
    /**
     *
     * @param array $step 
     */
    public function addStep( $step )
    {
	$this->_steps[$step['label']] = $step['url'];
    }
}