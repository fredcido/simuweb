<?php

/**
 * 
 */
abstract class App_Model_Abstract
{

    /**
     *
     * @var array
     */
    protected $_data = array( );

    /**
     *
     * @var array
     */
    protected $_methodsValidators = array( );

    /**
     *
     * @var bool
     */
    protected $_breakOnFailure = true;

    /**
     *
     * @var App_General_Message
     */
    protected $_message;

    /**
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     *
     * @var Zend_Config
     */
    protected $_config;
    
    /**
     *
     * @var array
     */
    protected $_invalidFields = array();

    /**
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
	if ( method_exists( $this, '_getDbTable' ) )
	    $this->_getDbTable();

	$this->_config = Zend_Registry::get( 'config' );
	$this->_session = new Zend_Session_Namespace( $this->_config->general->appid );
	$this->_message = new App_General_Message();
    }
    
    /**
     * 
     * @param string $field
     */
    public function addFieldError( $field, $key = null )
    {
	if ( !empty( $key ) )
	    $this->_invalidFields[$key] = $field;
	else
	    $this->_invalidFields[] = $field;
	
	return $this;
    }
    
    /**
     * 
     * @param array $fields
     */
    public function setFieldsError( $fields )
    {
	$this->_invalidFields = $fields;
    }
    
    /**
     * 
     * @return array
     */
    public function getFieldsError()
    {
	return $this->_invalidFields;
    }

    /**
     * 
     * @access 	public
     * @param 	array $data
     * @return 	void
     */
    public function setData( $data )
    {
	$this->_data = $data;
	return $this;
    }

    /**
     * 
     * @access public
     * @return array
     */
    public function getData()
    {
	return $this->_data;
    }

    /**
     * 
     * @access 	public
     * @param 	array $validators
     * @param 	bool $breakOnFailure
     * @return 	void
     */
    public function setValidators( array $validators, $breakOnFailure = true )
    {
	$this->_methodsValidators = $validators;

	$this->setBreakOnFailure( $breakOnFailure );
    }

    /**
     * 
     * @access 	public
     * @param 	bool $breakOnFailure
     * @return 	void
     */
    public function setBreakOnFailure( $breakOnFailure = true )
    {
	$this->_breakOnFailure = (bool) $breakOnFailure;
    }

    /**
     * 
     * @access public
     * @return bool
     * @throws Exception
     */
    public function isValid()
    {
	$check = true;

	foreach ( $this->_methodsValidators as $method ) {

	    if ( method_exists( $this, $method ) ) {

		if ( !call_user_func( array( $this, $method ) ) ) {
		    if ( $this->_breakOnFailure )
			return false;
		    else
			$check = false;
		}
	    } else {

		throw new Exception( 'Method ' . $method . ' is not valid in the context of validation.' );
	    }
	}

	return $check;
    }

    /**
     * 
     * @access public
     * @return App_General_Message
     */
    public function getMessage()
    {
	return $this->_message;
    }

    /**
     * 
     * @access 	protected
     * @param 	array $data
     * @param 	App_Model_DbTable_Abstract $dbTable
     * @return 	array
     */
    protected function _cleanData( array $data, App_Model_DbTable_Abstract $dbTable )
    {
	$fields = $dbTable->info( App_Model_DbTable_Abstract::COLS );

	foreach ( $data as $key => $value )
	    if ( !in_array( $key, $fields ) )
		unset( $data[$key] );

	return $data;
    }

    /**
     * 
     * @access 	protected
     * @param 	string $field
     * @param 	string $value
     * @return 	string
     */
    protected function urlAmigavel( $field, $value )
    {
	$url = App_General_String::friendName( $value );
	
	$count = 0;

	do {

	    $select = $this->_dbTable->select()->where( $field . ' = ?', $url );

	    $data = $this->_dbTable->fetchRow( $select );

	    if ( !empty( $data ) )
		$url = $url . '-' . ++$count;
	    else
		break;
	} while ( true );

	return $url;
    }
    
    /**
     * 
     * @access 	public
     * @return 	string
     */
    public function randomName()
    {
	return md5( uniqid( time() ) );
    }

    /**
     * 
     * @access protected
     * @return mixed
     */
    protected function _simpleSave( App_Model_DbTable_Abstract $dbTable = null, $addMessage = true )
    {
	if ( empty( $dbTable ) )
	    $dbTable = $this->_dbTable;
	
	$primary = $dbTable->getPrimaryKey();

	$this->_data = $this->_emptyToNull( $this->_data );

	if ( empty( $this->_data[$primary] ) ) {

	    $row = $dbTable->createRow();
	    $row->setFromArray( $this->_data );
	    
	    $result = $row->save();
	} else {

	    $id = $this->_data[$primary];

	    unset( $this->_data[$primary] );
	 
	    $where = $dbTable->getAdapter()->quoteInto( $primary . ' = ?', $id );

	    // Limpa dados para alteracao
	    $data = $this->_cleanData( $this->_data, $dbTable );
	    
	    $result = ( false !== $dbTable->update( $data, $where ) ) ? $id : false;
	}

	if ( $result ) {
	    
	    if ( $addMessage )
		$this->_message->addMessage( $this->_config->messages->success, App_Message::SUCCESS );
	    
	    return $result;
	} else {
	    
	    if ( $addMessage )
		    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    
	    return false;
	}
    }

    /**
     * 
     * @access public
     * @return Zend_Db_Table_Rowset
     */
    public function fetchAll( $where = null, $order = null, $count = null, $offset = null )
    {
	return $this->_getDbTable()->fetchAll( $where, $order, $count, $offset );
    }

    /**
     * 
     * @access 	public
     * @param 	int $id
     * @return 	Zend_Db_Table_Row
     */
    public function fetchRow( $id )
    {
	$primary = $this->_getDbTable()->getPrimaryKey();
	$select = $this->_getDbTable()->select()->where( $primary . ' = ?', $id );

	return $this->_getDbTable()->fetchRow( $select );
    }

    /**
     * 
     * @access 	protected 
     * @param 	string $dbTable
     * @param 	array $data
     * @return 	mixed
     */
    protected function _saveImage( $dbTable, array $data )
    {
	$dbTable = App_Model_DbTable_Factory::get( $dbTable );

	if ( empty( $data['id'] ) ) {

	    $row = $dbTable->createRow();
	    $row->setFromArray( $data );

	    return $row->save();
	} else {

	    $id = $data['id'];

	    unset( $data['id'] );

	    $where = $dbTable->getAdapter()->quoteInto( 'id = ?', $id );

	    $data = $this->_cleanData( $data, $dbTable );

	    return (false !== $dbTable->update( $data, $where )) ? $id : false;
	}
    }

    /**
     *
     * @access 	public
     * @param 	int $id
     * @return 	Zend_Db_Table_Row
     */
    public function getImage( $id )
    {
	$select = $this->_getDbTable()->select()->where( 'id = ?', $id );

	return $this->_getDbTable()->fetchRow( $select );
    }

    /**
     * 
     * @param array $param
     * @return bool
     */
    public function setStatus( array $param )
    {
	try {

	    $data = array( 'status' => (int)$param['action'] );

	    $where = $this->_dbTable->getAdapter()->quoteInto( 'id IN(?)', (array)$param['value'] );
	    	    
	    $this->_dbTable->update( $data, $where );

	    return array( 'result' => true );
	    
	} catch ( Exception $e ) {
	    
	    return array( 'result' => false );
	}
    }

    /**
     *
     * @param array $data
     * @return array 
     */
    protected function _emptyToNull( $data )
    {
	foreach ( $data as $key => $value )
	    if (  $value === '' )
		$data[$key] = null;
	    
	return $data;
    }
}
