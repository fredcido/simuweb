<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_BankConsolidate extends App_Form_Default
{
    
    const ID = 194;
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'formconsolidate' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'consolidate' )->setIsArray( true );
	
	$this->addElements( $elements );

	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}