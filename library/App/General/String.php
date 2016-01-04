<?php

/**
 * 
 */
class App_General_String
{

    /**
     * Retorna um hash aleatorio
     * 
     * @access public
     * @static
     * @return string
     */
    public static function randomHash()
    {
	return md5( uniqid( time() ) );
    }

    /**
     * Retorna um password aleatorio
     * 
     * @access 	public
     * @static
     * @param 	int $length
     * @param 	bool $lower
     * @param 	bool $upper
     * @param 	bool $number
     * @return 	string
     */
    public static function randomPassword( $length = 6, $lower = true, $upper = true, $number = true )
    {
	$password = '';

	for ( $i = 0; $i < $length; $i++ ) {

	    $character = array();

	    switch ( true ) {

		case $lower:
		    $character[] = chr( rand( 97, 122 ) );

		case $upper:
		    $character[] = chr( rand( 65, 90 ) );

		case $number:
		    $character[] = chr( rand( 48, 57 ) );
	    }

	    $password .= $character[rand( 0, count( $character ) - 1 )];
	}

	return $password;
    }

    /**
     * Retorna nome amigavel
     * 
     * @access 	public
     * @static
     * @param 	string $value
     * @return 	string
     */
    public static function friendName( $string )
    {
	// Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
        $string = strtolower( $string );
        // Strip any unwanted characters
        $string = preg_replace( "/[^a-z0-9_\s-]/", "", $string );
        // Clean multiple dashes or whitespaces
        $string = preg_replace( "/[\s-]+/", " ", $string );
        // Convert whitespaces and underscore to dash
        $string = preg_replace( "/[\s_]/", "-", $string );
	
        return $string;	
    }
    
    /**
     *
     * @param string $string
     * @param string $separator
     * @return string 
     */
    public static function toCamelCase( $string, $separator = '-' )
    {
	$string = preg_replace('/^_/', '', $string );
        $pieces = explode( $separator, $string);
        
        $string = '';
        foreach ( $pieces as $piece ) {
            $string .= ucfirst( $piece );
        }

        return $string;
    }
    
    /**
     *
     * @param string $value
     * @param string $mask
     * @return string 
     */
    public static function mask( $value, $mask )
    {
	$value = preg_replace( '/[^0-9]/', '', $value );
	$cleanMask = preg_replace( '/[^#]/', '', $mask );
	
	if ( strlen( $cleanMask ) > strlen( $value ) )
	    $value = str_pad ( $value, strlen( $cleanMask ), 0, STR_PAD_LEFT );
	
	$finalValue = '';
	$pos = 0;
	for ( $i = 0; $i < strlen( $mask ); $i++ ) {
	    
	    if ( $mask[$i] == '#' )
		$char = $value[$pos++];
	    else
		$char = $mask[$i];
	    
	    $finalValue .= $char;
	}
	
	return $finalValue;
    }
    
    /**
     *
     * @param string $phone
     * @return string
     */
    public static function cleanFone( $phone )
    {
	$value = preg_replace( '/[^0-9]/', '', $phone );
	$value = preg_replace( '/^670?/', '', $value );
	$value = preg_replace( '/^0+?/', '', $value );
	
	if ( empty( $value ) )
	    return $value;
	
	if ( strlen( $value ) > 8 )
	    $value = substr( $value, 0, 8 );
	
	$value = str_pad ( $value, 8, 7, STR_PAD_LEFT );
	
	$value = '670' . $value;
	
	return self::mask( $value, '(###)####-####' );
    }

    /**
     *
     * @param string $string
     * @param int $interval
     * @param string $break
     * @return string
     */
    public static function addBreakLine( $string, $interval = 3, $break = "\n\r" )
    {
	$text_array = explode( ' ', $string );
	$chunks = array_chunk( $text_array, $interval );
	
	$newString = array();
	foreach ( $chunks as $chunk ) {
	    
	    $line = implode( ' ', $chunk );
	    $newString[] = $line;
	    $newString[] = $break;
	}
	
	return implode( ' ', $newString );
    }
    
    /**
     * 
     * @param string $number
     * @return boolean|string
     */
    public static function validateNumber( $number )
    {
	$value = preg_replace( '/[^0-9]/', '', $number );
	//$value = preg_replace( '/^670/', '', $value );
	//$value = '5562' . $value;
	
	if ( 
		empty( $value ) 
		||
		!preg_match( '/^6707/i', $value )
		||
		strlen( $value ) != 11
	    )
	    return false;
	
	return $value;
    }
    
    /**
     * 
     * @param string $value
     * @return float
     */
    public static function toFloat( $value )
    {
	return (float)str_replace( ',', '', $value );
    }
}