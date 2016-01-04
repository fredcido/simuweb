<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_Rule extends App_Form_Default
{
    const ID = 198;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'rule' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'value' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'message' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'required' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'time_unit' )->setIsArray( true );
	
	$mapperExpense = new Fefop_Model_Mapper_Expense();
	$itemsConfig = $mapperExpense->getItemsConfig();
	
	$items = array( '' => '' ) + $itemsConfig;
	
	$elements[] = $this->createElement( 'select', 'identifier' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Hili Item ba Konfigurasaun' )
			    ->addMultiOptions( $items )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}