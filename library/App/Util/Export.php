<?php

abstract class App_Util_Export
{
    
    /**
     * 
     * @param array $exportData
     */
    public static function toExcel( $exportData )
    {
	$phpToExcel = new App_Export_Excel( $exportData['content'] );
	$exported = $phpToExcel->export( $exportData['title'] );
	
	header( 'Content-Description: File Transfer' );
	header( 'Content-Type: application/vnd.ms-excel' );
	header( 'Content-Disposition: attachment; filename=' . $exported );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Expires: 0' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Pragma: public' );
	header( 'Content-Length: ' . filesize( $exported ) );
	
	ob_clean();
	flush();
	readfile( $exported );
	unlink( $exported );
    }
    
    /**
     * 
     * @param string $fileName
     * @param string $content
     */
    public static function toDoc( $exportData )
    {	
	$phpToDoc = new App_Export_Docx( $exportData['content'] );
	
	if ( !empty( $exportData['orientation'] ) )
	    $phpToDoc->setOrientation( $exportData['orientation'] );
	
	$exported = $phpToDoc->export( $exportData['title'] );
	
	header( 'Content-Description: File Transfer' );
	header( 'Content-Type: application/vnd.ms-word' );
	header( 'Content-Disposition: attachment; filename=' . $exported );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Expires: 0' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Pragma: public' );
	header( 'Content-Length: ' . filesize( $exported ) );
	
	ob_clean();
	flush();
	readfile( $exported );
	unlink( $exported );
    }
    
    /**
     * 
     * @param array $exportData
     */
    public static function toPdf( $exportData )
    {
	require_once( APPLICATION_PATH . "/../library/DomPdf/dompdf_config.inc.php" );
	
	Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(false);
	
	spl_autoload_register( 'DOMPDF_autoload' );
	setlocale( LC_NUMERIC, 'en_US' );
	
	$orientation = 'portrait';
	if ( !empty( $exportData['orientation'] ) )
	    $orientation = $exportData['orientation'];

        $dom = new DOMPDF();
        $dom->set_paper( 'a4', $orientation );
	$dom->load_html( $exportData['content'] );
	$dom->render();
	$dom->stream( $exportData['title'] );
	
	spl_autoload_unregister( 'DOMPDF_autoload' );
	spl_autoload_unregister( '__autoload' );

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
    }
    
    public static function toRtf( $file, $data, $pattern = '[*%s*]' )
    {
	$rtfData = array();
	foreach ( $data as $key => $value )
	    $rtfData[sprintf( $pattern, strtoupper( $key ) ) ] = utf8_decode( $value );
	
	$filename = basename( $file );
	
	header ( "Cache-Control: no-cache, must-revalidate" );
	header ( "Pragma: no-cache" );
	header ( "Content-type: application/msword;charset=UTF-8" );
	header ( "Content-Disposition: attachment; filename=" . $filename );
	
	$output = file_get_contents( $file );

	foreach ( $rtfData as $key => $value )
	    $output = str_replace( $key, $value, $output );
	
	echo $output;
	exit;
    }
}
