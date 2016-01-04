<?php

require_once APPLICATION_PATH . '/../library/PHPExcel/PHPExcel.php';

class App_Export_Excel
{
    /**
     *
     * @var PHPExcel
     */
    protected $_excel;
    
    /**
     *
     * @var DOMElement
     */
    protected $_content;
    
    /**
     *
     * @var PHPExcel_Worksheet
     */
    protected $_mainSheet;
    
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
     * @var type 
     */
    protected $_styles = array();
    
    /**
     *
     * @var int
     */
    protected $_startRow = 8;
    
    /**
     *
     * @var int
     */
    protected $_startCol = 2;
    
    /**
     *
     * @var int
     */
    protected $_currentRow = 0;
    
    /**
     *
     * @var int
     */
    protected $_currentCell = 0;
    
    /**
     *
     * @var array
     */
    protected $_rowSpans = array();
    
    /**
     *
     * @param string $content 
     */
    public function __construct( $content = '' )
    {
	if ( !empty( $content ) )
	    $this->setContent( $content );
	
	$this->_initSharedStyles();
    }
    
    /**
     * 
     */
    protected function _initSharedStyles()
    {
	$headerTable = new PHPExcel_Style();
	$rowEven = new PHPExcel_Style();
	$rowOdd = new PHPExcel_Style();
	$footerTable = new PHPExcel_Style();
	
	$borders = array(
	    'allborders' => array(
		'style' => PHPExcel_Style_Border::BORDER_THIN,
		'color'	=>  array( 'argb' => 'FFDDDDDD' )
	   )
	);
	
	$alignment = array(
			'wrap'       => true,
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
		    );
	
	$headerTable->applyFromArray(
			    array( 
				'fill' => array(
				    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
				    'color' => array( 'argb' => 'FFFCFCFC' )
				),
				'borders' => $borders,
				'font'	=>  array(
				    'bold'  =>	true
				),
				'alignment'  =>	$alignment
			    )
			);
	
	$footerTable->applyFromArray(
			    array( 
				'fill' => array(
				    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
				    'color' => array( 'argb' => 'FFFCFCFC' )
				),
				'borders' => $borders,
				'font'	=>  array(
				    'bold'  =>	true
				),
				'alignment'  =>	$alignment,
		        'numberformat' => array('code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE),
			    )
			);
	
	$rowEven->applyFromArray(
			    array( 
				'borders' => $borders,
				'alignment'  =>	$alignment,
		        'numberformat' => array('code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE),
			    )
			);
	
	$rowOdd->applyFromArray(
			    array( 
				'fill' => array(
				    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
				    'color' => array( 'argb' => 'FFFCF9F9' )
				),
				'borders' => $borders,
				'alignment'  =>	$alignment,
		        'numberformat' => array('code' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE),
			    )
			);
	
	$this->_styles['table_header'] = $headerTable;
	$this->_styles['table_footer'] = $footerTable;
	$this->_styles['row_even'] = $rowEven;
	$this->_styles['row_odd'] = $rowOdd;
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
	
	$this->_initExcel();
	$this->_addTable();
	$this->_addGraphs();
	$this->_addHeader();
	
	$this->_styleWorkSheet();
	
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
	$this->_fileName = 'reports/' . $filename . '.xlsx';
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
    protected function _initExcel()
    {
	$this->_excel = new PHPExcel();
	$this->_excel->setActiveSheetIndex(0);
	$this->_mainSheet = $this->_excel->getActiveSheet();
	
	$styleArray = array(
	    'borders' => array(
		'allborders' => array(
		     'style'	=> PHPExcel_Style_Border::BORDER_NONE,
		     'color'	=>  array( 'argb' => 'FFFFFFFF' )
		)
	    ),
	    'fill' => array(
		'type'	=> PHPExcel_Style_Fill::FILL_SOLID,
		'color' => array( 'argb' => 'FFFFFFFF' )
	    ),
	    'font'  =>	array(
		'name'	=>  'Calibri',
		'size'	=>  10
	    )
	);
	
	$this->_mainSheet->getDefaultStyle()->applyFromArray( $styleArray );
	$this->_mainSheet->getColumnDimension('A')->setVisible( false );
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
	$objWriter = PHPExcel_IOFactory::createWriter( $this->_excel, 'Excel2007' );
	$objWriter->save( $this->_fileName );
    }
    
    /**
     * 
     */
    protected function _addHeaderReport()
    {
	$titles = $this->_content->getElementsByTagName( 'h4' );

	// Add the titles
	$titlesReport = array();
	foreach ( $titles as $title )
	    $titlesReport[] = $title->nodeValue;
	
	$cellHeader = $this->_mainSheet->getCell( 'C2' );
	$cellHeader->setValue( implode( "\r\n", $titlesReport ) );
	
	$styleHeader = $cellHeader->getStyle();
	$styleHeader->getAlignment()->setWrapText( true );
	$styleHeader->getFont()->setBold( true );
	
	$this->_mainSheet->mergeCells( 'C2:T2' );
	
	// Add the logo
	$logoPath = APPLICATION_PATH . '/../public/images/logo_report.png';
	
	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setPath( $logoPath )
		    ->setResizeProportional( true )
		    ->setCoordinates( 'I2' )
		    ->setWorksheet( $this->_mainSheet )
		    ->setWidthAndHeight( 60, 60 );
	
	$this->_mainSheet->getColumnDimension('J')->setWidth( 50 );
	$this->_mainSheet->getRowDimension( '2' )->setRowHeight( 50 );
	
	
	// Create the report title
	$reportTitle = $this->_content->getElementsByTagName( 'h5' );
	
	$titlesValues = array();
	foreach ( $reportTitle as $title )
	    $titlesValues[] = trim( $title->nodeValue );
	
	$cellTitle = $this->_mainSheet->getCell( 'C4' );
	$cellTitle->setValue( implode( ' - ', $titlesValues ) );
	
	$this->_mainSheet->mergeCells( 'C4:T4' );
    }
    
    /**
     * 
     */
    protected function _addFilters()
    {
	$pFilters = $this->_content->getElementsByTagName( 'p' );
	
	if ( empty( $pFilters->length ) )
	    return false;
	
	$filters = array();
	foreach ( $pFilters->item( 0 )->childNodes as $child ) {
	    
	    $nodeValue = trim( $child->nodeValue );

	    if ( !empty( $nodeValue ) )
		$filters[] = $nodeValue;
	}
		
	$this->_mainSheet->getCell( 'C6' )->setValue( implode( ' ', $filters ) )->getStyle()->getAlignment()->setWrapText( true );
	$this->_mainSheet->mergeCells( 'C6:T6' );
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
	
	//$table = $table->item( 0 );
	
	foreach ( $tables as $table ) {
	
	    // Add table elements
	    $this->_addTableHeader( $table );
	    $this->_addTableBody( $table );
	    $this->_addTableFooter( $table );
	    
	    $this->_startRow = $this->_currentRow + 1;
	}
    }
    
    /**
     *
     * @param DOMElment $table
     * @return boolean 
     */
    protected function _addTableHeader( $table )
    {
	$thead = $table->getElementsByTagName( 'thead' );
	
	if ( empty( $thead->length ) )
	    return false;
	
	$thead = $thead->item( 0 );
	$rows = $thead->getElementsByTagName( 'tr' );
	
	$this->_currentRow = $this->_startRow;
	
	foreach ( $rows as $row ) {
	    
	    $cells = $row->getElementsByTagName( 'th' );
	    
	    $this->_currentCell = $this->_startCol;
	    
	    // Insert each row cell
	    foreach ( $cells as $cell )
		$this->_addCell( $cell );
	    
	    $this->_currentRow++;
	}
	
	$range = array(
	    PHPExcel_Cell::stringFromColumnIndex( $this->_startCol ) . $this->_startRow,
	    PHPExcel_Cell::stringFromColumnIndex( $this->_currentCell - 1 ) . ( $this->_currentRow - 1 )
	);
	
	$range = implode( ':', $range );
	$this->_mainSheet->setSharedStyle( $this->_styles['table_header'], $range );
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
     * @return boolean 
     */
    protected function _addTableBody( $table )
    {
	$tbody = $table->getElementsByTagName( 'tbody' );
	
	if ( empty( $tbody->length ) )
	    return false;
	
	$tbody = $tbody->item( 0 );
	$rows = $tbody->getElementsByTagName( 'tr' );
	
	foreach ( $rows as $keyRow => $row ) {
	    
	    //$cells = $row->getElementsByTagName( 'td' );
	    $cells = $this->_getCells( $row );
	    
	    $cellStyle = $this->_styles['row_odd'];
	    if ( $row->hasAttribute( 'class' ) && 'two' == $row->getAttribute( 'class' ) )
		$cellStyle = $this->_styles['row_even'];
	    
	    $this->_currentCell = $this->_startCol;
	    
	    $rowSpans = array();
	   
	    // Insert each row cell
	    foreach ( $cells as  $key => $cell ) {
		
		$rowSpan = 0;
		if ( $cell->hasAttribute( 'rowspan' ) ) {
		    
		    $rowSpan = (int)$cell->getAttribute( 'rowspan' );
		    if ( $rowSpan < 2 )
			$rowSpan = 0;
		}
		
		$this->_handleRowSpan( $key );
					
		// add the cell to the row
		$this->_addCell( $cell, $keyRow );
		
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
	    
	    $range = array(
		PHPExcel_Cell::stringFromColumnIndex( $this->_startCol ) . $this->_currentRow,
		PHPExcel_Cell::stringFromColumnIndex( $this->_currentCell - 1 ) . ( $this->_currentRow )
	    );

	    $range = implode( ':', $range );
	  
	    $this->_mainSheet->setSharedStyle( $cellStyle, $range );
	    
	    $this->_currentRow++;
	}
    }
    
    /**
     * 
     * @param int $key
     * @param PHPWord_Section_Table $table
     */
    protected function _handleRowSpan( $key )
    {
	foreach ( $this->_rowSpans as $keyCell => $rowSpan ) {

	    if ( $keyCell < $key || in_array( $keyCell, $this->_currentRowSpans ) )
		continue;

	    $this->_currentRowSpans[] = $keyCell;
	    $this->_rowSpans[$keyCell] = --$rowSpan;
	    
	    $this->_currentCell++;
	   
	    if ( empty( $rowSpan ) )
		unset( $this->_rowSpans[$keyCell] );
	}
    }
    
    /**
     *
     * @param DOMElement $table
     * @return boolean 
     */
    protected function _addTableFooter( $table )
    {
	$tfoot = $table->getElementsByTagName( 'tfoot' );
	
	if ( empty( $tfoot->length ) )
	    return false;
	
	$tfoot = $tfoot->item( 0 );
	$rows = $tfoot->getElementsByTagName( 'tr' );
	
	$rowStarted = $this->_currentRow;
	
	foreach ( $rows as $row ) {
	    
	    $this->_currentCell = $this->_startCol;
	   
	    $cells = $row->getElementsByTagName( 'th' );
	   
	    // Insert each row cell
	    foreach ( $cells as $cell )
		$this->_addCell( $cell );
	    
	    $this->_currentRow++;
	}
	
	$range = array(
	    PHPExcel_Cell::stringFromColumnIndex( $this->_startCol ) . $rowStarted,
	    PHPExcel_Cell::stringFromColumnIndex( $this->_currentCell - 1 ) . ( $this->_currentRow - 1 )
	);
	
	$range = implode( ':', $range );
	$this->_mainSheet->setSharedStyle( $this->_styles['table_footer'], $range );
    }
    
    /**
     * 
     * @param DOMElement $element
     * @return PHPExcel_Cell
     */
    protected function _addCell( $element, $row = null )
    {
    	$text = trim($element->nodeValue);
    	
    	$cell = $this->_mainSheet->getCellByColumnAndRow($this->_currentCell, $this->_currentRow);
    		
    	if (preg_match('/^\$ *(\d{1,3}(\,\d{3})*|(\d+))(\.\d{2})?$/', $text)) {
    		
    		$currencyCode         = PHPExcel_Shared_String::getCurrencyCode();
    		$decimalSeparator     = PHPExcel_Shared_String::getDecimalSeparator();
    		$thousandsSeparator   = PHPExcel_Shared_String::getThousandsSeparator();
    		
    		$value = (float) trim(str_replace(array($currencyCode, $thousandsSeparator, $decimalSeparator), array('', '', '.'), $text));
    		
    		$cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            
    		//Style
    		$cell->getWorksheet()
                ->getStyle($cell->getCoordinate())
                ->getNumberFormat()
                ->setFormatCode(str_replace('$', $currencyCode, PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE));
    		
    	} else {
    	    
    		$cell->setValueExplicit($text, PHPExcel_Cell_DataType::TYPE_STRING);
    		
    	}
    	
    	$currentCell = $this->_currentCell;
    	
    	if ( $element->hasAttribute( 'colspan' ) ) {
    	    
    	    $colspan = (int)$element->getAttribute( 'colspan' );
    	    $cellTarget = $this->_currentCell + $colspan - 1;
    	    $this->_mainSheet->mergeCellsByColumnAndRow( $this->_currentCell, $this->_currentRow, $cellTarget, $this->_currentRow );
    	    
    	    $this->_currentCell += $colspan;
    	    
    	} else {
    	    $this->_currentCell++;
    	}
    	
    	if ( $element->hasAttribute( 'rowspan' ) ) {
    	    
    	    $rowSpan = (int)$element->getAttribute( 'rowspan' );
    	    
    	    if ( $rowSpan < 2 ) {
                return $cell;
    	    }
    	    
    	    $rowTarget = $this->_currentRow + ( $rowSpan - 1 );
    	    
    	    $this->_mainSheet->mergeCellsByColumnAndRow( $currentCell, $this->_currentRow, $currentCell, $rowTarget );
    	    
    	}
    	    
    	return $cell;
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
	    
	    $position = PHPExcel_Cell::stringFromColumnIndex( $this->_startCol ) . (  $this->_currentRow += 2 );
	    
	    $objDrawing = new PHPExcel_Worksheet_Drawing();
	    $objDrawing->setPath( $fileName )
			->setResizeProportional( true )
			->setCoordinates( $position )
			->setWorksheet( $this->_mainSheet );
	    
	    $this->_currentRow += ceil( $size[1] / 25 ) + 4;
	}
	
	return true;
    }
    
    /**
     * 
     */
    protected function _styleWorkSheet()
    {
	$highestColumn = $this->_mainSheet->getHighestColumn();
	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString( $highestColumn );

	for( $column = $this->_startCol; $column < $highestColumnIndex; $column++ ) {
	    $this->_mainSheet->getColumnDimension( PHPExcel_Cell::stringFromColumnIndex( $column ) )->setAutoSize( true );
	}
	
	$this->_mainSheet->getColumnDimension('B')->setWidth( 3 );
    }
}