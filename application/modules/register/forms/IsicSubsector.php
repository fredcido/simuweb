<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_IsicSubsector extends App_Form_Default
{

    const ID = 167;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_isic_subsector' )->setDecorators( array( 'ViewHelper' ) );
	
	$mapperSector =  new Register_Model_Mapper_IsicTimor();
	$sectors = $mapperSector->fetchAll();
	
	$optSector[''] = '';
	foreach ( $sectors as $sector )
	    $optSector[$sector['id_isicclasstimor']] = $sector['acronym'] . ' - ' . $sector['name_classtimor'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicclasstimor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Sektor' )
			    ->addMultiOptions( $optSector )
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'name_subsector' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Subsektor' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'code' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 1 )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setLabel( 'Sigla' )
			    ->setRequired( true );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}