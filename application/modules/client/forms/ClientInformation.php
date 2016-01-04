<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientInformation extends App_Form_Default
{

    const ID = 3;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_perdata' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			   ->setDecorators( array( 'ViewHelper' ) )
			   ->setAttrib( 'class', 'no-clear' )
			   ->setValue( 'information' );
	
	$elements[] = $this->createElement( 'hidden', 'by_pass_name' )
			   ->setDecorators( array( 'ViewHelper' ) )
			   ->setValue( '0' );
	
	$elements[] = $this->createElement( 'text', 'first_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 80 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Primeiru' );
	
	$elements[] = $this->createElement( 'text', 'medium_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 80 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Segundu' );
	
	$elements[] = $this->createElement( 'text', 'last_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 80 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Apelidu' );
	
	$elements[] = $this->createElement( 'text', 'date_registration' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setValue( Zend_Date::now()->toString( 'dd/MM/yyyy' ) )
			    ->setLabel( 'Data Rejistu' );
	
	$optMaritalStatus['KABENNAIN'] = 'KABENNAIN';
	$optMaritalStatus['SOLTEIRU'] = 'SOLTEIRU';
	$optMaritalStatus['DIVORS'] = 'DIVORS';
	$optMaritalStatus['FALUK'] = 'FALUK';
	
	$elements[] = $this->createElement( 'select', 'marital_status' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Estadu Sivil' )
			    ->addMultiOptions( $optMaritalStatus );
	
	$elements[] = $this->createElement( 'text', 'father_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 80 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Aman' );
	
	$elements[] = $this->createElement( 'text', 'birth_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    //->setAttrib( 'readOnly', true )
			    ->setLabel( 'Data Moris' );
	
	$elements[] = $this->createElement( 'text', 'age' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span6' )
			    ->setAttrib( 'readOnly', 'true' )
			    ->setLabel( 'Tinan' );
	
	$elements[] = $this->createElement( 'text', 'mother_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 80 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Inan' );
	
	$optGender['FETO'] = 'FETO';
	$optGender['MANE'] = 'MANE';
	
	$elements[] = $this->createElement( 'select', 'gender' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Seksu' )
			    ->setRequired( true )
			    ->addMultiOptions( $optGender );
	
	$mapperCountry =  new Register_Model_Mapper_AddCountry();
	$countries = $mapperCountry->fetchAll();
	
	$optNations[''] = '';
	foreach ( $countries as $country )
	    $optNations[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_country_birth' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->setLabel( 'Nasionalidade' )
			    ->addMultiOptions( $optNations );
	
	// List Districts just from Timor
	$mapperDistrict = new Register_Model_Mapper_AddDistrict();
	$districts = $mapperDistrict->listAll( 1 );
	
	$optDistrict[''] = '';
	foreach ( $districts as $district )
	    $optDistrict[$district->id_adddistrict] = $district->District;
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optDistrict )
			    ->setLabel( 'Distritu Kliente' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'num_subdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true )
			    ->setLabel( 'Sub-Distritu' );
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$optCeop[''] = '';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Rejistu iha CEOP' )
			    ->addMultiOptions( $optCeop )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$elements[] = $this->createElement( 'text', 'client_fone' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 15 )
			    ->setAttrib( 'class', 'm-wrap span12 phone-mask' )
			    ->setLabel( 'Telefone' );
	
	$elements[] = $this->createElement( 'text', 'website' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 250 )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Website' );
	
	$elements[] = $this->createElement( 'text', 'email' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 250 )
			    ->addFilter( 'StringTrim' )
			    ->addValidator( 'EmailAddress' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'E-mail' );
	
	$elements[] = $this->createElement( 'checkbox', 'active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 1 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Ativu?' );
	
	$elements[] = $this->createElement( 'checkbox', 'hired' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Iha serbisu?' );
	
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
	if ( !empty( $data['id_perdata'] ) ) {
	    
	    $this->getElement( 'date_registration' )->setRequired( false );
	    $this->getElement( 'fk_id_adddistrict' )->setRequired( false );
	    $this->getElement( 'num_subdistrict' )->setRequired( false );
	    
	} else {
	    
	    if ( $data['age'] >= 16 ) {
	    
		$clientDocument = new Client_Form_ClientDocument();
		$elements = $clientDocument->getDocumentElements();

		$this->addElements( $elements );
	    } 
	}
	
	return parent::isValid( $data );
    }
}