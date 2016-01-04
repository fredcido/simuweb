<?php

/**
 * 
 */
class Fefop_RuleController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_Rule
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_Rule();
	
	$stepBreadCrumb = array(
	    'label' => 'Konfigurasaun Regra',
	    'url'   => 'fefop/rule'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Konfigurasaun Regra' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$this->view->form = $this->_getForm( $this->_helper->url( 'save' ) );
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_Rule();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function addRuleAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$options = $this->_mapper->getOptionsRules();
	$options = array( '' => '' ) + $options;
	
	$this->view->row = $this->_getParam( 'row' );
	$this->view->optionsRules = $options; 
    }
    
    /**
     * 
     */
    public function loadRulesAction()
    {
	$id = $this->_getParam( 'id' );
	
	$this->_helper->layout()->disableLayout();
	$this->view->rules = $this->_mapper->listRules( $id );
    }
    
    /**
     * 
     */
    public function printAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$mapperExpense = new Fefop_Model_Mapper_Expense();
	$itemsConfig = $mapperExpense->getItemsConfig();
	
	$this->view->identifier = $itemsConfig[$id];
	$this->view->rules = $this->_mapper->listRules( $id );
	$this->view->rulesOptions = $this->_mapper->getOptionsRules();
	$this->view->user = Zend_Auth::getInstance()->getIdentity();
    }
}