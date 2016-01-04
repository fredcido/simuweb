<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_ContractFund extends Fefop_Form_Financial
{
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'contract_fund' );
	
	$elements = array();
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fund' )->setIsArray( true );
	
	$this->addElements( $elements );
    }
}