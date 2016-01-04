<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientVisit extends App_Form_Default
{

    const ID = 31;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'id_pervisitpurpose' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'visit' );
	
	$dbVisitPurpose = App_Model_DbTable_Factory::get( 'VisitPurpose' );
	$purposes = $dbVisitPurpose->fetchAll();
	
	$optVisitPurpose[''] = '';
	foreach ( $purposes as $purpose )
	    $optVisitPurpose[$purpose['id_visitpurpose']] = $purpose['purpose'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_visitpurpose' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Objetivu' )
			    ->addMultiOptions( $optVisitPurpose )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 255 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Deskrisaun' );
	
	$elements[] = $this->createElement( 'textarea', 'observation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 8 )
			    ->setLabel( 'Observasaun' );
	
	$elements[] = $this->createElement( 'text', 'visit_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setValue( Zend_Date::now()->toString( 'dd/MM/yyyy' ) )
			    ->setLabel( 'Data Visita' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}