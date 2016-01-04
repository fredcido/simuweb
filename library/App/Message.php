<?php

class App_Message
{
    const WARNING = 'warning'; //warning

    const ERROR = 'error'; //error

    const SUCCESS = 'success'; //success

    const INFO = 'info'; //info
    

    const ISO = 'ANSI';

    const UTF8 = 'UTF-8';
    

    protected $_messages = array();

    protected $_encoding;

    protected $_avaliableLevels = array( self::WARNING, self::ERROR, self::SUCCESS, self::INFO );

    protected $_avaliableEncodings = array( self::ISO, self::UTF8 );

    public function __construct( $encoding = self::UTF8 ) {

        $this->setEnconding( $encoding );
    }

    public function addMessage( $message, $level = self::INFO )
    {
        if ( !in_array( $level, $this->_avaliableLevels ) )
            throw new Exception( $level . ' is a level not avaliable.');
        
        $this->_messages[] = array(
            'level'   => $level,
            'message' => $message
        );
    }

    public function setEnconding( $encoding )
    {
        if ( !in_array( $encoding, $this->_avaliableEncodings ) )
            throw new Exception( $encoding . ' is an encoding not avaliable.');

        $this->_encoding = $encoding;
    }

    public function encodeMessage( $message )
    {
        if ( mb_detect_encoding( $message['message'] ) != $this->_encoding )
            $message['message'] = mb_convert_encoding ( $message['message'], $this->_encoding );

        return $message;
    }

    public function toArray()
    {
        $this->_messages = array_map( array( $this, 'encodeMessage' ), $this->_messages );

        return $this->_messages;
    }

    public function toJson()
    {
        return json_encode( $this->toArray() );
    }

    public function toHtml( $item = '<li class="%s">%s</li>', $container = '<ul class="message">%s</ul>' )
    {
        $messagesCollection = $this->toArray();
        $messages = '';

        foreach ( $messagesCollection as $message )
            $messages .= sprintf( $item, $message['level'], $message['message'] );

        return sprintf( $container, $messages );
    }

    public function getMessage( $pos = 0 )
    {
        $messagesCollection = $this->toArray();
        return empty( $messagesCollection[$pos]['message'] ) ? 
		'' :
		$messagesCollection[$pos]['message'];
    }

    public function toXml()
    {
        $messagesCollection = $this->toArray();

        $xml = simplexml_load_string('<messages />');

        foreach ( $messagesCollection as $message ) {

            $message = $xml->addChild( 'message' );
            $message->addChild('level', $message['level'] );
            $message->addChild('text', $message['message'] );
        }

        return $xml;
    }

    public function __toString()
    {
        return $this->toHtml();
    }
    
    public function clearMessages()
    {
	$this->_messages = array();
    }
}