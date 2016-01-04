<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_Barrier extends App_Form_Default
{

    const ID = 137;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_barrier_name' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperTypeBarrier =  new Register_Model_Mapper_BarrierType();
	$sections = $mapperTypeBarrier->fetchAll();
	
	$optTypeBarrier[''] = '';
	foreach ( $sections as $section )
	    $optTypeBarrier[$section['id_barrier_type']] = $section['barrier_type_name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_barrier_type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Barreira' )
			    ->addMultiOptions( $optTypeBarrier )
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'barrier_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Barreira' )
			    ->setRequired( true );
	
	$optStatus[''] = '';
	$optStatus['1'] = 'Loos';
	$optStatus['0'] = 'Lae';
	
	$elements[] = $this->createElement( 'select', 'barrier_name_active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setLabel( 'Ativu' )
			    ->setRequired( true )
			    ->addMultiOptions( $optStatus );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}