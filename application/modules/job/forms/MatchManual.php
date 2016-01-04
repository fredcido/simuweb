<?php

/**
 *
 * @author Frederico Estrela
 */
class Job_Form_MatchManual extends Job_Form_Match
{
    /**
     * 
     */
    public function init()
    {
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_jobvacancy' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'clients' )->setIsArray( true )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'source' )->setValue( 'M' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'minimum_age' )->setValue( 20 )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'maximum_age' )->setValue( 40 )->setDecorators( array( 'ViewHelper' ) );
	
	// List Districts just from Timor
	$mapperDistrict = new Register_Model_Mapper_AddDistrict();
	$districts = $mapperDistrict->listAll( 1 );
	
	$optDistrict[''] = '';
	foreach ( $districts as $district )
	    $optDistrict[$district->id_adddistrict] = $district->District;
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optDistrict )
			    ->setLabel( 'Distritu' )
			    ->setRegisterInArrayValidator( false );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsubdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Sub-Distritu' );
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optCategory = $mapperScholarity->getOptionsCategory( Register_Model_Mapper_PerTypeScholarity::FORMAL );
	
	$elements[] = $this->createElement( 'select', 'category' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optCategory )
			    ->setLabel( 'Kategoria' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Kurso' )
			    ->setRegisterInArrayValidator( false );
	
	$dbLanguage = App_Model_DbTable_Factory::get( 'PerLanguage' );
	$languages = $dbLanguage->fetchAll( array(), array( 'language' ) );
	
	$optLanguage[''] = '';
	foreach ( $languages as $language )
	    $optLanguage[$language['id_perlanguage']] = $language['language'];
	
	$elements[] = $this->createElement( 'multiselect', 'fk_id_perlanguage' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Lian Fuan' )
			    ->addMultiOptions( $optLanguage );
	
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$occupations = $dbOccupationTimor->fetchAll();
	
	$optOccupations[''] = '';
	foreach ( $occupations as $occupation )
	    $optOccupations[$occupation['id_profocupationtimor']] = $occupation['acronym'] . ' ' . $occupation['ocupation_name_timor'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_profocupation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Okupasaun' )
			    ->addMultiOptions( $optOccupations );
	
	$elements[] = $this->createElement( 'text', 'minimum_experience' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span8 text-numeric4' )
			    ->setAttrib( 'style', 'background: #fff;')
			    ->setLabel( 'Esperiensia Minima (Anos)' );
	
	$optGender[''] = 'MANE NO FETO';
	$optGender['M'] = 'MANE';
	$optGender['F'] = 'FETO';
	
	$elements[] = $this->createElement( 'select', 'gender' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Seksu' )
			    ->addMultiOptions( $optGender );
	
	$optDrive['1'] = 'Presiza';
	$optDrive['0'] = 'La Presiza';
	
	$elements[] = $this->createElement( 'radio', 'drive_licence' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Karta Kondusaun' )
			    ->addMultiOptions( $optDrive )
			    ->setAttrib( 'label_class', 'radio' )
			    ->setSeparator( '' )
			    ->setValue( 0 );
	
	$filters = array(
	    'type'  => Register_Model_Mapper_PerTypeScholarity::NON_FORMAL
	);
	
	$optProfessional = $mapperScholarity->getOptionsScholarity( $filters );
	
	$elements[] = $this->createElement( 'multiselect', 'fk_id_training' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Formasaun Profisional' )
			    ->addMultiOptions( $optProfessional );
	
	$this->addElements( $elements );
    }
}