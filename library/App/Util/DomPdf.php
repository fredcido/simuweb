<?php

/**
 * 
 */
class App_Util_DomPdf
{
    /**
     *
     * @var DOMPDF
     */
    protected $_domPdf;
    
    /**
     * 
     */
    public function __construct()
    {
	require_once('dompdf/dompdf_config.inc.php');
	$autoloader = Zend_Loader_Autoloader::getInstance();
	$autoloader->pushAutoloader('DOMPDF_autoload', '');
	
	$this->_domPdf = new DOMPDF();
	$this->setPaper();
    }
    
    /**
     *
     * @param string $html 
     */
    public function renderHtml( $html )
    {
	$this->_domPdf->load_html( $html );
    }
    
    /**
     *
     * @param string $file 
     */
    public function renderFile( $file )
    {
	$this->_domPdf->load_html_file( $file );
    }
    
    /**
     *
     * @param string|array $size
     * @param string $orientation 
     */
    public function setPaper( $size = 'a4', $orientation = 'portrait' )
    {
	$this->_domPdf->set_paper( $size, $orientation );
    }
    
    /**
     *
     * @param string $name 
     */
    public function savePdf( $name = 'arquivo.pdf' )
    {
	$this->_domPdf->render();
	$this->_domPdf->stream( $name );
    }
    /**
     *
     * @return string
     */
    public function renderPdf()
    {
	$this->_domPdf->render();
	return $this->_domPdf->output();
    }
}