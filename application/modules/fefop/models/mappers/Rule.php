<?php

class Fefop_Model_Mapper_Rule extends App_Model_Abstract
{
    
    const AGE_MIN = 'age_min';
    const AGE_MAX = 'age_max';
    const AMOUNT_MIN = 'amount_min';
    const AMOUNT_MAX = 'amount_max';
    const DURATION_MIN = 'duration_min';
    const DURATION_MAX = 'duration_max';
    
    /**
     *
     * @var array
     */
    protected $_labels = array(
	self::AGE_MIN	    => 'Tinan minimu',
	self::AGE_MAX	    => 'Tinan masimu',
	self::AMOUNT_MIN    => 'Folin hira mininu',
	self::AMOUNT_MAX    => 'Folin hira masimu',
	self::DURATION_MIN  => 'Tempu hira minimu',
	self::DURATION_MAX  => 'Tempu hira masimu',
    );
    
    /**
     * 
     * @var Model_DbTable_FEFOPRule
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPRule();

	return $this->_dbTable;
    }

    /**
     * 
     * @return int|bool
     */
    public function save()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbFEFOPRule = App_Model_DbTable_Factory::get( 'FEFOPRule' );
	    
	    $where = array( 'identifier = ?'    => $this->_data['identifier']);
	    $dbFEFOPRule->delete( $where );
	    
	    if ( !empty( $this->_data['rule'] ) ) {
		
		$order = 0;
		foreach ( $this->_data['rule'] as $id => $rule ) {

		    $where = array(
			'identifier = ?'	=> $this->_data['identifier'],
			'rule = ?'		=> $rule,
		    );

		    $row = $dbFEFOPRule->fetchRow( $where );
		    if ( empty( $row ) ) {

			$row = $dbFEFOPRule->createRow();
			$row->identifier = $this->_data['identifier'];
			$row->rule = $rule;
		    }

		    $timeUnit = null;
		    if ( !empty( $this->_data['time_unit'][$rule][$id] ) )
			$timeUnit = $this->_data['time_unit'][$rule][$id];

		    $row->message = $this->_data['message'][$id];
		    $row->required = (int)!empty( $this->_data['required'][$id] );
		    $row->value = $this->ruleToValue( $rule, $this->_data['value'][$rule][$id], $timeUnit );
		    $row->order = ++$order;

		    $row->save();
		}
	    }
	    
	    $history = 'INSERE REGRA BA: %s';
	    $history = sprintf( $history, $this->_data['identifier'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return true;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param string $rule
     * @param string $value
     * @param string $unit
     * @return mixed
     */
    public function ruleToValue( $rule, $value, $unit = false )
    {
	switch ( $rule ) {
	    case self::AGE_MAX:
	    case self::AGE_MIN:
		return (int)$value;
	    case self::AMOUNT_MAX:
	    case self::AMOUNT_MIN:
		return App_General_String::toFloat( $value );
	    case self::DURATION_MAX:
	    case self::DURATION_MIN:    
		return sprintf( '%s-%s', $value, $unit );
	}
    }
    
    /**
     * 
     * @param string $identifier
     * @return Zend_Db_Table_Rowset
     */
    public function listRules( $identifier )
    {
	$dbFefopRules = App_Model_DbTable_Factory::get( 'FEFOPRule' );
	return $dbFefopRules->fetchAll( array( 'identifier = ?' => $identifier ), array( 'order' ) );
    }
    
    /**
     * 
     * @param string $identifier
     * @param string $rule
     * @return Zend_Db_Table_Row
     */
    public function getRuleIdentifier( $identifier, $rule )
    {
	$rules = $this->listRules( $identifier );
	$ruleSelected = null;
	foreach ( $rules as $ruleRow ) {
	    
	    if ( $ruleRow->rule == $rule ) {
		$ruleSelected = $ruleRow;
		break;
	    }
	}
	
	return $ruleSelected;
    }
    
    /**
     * 
     * @param App_General_Message $message
     * @param array $data
     * @param string $identifier
     */
    public function validate( App_General_Message $message, array $data, $identifier )
    {
	$this->_messageModel = $message;
	$this->_dataModel = $data;
	
	$rules = $this->listRules( $identifier );
	
	foreach ( $rules as $rule ) {
	    $method = '_validate' . App_General_String::toCamelCase( $rule->rule, '_' );
	    if ( method_exists( $this, $method ) )
		call_user_func_array( array( $this, $method ), array( $rule ) );
	}
    }
    
    /**
     * 
     * @return int
     * @throws Exception
     */
    protected function _getAge()
    {
	switch ( true ) {
	    case !empty( $this->_dataModel['age'] ):
		return $this->_dataModel['age'];
	    case !empty( $this->_dataModel['client'] ) && is_object( $this->_dataModel['client'] ):
		return $this->_dataModel['client']->age;
	    case !empty( $this->_dataModel['client'] ) && is_array( $this->_dataModel['client'] ):
		$mapperClient = new Client_Model_Mapper_Client();
		$select = $mapperClient->selectClient();
		
		$select->where( 'c.id_perdata IN(?)', (array)$this->_dataModel['client'] );
		$this->_dataModel['client'] = $this->_dbTable->fetchAll($select);
		
		$ages = array();
		foreach( $this->_dataModel['client'] as $client )
		    $ages[] = $client->age;
		
		return $ages;
		
	    case !empty( $this->_dataModel['fk_id_perdata'] ):
		$mapperClient = new Client_Model_Mapper_Client();
		$this->_dataModel['client'] = $mapperClient->detailClient( $this->_dataModel['fk_id_perdata'] );
		return $this->_dataModel['client']->age;
	    default:
		$message = 'La hetan kliente tinan ba halo validasaun';
		$this->_messageModel->addMessage( $message, App_Message::ERROR );
		throw new Exception( $message );
	}
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $rule
     * @throws Exception
     */
    protected function _validateAgeMin( $rule )
    {
	$ages = (array)$this->_getAge();
	foreach ( $ages as $age ) {
	    
	    if ( (int)$age < (int)$rule->value ) {

		if ( !empty( $rule->message ) )
		    $message = $rule->message;
		else {

		    $message = 'Tinan kliente %s menos tinan minimu %s';
		    $message = sprintf( $message, $age, $rule->value );
		}

		$this->_messageModel->addMessage( $message, App_Message::ERROR );
		if ( !empty( $rule->required ) )
		    throw new Exception( $message );
	    }
	}
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $rule
     * @throws Exception
     */
    protected function _validateAgeMax( $rule )
    {
	$ages = (array)$this->_getAge();
	foreach ( $ages as $age ) {
	    if ( (int)$age > (int)$rule->value ) {

		if ( !empty( $rule->message ) )
		    $message = $rule->message;
		else {

		    $message = 'Tinan kliente %s liu tinan masimu %s';
		    $message = sprintf( $message, $age, $rule->value );
		}

		$this->_messageModel->addMessage( $message, App_Message::ERROR );
		if ( !empty( $rule->required ) )
		    throw new Exception( $message );
	    }
	}
    }
    
    /**
     * 
     * @return mixed
     * @throws Exception
     */
    protected function _getAmount()
    {
	switch ( true ) {
	    case array_key_exists( 'amount', $this->_dataModel ):
		return App_General_String::toFloat( $this->_dataModel['amount'] );
	    case array_key_exists( 'total', $this->_dataModel ):
		return App_General_String::toFloat( $this->_dataModel['total'] );
	    case array_key_exists( 'amount_expenses', $this->_dataModel ):
		return App_General_String::toFloat( $this->_dataModel['amount_expenses'] );
	    default:
		$message = 'La hetan folin hira ba halo validasaun';
		$this->_messageModel->addMessage( $message, App_Message::ERROR );
		throw new Exception( $message );
	}
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $rule
     * @throws Exception
     */
    protected function _validateAmountMin( $rule )
    {
	$amount = $this->_getAmount();
	if ( (string)$amount < (string)$rule->value ) {
	    
	    if ( !empty( $rule->message ) )
		$message = $rule->message;
	    else {
		
		$currency = new Zend_Currency();
		$message = 'Folin hira %s menos folin hira minimu %s';
		$message = sprintf( $message, $currency->setValue( $amount )->toCurrency(), $currency->setValue( $rule->value )->toCurrency() );
	    }
	    
	    $this->_messageModel->addMessage( $message, App_Message::ERROR );
	    if ( !empty( $rule->required ) )
		throw new Exception( $message );
	}
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $rule
     * @throws Exception
     */
    protected function _validateAmountMax( $rule )
    {
	$amount = $this->_getAmount();
	if ( (string)$amount > (string)$rule->value ) {
	    
	    if ( !empty( $rule->message ) )
		$message = $rule->message;
	    else {
		
		$currency = new Zend_Currency();
		$message = 'Folin hira %s liu folin hira masimu %s';
		$message = sprintf( $message, $currency->setValue( $amount )->toCurrency(), $currency->setValue( $rule->value )->toCurrency() );
	    }
	    
	    $this->_messageModel->addMessage( $message, App_Message::ERROR );
	    if ( !empty( $rule->required ) )
		throw new Exception( $message );
	}
    }
    
    /**
     * 
     * @param string $unit
     * @return mixed
     * @throws Exception
     */
    protected function _getDiff( $unit )
    {
	$start_date = null;
	$finish_date = null;
	
	switch ( true ) {
	    case !empty( $this->_dataModel['date_start'] ):
		$start_date = $this->_dataModel['date_start'];
		break;
	    case !empty( $this->_dataModel['start_date'] ):
		$start_date = $this->_dataModel['start_date'];
		break;
	}
	
	switch ( true ) {
	    case !empty( $this->_dataModel['date_finish'] ):
		$finish_date = $this->_dataModel['date_finish'];
		break;
	    case !empty( $this->_dataModel['finish_date'] ):
		$finish_date = $this->_dataModel['finish_date'];
		break;
	}
	
	if ( empty( $start_date ) || empty( $finish_date ) ) {
	    
	    $message = 'La hetan loron hahu no loron remata ba halo validasaun';
	    $this->_messageModel->addMessage( $message, App_Message::ERROR );
	    throw new Exception( $message );
	}
	
	$dateStart = new Zend_Date( $start_date );
	$dateFinish = new Zend_Date( $finish_date );
	
	$diff = $dateFinish->sub( $dateStart );
	
	$measure = null;
	switch ( $unit ) {
	    case 'D':
		$measure = Zend_Measure_Time::DAY;
		break;
	    case 'M':
		$measure = Zend_Measure_Time::MONTH;
		break;
	    case 'Y':
		$measure = Zend_Measure_Time::YEAR;
		break;
	}
		
	$measureZend = new Zend_Measure_Time( $diff->toValue(), Zend_Measure_Time::SECOND );
	$diffFinal = $measureZend->convertTo( $measure, 0 );
	
	return preg_replace( '/[^0-9]/i', '', $diffFinal );
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $rule
     * @throws Exception
     */
    protected function _validateDurationMin( $rule )
    {
	$value = explode( '-', $rule->value );
	$unit = $value[1];
	$value = $value[0];
	
	$diff = $this->_getDiff( $unit );
	
	if ( (int)$value > (int)$diff ) {
	    
	    if ( !empty( $rule->message ) )
		$message = $rule->message;
	    else {
		
		$unitName = '';
		switch ( $unit ) {
		    case 'D':
			$unitName = 'Loron';
			break;
		    case 'M':
			$unitName = 'Fulan';
			break;
		    case 'Y':
			$unitName = 'Tinan';
			break;
		}
		
		
		$durationRule = $value . ' ' . $unitName;
		$durationFound = $diff . ' ' . $unitName;
		$message = 'Durasaun nee %s menos durasaun: %s';
		$message = sprintf( $message, $durationFound, $durationRule );
	    }
	    
	    $this->_messageModel->addMessage( $message, App_Message::ERROR );
	    if ( !empty( $rule->required ) )
		throw new Exception( $message );
	}
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $rule
     * @throws Exception
     */
    protected function _validateDurationMax( $rule )
    {
	$value = explode( '-', $rule->value );
	$unit = $value[1];
	$value = $value[0];
	
	$diff = $this->_getDiff( $unit );
	
	if ( (int)$value < (int)$diff ) {
	    
	    if ( !empty( $rule->message ) )
		$message = $rule->message;
	    else {
		
		$unitName = '';
		switch ( $unit ) {
		    case 'D':
			$unitName = 'Loron';
			break;
		    case 'M':
			$unitName = 'Fulan';
			break;
		    case 'Y':
			$unitName = 'Tinan';
			break;
		}
		
		
		$durationRule = $value . ' ' . $unitName;
		$durationFound = $diff . ' ' . $unitName;
		$message = 'Durasaun nee %s liu durasaun: %s';
		$message = sprintf( $message, $durationFound, $durationRule );
	    }
	    
	    $this->_messageModel->addMessage( $message, App_Message::ERROR );
	    if ( !empty( $rule->required ) )
		throw new Exception( $message );
	}
    }
    
    /**
     * 
     * @return array
     */
    public function getOptionsRules()
    {
	return $this->_labels;
    }
   
    /**
     *
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkTypeTransaction()
    {
	$select = $this->_dbTable->select()->where( 'description = ?', $this->_data['description'] );

	if ( !empty( $this->_data['id_fefop_type_transaction'] ) )
	    $select->where( 'id_fefop_type_transaction <> ?', $this->_data['id_fefop_type_transaction'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listAll()
    {
	$dbTypeTransaction = $this->_dbTable;
	
	$select = $dbTypeTransaction->select()
				->from( array( 'tt' => $dbTypeTransaction ) )
				->setIntegrityCheck( false )
				->order( array( 'description' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::FEFOP,
	    'fk_id_sysform'	    => Fefop_Form_Rule::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}