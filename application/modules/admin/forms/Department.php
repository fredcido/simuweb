<?php

/**
 *
 * @author Frederico Estrela
 */
class Admin_Form_Department extends App_Form_Default
{
    const ID = 168;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_department' )->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Departamentu' )
			    ->setRequired( true );
	
	$mapperSysUser =  new Admin_Model_Mapper_SysUser();
	$users = $mapperSysUser->listAll();
	
	$optUsers[''] = '';
	foreach ( $users as $user )
	    $optUsers[$user['id_sysuser']] = $user['name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_sysuser' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Responsavel' )
			    ->addMultiOptions( $optUsers )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}