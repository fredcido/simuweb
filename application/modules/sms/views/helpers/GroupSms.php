<?php

class App_View_Helper_GroupSms extends Zend_View_Helper_Abstract
{
    /**
     *
     * @var int|Zend_Db_Table_Row
     */
    protected $_group;
    
    /**
     *
     * @var array
     */
    protected $_messages = array();
    
    /**
     *
     * @var Sms_Model_Mapper_Group 
     */
    protected $_mapper;
    
    /**
     * 
     */
    public function __construct()
    {
	 $this->_mapper = new Sms_Model_Mapper_Group();;
    }
    
    /**
     *
     * @param mixed $group 
     */
    public function setGroup( $group )
    {
	if ( !($group instanceof Zend_Db_Table_Row) )
	    $group = $this->_mapper->fetchRow( $group );
	    
	$this->_group = $group;
    }
    
    /**
     * 
     * @return Zend_Db_Table_Row
     */
    public function getGroup()
    {
	return $this->_group;
    }
    
    /**
     *
     * @param mixed $group
     * @return \App_View_Helper_Group 
     */
    public function groupSms( $group = null )
    {
	if ( !empty( $group ) )
	    $this->setGroup( $group );
	
	return $this;
    }
    
    /**
     * 
     */
    public function isEnabled()
    {
	$validators = array(
	    '_validateCampaign'
	);
	
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
    protected function _validateCampaign()
    {
	if ( empty( $this->_group ) )
	    return true;
	
	$campaigns = $this->_mapper->listCampaignsRelated( $this->_group->id_sms_group );
	
	if ( $campaigns->count() > 0 ) {
	    
	    $this->addMessage( 'Grupu ida ne\'e usa dadaun iha kampanha ida. Keta edita.' );
	    return false;
	}
	
	return true;
    }
    
    /**
     * 
     * @param string $message
     * @param string $level
     * @return \App_View_Helper_Group
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
     * @return boolean
     */
    public function hasMessage()
    {
	return !empty( $this->_messages );
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
}
