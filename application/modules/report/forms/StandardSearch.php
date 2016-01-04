<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_StandardSearch extends App_Form_Default
{

    /**
     * 
     */
    public function init()
    {
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'text', 'date_start' )
		    ->setDecorators( $this->getDefaultElementDecorators() )
		    ->setAttrib( 'maxlength', 10 )
		    ->setAttrib( 'class', 'span12 focused date-mask' )
		    ->setLabel( 'Husi' )
		    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
		    ->setDecorators( $this->getDefaultElementDecorators() )
		    ->setAttrib( 'maxlength', 10 )
		    ->setAttrib( 'class', 'span12 focused date-mask' )
		    ->setLabel( 'To\'o' )
		    ->setRequired( true );
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$optCeop['0'] = 'HOTU-HOTU';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'CEOP' )
			    ->addMultiOptions( $optCeop )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$save = $this->createElement( 'submit', 'save' )
			     ->setDecorators( array( 'ViewHelper' ) )
			     ->setAttrib( 'class', 'btn blue' )
			     ->setLabel( 'Haree Relatoriu' );
	
	$clear = $this->createElement( 'reset', 'clear' )
			     ->setDecorators( array( 'ViewHelper' ) )
			     ->setAttrib( 'class', 'btn green' )
			     ->setLabel( 'Hamoos' );
	
	$this->addDisplayGroup( array( $save, $clear ), 'toolbar' );
	$displayGroup = $this->getDisplayGroup( 'toolbar' );
	$displayGroup->setDecorators( $this->_toolbarDecorator );   
	
	$this->addElements( $elements );
    }
}