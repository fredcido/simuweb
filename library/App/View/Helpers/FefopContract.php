<?php

class App_View_Helper_FefopContract extends Zend_View_Helper_Abstract
{
    /**
     *
     * @var int|Zend_Db_Table_Row
     */
    protected $_contract;
    
    /**
     *
     * @var array
     */
    protected $_messages = array();
    
    /**
     *
     * @param mixed $contract 
     */
    public function setContract( $contract )
    {
	if ( !($contract instanceof Zend_Db_Table_Row) ) {
	    
	    $mapperContract = new Fefop_Model_Mapper_Contract();
	    $contract = $mapperContract->detail( $contract );
	}
	    
	$this->_contract = $contract;
	
	return $this;
    }
    
    /**
     * 
     * @return Zend_Db_Table_Row
     */
    public function getContract()
    {
	return $this->_contract;
    }
    
    /**
     *
     * @param mixed $contract
     * @return \App_View_Helper_Contract 
     */
    public function fefopContract( $contract = null )
    {
	if ( !empty( $contract ) )
	    $this->setContract( $contract );
	
	return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isEditable()
    {
	$validators = array(
	    '_validFundContract'
	);
	
	return $this->_runValidators( $validators );
    }
    
    /**
     * 
     * @param array $validators
     * @return boolean
     */
    protected function _runValidators( array $validators )
    {
	$validGeneral = true;
	foreach ( $validators as $validator ) {
		
	    $valid = call_user_func( array( $this, $validator ) );
	    if ( is_null( $valid ) )
		continue;

	    $validGeneral = empty( $valid ) || empty( $validGeneral ) ? false : true;
	}
	
	return $validGeneral;
    }
    
    /**
     * 
     * @return boolean
     */
    protected function _validFundContract()
    {
	$mapperFinancial = new Fefop_Model_Mapper_Financial();
	$fundContract = $mapperFinancial->listFundsContract( $this->_contract->id_fefop_contract );
	
	// If there is fund contract statement
	if ( !empty( $fundContract['funds'] ) ) {
	    
	    $this->addMessage( 'Ita la bele edita kontratu nee tamba iha lansamentu finanseiru tiha ona ba fundu.' );
	    return false;
	}
	
	return true;
    }
    
    /**
     * 
     * @param string $message
     * @param string $level
     * @return \App_View_Helper_Contract
     */
    public function addMessage( $message, $level = 'error' )
    {
	$this->_messages[] = array(
	    'level'	=> $level,
	    'message'	=> $message
	);
	
	return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getMessages()
    {
	$dom = new DOMDocument();
	
	foreach ( $this->_messages as $message ) {
	    
	    $containerMsg = $dom->createElement( 'div' );
	    $containerMsg->setAttribute( 'class', 'alert not-remove alert-' . $message['level'] );

	    $containerMsg->appendChild( $dom->createTextNode( $message['message'] ) );
	    
	    $dom->appendChild( $containerMsg );
	}
	
	return $dom->saveHTML();
    }
    
    /**
     * 
     * @return string
     */
    public function getStatusLabel( $additionalClasses = '' )
    {
	$status = empty( $this->_contract ) ? null : $this->_contract->id_fefop_status;
	
	return $this->renderStatusLabel( $status, $additionalClasses );
    }
    
    
    /**
     * 
     * @param string $status
     * @return array
     */
    public function getAttribsLabel( $status )
    {
	switch ( $status ) {
	    case Fefop_Model_Mapper_Status::ANALYSIS:
		$color = 'yellow';
		$icon = 'icon-time';
		break;
	    case Fefop_Model_Mapper_Status::REJECTED:
		$color = 'red';
		$icon = 'icon-remove-sign';
		break;
	    case Fefop_Model_Mapper_Status::PROGRESS:
		$color = 'green';
		$icon = 'icon-random';
		break;
	    case Fefop_Model_Mapper_Status::CANCELLED:
		$color = 'red';
		$icon = 'icon-remove-circle';
		break;
	    case Fefop_Model_Mapper_Status::FINALIZED:
		$color = 'blue';
		$icon = 'icon-ok-sign';
		break;
	    case Fefop_Model_Mapper_Status::SEMI:
		$color = 'blue';
		$icon = 'icon-adjust';
		break;
	    case Fefop_Model_Mapper_Status::CEASED:
		$color = 'purple';
		$icon = 'icon-off';
		break;
	    case Fefop_Model_Mapper_Status::INITIAL:
		$color = 'blue';
		$icon = 'icon-road';
		break;
	    case Fefop_Model_Mapper_Status::REVIEWED:
		$color = 'green';
		$icon = 'icon-check';
		break;
	    default:
		$color = 'yellow';
		$icon = 'icon-warning-sign';
	}
	
	return array( 
	    'color' => $color,
	    'icon'  => $icon
	);
    }
    
    /**
     * 
     * @param int $status
     * @return string
     */
    public function renderStatusLabel( $status = false, $additionalClasses = '' )
    {
	if ( empty( $status ) )
	    $status = $this->_contract->id_fefop_status;
	
	$dom = new DOMDocument();
	
	$attribsStatus = $this->getAttribsLabel( $status );
	
	$labels = $this->getStatuses();
	$label = empty( $labels[$status] ) ? $labels[Fefop_Model_Mapper_Status::ANALYSIS] : $labels[$status];
	
	$container = $dom->createElement( 'a' );
	$container->setAttribute( 'href', 'javascript:;' );
	$container->setAttribute( 'class', 'btn disabled ' . $attribsStatus['color'] . ' ' . $additionalClasses );
	
	$i = $dom->createElement( 'i' );
	$i->setAttribute( 'class', $attribsStatus['icon'] );
	
	$container->appendChild( $i );
	$container->appendChild( $dom->createTextNode( ' ' . $label ) );
	
	$dom->appendChild( $container );
	
	return $dom->saveHTML();
    }
    
    /**
     * 
     * @return array
     */
    public function getStatuses()
    {
	$mapper = new Fefop_Model_Mapper_Status();
	$rows = $mapper->getStatuses();
	
	$statuses = array();
	foreach ( $rows as $row )
	    $statuses[$row->id_fefop_status] = $row->status_description;
	
	return $statuses;
    }
}
