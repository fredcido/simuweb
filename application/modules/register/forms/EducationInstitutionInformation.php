<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_EducationInstitutionInformation extends App_Form_Default
{

    const ID = 53;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_fefpeduinstitution' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			   ->setDecorators( array( 'ViewHelper' ) )
			   ->setAttrib( 'class', 'no-clear' )
			   ->setValue( 'information' );
	
	$elements[] = $this->createElement( 'text', 'institution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Instituisaun Ensinu' );
	
	$elements[] = $this->createElement( 'text', 'date_visit' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Visita' );
	
	$elements[] = $this->createElement( 'text', 'num_register' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Numeru Rejistu' );
	
	$elements[] = $this->createElement( 'text', 'year_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span6 year-mask' )
			    ->setLabel( 'Tinan Iniciu' );
	
	$elements[] = $this->createElement( 'text', 'date_registration' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Rejistu' );
	
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
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$dbTypeInstitution = App_Model_DbTable_Factory::get( 'TypeInstitution' );
	$rows = $dbTypeInstitution->fetchAll( array(), array( 'type_institution' ) );
	
	$optType[''] = '';
	foreach ( $rows as $row )
	    $optType[$row->id_typeinstitution] = $row->type_institution;
	
	$elements[] = $this->createElement( 'select', 'fk_typeinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipu Instituisaun' )
			    ->addMultiOptions( $optType )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$optRegister[''] = '';
	$optRegister['1'] = 'Sim';
	$optRegister['0'] = 'Lae';
	
	$elements[] = $this->createElement( 'select', 'register' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Rejistu ?' )
			    ->addMultiOptions( $optRegister )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span6' );
	
	$mapperSysUser =  new Admin_Model_Mapper_SysUser();
	$users = $mapperSysUser->listAll();
	
	$optUsers[''] = '';
	foreach ( $users as $user )
	    $optUsers[$user['id_sysuser']] = $user['name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_sysuser' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Usuariu' )
			    ->addMultiOptions( $optUsers );
	
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
	if ( !empty( $data['id_fefpeduinstitution'] ) )
	    $this->getElement( 'fk_id_dec' )->setRequired( false );
	
	return parent::isValid( $data );
    }
}