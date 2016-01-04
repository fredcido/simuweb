<?php

require_once APPLICATION_PATH . '/../library/PHPWord/PHPWord.php';

class App_Export_Docx
{
    /**
     *
     * @var PHPWord 
     */
    protected $_word;
    
    /**
     *
     * @var DOMElement
     */
    protected $_content;
    
    /**
     *
     * @var PHPWord_Section
     */
    protected $_mainSection;
    
    /**
     *
     * @var DOMDocument 
     */
    protected $_dom;
    
    /**
     *
     * @var string
     */
    protected $_tempDir;
    
    /**
     *
     * @var string
     */
    protected $_fileName;
    
    /**
     *
     * @var array
     */
    protected $_rowSpans = array();
    
    /**
     *
     * @var array
     */
    protected $_currentRowSpans = array();
    
    /**
     *
     * @var string
     */
    protected $_orientation = 'portrait';
    
    /**
     *
     * @param string $content 
     */
    public function __construct( $content = '' )
    {
	if ( !empty( $content ) )
	    $this->setContent( $content );
    }
    
    /**
     * 
     * @param string $orientation
     */
    public function setOrientation( $orientation )
    {
	$this->_orientation = $orientation;
    }
    
    /**
     *
     * @param string $content 
     */
    public function setContent( $content )
    {
	$this->_dom = new DOMDocument();
	@$this->_dom->loadHTML( $content );
	
	$this->_content = $this->_dom->getElementsByTagName( 'body' )->item( 0 );
    }
    
    /**
     *
     * @param string $filename 
     */
    public function export( $filename )
    {
	$this->setFileName( $filename );
	
	$this->_createTempDir();
	
	$this->_initDoc();
	$this->_addHeader();
	$this->_addTable();
	
	$this->_mainSection->addTextBreak();
	
	$this->_addGraphs();
	
	$this->_save();
	$this->_clearTempDir();
	
	return $this->_fileName;
    }
    
    /**
     *
     * @param string $filename 
     */
    public function setFileName( $filename )
    {
	$this->_fileName = 'reports/' . $filename . '.docx';
    }
    
    /**
     * 
     */
    protected function _createTempDir()
    {
	$tempName = 'reports/' . App_General_String::randomHash();
	mkdir( $tempName );
		
	$this->_tempDir = $tempName;
    }
    
    /**
     * 
     */
    protected function _clearTempDir()
    {
	$iterator = new RecursiveIteratorIterator( 
			    new RecursiveDirectoryIterator( $this->_tempDir ), 
			    RecursiveIteratorIterator::CHILD_FIRST 
			);
	
	foreach ( $iterator as $item ) {
	    if ( $item->isDir() )
		rmdir( $item->getRealPath() );
	    else
		unlink( $item->getRealPath() );
	}
	
	rmdir( $this->_tempDir );
    }
    
    /**
     * 
     */
    protected function _initDoc()
    {
	$this->_word = new PHPWord();
	
	$sectionStyle = array(
			    'orientation'   => $this->_orientation,
			    'marginLeft'    => 900,
			    'marginRight'   => 900,
			    'marginTop'	    => 900,
			    'marginBottom'  => 900
			);
	
	$this->_mainSection = $this->_word->createSection( $sectionStyle );
    }
    
    /**
     * 
     */
    protected function _addHeader()
    {
	$this->_addHeaderReport();
	$this->_addFilters();
    }
    
    /**
     * 
     */
    protected function _save()
    {
	$objWriter = PHPWord_IOFactory::createWriter( $this->_word, 'Word2007' );
	$objWriter->save( $this->_fileName );
    }
    
    /**
     * 
     */
    protected function _addHeaderReport()
    {
	$titles = $this->_content->getElementsByTagName( 'h4' );
	
	// Add the logo
	$logoPath = APPLICATION_PATH . '/../public/images/logo_report.png';
	$this->_mainSection->addImage( $logoPath, array( 'align' => 'center', 'width' => 71, 'height' => 68 ) );

	// Create the header Table
	$tableHeader = $this->_mainSection->addTable();
	$tableHeader->addRow();
	
	$cellTitle = $tableHeader->addCell( 15000 );
	
	$styleCell = array( 'size' => 10, 'name' => 'Calibri', 'bold' => 'Bold' );
	$paragraphStyle = array( 'spacing' => 0, 'spaceBefore' => 0, 'spaceAfter' => 0, 'align' => 'center' ) ;
	
	// Add the titles
	foreach ( $titles as $title )
	    $cellTitle->addText( utf8_decode( $title->nodeValue ), $styleCell, $paragraphStyle );
	
	// Create the report title
	$styleCell['underline'] = PHPWord_Style_Font::UNDERLINE_SINGLE;
	$reportTitle = $this->_content->getElementsByTagName( 'h5' );
	
	$this->_mainSection->addTextBreak();
	
	foreach ( $reportTitle as $title )
	    $this->_mainSection->addText( utf8_decode( $title->nodeValue ), $styleCell );
    }
    
    /**
     * 
     */
    protected function _addFilters()
    {
	$pFilters = $this->_content->getElementsByTagName( 'p' );
	
	if ( empty( $pFilters->length ) )
	    return false;
	
	$styleCell = array( 'size' => 10, 'name' => 'Calibri' );
	
	$filter = $this->_mainSection->createTextRun();
	foreach ( $pFilters->item( 0 )->childNodes as $child ) {
	    
	    if ( $child instanceof DOMElement )
		$filter->addText( $child->nodeValue, $styleCell + array( 'bold' => true ) );
	    else {
		
		$nodeValue = trim( $child->nodeValue );
		
		if ( !empty( $nodeValue ) )
		    $filter->addText( $nodeValue, $styleCell );
	    }
	    
	    $filter->addText( ' ', $styleCell );
	}
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _addTable()
    {
	$tables = $this->_content->getElementsByTagName( 'table' );
	
	if ( empty( $tables->length ) )
	    return false;
	
	$tableStyle = array(
	    'borderSize'    =>	10,
	    'borderColor'   =>	'DDDDDD',
	);
	
	foreach ( $tables as $table ) {
	    
	    $mainTable = $this->_mainSection->addTable( $tableStyle );
	    
	    // Add table elements
	    $this->_addTableHeader( $table, $mainTable );
	    $this->_addTableBody( $table, $mainTable );
	    $this->_addTableFooter( $table, $mainTable );
	    $this->_mainSection->addTextBreak();
	}
    }
    
    /**
     *
     * @param DOMElment $table
     * @param PHPWord_Section_Table $mainTable
     * @return boolean 
     */
    protected function _addTableHeader( $table, $mainTable )
    {
	$thead = $table->getElementsByTagName( 'thead' );
	
	if ( empty( $thead->length ) )
	    return false;
	
	$thead = $thead->item( 0 );
	$rows = $thead->getElementsByTagName( 'tr' );
	foreach ( $rows as $row ) {
	    
	    $mainTable->addRow();
	    $cells = $row->getElementsByTagName( 'th' );
	    
	    $headerCellStyle = array(
		'bgColor'   =>	'FCFCFC'
	    );
	    
	    $fontHeaderStyle = array(
		'bold'	    =>  'Bold',
		'color'	    =>  '545151'
	    );
	   
	    // Insert each row cell
	    foreach ( $cells as $cell )
		$this->_addCell( $cell, $mainTable, $headerCellStyle, $fontHeaderStyle, array() );
	}
    }
    
    /**
     *
     * @param DOMElement $element
     * @return array
     */
    protected function _getCells( $element )
    {
	$nodes = $element->childNodes;
	$cells = array();
	foreach ( $nodes as $node ) {
	    
	    if ( $node->nodeName == 'td' || $node->nodeName == 'th' )
		$cells[] = $node;
	}
	
	return $cells;
    }
    
    /**
     *
     * @param DOMElement $table
     * @param PHPWord_Section_Table $mainTable
     * @return boolean 
     */
    protected function _addTableBody( $table, $mainTable )
    {
	$tbody = $table->getElementsByTagName( 'tbody' );
	
	if ( empty( $tbody->length ) )
	    return false;
	
	$tbody = $tbody->item( 0 );
	$rows = $tbody->getElementsByTagName( 'tr' );
	
	foreach ( $rows as $keyRow => $row ) {
	    
	    $mainTable->addRow();
	    //$cells = $row->getElementsByTagName( 'td' );
	    $cells = $this->_getCells( $row );
	    
	    $fontStyle = array( 'color'	=>  '8A8A8A' );
	    
	    $cellStyle = array();
	    if ( $row->hasAttribute( 'class' ) && 'two' == $row->getAttribute( 'class' ) )
		$cellStyle = array( 'bgColor' => 'FCF9F9' );
	    
	    $rowSpans = array();
	   
	    // Insert each row cell
	    foreach ( $cells as $key => $cell ) {
		
		$rowSpan = 0;
		if ( $cell->hasAttribute( 'rowspan' ) ) {
		    
		    $rowSpan = (int)$cell->getAttribute( 'rowspan' );
		    if ( $rowSpan < 2 )
			$rowSpan = 0;
		}
		
		// If the cell has rowspan
		if ( !empty( $rowSpan ) )
		    $cellStyle['vMerge'] = 'restart';
		else
		    unset( $cellStyle['vMerge'] );
		
		// Add rowspan control
		$this->_handleRowSpan( $key, $mainTable );
		
		// add the cell to the row
		$this->_addCell( $cell, $mainTable, $cellStyle, $fontStyle, array() );
		
		if ( !empty( $rowSpan ) )
		    $rowSpans[$key] = --$rowSpan;
	    }
	    
	    // Put tue current Row rowspan to be handled after
	    foreach ( $rowSpans as $key => $rowSpan ) {
		
		while ( array_key_exists( $key, $this->_rowSpans ) )
		    $key++;
		    
		$this->_rowSpans[$key] = $rowSpan;
	    }
	    
	     // Reset the rowspans memory
	    $this->_currentRowSpans = array();
	}
    }
    
    /**
     * 
     * @param int $key
     * @param PHPWord_Section_Table $table
     */
    protected function _handleRowSpan( $key, $table )
    {
	foreach ( $this->_rowSpans as $keyCell => $rowSpan ) {

	    if ( $keyCell < $key || in_array( $keyCell, $this->_currentRowSpans ) )
		continue;

	    $this->_currentRowSpans[] = $keyCell;
	    $this->_rowSpans[$keyCell] = --$rowSpan;
	    
	    $cellStyle = array(
		'valign'	=>  'center',
		'borderSize'    =>  10,
		'borderColor'   =>  'DDDDDD',
		'vMerge'	=> 'fusion'
	    );

	    $table->addCell( 5000, $cellStyle )->addText( '' );

	    if ( empty( $rowSpan ) )
		unset( $this->_rowSpans[$keyCell] );
	}
    }
    
    /**
     *
     * @param DOMElement $table
     * @param PHPWord_Section_Table $mainTable
     * @return boolean 
     */
    protected function _addTableFooter( $table, $mainTable )
    {
	$tfoot = $table->getElementsByTagName( 'tfoot' );
	
	if ( empty( $tfoot->length ) )
	    return false;
	
	$tfoot = $tfoot->item( 0 );
	$rows = $tfoot->getElementsByTagName( 'tr' );
	
	foreach ( $rows as $row ) {
	    
	    $mainTable->addRow();
	    $cells = $row->getElementsByTagName( 'th' );
	    
	    $fontStyle = array( 'color'	=>  '999999', 'bold' => 'Bold' );
	    $cellStyle = array( 'bgColor' => 'FCFCFC' );
	   
	    // Insert each row cell
	    foreach ( $cells as $cell )
		$this->_addCell( $cell, $mainTable, $cellStyle, $fontStyle, array() );
	}
    }
    
    /**
     *
     * @param DOMElement $element
     * @param PHPWord_Section_Table $table
     * @param array $cellStyle
     * @param array $fontStyle
     * @param array $paragraphStyle 
     */
    protected function _addCell( $element, $table, $cellStyle = array(), $fontStyle = array(), $paragraphStyle = array() )
    {
	$cellStyle += array(
	    'valign'	    =>  'center',
	    'borderSize'    =>	10,
	    'borderColor'   =>	'DDDDDD'
	);
	
	$fontStyle += array(
	    'size'  =>	10,
	    'name'  =>	'Arial'
	);
	
	$paragraphStyle += array(
	    'align'	    =>  'center',
	    'spaceBefore'   =>	50,
	    'spaceAfter'    =>	50
	);
	
	if ( $element->hasAttribute( 'colspan' ) )
	    $cellStyle['gridSpan'] = $element->getAttribute( 'colspan' );
	
	$text = trim( $element->nodeValue );
	$table->addCell( 5000, $cellStyle )->addText( $text, $fontStyle, $paragraphStyle );
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _addGraphs()
    {
	$xpath = new DOMXPath( $this->_dom );
	$graphs = $xpath->query( '//div[@class="graphs"]', $this->_content );
	
	if ( empty( $graphs->length ) )
	    return false;
	
	$images = $graphs->item( 0 )->getElementsByTagName( 'img' );
	foreach ( $images as $img ) {
	    
	    $src = $img->getAttribute( 'src' );
	    $src = preg_replace( '/^.+image\/id\//i', '', $src );
	    $src = preg_replace( '/\/image.png$/i', '', $src );
	    
	    $contents = App_Cache::load( $src );
	    
	    $randomName = App_General_String::randomHash();
	    $fileName = $this->_tempDir . DIRECTORY_SEPARATOR . $randomName . '.png';
	    	    
	    file_put_contents( $fileName, $contents );
	    $size = getimagesize( $fileName );
	    
	    $this->_mainSection->addImage( $fileName, array( 'align' => 'center', 'width' => 675, 'height' => $size[1] ) );
	}
	
	return true;
    }
}