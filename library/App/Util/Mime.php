<?php

class App_Util_Mime
{
    /* Atributos */

    /**
     * Guarda os mime-types em um array, sendo que a extencao do 
     * arquivo e o indice e o mime e o valor do array 
     * 
     * @access 	private
     * @var 	array
     */
    private static $mime = array(
	// Aplicacao
	'ai' => array('application/postscript'),
	'bcpio' => array('application/x-bcpio'),
	'bin' => array('application/octet-stream'),
	'ccad' => array('application/clariscad'),
	'cdf' => array('application/x-netcdf'),
	'class' => array('application/java'),
	'cpio' => array('application/x-cpio'),
	'cpt' => array('application/mac-compactpro'),
	'csh' => array('application/x-csh'),
	'dcr' => array('application/x-director'),
	'dir' => array('application/x-director'),
	'doc' => array('application/msword'),
	'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
	'drw' => array('application/drafting'),
	'dvi' => array('application/x-dvi'),
	'dwg' => array('application/acad'),
	'dxf' => array('application/dxf'),
	'dxr' => array('application/x-director'),
	'eps' => array('application/postscript'),
	'exe' => array('application/octet-stream'),
	'ez' => array('application/andrew-inset'),
	'gtar' => array('application/x-gtar'),
	'gz' => array('application/x-gzip'),
	'hdf' => array('application/x-hdf'),
	'hqx' => array('application/mac-binhex40'),
	'ips' => array('application/x-ipscript'),
	'ipx' => array('application/x-ipix'),
	'js' => array('application/x-javascript'),
	'latex' => array('application/x-latex'),
	'lha' => array('application/lha'),
	'lsp' => array('application/x-lisp'),
	'lzh' => array('application/octet-stream'),
	'man' => array('application/x-troff-man'),
	'me' => array('application/x-troff-me'),
	'mif' => array('application/vnd.mif'),
	'ms' => array('application/x-troff-ms'),
	'nc' => array('application/x-netcdf'),
	'oda' => array('application/oda'),
	'pdf' => array('application/pdf'),
	'pgn' => array('application/x-chess-pgn'),
	'php' => array('application/x-php'),
	'pot' => array('application/mspowerpoint'),
	'pps' => array('application/mspowerpoint'),
	'ppt' => array('application/mspowerpoint'),
	'ppz' => array('application/mspowerpoint'),
	'pre' => array('application/x-freelance'),
	'prt' => array('application/pro_eng'),
	'ps' => array('application/postscript'),
	'roff' => array('application/x-troff'),
	'scm' => array('application/x-lotusscreencam'),
	'set' => array('application/set'),
	'sh' => array('application/x-sh'),
	'shar' => array('application/x-shar'),
	'sit' => array('application/x-stuffit'),
	'skd' => array('application/x-koan'),
	'skm' => array('application/x-koan'),
	'skp' => array('application/x-koan'),
	'skt' => array('application/x-koan'),
	'smi' => array('application/smil'),
	'smil' => array('application/smil'),
	'sol' => array('application/solids'),
	'spl' => array('application/x-futuresplash'),
	'src' => array('application/x-wais-source'),
	'step' => array('application/STEP'),
	'stl' => array('application/SLA'),
	'stp' => array('application/STEP'),
	'sv4cpio' => array('application/x-sv4cpio'),
	'sv4crc' => array('application/x-sv4crc'),
	'swf' => array('application/x-shockwave-flash'),
	't' => array('application/x-troff'),
	'tar' => array('application/x-tar'),
	'tcl' => array('application/x-tcl'),
	'tex' => array('application/x-tex'),
	'texi' => array('application/x-texinfo'),
	'texinfo' => array('application/x-texinfo'),
	'tr' => array('application/x-troff'),
	'tsp' => array('application/dsptype'),
	'unv' => array('application/i-deas'),
	'ustar' => array('application/x-ustar'),
	'vcd' => array('application/x-cdlink'),
	'vda' => array('application/vda'),
	'xlc' => array('application/vnd.ms-excel'),
	'xll' => array('application/vnd.ms-excel'),
	'xlm' => array('application/vnd.ms-excel'),
	'xls' => array('application/vnd.ms-excel'),
	'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
	'xlw' => array('application/vnd.ms-excel'),
	'zip' => array('application/x-zip-compressed'),
	'rar' => array('application/x-forcedownload'),
	// Audio
	'aif' => array('audio/x-aiff'),
	'aifc' => array('audio/x-aiff'),
	'aiff' => array('audio/x-aiff'),
	'au' => array('audio/basic'),
	'kar' => array('audio/midi'),
	'mid' => array('audio/midi'),
	'midi' => array('audio/midi'),
	'mp2' => array('audio/mpeg'),
	'mp3' => array('audio/mpeg'),
	'mpga' => array('audio/mpeg'),
	'ra' => array('audio/x-realaudio'),
	'ram' => array('audio/x-pn-realaudio'),
	'rm' => array('audio/x-pn-realaudio'),
	'rpm' => array('audio/x-pn-realaudio-plugin'),
	'snd' => array('audio/basic'),
	'tsi' => array('audio/TSP-audio'),
	'wav' => array('audio/x-wav'),
	// Texto
	'asc' => array('text/plain'),
	'c' => array('text/plain'),
	'cc' => array('text/plain'),
	'css' => array('text/css'),
	'etx' => array('text/x-setext'),
	'f' => array('text/plain'),
	'f90' => array('text/plain'),
	'h' => array('text/plain'),
	'hh' => array('text/plain'),
	'htm' => array('text/html'),
	'html' => array('text/html'),
	'm' => array('text/plain'),
	'rtf' => array('text/rtf'),
	'rtx' => array('text/richtext'),
	'sgm' => array('text/sgml'),
	'sgml' => array('text/sgml'),
	'tsv' => array('text/tab-separated-values'),
	'tpl' => array('text/template'),
	'txt' => array('text/plain'),
	'xml' => array('text/xml'),
	// Video
	'avi' => array('video/x-msvideo'),
	'fli' => array('video/x-fli'),
	'mov' => array('video/quicktime'),
	'movie' => array('video/x-sgi-movie'),
	'mpe' => array('video/mpeg'),
	'mpeg' => array('video/mpeg'),
	'mpg' => array('video/mpeg'),
	'qt' => array('video/quicktime'),
	'viv' => array('video/vnd.vivo'),
	'vivo' => array('video/vnd.vivo'),
	'wmv' => array('video/x-msvideo'),
	// Imagem
	'bmp' => array('image/x-MS-bmp'),
	'ico' => array('image/ico'),
	'gif' => array('image/gif'),
	'ief' => array('image/ief'),
	'jpe' => array('image/jpeg'),
	'jpeg' => array('image/jpeg'),
	'jpg' => array('image/jpeg', 'image/pjpeg'),
	'pbm' => array('image/x-portable-bitmap'),
	'pgm' => array('image/x-portable-graymap'),
	'png' => array('image/png'),
	'pnm' => array('image/x-portable-anymap'),
	'ppm' => array('image/x-portable-pixmap'),
	'ras' => array('image/cmu-raster'),
	'rgb' => array('image/x-rgb'),
	'tif' => array('image/tiff'),
	'tiff' => array('image/tiff'),
	'xbm' => array('image/x-xbitmap'),
	'xpm' => array('image/x-xpixmap'),
	'xwd' => array('image/x-xwindowdump'),
	// Conferencia
	'ice' => array('x-conference/x-cooltalk'),
	// Modelo
	'iges' => array('model/iges'),
	'igs' => array('model/iges'),
	'mesh' => array('model/mesh'),
	'msh' => array('model/mesh'),
	'silo' => array('model/mesh'),
	'vrml' => array('model/vrml'),
	'wrl' => array('model/vrml'),
	// WWW
	'mime' => array('www/mime'),
	// Quimica
	'pdb' => array('chemical/x-pdb'),
	'xyz' => array('chemical/x-pdb')
    );

    /* Metodos */

    /**
     * Construtor
     * 
     * @access public
     */
    public function __construct()
    {
	
    }

    /**
     * Destrutor
     * 
     * @access public
     */
    public function __destruct()
    {
	
    }

    /* Getters e Setters */

    /**
     * Retorna lista de mime-types
     * 
     * @access public
     * @return array 
     */
    public static function getMime()
    {
	return self::$mime;
    }

    public static function getMimeFile( $ext )
    {
	if ( empty( self::$mime[$ext] ) )
	    $mime = array('octet-stream');
	else
	    $mime = self::$mime[$ext];

	return $mime;
    }

}
