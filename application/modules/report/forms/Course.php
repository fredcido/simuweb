<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_Course extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$this->removeElement( 'date_start' );
	$this->removeElement( 'date_finish' );
	$this->removeElement( 'fk_id_dec' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'register/course-report' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Rejistu Kursu' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'orientation' )
			    ->setValue( 'landscape' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperTypeScholarity = new Register_Model_Mapper_PerTypeScholarity();
	$sections = $mapperTypeScholarity->fetchAll();
	
	$optTypeScholarity[''] = '';
	foreach ( $sections as $section )
	    $optTypeScholarity[$section['id_pertypescholarity']] = $section['type_scholarity'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_pertypescholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Kursu' )
			    ->addMultiOptions( $optTypeScholarity );
	
	$mapperScholarityArea = new Register_Model_Mapper_ScholarityArea();
	$sections = $mapperScholarityArea->fetchAll();
	
	$optScholarityArea[''] = '';
	foreach ( $sections as $section )
	    $optScholarityArea[$section['id_scholarity_area']] = $section['scholarity_area'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_scholarity_area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen focused' )
			    ->setLabel( 'Area Kursu' )
			    ->addMultiOptions( $optScholarityArea );
	
	$dbLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	$rows = $dbLevelScholarity->fetchAll( array(), array( 'id_perlevelscholarity' ) );
	
	$optLevelScholarity[''] = '';
	foreach ( $rows as $row )
	    $optLevelScholarity[$row['id_perlevelscholarity']] = $row['level_scholarity'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_perlevelscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addMultiOptions( $optLevelScholarity )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nivel' );
	
	$optCategory[''] = '';
	$optCategory['S'] = 'Eskola';
	$optCategory['U'] = 'Superior';
	$optCategory['V'] = 'Formasaun Teknika Profisional';
	$optCategory['C'] = 'Formasaun Comunitaria';
	$optCategory['N'] = 'Formasaun Profisional';
	
	$elements[] = $this->createElement( 'select', 'category_school' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optCategory )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Kategoria' );
	
	$this->addElements( $elements );
    }
}