<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientBank extends App_Form_Default
{
    const ID = 164;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'bank' );
	
	$elements[] = $this->createElement( 'text', 'num_bankaccount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRequired( true )
			    ->setLabel( 'Numeru' );
	
	$dbBank = App_Model_DbTable_Factory::get( 'Bank' );
	$rows = $dbBank->fetchAll();
	
	$optBank[''] = '';
	foreach ( $rows as $bank )
	    $optBank[$bank['id_bank']] = $bank['name_bank'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_bank' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Banku' )
			    ->addMultiOptions( $optBank )
			    ->setRequired( true );
	
	/*
	$dbTypeBankAccount = App_Model_DbTable_Factory::get( 'TypeBankAccount' );
	$rows = $dbTypeBankAccount->fetchAll();
	
	$optTypeBankAccount[''] = '';
	foreach ( $rows as $row )
	    $optTypeBankAccount[$row['id_typebankaccount']] = $row['type_bankaccount'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_typebankaccount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Konta Banku' )
			    ->addMultiOptions( $optTypeBankAccount )
			    ->setRequired( true );
	 */
	
	$dbAddDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$rows = $dbAddDistrict->fetchAll();
	
	$optDistrict[''] = '';
	foreach ( $rows as $row )
	    $optDistrict[$row['id_adddistrict']] = $row['District'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Distritu' )
			    ->addMultiOptions( $optDistrict )
			    ->setRequired( true );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}