<?php

/**
 * 
 */
class App_Model_Exception extends Exception
{
	/**
	 * 
	 * @access 	public
	 * @param 	string $msg
	 * @param 	int $code
	 * @param 	Exception $previous
	 */
	public function __construct ( $msg = '', $code = 0, Exception $previous = null )
	{
		parent::__construct( $msg, $code, $previous );
		
		$this->_log();
	}

	/**
	 * (non-PHPdoc)
	 * @see Exception::getMessage()
	 */
	public function getMessage ()
	{
		$config = Zend_Registry::get('config');
		
		if ( APPLICATION_ENV === 'production' )
			return $config->messages->error;
		else 
			return $this->getTraceAsString();
	}

	/**
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _log ()
	{
		$config = Zend_Registry::get('config');
		
		if ( $config->log->active ) {
			
			$writer = new Zend_Log_Writer_Stream($config->log->db);
			$logger = new Zend_Log($writer);
			
			$logger->log( $this->getTraceAsString(), Zend_Log::ERR );
			
		}
	}
}