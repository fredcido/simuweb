<?php

/**
 *
 * @author Frederico Estrela
 */
abstract class Fefop_Form_PERContract extends App_Form_Default
{
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_per_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_modules' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpenterprise' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'item_expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'employment_expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'formation_expense' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'enterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'span12 focused' )
			    ->setLabel( 'Instituisaun responsavel ba implementasaun' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_per_area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'span12 chosen' )
			    ->setLabel( 'Area projetu nian' )
			    ->setRequired( true );
	
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
			    ->setLabel( 'Distritu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsubdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Sub-Distritu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsucu' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Suku' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'span12 date-mask' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'span12 date-mask' )
			    ->setRequired( true )
			    ->setLabel( 'Loron Remata' );
	
	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 3 )
			    ->setLabel( 'Deskrisaun Projetu nian' );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->removeDecorator( 'Label' )
			    ->setAttrib( 'class', 'span12 money-mask required' )
			    ->setLabel( 'Total' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
    }
}