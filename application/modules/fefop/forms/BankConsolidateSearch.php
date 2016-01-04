<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_BankConsolidateSearch extends App_Form_Default
{   
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'id_fefop_bank_contract' )->setDecorators( array( 'ViewHelper' ) );
	
	$dbTypeTransaction = App_Model_DbTable_Factory::get( 'FEFOPTypeTransaction' );
	$rows = $dbTypeTransaction->fetchAll( array( 'type  = ?' => 'F' ) );
	
	$optTypeTransaction[''] = '';
	foreach ( $rows as $row )
	    $optTypeTransaction[$row['id_fefop_type_transaction']] = $row['acronym'] . ' - ' . $row['description'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_type_transaction' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optTypeTransaction )
			    ->setLabel( 'Tipu Transasaun' );
	
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$rows = $dbBudgetCategoryType->fetchAll();
	
	$optBudgetCategoryType[''] = '';
	foreach ( $rows as $row )
	    $optBudgetCategoryType[$row['id_budget_category_type']] = $row['description'];
	
	$elements[] = $this->createElement( 'multiselect', 'fk_id_budget_category_type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optBudgetCategoryType )
			    ->setLabel( 'Komponente' );
	
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$rows = $dbBudgetCategory->fetchAll();
	
	$optBudgetCategory[''] = '';
	foreach ( $rows as $row )
	    $optBudgetCategory[$row['id_budget_category']] = $row['description'];
	
	$elements[] = $this->createElement( 'multiselect', 'fk_id_budget_category' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optBudgetCategory )
			    ->setLabel( 'Rúbrica' );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Loron Remata' );
	
	$elements[] = $this->createElement( 'text', 'minimum_amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask text-right' )
			    ->setLabel( 'Valor total mínimo' );
	
	$elements[] = $this->createElement( 'text', 'maximum_amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask text-right' )
			    ->setLabel( 'Valor total máximo' );
	
	$elements[] = $this->createElement( 'text', 'num_contract' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Kontratu' );
	
	$elements[] = $this->createElement( 'text', 'contract' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Kontratu' );
	
	$this->addElements( $elements );
	
	$this->removeElement( 'status' );
    }
}