<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_Followup extends App_Form_Default
{
    const ID = 184;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperStatus = new Fefop_Model_Mapper_Status();
	$rows = $mapperStatus->getStatuses();
	
	$optStatuses[''] = '';
	foreach ( $rows as $row )
	    $optStatuses[$row['id_fefop_status']] = $row['status_description'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_status' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Status' )
			    ->addMultiOptions( $optStatuses );

	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 600 )
			    ->setAttrib( 'rows', 5 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Deskrisaun' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}