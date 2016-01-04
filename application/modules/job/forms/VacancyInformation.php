<?php

/**
 *
 * @author Frederico Estrela
 */
class Job_Form_VacancyInformation extends App_Form_Default
{

    const ID = 64;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_jobvacancy' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			   ->setDecorators( array( 'ViewHelper' ) )
			   ->setAttrib( 'class', 'no-clear' )
			   ->setValue( 'information' );
	
	$elements[] = $this->createElement( 'text', 'vacancy_titule' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 250 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Titulu Vaga' );
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$optCeop[''] = '';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'CEOP' )
			    ->addMultiOptions( $optCeop )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$elements[] = $this->createElement( 'text', 'start_salary' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 20 )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Salariu Husi' );
	
	$elements[] = $this->createElement( 'text', 'finish_salary' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 20 )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Salariu Too' );
	
	$elements[] = $this->createElement( 'text', 'additional_salary' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 20 )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Salariu Adisional' );
	
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$occupations = $dbOccupationTimor->fetchAll();
	
	$optOccupations[''] = '';
	foreach ( $occupations as $occupation )
	    $optOccupations[$occupation['id_profocupationtimor']] = $occupation['acronym'] . ' ' . $occupation['ocupation_name_timor'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_profocupation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Okupasaun' )
			    ->addMultiOptions( $optOccupations )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'registration_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setValue( Zend_Date::now()->toString( 'dd/MM/yyyy' ) )
			    ->setLabel( 'Data Rejistu' );
	
	$elements[] = $this->createElement( 'text', 'start_job_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Inisiu Serbisu' );
	
	$elements[] = $this->createElement( 'text', 'finish_job_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Remata Serbisu' );
	
	$mapperEnterprise = new Register_Model_Mapper_Enterprise();
	$rows = $mapperEnterprise->listByFilters();
	
	$optEnterprise[''] = '';
	foreach ( $rows as $row )
	    $optEnterprise[$row->id_fefpenterprise] = $row->enterprise_name;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpenterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Empreza' )
			    ->addMultiOptions( $optEnterprise )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$elements[] = $this->createElement( 'text', 'open_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Loke' );
	
	$elements[] = $this->createElement( 'text', 'close_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Taka' );
	
	$elements[] = $this->createElement( 'text', 'num_position' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Pozisaun Hira' );
	
	$optDrive['1'] = 'Presiza';
	$optDrive['0'] = 'La Presiza';
	
	$elements[] = $this->createElement( 'radio', 'drive_licence' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Karta Kondusaun' )
			    ->addMultiOptions( $optDrive )
			    ->setAttrib( 'label_class', 'radio' )
			    ->setSeparator( '' )
			    ->setValue( 0 )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'minimum_experience' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Esperiensia Minima (Anos)' );
	
	$elements[] = $this->createElement( 'text', 'category_drive_licence' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Kategoria Karga Kondusaun' );
	
	$elements[] = $this->createElement( 'text', 'minimum_age' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Idade Minima (Anos)' );
	
	$elements[] = $this->createElement( 'text', 'maximum_age' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Idade Masima (Anos)' );
	
	$elements[] = $this->createElement( 'text', 'weekly_workload' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Oras Semana' );
	
	$optTransport['1'] = 'Presiza';
	$optTransport['0'] = 'La Presiza';
	
	$elements[] = $this->createElement( 'radio', 'use_vehicle' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Transporte Rasik' )
			    ->addMultiOptions( $optTransport )
			    ->setAttrib( 'label_class', 'radio' )
			    ->setSeparator( '' )
			    ->setValue( 0 )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'start_time_job' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12 time-picker' )
			    ->setLabel( 'Oras Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'finish_time_job' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12 time-picker' )
			    ->setLabel( 'Oras Remata' );
	
	$elements[] = $this->createElement( 'text', 'vehicle' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 255 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Deskrisaum Transporte' );
	
	$elements[] = $this->createElement( 'textarea', 'description_vacancy' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setLabel( 'Deskrisaum Vaga' );
	
	$elements[] = $this->createElement( 'textarea', 'description_job' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'rows', 3 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Observasaun' );
	
	$mapperCountry =  new Register_Model_Mapper_AddCountry();
	$countries = $mapperCountry->fetchAll();
	
	$optNations[''] = '';
	foreach ( $countries as $country )
	    $optNations[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_location_overseas' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Lokalizasaun Internasional' )
			    ->addMultiOptions( $optNations );
	
	$optGender['0'] = 'MANE NO FETO';
	$optGender['M'] = 'MANE';
	$optGender['F'] = 'FETO';
	
	$elements[] = $this->createElement( 'select', 'gender' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Seksu' )
			    ->addMultiOptions( $optGender )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRequired( true );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}