<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_EnterpriseInformation extends App_Form_Default
{

    const ID = 51;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_fefpenterprise' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			   ->setDecorators( array( 'ViewHelper' ) )
			   ->setAttrib( 'class', 'no-clear' )
			   ->setValue( 'information' );
	
	$elements[] = $this->createElement( 'text', 'enterprise_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Empreza' );
	
	$elements[] = $this->createElement( 'text', 'num_workers' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span8 text-numeric' )
			    ->setLabel( 'Numeru Traballador Serbisu' );
	
	$elements[] = $this->createElement( 'text', 'men_workers' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span8 text-numeric' )
			    ->setLabel( 'Traballador sira Mane nain Hira' );
	
	$elements[] = $this->createElement( 'text', 'women_workers' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span8 text-numeric' )
			    ->setLabel( 'Traballador sira Feto nain Hira' );
	
	$elements[] = $this->createElement( 'text', 'num_handicapped' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span8 text-numeric' )
			    ->setLabel( 'Traballador sira Defisiante nain Hira' );
	
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$optCeop[''] = '';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'CEOP' )
			    ->addMultiOptions( $optCeop )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$dbTypeEnterprise = App_Model_DbTable_Factory::get( 'FEFPTypeEnterprise' );
	$rows = $dbTypeEnterprise->fetchAll( array(), array( 'type_enterprise' ) );
	
	$optType[''] = '';
	foreach ( $rows as $row )
	    $optType[$row->id_fefptypeenterprise] = $row->type_enterprise;
	
	$elements[] = $this->createElement( 'select', 'fk_fefptypeenterprite' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipu Empreza' )
			    ->addMultiOptions( $optType )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$countries = $dbCountry->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $countries as $country )
	    $optCountry[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_nationality' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nasionalidade' )
			    ->addMultiOptions( $optCountry )
			    ->setRequired( true );
	
	$mapperClassTimor = new Register_Model_Mapper_IsicTimor();
	$rows = $mapperClassTimor->listAll();
	
	$optClassTimor[''] = '';
	foreach ( $rows as $row )
	    $optClassTimor[$row->id_isicclasstimor] = $row->name_classtimor;
	
	$elements[] = $this->createElement( 'select', 'fk_id_sectorindustry' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Setor da Industria' )
			    ->addMultiOptions( $optClassTimor )
			     ->setRequired( true );
	
	$elements[] = $this->createElement( 'textarea', 'descrition' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 5 )
			    ->setLabel( 'Deskrisaun' );
	
	$elements[] = $this->createElement( 'textarea', 'condition_handicapped' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 5 )
			    ->setLabel( 'Tipu defisiencia' );
	
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
	if ( !empty( $data['id_fefpenterprise'] ) )
	    $this->getElement( 'fk_id_dec' )->setRequired( false );
	
	return parent::isValid( $data );
    }
}