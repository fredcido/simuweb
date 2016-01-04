<?php

abstract class App_Util_Indmo
{
    const TOKEN = '95feba761d92f1901eefe978456ea4bc';
    
    const API_URL = 'http://sdb-indmo.com/';

    /**
     *
     * @param string $method
     * @param array $parameters
     * @return string
     */
    public static function request( $method, array $parameters )
    {
	$data = array(
	    'access' => self::TOKEN
	);
	
	$parameters += $data;
	
	$url = self::API_URL . $method;
	
	$clientHttp = new Zend_Http_Client();
	$clientHttp->setUri( $url )
		->setMethod( Zend_Http_Client::POST )
		->setParameterPOST( $parameters );

	$response = $clientHttp->request();
	
	if ( $response->isSuccessful() ) {

	    $body = $response->getBody();
	    $json = json_decode( $body, true );
	    
	    if ( empty( $json ) )
		throw new Exception( 'Erro na leitura dos dados' );
	    else
		return $json;
	    
	} else
	    throw new Exception( 'Erro ao realizar requisição' );
    }
}
