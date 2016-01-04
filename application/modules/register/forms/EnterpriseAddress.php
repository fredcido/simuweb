<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_EnterpriseAddress extends App_Form_Default
{

    const ID = 83;
    
    const TIMOR_LESTE = 1;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpenterprise' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'id_addaddress' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'address' );
	
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$countries = $dbCountry->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $countries as $country )
	    $optCountry[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_addcountry' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nasaun' )
			    ->addMultiOptions( $optCountry )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Distritu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsubdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Sub-Distritu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsucu' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Suku' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'street' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Dalan' );
	
	$elements[] = $this->createElement( 'text', 'number' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 50 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Numeru' );
	
	$elements[] = $this->createElement( 'text', 'postal_code' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 50 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Kodiu Postal' );
	
	$elements[] = $this->createElement( 'text', 'village' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Vila' );
	
	$elements[] = $this->createElement( 'text', 'start_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readonly', true )
			    //->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Data Iniciu' );
	
	$elements[] = $this->createElement( 'text', 'finish_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'readonly', true )
			    //->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Data Fim' );
	
	$elements[] = $this->createElement( 'textarea', 'complement' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 3 )
			    ->setLabel( 'Komplementu' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
    
    /**
     *
     * @param array $data
     * @return boolean 
     */
    public function isValid( $data )
    {
	if ( $data['fk_id_addcountry'] != self::TIMOR_LESTE ) {
	    
	    foreach ( $this->getElements() as $element )
		$element->setRequired( false );
	    
	    $requireds = array( 'fk_id_addcountry', 'complement' );
	    foreach ( $requireds as $id )
		$this->getElement ( $id )->setRequired( true );
	}
	
	return parent::isValid( $data );
    }
}