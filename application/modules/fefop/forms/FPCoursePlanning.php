<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FPCoursePlanning extends Fefop_Form_FPAnnualPlanning
{   
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$elements[] = $this->createElement( 'hidden', 'year' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_unit_cost' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'unit_cost' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'id_planning_course' )->setDecorators( array( 'ViewHelper' ) );
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$categories = $mapperScholarity->getOptionsCategory( Register_Model_Mapper_PerTypeScholarity::NON_FORMAL );
	
	$optCategory[''] = '';
	foreach ( $categories as $id => $category )
	    $optCategory[$id] = $category;
	
	$elements[] = $this->createElement( 'select', 'category' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->addMultiOptions( $optCategory )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Kategoria' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setAttrib( 'onchange', 'Fefop.FPAnnualPlanning.fetchUnitCost()' )
			    ->setLabel( 'Kursu' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'total_woman' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Feto Nain Hira' );
	
	$elements[] = $this->createElement( 'text', 'total_man' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setLabel( 'Mane Nain Hira' );
	
	$elements[] = $this->createElement( 'text', 'unit_cost' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setAttrib( 'onchange', 'Fefop.FPAnnualPlanning.calcTotalFormation()' )
			    ->setLabel( 'Kustu Unitariu' );
	
	$elements[] = $this->createElement( 'text', 'total_cost' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Kustu total' );
	
	$elements[] = $this->createElement( 'text', 'total_students' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'class', 'm-wrap span12 text-numeric4' )
			    ->setAttrib( 'onchange', 'Fefop.FPAnnualPlanning.calcTotalFormation()' )
			    ->setLabel( 'Total Partisipante' );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setRequired( true )
			    ->setLabel( 'Loron Remata' );
	
	
	$this->getElement( 'year_planning' )->setAttrib( 'disabled', true );
	
	$this->addElements( $elements );
    }
    
    /**
     * 
     * @param array $data
     * @return boolean
     */
    public function isValid( $data )
    {
	$data['year_planning'] = $data['year'];
	
	return parent::isValid( $data );
    }

}