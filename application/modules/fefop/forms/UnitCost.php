<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_UnitCost extends App_Form_Default
{
    const ID = 180;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'text', 'cost' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Folin Hira' )
			    ->setRequired( true );
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$categories = $mapperScholarity->getOptionsCategory( Register_Model_Mapper_PerTypeScholarity::NON_FORMAL );
	
	$optCategory[''] = '';
	foreach ( $categories as $id => $category )
	    $optCategory[$id] = $category;
	
	$elements[] = $this->createElement( 'select', 'category' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->addMultiOptions( $optCategory )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Kategoria' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Kursu' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'textarea', 'comment' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 3 )
			    ->setRequired( true )
			    ->setLabel( 'Deskrisaun' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}