<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_JobListShortlist extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setValue( 'job/list-shortlist-report' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setValue( 'Relatoriu: List Shortlist' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'orientation' )
			    ->setValue( 'landscape' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$optTransport['1'] = 'Loke';
	$optTransport['0'] = 'Taka';
	$optTransport['2'] = 'Kansela';
	
	$elements[] = $this->createElement( 'select', 'active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Status' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optTransport )
			    ->setValue( 1 )
			    ->setRequired( true );
	
	$optHired[''] = '';
	$optHired['S'] = 'Sim';
	$optHired['L'] = 'Lae';
	
	$elements[] = $this->createElement( 'select', 'hired' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Hetan Serbisu' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optHired );
	
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$rows = $dbUser->fetchAll( array(), array( 'name' ) );
	
	$users[''] = '';
	foreach ( $rows as $row )
	    $users[$row->id_sysuser] = $row->name . ' (' . $row->login . ')';
	
	$elements[] = $this->createElement( 'select', 'fk_id_sysuser' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Se mak refere' )
			    ->addMultiOptions( $users );
	
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$occupations = $dbOccupationTimor->fetchAll();
	
	$optOccupations[''] = '';
	foreach ( $occupations as $occupation )
	    $optOccupations[$occupation['id_profocupationtimor']] = $occupation['acronym'] . ' ' . $occupation['ocupation_name_timor'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_profocupation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Okupasaun' )
			    ->addMultiOptions( $optOccupations );
	
	$mapperEnterprise = new Register_Model_Mapper_Enterprise();
	$rows = $mapperEnterprise->listByFilters();
	
	$optEnterprise[''] = '';
	foreach ( $rows as $row )
	    $optEnterprise[$row->id_fefpenterprise] = $row->enterprise_name;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpenterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Empreza' )
			    ->addMultiOptions( $optEnterprise )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$this->addElements( $elements );
    }
}