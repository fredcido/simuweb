<?php

/**
 *
 * @author Frederico Estrela
 */
class Default_Form_Note extends App_Form_Default
{
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	$elements[] = $this->createElement( 'hidden', 'fk_id_sysuser' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'text', 'title' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Titulu' )
			    ->setRequired( true );
	
	$optLevel[''] = '';
	$optLevel[0] = 'Urgente';
	$optLevel[1] = 'Normal';
	
	$elements[] = $this->createElement( 'select', 'level' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Nivel' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRequired( true )
			    ->addMultiOptions( $optLevel );
	
	$elements[] = $this->createElement( 'text', 'date_scheduled' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Ajenda' );
	
	$dbSysUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$users = $dbSysUser->fetchAll( array( 'active = ?' => 1 ), array( 'name' ) );
	
	$optUsers[''] = '';
	foreach ( $users as $user )
	    $optUsers[$user['id_sysuser']] = $user['name'];
	
	$elements[] = $this->createElement( 'multiselect', 'users' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Uzuariu' )
			    ->addMultiOptions( $optUsers );
	
	$dbUserGroup = App_Model_DbTable_Factory::get( 'UserGroup' );
	$groups = $dbUserGroup->fetchAll( array(), array( 'name' ) );
	
	$optGroups[''] = '';
	foreach ( $groups as $group )
	    $optGroups[$group['id_usergroup']] = $group['name'];
	
	$elements[] = $this->createElement( 'multiselect', 'groups' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Grupu' )
			    ->addMultiOptions( $optGroups );
	
	$elements[] = $this->createElement( 'checkbox', 'status' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 1 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Ativu?' );
	
	$elements[] = $this->createElement( 'textarea', 'message' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'rows', 3 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap ckeditor  span12' );
	
	$this->addElements( $elements );
    }

}