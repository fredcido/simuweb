<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_TransactionContract extends Fefop_Form_Financial
{
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'id_fefop_transaction' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setDecorators( array( 'ViewHelper' ) );
	
	$dbTypeTransaction = App_Model_DbTable_Factory::get( 'FEFOPTypeTransaction' );
	$rows = $dbTypeTransaction->fetchAll( array( 'type = ?' => 'F' ) );
	
	$optTypeTransaction = array();
	foreach ( $rows as $row )
	    $optTypeTransaction[$row['id_fefop_type_transaction']] = $row['acronym'] . ' - ' . $row['description'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_type_transaction' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optTypeTransaction )
			    ->setRequired( true )
			    ->setLabel( 'Tipu Transasaun' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_budget_category_type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true )
			    ->setLabel( 'Komponente' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_budget_category' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true )
			    ->setLabel( 'Rúbrica' );
	
	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', '1' )
			    ->setLabel( 'Deskrisaun' );
	
	$elements[] = $this->createElement( 'text', 'total_contract' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask bold text-right' )
			    ->setValue( 0 )
			    ->setLabel( 'Total' );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask bold text-right' )
			    ->setLabel( 'Total' );
	
	$elements[] = $this->createElement( 'text', 'date_reference' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setValue( Zend_Date::now()->toString( 'dd/MM/yyyy' ) )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Data' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}