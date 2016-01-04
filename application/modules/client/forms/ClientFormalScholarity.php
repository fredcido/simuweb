<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientFormalScholarity extends Client_Form_ClientScholarity
{
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'formalScholarity' );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_pertypescholarity' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setValue( 1 )
			    ->setAttrib( 'class', 'no-clear' );
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optCategory = $mapperScholarity->getOptionsCategory( Register_Model_Mapper_PerTypeScholarity::FORMAL );
	
	$elements[] = $this->createElement( 'select', 'category' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->addMultiOptions( $optCategory )
			    ->setLabel( 'Kategoria' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true )
			    ->setLabel( 'Kursu' );
	
	// Search just for Formal Education Institute
	$filters['fk_id_pertypescholarity'] = Register_Model_Mapper_PerTypeScholarity::FORMAL;
	
	$mapperEducationInsitute = new Register_Model_Mapper_EducationInstitute();
	$rows = $mapperEducationInsitute->listByFilters( $filters );
	
	$optEducationInstitute[''] = '';
	foreach ( $rows as $row )
	    $optEducationInstitute[$row->id_fefpeduinstitution] = $row->institution;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Instituisaun Ensinu' )
			    ->addMultiOptions( $optEducationInstitute )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$elements[] = $this->createElement( 'text', 'start_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    //->setAttrib( 'readonly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Data Iniciu' );
	
	$elements[] = $this->createElement( 'text', 'finish_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    //->setAttrib( 'readonly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Data Fim' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}