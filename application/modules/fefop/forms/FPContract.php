<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FPContract extends App_Form_Default
{
    const ID = 183;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_fp_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_annual_planning' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_perscholarity' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_planning_course' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_unit_cost' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpstudentclass' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'id_budget_category' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'cost_client' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'client_handicapped' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'institute' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Sentru ba formasaun ne\'ebé akreditadu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'class_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Turma' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'scholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Kursu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span6 money-mask' )
			    ->setLabel( 'Total' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'unit_cost' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span6 money-mask' )
			    ->setLabel( 'Kustu Unitariu' )
			    ->setRequired( true );
	
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
	
	$elements[] = $this->createElement( 'text', 'start_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'finish_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date' )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setLabel( 'Loron Remata' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}