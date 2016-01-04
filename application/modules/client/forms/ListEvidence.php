<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ListEvidence extends App_Form_Default
{

    const ID = 125;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_job_list' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'text', 'list_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setAttrib( 'disabled', true )
			    ->setLabel( 'Naran Lista' );
	
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$user = Zend_Auth::getInstance()->getIdentity();
	
	$optCeop[''] = '';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'CEOP' )
			    ->addMultiOptions( $optCeop )
			    ->setRequired( true )
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
			    ->setRequired( true )
			    ->setLabel( 'Uzuariu' )
			    ->setValue( $user->id_sysuser )
			    ->addMultiOptions( $users );
	
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
	if ( !empty( $data['id_job_list'] ) ) {
	 
	    $this->getElement( 'fk_id_user' )->setRequired( false );
	    $this->getElement( 'fk_id_dec' )->setRequired( false );
	}
	
	return parent::isValid( $data );
    }
    
    /**
     * 
     */
    public function setPrintMode()
    {
	$toolbar = $this->getDisplayGroup( 'toolbar' );
	$toolbar->clearElements();
	
	$printButton = $this->createElement( 'button', 'print_list' )
			     ->setAttrib( 'type', 'button' )
			     ->setAttrib( 'onClick', 'Client.ListEvidence.printList();' )
			     ->setDecorators( array( 'ViewHelper' ) )
			     ->setAttrib( 'class', 'btn green' )
			     ->setLabel( 'Imprime' );
	
	$toolbar->addElement( $printButton );
    }
}