<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FERegistration extends App_Form_Default
{
    const ID = 199;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_fe_registration' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'entity' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'client_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Kompletu' );
	
	$elements[] = $this->createElement( 'text', 'client_fone' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Kontatu' );
	
	$elements[] = $this->createElement( 'text', 'email' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'E-mail' );
	
	$elements[] = $this->createElement( 'text', 'evidence' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Kartaun Evidensia' );
	
	$elements[] = $this->createElement( 'text', 'electoral' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Kartaun Eleitoral' );
	
	$elements[] = $this->createElement( 'text', 'date_inserted' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Data' );
	
	
	$mapperScholarityArea = new Register_Model_Mapper_ScholarityArea();
	$sections = $mapperScholarityArea->fetchAll();
	
	$optScholarityArea[''] = '';
	foreach ( $sections as $section )
	    $optScholarityArea[$section['id_scholarity_area']] = $section['scholarity_area'];
	
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$occupations = $dbOccupationTimor->fetchAll();
	
	$optOccupations[''] = '';
	foreach ( $occupations as $occupation )
	    $optOccupations[$occupation['id_profocupationtimor']] = $occupation['acronym'] . ' ' . $occupation['ocupation_name_timor'];
	
	$dbScholarityLevel = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	$levels = $dbScholarityLevel->fetchAll( array(), array( 'id_perlevelscholarity' ) );
	
	$optLevel[''] = '';
	foreach ( $levels as $level )
	    $optLevel[$level->id_perlevelscholarity] = $level->level_scholarity;
	
	
	$elementsProfessional = array();
	$elementsProfessional[] = $this->createElement( 'select', 'area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen focused' )
			    ->setLabel( 'Area Kursu' )
			    ->setBelongsTo('professional')
			    ->addMultiOptions( $optScholarityArea );
	
	$elementsProfessional[] = $this->createElement( 'select', 'occupation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Dezignasaun' )
			    ->setBelongsTo('professional')
			    ->addMultiOptions( $optOccupations );
	
	$elementsProfessional[] = $this->createElement( 'select', 'level' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optLevel )
			    ->setBelongsTo('professional')
			    ->setLabel( 'Nivel' );
	
	$professionalSubForm = new Zend_Form_SubForm();
	$professionalSubForm->addElements($elementsProfessional);
	
	$this->addSubForm( $professionalSubForm, 'professional' );
	
	$elementsFormation = array();
	$elementsFormation[] = $this->createElement( 'select', 'area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen focused' )
			    ->setLabel( 'Area Kursu' )
			    ->setBelongsTo('formation')
			    ->addMultiOptions( $optScholarityArea );
	
	$elementsFormation[] = $this->createElement( 'select', 'occupation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Dezignasaun' )
			    ->setBelongsTo('formation')
			    ->addMultiOptions( $optOccupations );
	
	$elementsFormation[] = $this->createElement( 'select', 'level' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optLevel )
			    ->setBelongsTo('formation')
			    ->setLabel( 'Nivel' );
	
	$formationSubForm = new Zend_Form_SubForm();
	$formationSubForm->addElements($elementsFormation);
	
	$this->addSubForm( $formationSubForm, 'formation' );
	
	$selectedElements = array();
	$selectedElements[] = $this->createElement( 'select', 'area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen focused' )
			    ->setLabel( 'Area Kursu' )
			    ->setBelongsTo('selected')
			    ->setRequired( true )
			    ->addMultiOptions( $optScholarityArea );
	
	$selectedElements[] = $this->createElement( 'select', 'occupation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Dezignasaun' )
			    ->setBelongsTo('selected')
			    ->setRequired( true )
			    ->addMultiOptions( $optOccupations );
	
	$selectedSubForm = new Zend_Form_SubForm();
	$selectedSubForm->addElements($selectedElements);
	
	$this->addSubForm( $selectedSubForm, 'selected' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}