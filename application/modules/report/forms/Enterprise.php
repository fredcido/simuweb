<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_Enterprise extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$this->removeElement( 'date_start' );
	$this->removeElement( 'date_finish' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'register/enterprise-report' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Rejistu Empreza' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'orientation' )
			    ->setValue( 'landscape' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
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
	
	$elements[] = $this->createElement( 'select', 'fk_id_addcountry' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nasionalidade' )
			    ->addMultiOptions( $optCountry );
	
	$this->addElements( $elements );
    }
}