<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_BeneficiaryBlacklist extends App_Form_Default
{
    const ID = 178;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setAttrib( 'class', 'identifier' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_staff' )
			    ->setAttrib( 'class', 'identifier' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )
			    ->setAttrib( 'class', 'identifier' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpenterprise' )
			    ->setAttrib( 'class', 'identifier' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$dbFefopPrograms = App_Model_DbTable_Factory::get( 'FEFOPPrograms' );
	$programs = $dbFefopPrograms->fetchAll();
	
	$optPrograms[''] = '';
	foreach ( $programs as $program )
	    $optPrograms[$program['id_fefop_programs']] = $program['acronym'] . ' - '. $program['description'];
	
	$elements[] = $this->createElement( 'select', 'id_fefop_programs' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Programa FEFOP' )
			    ->addMultiOptions( $optPrograms )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_modules' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Modulu FEFOP' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'beneficiary' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Beneficiariu' );
	
	$elements[] = $this->createElement( 'textarea', 'comment' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setAttrib( 'maxlength', 400 )
			    ->setLabel( 'Comentariu' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}