<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_BudgetCategory extends App_Form_Default
{

    const ID = 166;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_budget_category' )
			   ->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 300 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Categoria' )
			    ->setRequired( true );
	
	$optType[''] = '';
	$optType['E'] = 'Rúbricas';
	$optType['I'] = 'Receitas';
	$optType['S'] = 'Inicial';
	$optType['A'] = 'Anual';
	
	$elements[] = $this->createElement( 'select', 'type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipu' )
			    ->addMultiOptions( $optType )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}