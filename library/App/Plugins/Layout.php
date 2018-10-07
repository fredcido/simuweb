<?php

/**
 * 
 */
class App_Plugins_Layout extends Zend_Controller_Plugin_Abstract
{

    /**
     * 
     * @var Zend_Controller_Plugin_Abstract
     */
    protected $_request;
    
    /**
     *
     * @var array
     */
    protected $_modulesName = array(
        'admin'         => 'Administrativu',
        'client'        => 'Jestaun Kazu',
        'job'           => 'Empregu',
        'register'      => 'Rejistu Jeral',
        'student-class' => 'Formasaun Profisional',
        'report'	=> 'Relatoriu',
        'sms'		=> 'SMS'
    );

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::dispatchLoopStartup()
     */
    public function dispatchLoopStartup( Zend_Controller_Request_Abstract $request )
    {
	$this->_request = $request;
	
	$this->_configView();

	$this->_includeJsController();
	$this->_includeCssController();
	$this->_setBreadCrumb();
    }


    /**
     * @access protected
     * @return void
     */
    protected function _configView()
    {
	$view = Zend_Controller_Front::getInstance()->getParam( 'bootstrap' )->getResource( 'view' );

	$config = Zend_Registry::get( 'config' );

	$view->headTitle( $config->general->title );

	$view->headLink()->headLink( array('href' => $view->baseUrl( '/favicon.ico' ), 'rel' => 'SHORTCUT ICON') );
	
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/bootstrap/css/bootstrap.min.css' ), '' );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/bootstrap/css/bootstrap-responsive.min.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/font-awesome/css/font-awesome.min.css' ), '' );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/css/style-metro.css' ), '' );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/css/style.css' ), '' );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/css/style-responsive.css' ), '' );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/css/themes/default.css' ), '' );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/uniform/css/uniform.default.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/select2/select2_metro.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/chosen-bootstrap/chosen/chosen.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/data-tables/DT_bootstrap.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/bootstrap-datepicker/css/datepicker.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/bootstrap-datetimepicker/css/datetimepicker.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/bootstrap-timepicker/compiled/timepicker.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/bootstrap-daterangepicker/daterangepicker.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/bootstrap-toggle-buttons/static/stylesheets/bootstrap-toggle-buttons.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/jquery-ui/jquery-ui-1.10.1.custom.min.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/jquery-multi-select/css/multi-select-metro.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/glyphicons/css/glyphicons.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/template/plugins/glyphicons_halflings/css/halflings.css' ) );
	$view->headLink()->appendStylesheet( $view->baseUrl( 'public/styles/app.css' ), '' );
	
	/*
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery-1.10.1.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery-migrate-1.2.1.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery-ui/jquery-ui-1.10.1.custom.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery-validation/dist/jquery.validate.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/bootstrap/js/bootstrap.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery.cookie.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery.blockui.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/uniform/jquery.uniform.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/select2/select2.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/bootstrap-daterangepicker/date.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/bootstrap-daterangepicker/daterangepicker.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/bootstrap-toggle-buttons/static/js/jquery.toggle.buttons.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/data-tables/jquery.dataTables.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/data-tables/DT_bootstrap.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery-multi-select/js/jquery.multi-select.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery.pulsate.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/jquery-slimscroll/jquery.slimscroll.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/plugins/ckeditor/ckeditor.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/template/scripts/app.js' ) );
	
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/plugins/highcharts/highcharts.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/plugins/highcharts/modules/exporting.js' ) );
	
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/plugins/jquery.populate.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/plugins/jquery.maxlength.min.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/plugins/jquery.maskmoney.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/plugins/jquery.dataTables.dateSorting.js' ) );
	
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/app/general.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/app/message.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/app/form.js' ) );
	$view->headScript()->appendFile( $view->baseUrl( 'public/scripts/app/portlet.js' ) );
	*/
	
	$view->config = Zend_Registry::get( 'config' );
    }

    /**
     * 
     * @access protected
     * @return void
     */
    protected function _includeJsController()
    {
	$ds = '/';

	$file = 'public' . $ds . 'scripts' .$ds . 'controller' . $ds . $this->_request->getModuleName() . $ds  .   $this->_request->getControllerName() . '.js';
	
	if ( file_exists( APPLICATION_PATH . $ds . '..' . $ds . $file ) ) {
	    $view = Zend_Controller_Front::getInstance()->getParam( 'bootstrap' )->getResource( 'view' );
	    $view->headScript()->appendFile( $view->baseUrl( $file ) );
	}
    }

    /**
     * 
     * @access protected
     * @return void
     */
    protected function _includeCssController()
    {
	$ds = '/';

	$file = 'public' . $ds . 'styles' .$ds . 'controller' . $ds . $this->_request->getModuleName() . $ds  .   $this->_request->getControllerName() . '.css';
	
	if ( file_exists( APPLICATION_PATH . $ds . '..' . $ds . $file ) ) {
	    $view = Zend_Controller_Front::getInstance()->getParam( 'bootstrap' )->getResource( 'view' );
	    $view->headLink()->appendStylesheet( $view->baseUrl( $file ) );
	}
    }
    
    /**
     * 
     */
    protected function _setBreadCrumb()
    {
	$currentModule = $this->_request->getModuleName();
	if ( 'default' == $currentModule )
	    return false;
	
	if ( empty( $this->_modulesName[$currentModule] ) )
	    return false;
	
	$view = Zend_Controller_Front::getInstance()->getParam( 'bootstrap' )->getResource( 'view' );
	
	$view->breadcrumb()->addStep(
	    array(
		'label' => $this->_modulesName[$currentModule],
		'url'	=> $currentModule
	    )
	);
	
	if ( $currentModule == 'report' ) {
	    
	    $view = Zend_Controller_Front::getInstance()->getParam( 'bootstrap' )->getResource( 'view' );
	    $view->headScript()->appendFile( $view->baseUrl( 'public/scripts/app/report.js' ) );
	}
    }

}