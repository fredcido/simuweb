<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_BankStatement extends App_Form_Default
{
    
    const ID = 193;
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'formstatement' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_fefop_bank_statements' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'status' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'id_fefop_bank_contract' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'total_contract' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'total_expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_budget_category' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'date_statement' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setValue( Zend_Date::now()->toString( 'dd/MM/yyyy' ) )
			    ->setLabel( 'Data' );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask bold text-right' )
			    ->setAttrib( 'style', 'font-size: 19px' )
			    ->setLabel( 'Total' );
	
	$dbTypeTransaction = App_Model_DbTable_Factory::get( 'FEFOPTypeTransaction' );
	$rows = $dbTypeTransaction->fetchAll();
	
	$optTypeTransaction = array( '' => '' );
	foreach ( $rows as $row )
	    $optTypeTransaction[$row['id_fefop_type_transaction']] = $row['acronym'] . ' - ' . $row['description'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_type_transaction' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optTypeTransaction )
			    ->setRequired( true )
			    ->setLabel( 'Tipu Transasaun' );
	
	$optOperation[Fefop_Model_Mapper_BankStatement::DEBIT] = 'Saída';
	$optOperation[Fefop_Model_Mapper_BankStatement::CREDIT] = 'Entrada';
	
	$elements[] = $this->createElement( 'select', 'operation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optOperation )
			    ->setRequired( true )
			    ->setLabel( 'Operasaun' );
	
	$mapperFund = new Fefop_Model_Mapper_Fund();
	$funds = $mapperFund->fetchAll();
	
	$optFunds[''] = '';
	foreach ( $funds as $fund )
	    $optFunds[$fund['id_fefopfund']] = $fund['name_fund'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefopfund' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optFunds )
			    ->setLabel( 'Fundu' );
	
	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', '2' )
			    ->setRequired( true )
			    ->setLabel( 'Deskrisaun' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}