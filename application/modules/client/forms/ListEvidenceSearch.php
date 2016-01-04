<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ListEvidenceSearch extends Client_Form_ListEvidence
{

    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'text', 'list_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 100 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Lista' );
	
	$user = Zend_Auth::getInstance()->getIdentity();
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$optCeop[''] = '';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'CEOP' )
			    ->addMultiOptions( $optCeop )
			    ->setValue( $user->fk_id_dec )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$rows = $dbUser->fetchAll( array(), array( 'name' ) );
	
	$users[''] = '';
	foreach ( $rows as $row )
	    $users[$row->id_sysuser] = $row->name . ' (' . $row->login . ')';
	
	$elements[] = $this->createElement( 'select', 'fk_id_user' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Uzuariu' )
			    ->setValue( $user->id_sysuser )
			    ->addMultiOptions( $users );
	
	$optPrinted[''] = '';
	$optPrinted['1'] = 'Tiha ona';
	$optPrinted['0'] = 'Seidauk';
	
	$elements[] = $this->createElement( 'select', 'printed' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Print' )
			    ->addMultiOptions( $optPrinted );
	
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
	
	$this->addElements( $elements );
    }
}