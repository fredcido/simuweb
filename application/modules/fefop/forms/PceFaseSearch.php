<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_PceFaseSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'minimum_amount' )->setValue( 0 )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'maximum_amount' )->setValue( 100000 )->setDecorators( array( 'ViewHelper' ) );
	
	$mapperPceFase = new Fefop_Model_Mapper_PCEContract();
	$studentClasses = $mapperPceFase->listStudentClassContract();
	
	$optStudentClass[''] = '';
	foreach ( $studentClasses as $class )
	    $optStudentClass[$class['id_fefpstudentclass']] = $class['class_name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpstudentclass' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optStudentClass )
			    ->setLabel( 'Formasaun Profisional' );
	
	$elements[] = $this->createElement( 'text', 'beneficiary' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Benefisiariu' );
	
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$districts = $dbDistrict->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $districts as $district )
	    $optCountry[$district['id_adddistrict']] = $district['District'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optCountry )
			    ->setLabel( 'Distritu' );
	
	$mapperIsicDivision = new Register_Model_Mapper_IsicDivision();
	$rows = $mapperIsicDivision->listAll();
	
	$optDivisionTimor[''] = '';
	foreach ( $rows as $row )
	    $optDivisionTimor[$row->id_isicdivision] = $row->name_disivion;
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicdivision' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Setor de Atividade' )
			    ->addMultiOptions( $optDivisionTimor );
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicclasstimor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Atividade de Negócio' );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Remata' );
	
	$this->addElements( $elements );
    }
}