<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_SmsCredit extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$this->removeElement( 'fk_id_dec' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'sms/credit-report' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Pulsa Departamentu' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	
	$mapperDepartment = new Admin_Model_Mapper_Department();
	$rows = $mapperDepartment->fetchAll();
	
	$optDepartment[''] = '';
	foreach ( $rows as $row )
	    $optDepartment[$row->id_department] = $row->name;
	
	$elements[] = $this->createElement( 'select', 'fk_id_department' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Departamentu' )
			    ->addMultiOptions( $optDepartment );
	
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$rows = $dbUser->fetchAll( array(), array( 'name' ) );
	
	$users[''] = '';
	foreach ( $rows as $row )
	    $users[$row->id_sysuser] = $row->name . ' (' . $row->login . ')';
	
	$elements[] = $this->createElement( 'select', 'fk_id_sysuser' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Uzuariu mak halo' )
			    ->addMultiOptions( $users );
	
	$this->addElements( $elements );
    }
}