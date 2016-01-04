<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_EnterpriseSearch extends App_Form_Default
{

    const ID = 52;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'text', 'enterprise_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Empreza' );
	
	$mapperDistrict = new Register_Model_Mapper_AddDistrict();
	$rows = $mapperDistrict->listAll();
	
	$optDistrict[''] = '';
	foreach ( $rows as $row )
	    $optDistrict[$row->id_adddistrict] = $row->District;
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Distritu' )
			    ->addMultiOptions( $optDistrict );
	
	$dbTypeEnterprise = App_Model_DbTable_Factory::get( 'FEFPTypeEnterprise' );
	$rows = $dbTypeEnterprise->fetchAll( array(), array( 'type_enterprise' ) );
	
	$optTypeEnterprise[''] = '';
	foreach ( $rows as $row )
	    $optTypeEnterprise[$row->id_fefptypeenterprise] = $row->type_enterprise;
	
	$elements[] = $this->createElement( 'select', 'fk_fefptypeenterprite' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Empreza' )
			    ->addMultiOptions( $optTypeEnterprise );
	
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
        
        $dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$countries = $dbCountry->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $countries as $country )
	    $optCountry[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_nationality' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nasionalidade' )
			    ->addMultiOptions( $optCountry );
	
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
	
	$this->addElements( $elements );
    }
}