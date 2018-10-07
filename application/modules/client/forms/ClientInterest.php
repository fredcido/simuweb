<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientInterest extends Client_Form_ClientCompetency
{
    public function init()
    {
      $this->setAttrib( 'class', 'horizontal-form' );
      
      $elements = array();	
      
      $elements[] = $this->createElement( 'hidden', 'id_perinterest' )
                        ->setAttrib( 'class', 'no-clear' )
                        ->setDecorators( array( 'ViewHelper' ) );

      $elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
              ->setAttrib( 'class', 'no-clear' )
              ->setDecorators( array( 'ViewHelper' ) );
      
      $elements[] = $this->createElement( 'hidden', 'step' )
              ->setDecorators( array( 'ViewHelper' ) )
              ->setAttrib( 'class', 'no-clear' )
              ->setValue( 'interest' );
      
      $elements[] = $this->createElement( 'textarea', 'comment' )
              ->setDecorators( $this->getDefaultElementDecorators() )
              ->addFilter( 'StringTrim' )
              ->addFilter( 'StringToUpper' )
              ->setAttrib( 'class', 'span12' )
              ->setAttrib( 'cols', 100 )
              ->setRequired( true )
              ->setAttrib( 'rows', 4 )
              ->setLabel( 'Interesse sira' );
      
      App_Form_Toolbar::build( $this, self::ID );
      $this->addElements( $elements );
    }
}