<?php

/**
 *
 * @author Frederico Estrela
 */
class StudentClass_Form_JobTrainingSearch extends App_Form_Default
{

    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'text', 'title' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Titulu' );
	
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
	
	$mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	$rows = $mapperJobTraining->listEnterprises();
	
	$optEnterprise[''] = '';
	foreach ( $rows as $row )
	    $optEnterprise[$row->fk_id_fefpenterprise] = $row->entity;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpenterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Empreza' )
			    ->addMultiOptions( $optEnterprise )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$rows = $mapperJobTraining->listInstitutes();
	
	$optInstitute[''] = '';
	foreach ( $rows as $row )
	    $optInstitute[$row->fk_id_fefpeduinstitution] = $row->entity;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Inst. Ensinu' )
			    ->addMultiOptions( $optInstitute )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$optTransport['1'] = 'Loke';
	$optTransport['0'] = 'Taka';
	
	$elements[] = $this->createElement( 'select', 'status' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Status' )
			    ->setAttrib( 'class', 'span12' )
			    ->addMultiOptions( $optTransport )
			    ->setValue( 1 )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Planu Remata' );
	
	$filters = array(
	    'type'	=> Register_Model_Mapper_PerTypeScholarity::NON_FORMAL
	);
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optScholarity = $mapperScholarity->getOptionsScholarity( $filters );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRegisterInArrayValidator( false )
			    ->addMultiOptions( $optScholarity )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Kursu' );
	
	$this->addElements( $elements );
    }
}