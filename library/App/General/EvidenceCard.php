<?php

abstract class App_General_EvidenceCard
{
    
    /**
     *
     * @param mixed $clients 
     */
    public static function generate( $clients )
    {
	include( APPLICATION_PATH . '/../library/mpdf/mpdf.php' );
	ini_set( 'memory_limit', '1G' );
	
	if ( !is_array( $clients ) )
	    $clients = array( $clients );
	
	$layoutPath = APPLICATION_PATH . '/modules/client/views/scripts/';
	
	$layoutView = new Zend_View();
	$layoutView->setScriptPath( $layoutPath );
	$layoutView->addHelperPath( 'App/View/Helpers/', 'App_View_Helper' );
	
	$pdf = new mPDF( '', array(86,55), '', '', 0, 0, 0, 0, 0, 0,  'P' );
	$pdf->debug = true;
	
	foreach ( $clients as $client ) {
	    
	    $layoutView->client = $client;
	    $html = $layoutView->render( 'client/evidence.phtml' );
	    
	    $pdf->AddPage();
            $pdf->WriteHTML( $html );
	}
	
	$pdf->Output();
        exit;
    }
}