<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_RIContract extends App_Form_Default
{
    const ID = 186;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_ri_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'cost_expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'item_expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'quantity' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'amount_unit' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'amount_total' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'comments' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'institute' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Sentru Formasaun' );
	
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$districts = $dbDistrict->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $districts as $district )
	    $optCountry[$district['id_adddistrict']] = $district['District'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setValue( Admin_Model_Mapper_SysUser::userCeopToDistrict() )
			    ->addMultiOptions( $optCountry )
			    ->setLabel( 'Distritu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsubdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Sub-Distritu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setRequired( true )
			    ->setLabel( 'Loron Remata' );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->removeDecorator( 'Label' )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask required' )
			    ->setLabel( 'Total' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}