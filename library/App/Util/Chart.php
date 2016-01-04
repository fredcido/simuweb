<?php

abstract class App_Util_Chart
{
    /**
     *
     * @var array
     */
    protected static $_loadedClasses = array();
    
    /**
     *
     * @var string
     */
    protected static $_fontPath;
    
    /**
     *
     * @var int
     */
    protected static $_width = 700;
    
    /**
     *
     * @var int
     */
    protected static $_height = 500;
    
    /**
     * 
     * @param array|string $class
     */
    public static function loadClass( $class = array() )
    {
	$chartPath = APPLICATION_PATH . '/../library/pChart/class/';
	$class = (array)$class;

	if ( empty( $class ) ) {
	    
	    $dir = opendir( $chartPath );
	    while( $item = readdir( $dir ) ) {
		if ( in_array( $item, array( '.', '..' ) ) )
		    continue;
		
		$class[] = preg_replace( '/\..*/i', '', $item );
	    }
	}
		
	foreach ( $class as $file ) {
	    
	    $path = $chartPath . $file . '.class.php';
	    
	    if ( in_array( $file, self::$_loadedClasses ) || !file_exists( $path ) )
		continue;
	    
	    self::$_loadedClasses[$file] = $file;
	    require_once $path;
	}
    }
    
    /**
     *
     * @param array $data
     * @param string $title
     * @return \App_Util_Chart 
     */
    public static function pieChart( array $data, $title )
    {
	$fontsChart = APPLICATION_PATH . '/../library/pChart/fonts/verdana.ttf';

	App_Util_Chart::loadClass( array('pData', 'pDraw', 'pPie', 'pImage') );
	
	$dataGraph = new pData();
	
	$dataGraph->addPoints( $data['series'], 'Series' );
	$dataGraph->addPoints( $data['labels'], 'Labels' );
	$dataGraph->setAbscissa( 'Labels' );
	
	$myPicture = new pImage( 700, 300, $dataGraph, TRUE );

	$settings = array( 'R' => 250, 'G' => 250, 'B' => 250 );
	$myPicture->drawFilledRectangle( 0, 0, 700, 300, $settings );
	$myPicture->drawRectangle( 0, 0, 699, 299, array( 'R' => 0, 'G' => 0, 'B' => 0 ) );
	$myPicture->setFontProperties( array( 'FontName' => $fontsChart, 'FontSize' => 10, 'R' => 0, 'G' => 0, 'B' => 0 ) );

	$pieChart = new pPie( $myPicture, $dataGraph );
	$pieChart->draw3DPie( 350, 180, array( 'ValuePadding' => 30, 'WriteValues' => TRUE, 'ValuePosition' => PIE_VALUE_OUTSIDE, 'Border' => TRUE, 'ValueR' => 0, 'ValueG' => 0, 'ValueB' => 0, 'Radius' => 130 ) );
	
	$pos = floor( strlen( $title ) / 2 );
	$start = 350 - ( $pos * 9 );
	
	$myPicture->setFontProperties( array( 'FontName' => $fontsChart, 'FontSize' => 14 ) );
	$myPicture->drawText( $start, 25, $title, array( 'R' => 0, 'G' => 0, 'B' => 0 ) );
	
	$myPicture->setFontProperties( array( 'FontName' => $fontsChart, 'FontSize' => 10, 'R' => 0, 'G' => 0, 'B' => 0 ) );
	$pieChart->drawPieLegend( 500, 50, array( 'Style' => LEGEND_NOBORDER ) );

	ob_start();
	imagepng( $myPicture->Picture );
	$image = ob_get_clean();

	return $image;
    }
    
    /**
     *
     * @param int $width 
     */
    public static function setWidht( $width )
    {
	self::$_width = $width;
    }
    
    /**
     *
     * @param int $height 
     */
    public static function setHeight( $height )
    {
	self::$_height = $height;
    }
    
    /**
     *
     * @param array $data
     * @param string $title
     * @return resource
     */
    public static function columnChart( array $data, $title, $scaleConfig = array(), $legendConfig = array() )
    {
	$fontsChart = APPLICATION_PATH . '/../library/pChart/fonts/verdana.ttf';

	App_Util_Chart::loadClass( array('pData', 'pDraw', 'pImage') );

	$myData = new pData();

	foreach ( $data['series'] as $c => $serie ) {
	    
	    $serieId = 'Serie' . $c;
	    $myData->addPoints( $serie, $serieId );
	    $myData->setSerieDescription( $serieId, $data['names'][$c] );
	    $myData->setSerieOnAxis( $serieId, 0 );
	}
	
	$myData->addPoints( $data['labels'], 'Absissa' );
	$myData->setAbscissa( 'Absissa' );

	$myData->setAxisPosition( 0, AXIS_POSITION_LEFT );
	$myPicture = new pImage( 700, 500, $myData );

	$Settings = array("R" => 250, "G" => 250, "B" => 250);
	$myPicture->drawFilledRectangle( 0, 0, self::$_width, self::$_height, $Settings );

	$myPicture->drawRectangle( 0, 0, self::$_width - 1, self::$_height - 1, array("R" => 0, "G" => 0, "B" => 0) );

	$myPicture->setFontProperties( array("FontName" => $fontsChart . "verdana.ttf", "FontSize" => 14) );

	$myPicture->setGraphArea( 50, 50, self::$_width - 1, self::$_height - 100 );
	$myPicture->setFontProperties( array( "R" => 0, "G" => 0, "B" => 0, "FontName" => $fontsChart, "FontSize" => 10 ) );

	$Settings = array(
	    "Pos" => SCALE_POS_LEFTRIGHT,
	    "Mode" => SCALE_MODE_START0,
	    "LabelingMethod" => LABELING_ALL,
	    "GridR" => 255,
	    "GridG" => 255,
	    "GridB" => 255,
	    "GridAlpha" => 50,
	    "TickR" => 0,
	    "TickG" => 0,
	    "TickB" => 0,
	    "TickAlpha" => 50,
	    "CycleBackground" => 1,
	    "LabelRotation" => 45,
	    "DrawXLines" => 1,
	    "DrawSubTicks" => 1,
	    "SubTickR" => 255,
	    "SubTickG" => 0,
	    "SubTickB" => 0,
	    "SubTickAlpha" => 50,
	    "DrawYLines" => ALL
	);
	
	foreach ( $scaleConfig as $c => $v )
	    $Settings[$c] = $v;

	$myPicture->drawScale( $Settings );

	$Config = array( "DisplayValues" => 1, "AroundZero" => 1, "Gradient" => TRUE, "GradientMode" => GRADIENT_EFFECT_CAN );
	$myPicture->drawBarChart( $Config );
	
	$pos = floor( strlen( $title ) / 2 );
	$start = 350 - ( $pos * 9 );
	
	$myPicture->setFontProperties( array( 'FontName' => $fontsChart, 'FontSize' => 14 ) );
	$myPicture->drawText( $start, 25, $title, array( 'R' => 0, 'G' => 0, 'B' => 0 ) );

	$Config = array(
	    "FontR" => 0,
	    "FontG" => 0,
	    "FontB" => 0,
	    "FontName" => $fontsChart,
	    "FontSize" => 8,
	    "Margin" => 6,
	    "Alpha" => 30,
	    "BoxSize" => 5,
	    "Style" => LEGEND_NOBORDER,
	    "Mode" => LEGEND_HORIZONTAL
	);
	
	foreach ( $legendConfig as $c => $v )
	    $Config[$c] = $v;
	
	$myPicture->drawLegend( 480, 40, $Config );

	ob_start();
	imagepng( $myPicture->Picture );
	$image = ob_get_clean();
	
	return $image;
    }
}