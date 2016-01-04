<?php

class App_View_Helper_JobActive extends Zend_View_Helper_Abstract
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
    protected $_vacancy;
    
    /**
     *
     * @param type $idVacancy
     * @return \App_View_Helper_JobActive 
     */
    public function jobActive( $idVacancy = null )
    {
	if ( !empty( $idVacancy ) )
	    $this->setVacancy( $idVacancy );
	
        return $this;
    }
    
    /**
     *
     * @param type $idVacancy
     * @return \App_View_Helper_JobActive 
     */
    public function setVacancy( $idVacancy )
    {
	$mapperJobVacancy = new Job_Model_Mapper_JobVacancy();
	$this->_vacancy = $mapperJobVacancy->detailVacancy( $idVacancy );
	
	return $this;
    }
    
    /**
     *
     * @param Zend_Db_Table_Row $vacancy 
     */
    public function setVacancyRow( $vacancy )
    {
	$this->_vacancy = $vacancy;
	return $this;
    }
    
    /**
     *
     * @return boolean
     * @throws Exception 
     */
    public function hasAccessEdit( $checkCeop = true )
    {
	if ( empty( $this->_vacancy ) )
	    return false;
	
	if ( $this->_vacancy->active != 1 )
	    return false;
	
	if ( !$checkCeop )
	    return true;
	
	$user = Zend_Auth::getInstance()->getIdentity();
	
	return $this->_vacancy->fk_id_dec == $user->fk_id_dec;
    }
    
    /**
     *
     * @return string
     */
    public function getMessage()
    {
	if ( empty( $this->_vacancy ) )
	    return '';
	
	$this->_dom = new DOMDocument();
	$classMessage = 'alert ';
	
	$divFluid = $this->_dom->createElement( 'div' );
	$divMessage = $this->_dom->createElement( 'div' );
	
	$divFluid->setAttribute( 'class', 'row-fluid' );
	
	if ( $this->_vacancy->active != 1 ) {
	    
	    $classMessage .= 'alert-error';
	    $iconClass = 'icon-remove-sign';
	    $alertText = ' Atensaun ';
	    $message = ' Vaga ' . ( $this->_vacancy->active == 2 ? 'Kansela' : 'Taka' ) . ' tiha ona, La bele halo Atualizasaun.';
	    
	} else {
	    
	    $iconClass = 'icon-ok-sign';
	    $classMessage .= 'alert-success';
	    $alertText = '';
	    $message = ' Vaga Loke, então bele halo Atualizasaun.';
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