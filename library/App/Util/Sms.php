<?php

abstract class App_Util_Sms
{
    const USER = 'usrsms';
    
    const PASS = 'T1m0r5m52014';
    
    const METHOD_SEND = '/sms/envio';
    
    const METHOD_RETRIEVE = ':8082/sms/retriever.php';

    /**
     *
     * @param string $method
     * @param array $parameters
     * @return string
     */
    public static function send( $url, array $parameters )
    {
	$data = array(
	    'auth'    => self::USER,
	    'passwd' => self::PASS,
	    'access' => App_Util_Indmo::TOKEN
	);
	
	$parameters += $data;
	
	$parameters = array_map( 'utf8_encode', $parameters );
	
	// Treat the Url
	$url = self::handleUrl( $url );
	$url .= self::METHOD_SEND;
	
	$clientHttp = new Zend_Http_Client();
	$clientHttp->setUri( $url )
		    ->setConfig( array( 'timeout' => 60 ) )
		    ->setMethod( Zend_Http_Client::GET )
		    ->setParameterGet( $parameters );

	$response = $clientHttp->request();
	
	if ( $response->isSuccessful() ) {

	    $body = $response->getBody();
	    
	    //$fd = fopen( 'receive.log', 'a+' );
	    //fwrite( $fd, $body . "\n\n" );
	    //fclose( $fd );
	    
	    $json = json_decode( $body, true );
	    
	    if ( empty( $json ) )
		throw new Exception( 'Erro na leitura dos dados ' );
	    else
		return $json;
	    
	} else
	    throw new Exception( 'Erro ao realizar requisição' );
    }
    
    /**
     *
     * @param string $method
     * @param array $parameters
     * @return array
     */
    public static function retrieve( $url, array $parameters = array() )
    {
	$data = array(
	    'usr'    => self::USER,
	    'passwd' => self::PASS,
	    'access' => App_Util_Indmo::TOKEN
	);
	
	$parameters += $data;
	
	$parameters = array_map( 'urlencode', $parameters );
	
	// Treat the Url
	$url = self::handleUrl( $url );
	$url = preg_replace( '/:[0-9]+\/?$/i', '', $url );
	$url .= self::METHOD_RETRIEVE;
	
	$clientHttp = new Zend_Http_Client();
	$clientHttp->setUri( $url )
		->setMethod( Zend_Http_Client::GET )
		->setParameterGet( $parameters );

	$response = $clientHttp->request();
	
	if ( $response->isSuccessful() ) {

	    $body = $response->getBody();
	    $json = json_decode( $body, true );
	    
	    if ( !is_array( $json ) )
		throw new Exception( 'Erro na leitura dos dados ' );
	    else
		return $json;
	    
	} else
	    throw new Exception( 'Erro ao realizar requisição' );
    }
    
    /**
     * 
     * @param string $url
     * @return string
     */
    public static function handleUrl( $url )
    {
	// Make the treatment to the gateway URL
	$url = trim( $url );
	$url = preg_replace( '/\/+$/i', '', $url );
	$url = preg_replace( '/^https?:\/\//i', '', $url );
	$url = 'http://' . $url;
	
	return $url;
    }
}