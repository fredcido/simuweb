<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_Institution extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$this->removeElement( 'date_start' );
	$this->removeElement( 'date_finish' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'register/institution-report' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Rejistu Inst. Ensinu' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'orientation' )
			    ->setValue( 'landscape' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperDistrict = new Register_Model_Mapper_AddDistrict();
	$rows = $mapperDistrict->listAll();
	
	$optDistrict[''] = '';
	foreach ( $rows as $row )
	    $optDistrict[$row->id_adddistrict] = $row->District;
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Distritu' )
			    ->addMultiOptions( $optDistrict );
	
	$dbTypeInstitution = App_Model_DbTable_Factory::get( 'TypeInstitution' );
	$rows = $dbTypeInstitution->fetchAll( array(), array( 'type_institution' ) );
	
	$optType[''] = '';
	foreach ( $rows as $row )
	    $optType[$row->id_typeinstitution] = $row->type_institution;
	
	$elements[] = $this->createElement( 'select', 'fk_typeinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipu Instituisaun' )
			    ->addMultiOptions( $optType )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$optRegister[''] = '';
	$optRegister['1'] = 'Sim';
	$optRegister['0'] = 'Lae';
	
	$elements[] = $this->createElement( 'select', 'register' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Rejistu ?' )
			    ->addMultiOptions( $optRegister )
			    ->setAttrib( 'class', 'm-wrap span6' );
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optScholarity = $mapperScholarity->getOptionsScholarity();
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRegisterInArrayValidator( false )
			    ->addMultiOptions( $optScholarity )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Kursu' );
	
	$this->addElements( $elements );
    }
}