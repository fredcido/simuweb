<?php

class App_View_Helper_Campaign extends Zend_View_Helper_Abstract
{
    /**
     *
     * @var int|Zend_Db_Table_Row
     */
    protected $_campaign;
    
    /**
     *
     * @var array
     */
    protected $_messages = array();
    
    /**
     *
     * @var Zend_Db_Table_Row
     */
    protected $_department;
    
    /**
     *
     * @param mixed $campaign 
     */
    public function setCampaign( $campaign )
    {
	if ( !($campaign instanceof Zend_Db_Table_Row) ) {
	    
	    $mapperCampaign = new Sms_Model_Mapper_Campaign();
	    $campaign = $mapperCampaign->fetchRow( $campaign );
	}
	    
	$this->_campaign = $campaign;
    }
    
    /**
     * 
     * @return Zend_Db_Table_Row
     */
    public function getCampaign()
    {
	return $this->_campaign;
    }
    
    /**
     *
     * @param mixed $campaign
     * @return \App_View_Helper_Campaign 
     */
    public function campaign( $campaign = null )
    {
	if ( !empty( $campaign ) )
	    $this->setCampaign( $campaign );
	
	return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isEnabled()
    {
	$validators = array(
	    '_validateDepartment',
	    '_validateCredit',
	    '_validateStatus'
	);
	
	return $this->_runValidators( $validators );
    }
    
    /**
     * 
     * @return boolean
     */
    public function isEditable()
    {
	$validators = array(
	    '_validateDepartment',
	    '_validateStatus'
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
     * @param string $message
     * @param string $level
     * @return \App_View_Helper_Campaign
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
	    $containerMsg->setAttribute( 'class', 'alert alert-' . $message['level'] );

	    $containerMsg->appendChild( $dom->createTextNode( $message['message'] ) );
	    
	    $dom->appendChild( $containerMsg );
	}
	
	return $dom->saveHTML();
    }
    
    /**
     * 
     * @return array
     */
    public function getDepartment()
    {
	return $this->_department;
    }
    
    /**
     * 
     * @return boolean
     */
    protected function _validateDepartment()
    {
	$user = Zend_Auth::getInstance()->getIdentity();
	
	$mapperDepartment = new Admin_Model_Mapper_Department();
	$department = $mapperDepartment->getDepartmentByUser( $user->id_sysuser );
	
	// If there is no department
	if ( empty( $department ) ) {
	    
	    $this->addMessage( 'Ita la bele rejistu kampanha SMS. Tenki husu uzuariu husi departamentu' );
	    return false;
	}
	
	$this->_department = $department;
	    
	return true;
    }
    
    /**
     * 
     * @return boolean
     */
    protected function _validateCredit()
    {
	// If there is no department
	if ( empty( $this->_department ) )
	    return false;
	
	// If the department has no credit
	if ( $this->_department['balance'] <= 0 ) {
	    
	    $this->addMessage( 'Ita-nia departamentu la iha pulsa atu haruka SMS' );
	    return false;
	}
	    
	return true;
    }
    
    /**
     * 
     * @return boolean
     */
    protected function _validateStatus()
    {
	// If there is no campaign
	if ( empty( $this->_campaign ) )
	    return true;
	
	$statusRelease = array(
	    Sms_Model_Mapper_Campaign::STATUS_STOPPED,
	    Sms_Model_Mapper_Campaign::STATUS_SCHEDULED,
	);
	
	if ( !in_array( $this->_campaign->status, $statusRelease ) ) {
	    
	    $this->addMessage( 'Keta edita kampanha ida ne\'e, hare ninia status ho atensaun!' );
	    return false;
	}
	
	return true;
    }
    
    /**
     * 
     * @return string
     */
    public function getStatusLabel()
    {
	$dom = new DOMDocument();
	
	$status = empty( $this->_campaign ) ? null : $this->_campaign->status;
	
	switch ( $status ) {
	    case Sms_Model_Mapper_Campaign::STATUS_CANCELLED:
		$color = 'red';
		$icon = 'icon-remove-sign';
		break;
	    case Sms_Model_Mapper_Campaign::STATUS_COMPLETED:
		$color = 'green';
		$icon = 'icon-ok-sign';
		break;
	    case Sms_Model_Mapper_Campaign::STATUS_INITIED:
	    case Sms_Model_Mapper_Campaign::STATUS_ROBOT:
		$color = 'blue';
		$icon = 'icon-cog';
		break;
	    case Sms_Model_Mapper_Campaign::STATUS_SCHEDULED:
		$color = 'purple';
		$icon = 'icon-calendar';
		break;
	    default:
		$color = 'yellow';
		$icon = 'icon-warning-sign';
	}
	
	$labels = $this->getStatuses();
	$label = empty( $labels[$status] ) ? $labels[Sms_Model_Mapper_Campaign::STATUS_STOPPED] : $labels[$status];
	
	$container = $dom->createElement( 'a' );
	$container->setAttribute( 'href', 'javascript:;' );
	$container->setAttribute( 'class', 'btn disabled ' . $color );
	
	$i = $dom->createElement( 'i' );
	$i->setAttribute( 'class', $icon );
	
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
	$data = array(
	    Sms_Model_Mapper_Campaign::STATUS_STOPPED	=> 'Paradu',
	    Sms_Model_Mapper_Campaign::STATUS_CANCELLED => 'Kanselada',
	    Sms_Model_Mapper_Campaign::STATUS_COMPLETED => 'Kompletu',
	    Sms_Model_Mapper_Campaign::STATUS_INITIED	=> 'Hahu ona',
	    Sms_Model_Mapper_Campaign::STATUS_ROBOT	=> 'Hahu ona',
	    Sms_Model_Mapper_Campaign::STATUS_SCHEDULED	=> 'Ajendadu'
	);
	
	return $data;
    }
}
