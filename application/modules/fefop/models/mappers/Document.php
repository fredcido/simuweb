<?php

class Fefop_Model_Mapper_Document extends App_Model_Abstract
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
    protected $_extensions = array( 'doc', 'docx', 'pdf', 'png', 'jpeg', 'jpg', 'gif', 'xls', 'xlsx', 'txt' );
    
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
	    
	    $dir = $this->getFefopDir();
	    
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
		$fileInfo['delete_url'] = '/fefop/document/delete/?file=' . $pathFile;
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
     * @throws Exception 
     */
    protected function getFefopDir()
    {
	if ( empty( $this->_data['contract'] ) )
	    throw new Exception( 'Tenki informa kliente' );
	
	$dir = APPLICATION_PATH . $this->_ds . '..' . $this->_ds . 'public' . $this->_ds . self::DIR . $this->_ds;
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	$dir .= 'fefop' . $this->_ds;
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$contract = $mapperContract->detail( $this->_data['contract'] );
	
	$dir .= strtolower( $contract->num_program ) . $this->_ds;
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	$dir .= strtolower( $contract->num_module ) . $this->_ds;
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	$dir .= md5( $this->_data['contract'] ) . $this->_ds;
	if ( !is_dir( $dir ) )
	    mkdir( $dir );
	
	return realpath( $dir ) . $this->_ds;
    }
    
    /**
     *
     * @return array 
     */
    public function listFiles()
    {
	$files = array();
	
	try {
	    
	    $dir = $this->getFefopDir();
	  
	    $iterator = new FilesystemIterator( $dir );
	    foreach ( $iterator as $file ) {

		$files[] = array(
		    'path' => $this->publicFileUrl( $file->getPathname() ),
		    'size' => App_Util_Readable::readBytes( App_Util_ByteSize::convert( $file->getSize(), 'B', 'M' ), 'M', true ),
		    'type' => array_shift( App_Util_Mime::getMimeFile( strtolower( $file->getExtension() ) ) ),
		    'name' => $file->getBasename(),
		    'date' => date( 'd/m/Y H:i', $file->getMTime() )
		);
	    }
	    
	    return $files;
	    
	} catch ( Exception $e ) {
	    return $files;
	}
    }
}