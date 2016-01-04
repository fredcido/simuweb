<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_CaseGroup extends App_Form_Default
{
    
    const ID = 159;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'id_action_plan_group' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'step' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' )->setValue( 'caseGroup' );
	
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$rows = $dbUser->fetchAll( array( 'fk_id_dec = ?' => Zend_Auth::getInstance()->getIdentity()->fk_id_dec ), array( 'name' ) );
	
	$users[''] = '';
	foreach ( $rows as $row )
	    $users[$row->id_sysuser] = $row->name . ' (' . $row->login . ')';
	
	$elements[] = $this->createElement( 'select', 'fk_id_counselor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->setLabel( 'Konselleru' )
			    ->addMultiOptions( $users );
	
	$mapperEducationInsitute = new Register_Model_Mapper_EducationInstitute();
	$rows = $mapperEducationInsitute->listByFilters();
	
	$optEducationInstitute[''] = '';
	foreach ( $rows as $row )
	    $optEducationInstitute[$row->id_fefpeduinstitution] = $row->institution;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Instituisaun Ensinu' )
			    ->addMultiOptions( $optEducationInstitute )
			    ->setAttrib( 'onchange', 'Client.CaseGroup.setRequiredGroup( this.value, "E" )' )
			    ->setRegisterInArrayValidator( false )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$countries = $dbCountry->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $countries as $country )
	    $optCountry[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_addcountry' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setAttrib( 'onchange', 'Client.CaseGroup.setRequiredGroup( this.value, "C" )' )
			    ->setLabel( 'Nasaun' )
			    ->addMultiOptions( $optCountry )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 50 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
    
    /**
     * 
     */
    public function isValid( $data )
    {
	if ( !empty( $data['id_action_plan'] ) )
	    $this->getElement( 'fk_id_counselor' )->setRequired( false );
	
	if ( !empty( $data['fk_id_addcountry'] ) )
	    $this->getElement( 'fk_id_addcountry' )->setRequired( false );
	else
	    $this->getElement( 'fk_id_addcountry' )->setRequired( false );
	    
	
	return parent::isValid( $data );
    }
}