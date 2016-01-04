<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_PERContractSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'minimum_amount' )->setValue( 0 )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'maximum_amount' )->setValue( 100000 )->setDecorators( array( 'ViewHelper' ) );
	
	$mapperEnterprise = new Register_Model_Mapper_Enterprise();
	$rows = $mapperEnterprise->listByFilters();
	
	$optEnteprises[''] = '';
	foreach ( $rows as $row )
	    $optEnteprises[$row['id_fefpenterprise']] = $row['enterprise_name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpenterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optEnteprises )
			    ->setLabel( 'Instituisaun responsavel ba implementasaun' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_per_area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'span12' )
			    ->setLabel( 'Area projetu nian' );
	
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$districts = $dbDistrict->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $districts as $district )
	    $optCountry[$district['id_adddistrict']] = $district['District'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'span12' )
			    ->setValue( Admin_Model_Mapper_SysUser::userCeopToDistrict() )
			    ->addMultiOptions( $optCountry )
			    ->setLabel( 'Distritu' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsubdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Sub-Distritu' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsucu' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Suku' );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Remata' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_per_area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'span12' )
			    ->setLabel( 'Area projetu nian' );
	
	$this->addElements( $elements );
    }
}