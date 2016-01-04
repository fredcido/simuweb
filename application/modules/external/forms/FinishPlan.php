<?php

/**
 *
 * @author Frederico Estrela
 */
class External_Form_FinishPlan extends External_Form_Pce
{   
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'form_finishplan' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_businessplan' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'checkbox', 'submitted' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Submete ba avaliasaun?' );
	
	$elements[] = $this->createElement( 'text', 'date_sumitted' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Data submete' );
	
	$this->addElements( $elements );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}