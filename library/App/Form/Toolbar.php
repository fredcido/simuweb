<?php

abstract class App_Form_Toolbar
{
    
    /**
     *
     * @var array
     */
    protected static $_decoratorGroup = array(
					    'FormElements',
					    array( array( 'wrapper' => 'HtmlTag' ), array( 'tag' => 'div', 'class' => 'form-actions' ) )
					);
    
    /**
     *
     * @var array
     */
    protected static $_decoratorButton = array( 'ViewHelper' );
    
    /**
     *
     * @var array
     */
    protected static $_buttons = array(
				    'save'	=> Admin_Model_Mapper_SysUserHasForm::SAVE,
				    'clear'	=> null
				 );
    
    /**
     *
     * @var array
     */
    protected static $_elements = array();
    
    /**
     *
     * @var string
     */
    protected static $_path;
    
    /**
     *
     * @var string
     */
    protected static $_action;
    
    /**
     *
     * @var Zend_Form
     */
    protected static $_form;
    
    /**
     *
     * @var int
     */
    protected static $_idForm;
    
    /**
     *
     * @var array
     */
    protected static $_permissions = array();
    
    /**
     *
     * @param Zend_Form $form 
     */
    public static function build( Zend_Form $form, $idForm, $customButtons = array() )
    {
	self::_resetInstance();
	
	self::$_idForm = $idForm;
	
	self::$_form = $form;
	
	// Set Current path
	self::_getCurrentPath();
	
	// Get Permissions
	self::_findPermissions();

	$buttons = self::$_buttons;
	
	// create each button
	foreach ( $buttons as $button => $id ) {
	 
	    $methodName = '_btn' . ucfirst( $button ) ;
	    
	    if ( method_exists( __CLASS__, $methodName ) )
		call_user_func_array ( array( __CLASS__, $methodName ), array( $id ) );
	}
	
	self::$_elements = array_merge( self::$_elements, $customButtons );
	
	// if there isnt elements
	if ( empty( self::$_elements ) )
	    return false;
	
	self::$_form->addDisplayGroup( self::$_elements, 'toolbar' );
	$displayGroup = self::$_form->getDisplayGroup( 'toolbar' );
	$displayGroup->setDecorators( self::$_decoratorGroup );   
    }
    
    /**
     * 
     */
    protected static function _resetInstance()
    {
	self::$_buttons = array(
				    'save'	=> Admin_Model_Mapper_SysUserHasForm::SAVE,
				    'clear'	=> null
				 );
	
	self::$_elements = array();
	self::$_path = null;
	self::$_action = null;
	self::$_form = null;
	self::$_idForm = null;
	self::$_permissions = array();
    }
    
    /**
     * 
     */
    protected static function _btnClear()
    {
	$button = self::$_form->createElement( 'reset', 'clear' )
			     ->setDecorators( self::$_decoratorButton )
			     ->setAttrib( 'class', 'btn' )
			     ->setLabel( 'Hamoos' );
	
	self::$_elements[] = $button;
    }
    
    /**
     *
     * @param int $access
     * @return boolean 
     */
    protected static function _btnSave( $access )
    {
	// If there isn't acess to this operation
	if ( !in_array( $access, self::$_permissions ) )
	    return false;
	
	$button = self::$_form->createElement( 'submit', 'save' )
			     ->setDecorators( self::$_decoratorButton )
			     ->setAttrib( 'class', 'btn blue' )
			     ->setLabel( 'Halot' );
	
	self::$_elements[] = $button;
    }
    
    /**
     * 
     */
    protected static function _getCurrentPath()
    {
	$front = Zend_Controller_Front::getInstance();
	$request = $front->getRequest();
	
	$module = ( $front->getDefaultModule() == $request->getModuleName() ? '' : '/' . $request->getModuleName() );
	$controller = $request->getControllerName();
	
	self::$_path = $module . '/' . $controller . '/';
	self::$_action = $request->getActionName();
    }
    
    /**
     * 
     */
    protected static function _findPermissions()
    {
	$config = Zend_Registry::get( 'config' );
	$session = new Zend_Session_Namespace( $config->general->appid );
	
	$permissions = $session->permissions;
	self::$_permissions = empty( $permissions[self::$_idForm] ) ? array() : $permissions[self::$_idForm];
    }
}