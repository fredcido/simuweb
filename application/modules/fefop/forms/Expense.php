<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_Expense extends App_Form_Default
{
    const ID = 176;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_budget_category' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperExpenseType =  new Fefop_Model_Mapper_ExpenseType();
	$expenseTypes = $mapperExpenseType->fetchAll();
	
	$optExpenseTypes[''] = '';
	foreach ( $expenseTypes as $expenseType )
	    $optExpenseTypes[$expenseType['id_budget_category_type']] = $expenseType['description'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_budget_category_type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Komponente' )
			    ->addMultiOptions( $optExpenseTypes )
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'maxlength', 300 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Rúbrica' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}