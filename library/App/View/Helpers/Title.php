<?php

class App_View_Helper_Title extends Zend_View_Helper_Abstract
{
    /**
     *
     * @var DOMDocument
     */
    protected $_dom;
    
    /**
     *
     * @var string
     */
    protected $_title;
    
    /**
     *
     * @var string
     */
    protected $_subTitle;
    
    /**
     *
     * @param string $title
     * @return \App_View_Helper_Title 
     */
    public function title( $title = '' )
    {
	if ( !empty( $title ) )
	    $this->setTitle( $title );
	
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
	if ( empty( $this->_title ) )
	    return '';
	
	$this->_dom = new DOMDocument();
	
	$h3 = $this->_dom->createElement( 'h3' );
	$h3->setAttribute( 'class', 'page-title' );
	
	$h3->appendChild( $this->_dom->createTextNode( $this->_title ) );
	
	if ( !empty( $this->_subTitle ) ) {
	    
	    $small = $this->_dom->createElement( 'small' );
	    $this->_subTitle = ' - ' . $this->_subTitle;
	    $small->appendChild( $this->_dom->createTextNode( $this->_subTitle ) );
	    $h3->appendChild( $small );
	}
	
	$this->_dom->appendChild( $h3 );
	return $this->_dom->saveHTML();
    }
    
    /**
     *
     * @param string $title
     */
    public function setTitle( $title )
    {
	$this->_title = $title;
	return $this;
    }
    
    /**
     *
     * @param string $subTitle
     */
    public function setSubTitle( $subTitle )
    {
	$this->_subTitle = $subTitle;
	return $this;
    }
    
}