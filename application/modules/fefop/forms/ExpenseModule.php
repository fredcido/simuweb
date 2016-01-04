<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_ExpenseModule extends App_Form_Default
{
    const ID = 181;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();
	
	$mapperExpense = new Fefop_Model_Mapper_Expense();
	$itemsConfig = $mapperExpense->getItemsConfig();
	
	/*
	$dbFefopPrograms = App_Model_DbTable_Factory::get( 'FEFOPPrograms' );
	$programs = $dbFefopPrograms->fetchAll();
	
	$optPrograms[''] = '';
	foreach ( $programs as $program )
	    $optPrograms[$program['id_fefop_programs']] = $program['acronym'] . ' - '. $program['description'];
	
	$elements[] = $this->createElement( 'select', 'id_fefop_programs' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Programa FEFOP' )
			    ->addMultiOptions( $optPrograms )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_modules' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Modulu FEFOP' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );
	 */
	
	$items = array( '' => '' ) + $itemsConfig;
	
	$elements[] = $this->createElement( 'select', 'item_config' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Konfigurasaun' )
			    ->addMultiOptions( $items )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}