<?php

/**
 *
 * @author Frederico Estrela
 */
class External_Form_Pce extends App_Form_Default
{   
    CONST ID = 191;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'form_information' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_businessplan' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'partisipants' )->setDecorators( array( 'ViewHelper' ) )->setValue( 'S' );
	$elements[] = $this->createElement( 'hidden', 'clients' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'module' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )->setDecorators( array( 'ViewHelper' ) );

	$mapperIsicDivision = new Register_Model_Mapper_IsicDivision();
	$rows = $mapperIsicDivision->listAll();
	
	$optDivisionTimor[''] = '';
	foreach ( $rows as $row )
	    $optDivisionTimor[$row->id_isicdivision] = $row->name_disivion;
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicdivision' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setAttrib( 'onchange', 'Pce.searchIsicClass(this)' )
			    ->setLabel( 'Setor de Atividade' )
			    ->addMultiOptions( $optDivisionTimor )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicclasstimor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Atividade de Negócio' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'total_partisipants' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'disabled', true )
			    ->setLabel( 'Total de membros' )
			    ->setAttrib( 'class', 'm-wrap span2' )
			    ->setValue( 1 );
	
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$districts = $dbDistrict->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $districts as $district )
	    $optCountry[$district['id_adddistrict']] = $district['District'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optCountry )
			    ->setLabel( 'Distritu' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}