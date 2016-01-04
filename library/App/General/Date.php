<?php

/**
 * 
 */
class App_General_Date
{

    /**
     *
     * @param Zend_Date $birthDate
     * @return int
     */
    public static function getAge( Zend_Date $birthDate, $now = null )
    {
	$date = $now ? $now : new Zend_Date();
	$age = $date->get( Zend_Date::YEAR ) - $birthDate->get( Zend_Date::YEAR );
        
	$birthDay = clone $birthDate;
        $birthDay->set( $date, Zend_Date::YEAR );
	
        if ( 1 == $birthDay->compare( $date ) )
            $age = $age -1; // if birth day has not passed yet
	
        return $age;
    }
    
    /**
     *
     * @param Zend_Date $iniDate
     * @return int
     */
    public static function getMonth( Zend_Date $iniDate, $now = null )
    {
	$date = $now ? $now : new Zend_Date();
	$difference = $date->sub( $iniDate );

	$measure = new Zend_Measure_Time( $difference->toValue(), Zend_Measure_Time::SECOND );
	$measure->convertTo( Zend_Measure_Time::MONTH );
	
	return round( $measure->getValue() );
    }
}