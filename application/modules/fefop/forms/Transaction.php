<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_Transaction extends Fefop_Form_Financial
{
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_fefop_receipt' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpenterprise' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_transaction' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'total_contract' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'total_expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_budget_category' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_transaction_status' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'enterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    //->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Empreza' );
	
	$elements[] = $this->createElement( 'text', 'identifier' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    //->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setLabel( 'Identifikador Resibu' );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask bold text-right' )
			    ->setAttrib( 'style', 'font-size: 19px' )
			    ->setLabel( 'Total' );
	
	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', '3' )
			    ->setLabel( 'Deskrisaun' );
	
	$elements[] = $this->createElement( 'text', 'date_purchased' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setValue( Zend_Date::now()->toString( 'dd/MM/yyyy' ) )
			    ->setLabel( 'Data' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}