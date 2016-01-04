<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_EducationInstitutionSearch extends App_Form_Default
{

    const ID = 54;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'text', 'institution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Instituisaun Ensinu' );
	
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
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$optCeop[''] = '';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'CEOP' )
			    ->addMultiOptions( $optCeop )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
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