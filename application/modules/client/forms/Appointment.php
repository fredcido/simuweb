<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_Appointment extends App_Form_Default
{

    const ID = 142;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'id_appointment' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'objective' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'fk_id_action_plan' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$rows = $dbUser->fetchAll( array( 'fk_id_dec = ?' => Zend_Auth::getInstance()->getIdentity()->fk_id_dec ), array( 'name' ) );
	
	$users[''] = '';
	foreach ( $rows as $row )
	    $users[$row->id_sysuser] = $row->name . ' (' . $row->login . ')';
	
	$elements[] = $this->createElement( 'select', 'fk_id_counselor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->setLabel( 'Ho Jestor Kazu' )
			    ->addMultiOptions( $users );
	
	$elements[] = $this->createElement( 'select', 'fk_id_sysuser' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->setLabel( 'Ema neebe halo' )
			    ->addMultiOptions( $users );
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$optCeop[''] = '';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Distritu' )
			    ->addMultiOptions( $optCeop )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$elements[] = $this->createElement( 'text', 'date_appointment' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data ba audiensia' );
	
	$elements[] = $this->createElement( 'text', 'time_appointment' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 time-mask' )
			    ->setLabel( 'Oras' );
	
	$elements[] = $this->createElement( 'textarea', 'appointment_desc' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setRequired( true )
			    ->setAttrib( 'rows', 2 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Deskrisaun' );
	
	$elements[] = $this->createElement( 'checkbox', 'appointment_filled' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Kliente mai?' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}