<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FundPlanning extends Fefop_Form_Fund
{
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'modules_cost' )->setIsArray( true );
	
	$mapperFund = new Fefop_Model_Mapper_Fund();
	$funds = $mapperFund->fetchAll();
	
	$optFunds[''] = '';
	foreach ( $funds as $fund )
	    $optFunds[$fund['id_fefopfund']] = $fund['name_fund'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefopfund' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->addMultiOptions( $optFunds )
			    ->setLabel( 'Fundu' );
	
	$optYear[''] = '';
	$finalYear = 2014 + 10;
	for ( $yearIni = 2014 - 2; $yearIni <= $finalYear; $yearIni++  )
	    $optYear[$yearIni] = $yearIni;
	
	$elements[] = $this->createElement( 'select', 'year_planning' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->addMultiOptions( $optYear )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Tinan' );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->removeDecorator( 'Label' )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask required' )
			    ->setLabel( 'Total' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'additional_cost' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setValue( 0 )
			    ->removeDecorator( 'Label' )
			    ->setAttrib( 'class', 'm-wrap cost-module span12 money-mask required' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}