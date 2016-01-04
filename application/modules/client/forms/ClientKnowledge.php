<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientKnowledge extends Client_Form_ClientCompetency
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
			    ->setValue( 'knowledge' );
	
	$dbTypeLanguage = App_Model_DbTable_Factory::get( 'PerTypeKnowledge' );
	$typesKnowledge = $dbTypeLanguage->fetchAll( array(), array( 'type_knowledge' ) );
	
	$optTypeKnowledges[''] = '';
	foreach ( $typesKnowledge as $typeKnowledge )
	    $optTypeKnowledges[$typeKnowledge['id_pertypeknowledge']] = $typeKnowledge['type_knowledge'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_pertypeknowlegde' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Konesimentu' )
			    ->setRequired( true )
			    ->addMultiOptions( $optTypeKnowledges );
	
	$dbKnowledge = App_Model_DbTable_Factory::get( 'PerKnowledge' );
	$knowledges = $dbKnowledge->fetchAll( array(), array( 'name_knowledge' ) );
	
	$optKnowledge[''] = '';
	foreach ( $knowledges as $knowledge )
	    $optKnowledge[$knowledge['id_perknowledge']] = $knowledge['name_knowledge'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_perknowledge' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Koñesimentu' )
			    ->setRequired( true )
			    ->addMultiOptions( $optKnowledge );
	
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
	
	$elements[] = $this->createElement( 'textarea', 'comment' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 4 )
			    ->setLabel( 'Komentariu' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}