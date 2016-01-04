<?php

/**
 *
 * @author Frederico Estrela
 */
class Admin_Form_Audit extends App_Form_Default
{

    const ID = 102;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'text', 'start_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readonly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Data Iniciu' );
	
	$elements[] = $this->createElement( 'text', 'finish_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readonly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Data Fim' );
	
	$mapperSysUser =  new Admin_Model_Mapper_SysUser();
	$users = $mapperSysUser->listAll();
	
	$optUsers[''] = '';
	foreach ( $users as $user )
	    $optUsers[$user['id_sysuser']] = $user['name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_sysuser' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Usuariu' )
			    ->addMultiOptions( $optUsers )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Deskrisaun' );
	
	$dbModule = App_Model_DbTable_Factory::get( 'SysModule' );
	$modules = $dbModule->fetchAll( array(), array( 'module' ) );
	
	$optModule[''] = '';
	foreach ( $modules as $module )
	    $optModule[$module['id_sysmodule']] = $module['module'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_sysmodule' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Modulu' )
			    ->addMultiOptions( $optModule );
	
	$elements[] = $this->createElement( 'select', 'fk_id_sysform' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Formulariu' );
	
	$this->addElements( $elements );
	App_Form_Toolbar::build( $this, self::ID );
    }

}