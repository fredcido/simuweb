<?php

/**
 *
 * @author Frederico Estrela
 */
class External_Form_BusinessPlan extends External_Form_Pce
{   
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'form_businessplan' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_businessplan' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'detailed_expense' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'project_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setLabel( 'Nome do projeto' );
	
	$elements[] = $this->createElement( 'text', 'district' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'readonly', true )
			    ->setLabel( 'Distritu' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsubdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Sub-Distritu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsucu' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Suku' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'textarea', 'location_description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setLabel( 'Descrição da localização' );
	
	$elements[] = $this->createElement( 'text', 'phone' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 15 )
			    ->setAttrib( 'class', 'm-wrap span12 phone-mask' )
			    ->setLabel( 'Telefone' );
	
	$elements[] = $this->createElement( 'text', 'email' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 250 )
			    ->addFilter( 'StringTrim' )
			    ->addValidator( 'EmailAddress' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'E-mail' );
	
	$optYear[''] = '';
	for ( $year = date( 'Y' ); $year <= date( 'Y' ) + 10; $year++ )
	    $optYear[$year] = $year;
	
	$elements[] = $this->createElement( 'select', 'year_activity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optYear )
			    ->setLabel( 'Ano esperado de início de atividade' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'bussines_plan_developer' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Quem desenvolveu plano de negócios?' )
			    ->setRequired( true );
	
	$dbTypeEnterprise = App_Model_DbTable_Factory::get( 'FEFPTypeEnterprise' );
	$rows = $dbTypeEnterprise->fetchAll( array(), array( 'type_enterprise' ) );
	
	$optTypeEnterprise[''] = '';
	foreach ( $rows as $row )
	    $optTypeEnterprise[$row->id_fefptypeenterprise] = $row->type_enterprise;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefptypeenterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipo de Empresa' )
			    ->addMultiOptions( $optTypeEnterprise )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$mapperPce = new External_Model_Mapper_Pce();
	$descriptionFields = $mapperPce->getDescriptionFields();
	
	$dynamicElements = array();
	foreach ( $descriptionFields as $id => $label ) {
	    $dynamicElements[] = $this->createElement( 'textarea', $id )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setLabel( $label )
			    ->setBelongsTo( 'dynamic_fields' )
			    ->setRequired( false );
	}
	
	$this->addDisplayGroup( $dynamicElements, 'dynamic_fields' );
	
	$amountFields = array(
			    'total_expense',
			    'investiment',
			    'annual_expense',
			    'revenue',
			    'income_incr',
			    'income_cost',
			    'sale_tax',
			    'reserve_fund',
			    'first_year'     => 'PRIMEIRO ANO',
			    'following_year' => 'ANOS SEGUINTES',
			);
	
	foreach ( $amountFields as $id => $amountField ) {
	    
	    $label = 'Total';
	    $idElement = $id;
	    if ( is_int( $id ) ) {
		$idElement = $amountField;
	    } else
		$label = $amountField;
	    
	    $element = $this->createElement( 'text', $idElement )
				->setDecorators( $this->getDefaultElementDecorators() )
				->setAttrib( 'readOnly', true )
				->setBelongsTo( 'total_fields' )
				->setAttrib( 'class', 'm-wrap span12 money-mask total-fields' )
				->setLabel( $label );
	    
	    if ( is_int( $id ) )
		$element->removeDecorator( 'Label' );
	    
	    $elements[] = $element;
	}
	
	$this->addElements( $elements );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}