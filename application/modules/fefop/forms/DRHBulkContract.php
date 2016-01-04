<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_DRHBulkContract extends App_Form_Default
{
    const ID = 197;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_drh_trainingplan' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'contracts_ids' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'beneficiary' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'date_start' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'date_finish' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'duration_days' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'unit_cost' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'training_fund' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'num_training_plan' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Plano ba Formasaun' );
	
	$elements[] = $this->createElement( 'text', 'modality' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Modalidade' );
	
	$elements[] = $this->createElement( 'text', 'scholarity_area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Área' );
	
	$elements[] = $this->createElement( 'text', 'ocupation_name_timor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Dezignasaun' );
	
	$elements[] = $this->createElement( 'text', 'country' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Nasaun' );
	
	$elements[] = $this->createElement( 'text', 'city' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'readonly', true )
			    ->setLabel( 'Sidade' );
	
	$elements[] = $this->createElement( 'text', 'entity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'readonly', true )
			    ->setLabel( 'Sentru Formasaun' );
	
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$districts = $dbDistrict->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $districts as $district )
	    $optCountry[$district['id_adddistrict']] = $district['District'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optCountry )
			    ->setValue( Admin_Model_Mapper_SysUser::userCeopToDistrict() )
			    ->setLabel( 'Distritu' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}