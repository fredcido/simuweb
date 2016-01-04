<?php

class Default_Model_Mapper_Form extends App_Model_Abstract
{
    /**
     * 
     * @return string
     */
    public function getFormDir()
    {
	$path = APPLICATION_PATH . '/../public/forms/';
	if ( !is_dir( $path ) )
	    mkdir( $path );
	
	return $path;
    }
    
    /**
     * 
     * @return array
     */
    public function listFiles()
    {
	$folders = array();
	
	try {
	    
	    $dir = $this->getFormDir();
	  
	    $iterator = new DirectoryIterator( $dir );
	    foreach ( $iterator as $folder ) {
		
		if ( $folder->isDot() || substr( $folder->getFilename(), 0, 1 ) == '.' ) continue;
		
		$fileIterator = new FilesystemIterator( $folder->getPathname() );
		
		$files = array();
		foreach ( $fileIterator as $file ) {
		    
		    $type = App_Util_Mime::getMimeFile( strtolower( $file->getExtension() ) );
		    
		    $files[] = array(
			'path' => $this->publicFileUrl( $file->getPathname() ),
			'size' => App_Util_Readable::readBytes( App_Util_ByteSize::convert( $file->getSize(), 'B', 'M' ), 'M', true ),
			'type' => array_shift( $type ),
			'name' => $file->getBasename(),
			'date' => date( 'd/m/Y H:i', $file->getMTime() )
		    );
		}
		
		if ( !empty( $files ) )
		    $folders[$folder->getBasename()] = $files;
	    }
	    
	    return $folders;
	    
	} catch ( Exception $e ) {
	    return $folders;
	}
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
}