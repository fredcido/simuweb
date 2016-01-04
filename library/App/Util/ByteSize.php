<?php

/** 
 * 
 */
class App_Util_ByteSize
{
	/**
	 * Unidade com seu respectivo expoente
	 * 
	 * @var array
	 */
	protected static $_unit = array(
		'B'  => 0,
		'K' => 10,
		'M' => 20,
		'G' => 30,
		'T' => 40,
		'P' => 50
	);
		
	/**
	 * 
	 * @access 	public
	 * @param 	float 		$value
	 * @param 	string 		$ofUnit
	 * @param 	string 		$forUnit
	 * @return 	float
	 */
	public static function convert ( $value, $ofUnit, $forUnit )
	{
		$ofUnit 	= strtoupper( $ofUnit );
		$forUnit 	= strtoupper( $forUnit );
		
		switch ( true ) {
			
			case !is_numeric($value):
				throw new Exception('Not a number');
				break;
				
			case !in_array( $ofUnit, array_keys(self::$_unit) ):
				throw new Exception('Unit is not defined: ' . $ofUnit);
				break;
				
			case !in_array( $forUnit, array_keys(self::$_unit) ):
				throw new Exception('Unit is not defined: ' . $forUnit);
				break;
				
		}

		return $value * pow(2, self::$_unit[$ofUnit]) / pow(2, self::$_unit[$forUnit]);
	}
}