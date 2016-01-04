<?php

class Default_ErrorController extends Zend_Controller_Action
{

    /**
     * 
     * @return type
     */
    public function errorAction()
    {
	$this->_helper->layout()->disableLayout();
	
        $errors = $this->_getParam('error_handler');
	
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->_helper->viewRenderer->setRender( 'not-found' );
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->_helper->viewRenderer->setRender( 'error' );
                break;
        }
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
	
        $this->view->request   = $errors->request;
    }
    
    public function maintenanceAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    public function accessAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$config = Zend_Registry::get( 'config' );
	$session = new Zend_Session_Namespace( $config->general->appid );
	
	$this->view->route = $session->triedroute;
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

