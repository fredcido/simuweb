<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_CaseCancel extends App_Form_Default
{

    const ID = 158;
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'cancel' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_action_plan' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'step' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' )->setValue( 'caseCancel' );
	
	$elements[] = $this->createElement( 'textarea', 'cancel_justification' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'rows', 4 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Justifikasaun' );
	
	$this->addElements( $elements );
    }
}