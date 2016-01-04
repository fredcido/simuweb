<?php

/**
 * 
 */
class App_General_Message
{
	/* Tipo da mensagem */
	
	/**
	 * 
	 * @var string
	 */
    const WARNING = 'warning';

    /**
     * 
     * @var string
     */
    const ERROR = 'error';
    
	/**
	 * 
	 * @var string
	 */
    const SUCCESS = 'success';

    /**
     * 
     * @var string
     */
    const INFO = 'info';
    
    /* charset */

    /**
     * 
     * @var string
     */
    const ISO = 'ANSI';

    /**
     * 
     * @var string
     */
    const UTF8 = 'UTF-8';

    /**
     * 
     * @var array
     */
    protected $_messages = array();

    /**
     * 
     * @var string
     */
    protected $_encoding;

    /**
     * 
     * @var array
     */
    protected $_avaliableLevels = array( self::WARNING, self::ERROR, self::SUCCESS, self::INFO );

    /**
     * 
     * @var array
     */
    protected $_avaliableEncodings = array( self::ISO, self::UTF8 );

    /**
     *
     * @access 	public
     * @param 	string $encoding
     * @return 	void
     */
    public function __construct ( $encoding = self::UTF8 ) 
    {
        $this->setEnconding( $encoding );
    }

    /**
     * 
     * @access 	public
     * @param 	string $message
     * @param 	string $level
     * @return 	void
     * @throws 	Exception
     */
    public function addMessage ( $message, $level = self::INFO )
    {
        if ( !in_array( $level, $this->_avaliableLevels ) )
            throw new Exception( $level . ' is a level not avaliable.');
        
        $this->_messages[] = array(
            'level'   => $level,
            'message' => $message
        );
    }

    /**
     * 
     * @access 	public
     * @param 	string $encoding
     * @return 	void
     * @throws 	Exception
     */
    public function setEnconding ( $encoding )
    {
        if ( !in_array( $encoding, $this->_avaliableEncodings ) )
            throw new Exception( $encoding . ' is an encoding not avaliable.');

        $this->_encoding = $encoding;
    }

    /**
     * 
     * @access 	public
     * @param 	arary $message
     * @return 	array
     */
    public function encodeMessage ( $message )
    {
        if ( mb_detect_encoding($message['message']) != $this->_encoding )
            $message['message'] = mb_convert_encoding( $message['message'], $this->_encoding );

        return $message;
    }

    /**
     * 
     * @access 	public
     * @param 	int $pos
     * @return 	string
     */
	public function getMessage ( $pos = 0 )
    {
        $messages = $this->toArray();
        
        return empty($messages[$pos]['message']) ? '' : $messages[$pos]['message'];
    }
    
    /**
     * 
     * @access public
     * @return array
     */
    public function toArray ()
    {
        $this->_messages = array_map( array($this, 'encodeMessage'), $this->_messages );

        return $this->_messages;
    }

    /**
     * 
     * @access public
     * @return string
     */
    public function toJson ()
    {
        return json_encode( $this->toArray() );
    }

    /**
     * 
     * @access 	public
     * @param 	string $item
     * @param 	string $container
     * @return 	string
     */
    public function toHtml ( $item = '<li class="%s">%s</li>', $container = '<ul class="message">%s</ul>' )
    {
        $messages = $this->toArray();
        $html = '';

        foreach ( $messages as $message )
            $html .= sprintf( $item, $message['level'], $message['message'] );

        return sprintf( $container, $html );
    }

    /**
     * 
     * @access public
     * @return string
     */
    public function toXml ()
    {
        $messages = $this->toArray();

        $xml = simplexml_load_string('<messages />');

        foreach ( $messages as $value ) {

            $message = $xml->addChild( 'message' );
            
            $message->addChild('level', $value['level'] );
            $message->addChild('text', $value['message'] );
            
        }

        return $xml;
    }

    /**
     * 
     * @access public
     * @return string
     */
    public function __toString ()
    {
        return $this->toHtml();
    }
    
    public function clearMessages()
    {
	$this->_messages = array();
    }
}