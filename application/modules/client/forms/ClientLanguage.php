<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientLanguage extends Client_Form_ClientCompetency
{
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
			    ->setValue( 'language' );
	
	$dbLanguage = App_Model_DbTable_Factory::get( 'PerLanguage' );
	$languages = $dbLanguage->fetchAll( array(), array( 'language' ) );
	
	$optLanguage[''] = '';
	foreach ( $languages as $language )
	    $optLanguage[$language['id_perlanguage']] = $language['language'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_perlanguage' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Lian Fuan' )
			    ->setRequired( true )
			    ->addMultiOptions( $optLanguage );
	
	$dbLevelKnowledge = App_Model_DbTable_Factory::get( 'PerLevelKnowledge' );
	$levels = $dbLevelKnowledge->fetchAll( array(), array( 'name_level' ) );
	
	$optLevel[''] = '';
	foreach ( $levels as $level )
	    $optLevel[$level['id_levelknowledge']] = $level['name_level'] . ' - ' . $level['description'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_levelknowledge' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nivel Koñesimentu' )
			    ->setRequired( true )
			    ->addMultiOptions( $optLevel );
	
	$optUsage[''] = '';
	$optUsage['KUALIA'] = 'KUALIA';
	$optUsage['HATENE'] = 'HATENE';
	$optUsage['HAKEREK'] = 'HAKEREK';
	$optUsage['LE'] = 'HATENE LE';
	
	$elements[] = $this->createElement( 'select', 'usage' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Utilizasaun' )
			    ->setRequired( true )
			    ->addMultiOptions( $optUsage );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}