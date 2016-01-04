<?php

/**
 *
 * @author Frederico Estrela
 */
class Admin_Form_UserForm extends App_Form_Default
{

    const ID = 24;
    
    public function init()
    {
	$this->setAttrib( 'class', 'form-horizontal' );
	
	$elements = array();	
	
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
	
	$elements[] = $this->createElement( 'select', 'user_source' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Kopia husi' );

	$this->addElements( $elements );
	
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}