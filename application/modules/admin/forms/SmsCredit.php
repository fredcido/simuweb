<?php

/**
 *
 * @author Frederico Estrela
 */
class Admin_Form_SmsCredit extends App_Form_Default
{
    const ID = 170;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	

	$mapperDepartment =  new Admin_Model_Mapper_Department();
	$departments = $mapperDepartment->fetchAll();
	
	$optDepartments[''] = '';
	foreach ( $departments as $user )
	    $optDepartments[$user['id_department']] = $user['name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_department' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Departamentu' )
			    ->addMultiOptions( $optDepartments )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'value' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span4 money-mask' )
			    ->setLabel( 'Folin Hira' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span4 text-numeric' )
			    ->setLabel( 'Hira' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}