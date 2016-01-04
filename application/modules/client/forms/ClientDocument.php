<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientDocument extends App_Form_Default
{

    const ID = 29;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = $this->getDocumentElements();
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			   ->setDecorators( array( 'ViewHelper' ) )
			   ->setAttrib( 'class', 'no-clear' )
			   ->setValue( 'document' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
    
    /**
     *
     * @return array 
     */
    public function getDocumentElements()
    {
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdocument' )->setDecorators( array( 'ViewHelper' ) );
	
	$dbTypeDocument = App_Model_DbTable_Factory::get( 'PerTypeDocument' );
	$rows = $dbTypeDocument->fetchAll( array(), array( 'type_document' ) );
	
	$optTypeDocument[''] = '';
	foreach ( $rows as $row )
	    $optTypeDocument[$row->id_pertypedocument] = $row->type_document;
	
	$elements[] = $this->createElement( 'select', 'fk_id_pertypedocument' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipu Dokumentu' )
			    ->addMultiOptions( $optTypeDocument )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$elements[] = $this->createElement( 'text', 'number' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 50 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Numero Dokumentu' );
	
	$elements[] = $this->createElement( 'text', 'issue_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Dokumentu' );
	
	
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$countries = $dbCountry->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $countries as $country )
	    $optCountry[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_country' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Fatin Dokumentu' )
			    ->addMultiOptions( $optCountry )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'checkbox', 'original_proof' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Komprova dadus no fotu dokumentu orijinal?' );
	
	return $elements;
    }
}