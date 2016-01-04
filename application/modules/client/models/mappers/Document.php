<?php

class Client_Model_Mapper_Document extends App_Model_Abstract
{
    const DIR = 'documents';
    
    /**
     *
     * @var string
     */
    protected $_ds = '/';
    
    /**
     *
     * @var array
     */
    protected $_extensions = array( 'doc', 'docx', 'pdf', 'png', 'jpeg', 'jpg', 'gif', 'xls', 'xlsx' );
    
    /**
     *
     * @var int
     */
    protected $_maxSize = 10485760;
    
    /**
     *
     * @return array 
     */
    public function uploadFiles()
    {
	$return = array( 'files' => array() );
	
	try {
	    
	    $dir = $this->getDirDocs();
	    
	    $adapter = new Zend_File_Transfer_Adapter_Http();
	    $adapter->setDestination( $dir );
	    
	    $typeValidator = new Zend_Validate_File_Extension( $this->_extensions );
	    $sizeFile = new Zend_Validate_File_Size( $this->_maxSize );
	    
	    $adapter->addValidator( $typeValidator, true )->addValidator( $sizeFile, true );
	    
	    $files = $adapter->getFileInfo();
	    foreach ( $files as $file => $info ) {

		if ( !$adapter->isUploaded( $file ) ) continue; 

		$name = $this->_getNewFileName( $dir, $info['name'] );

		$fileInfo = array(
		    'size' => $info['size'],
		    'name' => $name
		);

		if ( !$adapter->isValid( $file ) ) {

		    $messages = $adapter->getMessages();
		    
		    $fileInfo['error'] = array_shift( $messages );
		    $return['files'][] = $fileInfo;
		    continue;
		}
		
		$adapter->addFilter( 'Rename', $dir . $name, $file );
		$adapter->receive( $file );
		
		$pathFile = $this->publicFileUrl( $dir . $name );
		
		$fileInfo['url'] = $pathFile;
		$fileInfo['delete_url'] = '/client/document/delete/?file=' . $pathFile;
		$fileInfo['delete_type'] = 'DELETE';
		
		$return['files'][] = $fileInfo;
	    }
	    
	    return $return;
	    
	} catch ( Exception $e ) {
	    
	    return $return;
	}
    }
    
    /**
     *
     * @param string $path
     * @param string $name
     * @return string
     */
    protected function _getNewFileName( $path, $name )
    {
	$fileName = $path . $name;
	$info = pathinfo( $fileName );
	
	$friendName = ucfirst( App_General_String::friendName( $info['filename'] ) );
	
	$name = $friendName;
	$fullPath = $path . $friendName . '.' . $info['extension'];
	$count = 0;
	while( file_exists( $fullPath ) ) {
	    
	    $friendName = $name . '_' . ( ++$count );
	    $fullPath = $path . $friendName . '.' . $info['extension'];
	}
	
	return $friendName . '.' . $info['extension'];
    }
    
    /**
     *
     * @param string $fileName
     * @return string
     */
    protected function publicFileUrl( $fileName )
    {
	if ( preg_match( '/public(.+?)$/i', $fileName, $match ) )
	    $fileName = str_replace( '\\', '/', $match[0] );
	
	return $fileName;
    }
    
    /**
     * 
     */
    public function deleteFile()
    {
	$file = $this->publicFileUrl( $this->_data['file'] );
	$file = APPLICATION_PATH . '/../' . $file;
	unlink( $file );
    }
    
    /**
     *
     * @return string 
     */
    protected function getDirDocs()
    {
	$clientDir = $this->getClientDir();
	
	if ( !empty( $this->_data['case'] ) ) {
	    
	    $clientDir .= $this->_ds . 'cases' . $this->_ds;
	    if ( !is_dir( $clientDir ) )
		mkdir( $clientDir );
	    
	    $mapperCase = new Client_Model_Mapper_Case();
	    $case = $mapperCase->fetchRow( $this->_data['case'] );
	    
	    $clientDir .= md5( $case->fk_id_dec ) . $this->_ds;
	    if ( !is_dir( $clientDir ) )
		mkdir( $clientDir );
	    
	    $clientDir .= md5( $this->_data['case'] ) . $this->_ds;
	    if ( !is_dir( $clientDir ) )
		mkdir( $clientDir );
	    
	} else {
	    
	    $clientDir .= $this->_ds . 'files' . $this->_ds;
	    if ( !is_dir( $clientDir ) )
		mkdir( $clientDir );
	}
	
	return $clientDir;
    }
    
    /**
     *
     * @return string
     * @throws Exception 
     */
    protected function getClientDir()
    {
	if ( empty( $this->_data['client'] ) )
	    throw new Exception( 'Tenki informa kliente' );
	
	$dir = APPLICATION_PATH . $this->_ds . '..' . $this->_ds . 'public' . $this->_ds . self::DIR . $this->_ds;
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	$dir .= 'clients' . $this->_ds;
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	$dir .= md5( $this->_data['client'] );
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	return realpath( $dir );
    }
    
    /**
     *
     * @return array 
     */
    public function listFiles()
    {
	$files = array(
	    'files' => array(),
	    'cases' => array()
	);
	
	try {
	    
	    $dir = $this->getClientDir();
	    
	    $files['files'] = $this->_listClientsFiles( $dir );
	    $files['cases'] = $this->_listCasesFiles( $dir );
	    
	    return $files;
	    
	} catch ( Exception $e ) {
	    
	    return $files;
	}
    }
    
    /**
     *
     * @param string $dir
     * @return array
     */
    protected function _listClientsFiles( $dir )
    {
	$dir .= $this->_ds . 'files';
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	$files = array();
	$iterator = new FilesystemIterator( $dir );
	
	foreach ( $iterator as $file ) {
	    
	    $files[] = array(
		'path' => $this->publicFileUrl( $file->getPathname() ),
		'name' => $file->getBasename()
	    );
	}
	
	return $files;
    }
    
    /**
     *
     * @return array
     */
    protected function _listCeops()
    {
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll();
	
	$ceops = array();
	foreach ( $rows as $row )
	    $ceops[md5( $row->id_dec)] = $row->name_dec;
	
	return $ceops;
    }
    
    /**
     *
     * @return array 
     */
    protected function _casesClient()
    {
	$mapperCase = new Client_Model_Mapper_Case();
	$rows = $mapperCase->casesByClient( $this->_data['client'] );
	
	$cases = array();
	foreach ( $rows as $row )
	    $cases[md5($row->id_action_plan)] = $row->ocupation_name_timor . ' - ' . $row->name;
	
	return $cases;
    }
    
    /**
     *
     * @param string $dir
     * @return array 
     */
    protected function _listCasesFiles( $dir )
    {
	$dir .= $this->_ds . 'cases';
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	$ceops = $this->_listCeops();
	$casesClient = $this->_casesClient();
	
	$userCeop = md5( Zend_Auth::getInstance()->getIdentity()->fk_id_dec );
	
	$cases = array();
	$ceopIterator = new DirectoryIterator( $dir );
	
	foreach ( $ceopIterator as $ceop ) {
	    
	    if ( !$ceop->isDir() || $ceop->isDot() )
		continue;
	    
	    $caseIterator = new DirectoryIterator( $ceop->getPathname() );
	    $currentCeop = $ceop->getFilename();
	    
	    $ceop = array(
		'name'  => $ceops[$currentCeop],
		'cases' => array()
	    );
	    
	    foreach ( $caseIterator as $caseItem ) {
		
		if ( !$caseItem->isDir() || $caseItem->isDot() )
		    continue;
		
		$currentCase = $caseItem->getFilename();
		
		$case = array(
		    'name'  => $casesClient[$currentCase],
		    'files' => array()
		);
		
		$fileIterator = new FilesystemIterator( $caseItem->getPathname(), FilesystemIterator::SKIP_DOTS );
		
		foreach ( $fileIterator as $file ) {
		
		    $case['files'][] = array(
			'path'	    => $this->publicFileUrl( $file->getPathname() ),
			'name'	    => $file->getBasename(),
			'editable'  => $userCeop == $currentCeop
		    );
		}
		
		$ceop['cases'][] = $case;
	    }
	    
	    $cases[] = $ceop;
	}
	
	return $cases;
    }
}