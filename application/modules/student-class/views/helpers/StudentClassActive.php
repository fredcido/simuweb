<?php

class App_View_Helper_StudentClassActive extends Zend_View_Helper_Abstract
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
    protected $_studentClass;
    
    /**
     *
     * @param type $idStudentClass
     * @return \App_View_Helper_StudentClassActive 
     */
    public function studentClassActive( $idStudentClass = null )
    {
	if ( !empty( $idStudentClass ) )
	    $this->setStudentClass( $idStudentClass );
	
        return $this;
    }
    
    /**
     *
     * @param type $idStudentClass
     * @return \App_View_Helper_StudentClassActive 
     */
    public function setStudentClass( $idStudentClass )
    {
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$this->_studentClass = $mapperStudentClass->fetchRow( $idStudentClass );
	
	return $this;
    }
    
    /**
     *
     * @param Zend_Db_Table_Row $studentClass 
     */
    public function setStudentClassRow( $studentClass )
    {
	$this->_studentClass = $studentClass;
	return $this;
    }
    
    /**
     *
     * @return boolean
     * @throws Exception 
     */
    public function hasAccessEdit( $checkCeop = true )
    {
	if ( empty( $this->_studentClass ) )
	    return false;
	
	if (  $this->_studentClass->active != 1 )
	    return false;
	
	if ( !$checkCeop )
	    return true;
	
	$user = Zend_Auth::getInstance()->getIdentity();
	
	return $this->_studentClass->fk_id_dec == $user->fk_id_dec;
    }
    
    /**
     *
     * @return string
     */
    public function getMessage()
    {
	if ( empty( $this->_studentClass ) )
	    return '';
	
	$this->_dom = new DOMDocument();
	$classMessage = 'alert ';
	
	$divFluid = $this->_dom->createElement( 'div' );
	$divMessage = $this->_dom->createElement( 'div' );
	
	$divFluid->setAttribute( 'class', 'row-fluid' );
	
	if ( $this->_studentClass->active != 1 ) {
	    
	    $classMessage .= 'alert-error';
	    $iconClass = 'icon-remove-sign';
	    $alertText = ' Atensaun ';
	    $message = ' Klase ' . ( $this->_studentClass->active == 2 ? 'Kansela' : 'Taka' ) . ' tiha ona, La bele halo Atualizasaun.';
	    
	} else {
	    
	    $iconClass = 'icon-ok-sign';
	    $classMessage .= 'alert-success';
	    $alertText = '';
	    $message = ' Klase Loke, então bele halo Atualizasaun.';
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