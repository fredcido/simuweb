<?php

/**
 * 
 */
class Default_StatisticsController extends Zend_Controller_Action
{

    /**
     *
     * @var Default_Model_Mapper_Statistics
     */
    protected $_mapper;
    
    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->view->title( 'Statistika Merkadu Traballu' );
	$this->_mapper = new Default_Model_Mapper_Statistics();
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