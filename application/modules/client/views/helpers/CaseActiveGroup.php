<?php

class App_View_Helper_CaseActiveGroup extends Zend_View_Helper_Abstract
{
    /**
     *
     * @var DOMDocument
     */
    protected $_dom;
    
    /**
     *
     * @var Zend_Db_Table_Row
     */
    protected $_caseGroup;
    
    /**
     *
     * @param type $idCase
     * @return \App_View_Helper_CaseActiveGroup 
     */
    public function caseActiveGroup( $idCase = null )
    {
	if ( !empty( $idCase ) )
	    $this->setCase( $idCase );
	
        return $this;
    }
    
    /**
     *
     * @param type $idCase
     * @return \App_View_Helper_CaseActive 
     */
    public function setCase( $idCase )
    {
	$mapperCase = new Client_Model_Mapper_CaseGroup();
	$this->_caseGroup = $mapperCase->fetchRow( $idCase );
	
	return $this;
    }
    
    /**
     *
     * @param Zend_Db_Table_Row $case 
     */
    public function setCaseGroupRow( $case )
    {
	$this->_caseGroup = $case;
	return $this;
    }
    
    /**
     *
     * @return boolean
     * @throws Exception 
     */
    public function hasAccessEdit()
    {
	if ( empty( $this->_caseGroup ) )
	    return true;
	
	if (  $this->_caseGroup->status != 1 )
	    return false;
	
	return true;
    }
    
    /**
     *
     * @return string
     */
    public function getMessage()
    {
	if ( empty( $this->_caseGroup ) )
	    return '';
	
	$this->_dom = new DOMDocument();
	$classMessage = 'alert ';
	
	$divFluid = $this->_dom->createElement( 'div' );
	$divMessage = $this->_dom->createElement( 'div' );
	
	$divFluid->setAttribute( 'class', 'row-fluid' );
	
	if ( $this->_caseGroup->status != 1 ) {
	    
	    $classMessage .= 'alert-error';
	    $iconClass = 'icon-remove-sign';
	    $alertText = ' Atensaun ';
	    $message = ' Kazu Grupu ' . ( $this->_caseGroup->status == 2 ? 'Kansela' : 'Taka' ) . ' tiha ona, La bele halo Atualizasaun.';
	    
	} else {
	    
	    $iconClass = 'icon-ok-sign';
	    $classMessage .= 'alert-success';
	    $alertText = '';
	    $message = ' Kazu Grupu Loke, então bele halo Atualizasaun.';
	}
	
	$divMessage->setAttribute( 'class', $classMessage );
	$strong = $this->_dom->createElement( 'strong' );
	$i = $this->_dom->createElement( 'i' );
	
	$i->setAttribute( 'class', $iconClass );
	$strong->appendChild( $i );
	$strong->appendChild( $this->_dom->createTextNode( $alertText ) );
	
	$divMessage->appendChild( $strong );
	$divMessage->appendChild( $this->_dom->createTextNode( $message ) );
	$divFluid->appendChild( $divMessage );
	
	$this->_dom->appendChild( $divFluid );
	return $this->_dom->saveHTML();
    }
}