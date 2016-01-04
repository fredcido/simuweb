<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientAbout extends Client_Form_ClientContact
{
    
    /**
     * 
     */
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
			    ->setValue( 'about' );
	
	$optAbout[''] = '';
	$optAbout['HUSI KOLEGA/MEMBRU FAMILIA'] = 'HUSI KOLEGA/MEMBRU FAMILIA';
	$optAbout['HUSI KOMUNIDADE'] = 'HUSI KOMUNIDADE';
	$optAbout['HUSI RADIO, TV, JORNAL'] = 'HUSI RADIO, TV, JORNAL';
	$optAbout['HUSI ESKOLA/UNIVERSIDADE'] = 'HUSI ESKOLA/UNIVERSIDADE';
	$optAbout['HUSI KONSELLEIRO CEOP NIAN'] = 'HUSI KONSELLEIRO CEOP NIAN';
	$optAbout['HUSI QUADRO INFORMASAUN PÚBLIKU NIAN'] = 'HUSI QUADRO INFORMASAUN PÚBLIKU NIAN';
	
	$elements[] = $this->createElement( 'select', 'learn_option' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Ita hatene CEOP husi ne\'be?' )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->addMultiOptions( $optAbout );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}