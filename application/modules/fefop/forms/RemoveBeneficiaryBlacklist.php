<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_RemoveBeneficiaryBlacklist extends App_Form_Default
{
    const ID = 179;
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_beneficiary_blacklist' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'textarea', 'comment_remove' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setAttrib( 'maxlength', 400 )
			    ->setRequired( true )
			    ->setLabel( 'Comentariu' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}