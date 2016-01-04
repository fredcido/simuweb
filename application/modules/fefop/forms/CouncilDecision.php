<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_CouncilDecision extends Fefop_Form_PCEContract
{
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_businessplan' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'checkbox', 'approved' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Deferimentu' );
	
	$elements[] = $this->createElement( 'text', 'date_contract' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'disabled', true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Data' );
	
	$elements[] = $this->createElement( 'textarea', 'council_negative' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setLabel( 'Karik iha indeferimentu, hatudu razaun' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}