<?php

/**
 *
 * @author Frederico Estrela
 */
class Admin_Form_Form extends App_Form_Default
{

    const ID = 4;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_sysform' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$dbModule = App_Model_DbTable_Factory::get( 'SysModule' );
	$modules = $dbModule->fetchAll( array(), array( 'module' ) );
	
	$optModule[''] = '';
	foreach ( $modules as $module )
	    $optModule[$module['id_sysmodule']] = $module['module'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_sysmodule' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Modulu' )
			    ->addMultiOptions( $optModule )
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'form' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Formulário' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12 focused' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 5 )
			    ->setLabel( 'Deskrisaun' );
	
	$dbOperations = App_Model_DbTable_Factory::get( 'SysOperations' );
	$operations = $dbOperations->fetchAll();
	
	$optOperations = array();
	foreach ( $operations as $operation )
	    $optOperations[$operation->id_sysoperation] = $operation->operation;
	
	$elements[] = $this->createElement( 'multiCheckbox', 'operations' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addMultiOptions( $optOperations )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setSeparator( '' )
			    ->setLabel( 'Operações' );
	
	$optRadio['1'] = 'Sim';
	$optRadio['0'] = 'Lai';
	
	$elements[] = $this->createElement( 'radio', 'active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Ativu' )
			    ->addMultiOptions( $optRadio )
			    ->setAttrib( 'label_class', 'radio' )
			    ->setSeparator( '' )
			    ->setValue( 1 )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	App_Form_Toolbar::build( $this, self::ID );
    }

}