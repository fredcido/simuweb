<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_Fund extends App_Form_Default
{
    const ID = 177;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_fefopfund' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$optType[''] = '';
	$optType['D'] = 'Doador';
	$optType['G'] = 'Governo';
	
	$elements[] = $this->createElement( 'select', 'type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Fundo' )
			    ->addMultiOptions( $optType )
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'name_fund' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Fundu/Doador' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}