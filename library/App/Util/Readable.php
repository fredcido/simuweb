<?php

/** 
 * 
 */
class App_Util_Readable
{
	/**
	 * Unidade com seu respectivo descritor
	 * 
	 * @var array
	 */
	protected static $_unit = array(
		'B' => 'Byte',
		'K' => 'Kilobyte',
		'M' => 'Megabyte',
		'G' => 'Gigabyte',
		'T' => 'Terabyte',
		'P' => 'Pentabyte'
	);
	
	/**
	 * Unidade reduziada com seu respectivo descritor
	 * 
	 * @var array
	 */
	protected static $_unitShort = array(
		'B' => 'B',
		'K' => 'KB',
		'M' => 'MB',
		'G' => 'GB',
		'T' => 'TB',
		'P' => 'PB'
	);
	
	/**
	 * Periodo com seu respectivo descritor
	 * 
	 * @var array
	 */
	protected static $_periodo = array(
		'M' => 'Minuto',
		'H' => 'Hora',
		'D' => 'Dia',
		'S' => 'Semana',
		'M' => 'M&ecirc;s',
		'A' => 'Ano',
	);
	
	/**
	 *
	 * @param float $value
	 * @param string $unit
	 * @return string 
	 */
	public static function readBytes( $value, $unit, $short = false )
	{
	    $unit 	= strtoupper( $unit );
	    
	    $units = $short ? self::$_unitShort : self::$_unit;
	    
	    switch ( $value ) {
			
		case !is_numeric($value):
			throw new Exception('Not a number');
			break;

		case !in_array( $unit, array_keys( $units ) ):
			throw new Exception('Unit is not defined: ' . $unit);
			break;
	    }
	    
	    $readable = round( $value, 2 ) . ' ' . $units[ $unit ];
	    
	    if ( !$short && $value > 1 ) 
		$readable .= 's';
	    
	    return $readable;
	}
	
	/**
	 *
	 * @param float $value
	 * @param string $time
	 * @return string 
	 */
	public static function readTime( $value, $time )
	{
	    $time = strtoupper( $time );
	    
	    switch ( $value ) {
			
		case !is_numeric($value):
			throw new Exception('Not a number');
			break;

		case !in_array( $time, array_keys( self::$_periodo ) ):
			throw new Exception('Unit is not defined: ' . $time);
			break;
	    }
	    
	    if ( $value > 1 )	
		$readable = $value . ' ' . ( $time != 'M' ?  ( self::$_periodo[ $time ] . 's' ) : 'Meses' );
	    else
		$readable = $value . ' ' . self::$_periodo[ $time ];
	    
	    return $readable;
	}
		
	
}