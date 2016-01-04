<?php

/**
 * 
 */
class Fefop_FollowupController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_Followup
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_helper->layout()->disableLayout();
	$this->_mapper = new Fefop_Model_Mapper_Followup();
    }

    /**
     * 
     */
    public function indexAction()
    {
	$this->view->id = $this->_getParam( 'id' );
    }
    
    /**
     * 
     */
    public function informationAction()
    {
	// Form
	$form = $this->_getForm( $this->_helper->url( 'save' ) );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) )
	    $form->getElement( 'fk_id_fefop_contract' )->setValue( $id );
	else
	    $this->_helper->redirector->goToSimple( 'index', 'fefop' );

	$this->view->form = $form;
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_Followup();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
    }
    
    /**
     * 
     */
    public function listFollowupAction()
    {
	$this->view->rows = $this->_mapper->listFollowups( $this->_getParam( 'id' ) );
    }
}