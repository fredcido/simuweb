<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_ContractSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'minimum_amount' )->setValue( 0 )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'maximum_amount' )->setValue( 200000 )->setDecorators( array( 'ViewHelper' ) );
	
	$mapperStatus = new Fefop_Model_Mapper_Status();
	$rows = $mapperStatus->getStatuses();
	
	$optStatuses[''] = '';
	foreach ( $rows as $row )
	    $optStatuses[$row['id_fefop_status']] = $row['status_description'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_status' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Status' )
			    ->addMultiOptions( $optStatuses );
	
	$dbFefopProgram = App_Model_DbTable_Factory::get( 'FEFOPPrograms' );
	$programs = $dbFefopProgram->fetchAll();
	
	$optPrograms[''] = '';
	foreach ( $programs as $program )
	    $optPrograms[$program['id_fefop_programs']] = $program['acronym'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_programs' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Programa' )
			    ->addMultiOptions( $optPrograms );
	
	$dbFefopModule = App_Model_DbTable_Factory::get( 'FEFOPModules' );
	$modules = $dbFefopModule->fetchAll();
	
	$optModules[''] = '';
	foreach ( $modules as $module )
	    $optModules[$module['id_fefop_modules']] = $module['acronym'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_modules' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Modules' )
			    ->addMultiOptions( $optModules );
	
	// List Districts just from Timor
	$mapperDistrict = new Register_Model_Mapper_AddDistrict();
	$districts = $mapperDistrict->listAll( 1 );
	
	$optDistrict[''] = '';
	foreach ( $districts as $district )
	    $optDistrict[$district->acronym] = $district->acronym;
	
	$elements[] = $this->createElement( 'select', 'num_district' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Distritu' )
			    ->addMultiOptions( $optDistrict )
			    ->setRegisterInArrayValidator( false );
	
	
	$elements[] = $this->createElement( 'text', 'num_year' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 2 )
			    ->setLabel( 'Tinan' )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' );
	
	$elements[] = $this->createElement( 'text', 'num_sequence' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 4 )
			    ->setLabel( 'Sequence' )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$this->addElements( $elements );
    }
}