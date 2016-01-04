<?php

/**
 * 
 */
class Default_FormController extends Zend_Controller_Action
{

    /**
     *
     * @var Default_Model_Mapper_Form
     */
    protected $_mapper;
    
    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->view->title( 'Formulariu' );
	$this->_mapper = new Default_Model_Mapper_Form();
    }

    /**
     * 
     * @access public
     * @return void
     */
    public function indexAction()
    {
	$this->view->files = $this->_mapper->listFiles();
    }
}