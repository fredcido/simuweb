<?php

/**
 *
 * @author Frederico Estrela
 */
class StudentClass_Form_RegisterInformation extends App_Form_Default
{

    const ID = 62;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_fefpstudentclass' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			   ->setDecorators( array( 'ViewHelper' ) )
			   ->setAttrib( 'class', 'no-clear' )
			   ->setValue( 'information' );
	
	$elements[] = $this->createElement( 'text', 'class_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Klase' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    //->addMultiOptions( $optScholarity )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
                            ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Kursu' );
	
	$mapperEducationInsitute = new Register_Model_Mapper_EducationInstitute();
	$rows = $mapperEducationInsitute->listByFilters();
	
	$optEducationInstitute[''] = '';
	foreach ( $rows as $row )
	    $optEducationInstitute[$row->id_fefpeduinstitution] = $row->institution;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Instituisaun Ensinu' )
			    ->addMultiOptions( $optEducationInstitute )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$dbTypeProposal = App_Model_DbTable_Factory::get( 'FEFPTypeProposal' );
	$typesProposal = $dbTypeProposal->fetchAll( array(), array( 'type_proposal' ) );
	
	$optTypeProposal[''] = '';
	foreach ( $typesProposal as $typeProposal )
	    $optTypeProposal[$typeProposal->id_fefptypeproposal] = $typeProposal->type_proposal;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpproposal' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipu Formasaun' )
			    ->addMultiOptions( $optTypeProposal )
			    ->setAttrib( 'disabled', true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$elements[] = $this->createElement( 'text', 'minimal_age' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Tinan Minimo' );
	
	$elements[] = $this->createElement( 'text', 'maximal_age' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Tinan Masimu' );
	
	$mapperPerScholarity = new Register_Model_Mapper_PerScholarity();
	$rows = $mapperPerScholarity->listAll( array( 'type' => Register_Model_Mapper_PerTypeScholarity::FORMAL ) );
	
	$optScholarity = array( '' => '' );
	foreach ( $rows as $scholarity )
	    $optScholarity[$scholarity->id_perscholarity] = $scholarity->scholarity;
	
	$elements[] = $this->createElement( 'select', 'fk_minimal_scholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optScholarity )
			    ->setLabel( 'Eskolaridade Minima' );
	
	$elements[] = $this->createElement( 'text', 'start_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'schedule_finish_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setRequired( true )
			    ->setLabel( 'Loron Planu Remata' );
	
	$elements[] = $this->createElement( 'text', 'real_finish_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Real Remata' );
	
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
	
	$elements[] = $this->createElement( 'text', 'formation_time' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Durasaun Formasaun' );
	
	$elements[] = $this->createElement( 'text', 'formation_time_class' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Oras Formasaun Klase' );
	
	$elements[] = $this->createElement( 'text', 'formation_time_outclass' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Durasaun Formasaun iha liu' );
	
	$mapperClassTimor = new Register_Model_Mapper_IsicTimor();
	$rows = $mapperClassTimor->listAll();
	
	$optClassTimor[''] = '';
	foreach ( $rows as $row )
	    $optClassTimor[$row->id_isicclasstimor] = $row->name_classtimor;
	
	$elements[] = $this->createElement( 'select', 'fk_id_sectorindustry' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Setor da Industria' )
			    ->addMultiOptions( $optClassTimor );
	
	$elements[] = $this->createElement( 'text', 'time_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readonly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 time-picker' )
			    ->setLabel( 'Oras Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'time_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readonly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 time-picker' )
			    ->setLabel( 'Oras Remata' );
	
	$elements[] = $this->createElement( 'checkbox', 'accommodation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Iha Akomodasaun?' );
	
	$elements[] = $this->createElement( 'checkbox', 'transport' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Iha Transporte?' );
	
	$elements[] = $this->createElement( 'checkbox', 'snack' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Iha Matabisu?' );
	
	$elements[] = $this->createElement( 'checkbox', 'lunch' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Iha Hahan Meiu-dia?' );
	
	$elements[] = $this->createElement( 'checkbox', 'dinner' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Iha Hahan Kalan?' );
	
	$elements[] = $this->createElement( 'text', 'num_women_student' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Feto Nain Hira' );
	
	$elements[] = $this->createElement( 'text', 'num_men_student' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Mane Nain Hira' );
	
	$elements[] = $this->createElement( 'text', 'num_total_student' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Total Partisipante' );
	
	$elements[] = $this->createElement( 'text', 'student_payment' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 20 )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Pagamentu Estudante' );
	
	$elements[] = $this->createElement( 'text', 'description_payment' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 500 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Deskrisaun Pagamentu Partisipante' );
	
	$elements[] = $this->createElement( 'text', 'subsidy' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 20 )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Subsidiu' );
	
	$elements[] = $this->createElement( 'text', 'description_subsity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 500 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Deskrisaun Subsidiu' );
	
	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setLabel( 'Deskrisaum Jeral' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
    
    public function isValid( $data )
    {
	if ( !empty( $data['id_fefpstudentclass'] ) ) {
	 
	    $this->getElement( 'fk_id_fefpeduinstitution' )->setRequired( false );
	    $this->getElement( 'fk_id_dec' )->setRequired( false );
	    $this->getElement( 'fk_id_perscholarity' )->setRequired( false );
	}
	
	return parent::isValid( $data );
    }
}