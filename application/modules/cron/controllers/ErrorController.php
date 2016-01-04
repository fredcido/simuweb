<?php

class Cron_ErrorController extends Zend_Controller_Action
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$this->_helper->viewRenderer->setNoRender( true );
	$this->_helper->layout()->disableLayout();
    }
    
    public function errorAction()
    {
	$errors = $this->_getParam('error_handler');
	Zend_Debug::dump($errors);
	exit;
    }
}