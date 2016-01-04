<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_BeneficiaryBlacklistSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$dbFefopPrograms = App_Model_DbTable_Factory::get( 'FEFOPPrograms' );
	$programs = $dbFefopPrograms->fetchAll();
	
	$optPrograms[''] = '';
	foreach ( $programs as $program )
	    $optPrograms[$program['id_fefop_programs']] = $program['acronym'] . ' - '. $program['description'];
	
	$elements[] = $this->createElement( 'select', 'id_fefop_programs' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Programa FEFOP' )
			    ->addMultiOptions( $optPrograms );
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefop_modules' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Modulu FEFOP' )
			    ->setRegisterInArrayValidator( false );
	
	$elements[] = $this->createElement( 'text', 'date_registration_ini' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Rejistu Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'date_registration_fim' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Rejistu Final' );
	
	$elements[] = $this->createElement( 'text', 'beneficiary_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Benefisiraiu' );
	
	$optStatus[''] = '';
	$optStatus['1'] = 'Loos';
	$optStatus['0'] = 'Lae';
	
	$elements[] = $this->createElement( 'select', 'status' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Ativu?' )
			    ->addMultiOptions( $optStatus );
	
	$mapperSysUser =  new Admin_Model_Mapper_SysUser();
	$users = $mapperSysUser->listAll();
	
	$optUsers[''] = '';
	foreach ( $users as $user )
	    $optUsers[$user['id_sysuser']] = $user['name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_user_inserted' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Usuariu Rejistu' )
			    ->addMultiOptions( $optUsers );
	
	$elements[] = $this->createElement( 'select', 'fk_id_user_removed' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Usuariu Libera' )
			    ->addMultiOptions( $optUsers );
	
	$optTypeBeneficiary[''] = '';
	$optTypeBeneficiary['fk_id_perdata'] = 'Kliente';
	$optTypeBeneficiary['fk_id_staff'] = 'Empreza Staff';
	$optTypeBeneficiary['fk_id_fefpeduinstitution'] = 'Inst. Ensinu';
	$optTypeBeneficiary['fk_id_fefpenterprise'] = 'Empreza';
	
	$elements[] = $this->createElement( 'select', 'type_beneficiary' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Benefisiariu' )
			    ->addMultiOptions( $optTypeBeneficiary );
	
	$this->addElements( $elements );
    }
}