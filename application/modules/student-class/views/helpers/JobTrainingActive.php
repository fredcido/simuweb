<?php

class App_View_Helper_JobTrainingActive extends Zend_View_Helper_Abstract
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
    protected $_jobTraining;
    
    /**
     *
     * @param type $idJobTraining
     * @return \App_View_Helper_JobTrainingActive 
     */
    public function jobTrainingActive( $idJobTraining = null )
    {
	if ( !empty( $idJobTraining ) )
	    $this->setJobTraining( $idJobTraining );
	
        return $this;
    }
    
    /**
     *
     * @param type $idJobTraining
     * @return \App_View_Helper_JobTrainingActive 
     */
    public function setJobTraining( $idJobTraining )
    {
	$mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	$this->_jobTraining = $mapperJobTraining->fetchRow( $idJobTraining );
	
	return $this;
    }
    
    /**
     *
     * @param Zend_Db_Table_Row $jobTraining 
     */
    public function setJobTrainingRow( $jobTraining )
    {
	$this->_jobTraining = $jobTraining;
	return $this;
    }
    
    /**
     *
     * @return boolean
     * @throws Exception 
     */
    public function hasAccessEdit( $checkCeop = true )
    {
	if ( empty( $this->_jobTraining ) )
	    return false;
	
	if (  $this->_jobTraining->status != 1 )
	    return false;
	
	if ( !$checkCeop )
	    return true;
	
	$user = Zend_Auth::getInstance()->getIdentity();
	
	return $this->_jobTraining->fk_id_dec == $user->fk_id_dec;
    }
    
    /**
     *
     * @return string
     */
    public function getMessage()
    {
	if ( empty( $this->_jobTraining ) )
	    return '';
	
	$this->_dom = new DOMDocument();
	$classMessage = 'alert ';
	
	$divFluid = $this->_dom->createElement( 'div' );
	$divMessage = $this->_dom->createElement( 'div' );
	
	$divFluid->setAttribute( 'class', 'row-fluid' );
	
	if ( $this->_jobTraining->status != 1 ) {
	    
	    $classMessage .= 'alert-error';
	    $iconClass = 'icon-remove-sign';
	    $alertText = ' Atensaun ';
	    $message = ' Job Training ' . ( $this->_jobTraining->status == 2 ? 'Kansela' : 'Taka' ) . ' tiha ona, La bele halo Atualizasaun.';
	    
	} else {
	    
	    $iconClass = 'icon-ok-sign';
	    $classMessage .= 'alert-success';
	    $alertText = '';
	    $message = ' Job Training Loke, então bele halo Atualizasaun.';
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