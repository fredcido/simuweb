<?php

/**
 * 
 */
class Admin_AccessController extends App_Controller_Default
{
    
    /**
     *
     * @var Admin_Model_Mapper_SysUserHasForm
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Admin_Model_Mapper_SysUserHasForm();
	
	$stepBreadCrumb = array(
	    'label' => 'Permissaum ba Usuariu',
	    'url'   => 'admin/access'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Asesu' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$this->view->form = $this->_getForm( $this->_helper->url( 'save' ) );
	$this->view->data = $this->_mapper->listGroupedOperations();
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_FormOperation
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Admin_Form_UserForm();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function seekPermissionsAction()
    {
	$id = $this->_getParam( 'id' );
	
	$dbSysUserHasForm = App_Model_DbTable_Factory::get( 'SysUserHasSysForm' );
	$rows = $dbSysUserHasForm->fetchAll( array( 'fk_id_sysuser = ?' => $id ) );
	
	$permissions = array();
	foreach ( $rows as $row ) {
	    
	    $permissions[] = array(
		'idForm'    => $row->fk_id_sysform,
		'idOper'    => $row->fk_id_sysoperation,
	    );
	}
	
	$this->_helper->json( $permissions );
    }
    
    /**
     * 
     */
    public function saveAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->save();
	$json['valid'] = $return;
	
	$this->_helper->json( $json );
    }
    
    /**
     * 
     */
    public function copyPermissionsAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->copyPermissions();
	$json['valid'] = $return;
	
	$this->_helper->json( $json );
    }
    
    /**
     * 
     */
    public function seekSourceUserAction()
    {
	$mapperSysUser =  new Admin_Model_Mapper_SysUser();
	$users = $mapperSysUser->listAll();
	
	$id = $this->_getParam( 'id' );
	
	$opt = array( array( 'id' => '', 'name' => '' ) );
	foreach ( $users as $user ) {
	    if ( $id == $user->id_sysuser )
		continue;
	    
	    $opt[] = array( 'id' => $user->id_sysuser, 'name' => $user->name );
	}
	
	$this->_helper->json( $opt );
    }
}