<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_PceFaseContract extends App_Form_Default
{
    const ID = 192;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_pce_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpstudentclass' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'detailed_expense' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'beneficiary' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Benefisiariu' );
	
	$elements[] = $this->createElement( 'text', 'class_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Formasaun téknika' );
	
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
	
	$optModule[''] = '';
	$optModule[Fefop_Model_Mapper_Module::CEC] = 'CEC';
	$optModule[Fefop_Model_Mapper_Module::CED] = 'CED';
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_modules' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optModule )
			    ->setLabel( 'Modulu' )
			    ->setRequired( true );
	
	$mapperIsicDivision = new Register_Model_Mapper_IsicDivision();
	$rows = $mapperIsicDivision->listAll();
	
	$optDivisionTimor[''] = '';
	foreach ( $rows as $row )
	    $optDivisionTimor[$row->id_isicdivision] = $row->name_disivion;
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicdivision' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Setór atividade nian' )
			    ->addMultiOptions( $optDivisionTimor )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicclasstimor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Área negósiu nian' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->removeDecorator( 'Label' );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readonly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Data hahú' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setRequired( true )
			    ->setLabel( 'Data Finalizasaun' );
	
	$elements[] = $this->createElement( 'text', 'duration' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRequired( true )
			    ->setLabel( 'Durasaun' );
	    
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}