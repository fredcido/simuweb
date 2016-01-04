<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_PCEContractSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();
	
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$districts = $dbDistrict->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $districts as $district )
	    $optCountry[$district['id_adddistrict']] = $district['District'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optCountry )
			    ->setLabel( 'Distritu' );
	
	$optModule[''] = '';
	
	$mapperModule = new Fefop_Model_Mapper_Module();
	$rows = $mapperModule->listModules( Fefop_Model_Mapper_Program::PCE );
	
	foreach ( $rows as $row )
	    $optModule[$row['id_fefop_modules']] = $row['num_module'] . ' - ' . $row['module'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_modules' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optModule )
			    ->setLabel( 'Modulu' );
	
	$mapperIsicDivision = new Register_Model_Mapper_IsicDivision();
	$rows = $mapperIsicDivision->listAll();
	
	$optDivisionTimor[''] = '';
	foreach ( $rows as $row )
	    $optDivisionTimor[$row->id_isicdivision] = $row->name_disivion;
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicdivision' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Setór atividade nian' )
			    ->setAttrib( 'onchange', 'Fefop.PceContract.searchIsicClass(this)' )
			    ->addMultiOptions( $optDivisionTimor );
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicclasstimor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Área negósiu nian' )
			    ->setRegisterInArrayValidator( false );
	
	$optPartisipants[''] = '';
	$optPartisipants['S'] = 'Ema ida';
	$optPartisipants['G'] = 'Grupu';
	
	$elements[] = $this->createElement( 'select', 'partisipants' )
		    ->setDecorators( $this->getDefaultElementDecorators() )
		    ->setAttrib( 'class', 'm-wrap span12' )
		    ->setLabel( 'Grupu' )
		    ->addMultiOptions( $optPartisipants );
	
	$mapperPce = new External_Model_Mapper_Pce();
	$rows = $mapperPce->listBeneficiaries();
	
	$optBeneficiaries[''] = '';
	foreach ( $rows as $row )
	    $optBeneficiaries[$row->id_perdata] = Client_Model_Mapper_Client::buildNumRow( $row ) . ' - ' . Client_Model_Mapper_Client::buildName( $row );
	
	$elements[] = $this->createElement( 'select', 'bussines_plan_developer' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( "Naran ema ne'ebé dezenvolve Planu Negósiu nian" )
			    ->addMultiOptions( $optBeneficiaries );
	
	$optIsSubmitted[''] = '';
	$optIsSubmitted['1'] = 'Tiha ona';
	$optIsSubmitted['0'] = 'Seidauk';
	
	$elements[] = $this->createElement( 'select', 'submitted' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Submit' )
			    ->addMultiOptions( $optIsSubmitted );
	
	$optIsSubmitted[''] = '';
	$optIsSubmitted['1'] = 'Tiha ona';
	$optIsSubmitted['0'] = 'Seidauk';
	
	$elements[] = $this->createElement( 'select', 'contract' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Iha Kontratu' )
			    ->addMultiOptions( $optIsSubmitted );
	
	
	$this->addElements( $elements );
    }
}