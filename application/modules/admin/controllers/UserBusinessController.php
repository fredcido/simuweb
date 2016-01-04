<?php

/**
 * 
 */
class Admin_UserBusinessController extends App_Controller_Default
{
    
    /**
     *
     * @var Admin_Model_Mapper_UserBusiness
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Admin_Model_Mapper_UserBusiness();
	
	$stepBreadCrumb = array(
	    'label' => 'Usuariu ba Planu Negosiu',
	    'url'   => 'admin/user-business'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Usuariu ba Planu Negosiu' );
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

            $this->_form = new Admin_Form_UserBusiness();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listAll();
    }
    
    /**
     * 
     */
    public function loadUsersAction()
    {
	$mapperSysUser =  new Admin_Model_Mapper_SysUser();
	$users = $mapperSysUser->listAll( $this->_getParam( 'id' ) );

	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $users as $user )
	    $opts[] = array( 'id' => $user->id_sysuser, 'name' => $user->name );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function searchUserCeopAction()
    {
	$userCeop = $this->_mapper->searchUserCeop( $this->_getParam( 'id' ) );
	
	$return = array();
	if ( !empty( $userCeop ) )
	    $return = array( 'id' => $userCeop->id_sysuser );
	
	$this->_helper->json( $return );
    }
}