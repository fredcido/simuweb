<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FinancialSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'minimum_amount' )->setValue( 0 )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'maximum_amount' )->setValue( 200000 )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpenterprise' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'text', 'enterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Empreza' );
	
	$elements[] = $this->createElement( 'text', 'name_contract' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Kontratu' );
	
	$elements[] = $this->createElement( 'text', 'identifier' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setLabel( 'Identifikador Resibu' );
	
	$dbTypeTransaction = App_Model_DbTable_Factory::get( 'FEFOPTypeTransaction' );
	$rows = $dbTypeTransaction->fetchAll( array( 'type = ?' => 'F' ) );
	
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
	
	$dbTransactionStatus = App_Model_DbTable_Factory::get( 'FEFOPTransactionStatus' );
	$rows = $dbTransactionStatus->fetchAll();
	
	$optTransactionStatus = array();
	foreach ( $rows as $row )
	    $optTransactionStatus[$row['id_fefop_transaction_status']] = $row['description'];
	
	$elements[] = $this->createElement( 'select', 'id_fefop_transaction_status' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optTransactionStatus )
			    ->setLabel( 'Status Transasaun' );
	
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
	
	$elements[] = $this->createElement( 'text', 'num_contract' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Kontratu' );
	
	$this->addElements( $elements );
    }
}