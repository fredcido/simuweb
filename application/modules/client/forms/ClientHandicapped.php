<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientHandicapped extends App_Form_Default
{

    const ID = 37;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'id_handicapped' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'handicapped' );
	
	$dbTypeHandicapped = App_Model_DbTable_Factory::get( 'TypeHandicapped' );
	$typeHandicappeds = $dbTypeHandicapped->fetchAll( array(), array( 'type_handicapped' ) );
	
	$optTypeHandicapped[''] = '';
	foreach ( $typeHandicappeds as $typeHandicapped )
	    $optTypeHandicapped[$typeHandicapped['id_typehandicapped']] = $typeHandicapped['type_handicapped'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_typehandicapped' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Defisiensia' )
			    ->setRequired( true )
			    ->addMultiOptions( $optTypeHandicapped );
	
	$elements[] = $this->createElement( 'text', 'handicapped' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Defisiensia' );
	
	$elements[] = $this->createElement( 'text', 'needs' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Nesesidade Espesial' );
	
	$elements[] = $this->createElement( 'textarea', 'details' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'rows', 3 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Komentariu' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}