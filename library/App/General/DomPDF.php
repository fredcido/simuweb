<?php

class App_General_DomPDF
{
    private $dom = null;
    
    private $html = '';

    public function __construct( $papel = 'a4', $orientacao = 'portrait', $marginFont = true ) 
    {
	require_once( "DomPDF/dompdf_config.inc.php" );
	
	Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(false);

	spl_autoload_register( 'DOMPDF_autoload' );
	
        //corrige problema da geração do pdf
        setlocale( LC_NUMERIC, 'en_US' );

        $this->dom = new DOMPDF();
        $this->dom->set_paper( $papel, $orientacao );
	
        //$this->html = "<html><head></head><body>";
//        if ( $marginFont ) {
//	    
//            $this->setMargin();
//            $this->setFont();
//        }
    }
    
    public function save( $path = "", $filename = "" ) 
    {
        if ( $path == "")  
            $path = getcwd();
	
        $this->dom->render();
        if ( $filename == "")
            $filename = date("dmyHis") . ".pdf";
	
        $content = $this->dom->output();
        $path = (substr($path, -1) != "/") ? $path . "/" : $path;
	
        $path = $path . $filename;
	
        file_put_contents($path, $content);
	
        $this->unregisterAutoload();
	
        return $path;
    }

    public function setMargin($top = '5px', $right = '5px', $bottom = '5px', $left = '5px') 
    {
        $this->html .= "<style> body { margin: $top $right $bottom $left; } </style>";
    }

    public function setFont($familia = 'helvetica', $tamanho = '9pt') 
    {
        $this->html .= "<style> body { font-family: $familia; font-size: $tamanho; } </style>";
    }

    public function unregisterAutoload() 
    {
        spl_autoload_unregister('DOMPDF_autoload');

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
    }

    public function download( $filename ) 
    {
        try {
	    $this->dom->render();
            $this->dom->stream( $filename );
            $this->unregisterAutoload();
	    
        } catch( DOMPDF_Exception $ex ) {
            
        }
    }

    public function loadHtml( $html ) 
    {
        $this->html = $html;
        //$this->html .= '</body></html>';
        $this->dom->load_html( $this->html );
    }

    public function loadHtmlFile( $file ) 
    {
        $this->dom->load_html_file($file);
    }
    
}