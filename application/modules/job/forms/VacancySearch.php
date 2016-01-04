<?php

/**
 *
 * @author Frederico Estrela
 */
class Job_Form_VacancySearch extends App_Form_Default
{

    const ID = 65;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'text', 'vacancy_titule' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
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
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$elements[] = $this->createElement( 'text', 'open_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readOnly', true )
			    //->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span8' )
			    ->setLabel( 'Data Loke' );
	
	$elements[] = $this->createElement( 'text', 'close_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readOnly', true )
			    //->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span8' )
			    ->setLabel( 'Data Taka' );
	
	$optTransport['1'] = 'Loke';
	$optTransport['0'] = 'Taka';
	$optTransport['2'] = 'Kansela';
	
	$elements[] = $this->createElement( 'select', 'active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Status' )
			    ->addMultiOptions( $optTransport )
			    ->setValue( 1 )
			    ->setRequired( true );
	
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
	
	$mapperEnterprise = new Register_Model_Mapper_Enterprise();
	$rows = $mapperEnterprise->listByFilters();
	
	$optEnterprise[''] = '';
	foreach ( $rows as $row )
	    $optEnterprise[$row->id_fefpenterprise] = $row->enterprise_name;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpenterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Empreza' )
			    ->addMultiOptions( $optEnterprise )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$this->addElements( $elements );
    }
}