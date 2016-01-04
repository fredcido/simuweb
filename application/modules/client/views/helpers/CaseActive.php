<?php

class App_View_Helper_CaseActive extends Zend_View_Helper_Abstract
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
    protected $_case;
    
    /**
     *
     * @param type $idCase
     * @return \App_View_Helper_CaseActive 
     */
    public function caseActive( $idCase = null )
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
	$mapperCase = new Client_Model_Mapper_Case();
	$this->_case = $mapperCase->fetchRow( $idCase );
	
	return $this;
    }
    
    /**
     *
     * @param Zend_Db_Table_Row $case 
     */
    public function setStudentClassRow( $case )
    {
	$this->_case = $case;
	return $this;
    }
    
    /**
     *
     * @return boolean
     * @throws Exception 
     */
    public function hasAccessEdit( $checkCeop = true )
    {
	if ( empty( $this->_case ) )
	    return true;
	
	if (  $this->_case->active != 1 )
	    return false;
	
	if ( !$checkCeop )
	    return true;
	
	$user = Zend_Auth::getInstance()->getIdentity();
	
	return true;//$this->_case->fk_id_dec == $user->fk_id_dec;
    }
    
    /**
     *
     * @return string
     */
    public function getMessage()
    {
	if ( empty( $this->_case ) )
	    return '';
	
	$this->_dom = new DOMDocument();
	$classMessage = 'alert ';
	
	$divFluid = $this->_dom->createElement( 'div' );
	$divMessage = $this->_dom->createElement( 'div' );
	
	$divFluid->setAttribute( 'class', 'row-fluid' );
	
	if ( $this->_case->active != 1 ) {
	    
	    $classMessage .= 'alert-error';
	    $iconClass = 'icon-remove-sign';
	    $alertText = ' Atensaun ';
	    $message = ' Kazu ' . ( $this->_case->active == 2 ? 'Kansela' : 'Taka' ) . ' tiha ona, La bele halo Atualizasaun.';
	    
	} else {
	    
	    $iconClass = 'icon-ok-sign';
	    $classMessage .= 'alert-success';
	    $alertText = '';
	    $message = ' Kazu Loke, então bele halo Atualizasaun.';
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