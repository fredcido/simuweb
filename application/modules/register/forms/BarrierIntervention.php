<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_BarrierIntervention extends App_Form_Default
{

    const ID = 139;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_barrier_intervention' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperTypeBarrierType =  new Register_Model_Mapper_BarrierType();
	$sections = $mapperTypeBarrierType->fetchAll();
	
	$optTypeBarrierType[''] = '';
	foreach ( $sections as $section )
	    $optTypeBarrierType[$section['id_barrier_type']] = $section['barrier_type_name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_barrier_type' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Barreira' )
			    ->addMultiOptions( $optTypeBarrierType )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_barrier_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Naran Barreira' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'barrier_Intervention_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Intervensaun' )
			    ->setRequired( true );
	
	$optStatus[''] = '';
	$optStatus['1'] = 'Loos';
	$optStatus['0'] = 'Lae';
	
	$elements[] = $this->createElement( 'select', 'barrier_Intervention_active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setLabel( 'Ativu' )
			    ->setRequired( true )
			    ->addMultiOptions( $optStatus );
	
	$elements[] = $this->createElement( 'select', 'barrier_intervention_observation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setLabel( 'Presiza Observasaun' )
			    ->setRequired( true )
			    ->addMultiOptions( $optStatus );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}