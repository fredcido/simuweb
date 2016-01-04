<?php

$cacheDir = APPLICATION_PATH . '/../public/cache/';

set_time_limit( 100 );

#########################################
## Compressor option file ##############
#########################################
## Access control
$compress_options['username'] = "";
$compress_options['password'] = "";
## Path info
$compress_options['document_root'] = realpath( __DIR__ . '../../../../' );
$compress_options['javascript_cachedir'] =  $cacheDir . "js";
$compress_options['css_cachedir'] = $cacheDir . "css";
## Comma separated list of JS Libraries to include
$compress_options['js_libraries'] = "";
## Ignore list
$compress_options['ignore_list'] = "";
## Minify options
$compress_options['minify']['javascript'] = "1";
$compress_options['minify']['page'] = "1";
$compress_options['minify']['css'] = "1";
## Gzip options
$compress_options['gzip']['javascript'] = "0";
$compress_options['gzip']['page'] = "0";
$compress_options['gzip']['css'] = "0";
## Versioning
$compress_options['far_future_expires']['javascript'] = "1";
$compress_options['far_future_expires']['css'] = "1";
## On or off 
$compress_options['active'] = "1";
## Display a link back to PHP Speedy
$compress_options['footer']['text'] = "0";
$compress_options['footer']['image'] = "0";
## Should Speedy Clean Up the cache directory?
$compress_options['cleanup']['on'] = "0";
## Should Speedy use data URIs for background images?
$compress_options['data_uris']['on'] = "1";
#########################################
?>