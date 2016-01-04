<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_TechnicalFeedback extends Fefop_Form_PCEContract
{
    /**
     *
     * @var array
     */
    protected $_customDecorators = array( 
				'ViewHelper',
				'Errors',
				array( 'Description', array( 'tag' => 'span', 'class' => 'help-inline' ) ),
				array( array( 'wrapper' => 'HtmlTag' ), array( 'tag' => 'div', 'class' => 'controls' ) ),
				array( 'Label', array( 'class' => 'control-label' ) ),
				array( array( 'row' => 'HtmlTag' ), array( 'tag' => 'div', 'class' => 'control-group' ) )
			    );
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_businessplan' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'checkbox', 'expenses' )
			    ->setDecorators( $this->_customDecorators )
			    ->setCheckedValue( 1 )
			    ->setDescription( "Responsabilidade husi tékniku Auto-empregu analiza bainhira mak despeza sira iha planu negósiu halo parate iha rúbrika elejível ne'ebé aprezenta (Ez: karik akizisaun viatura tau iha rúbrika ida válida nia okos, hanesan ekipamentu informátiku, entaun labele simu) - (manuál)." )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Elijibilidade kona-ba despeza sira' );
	
	$elements[] = $this->createElement( 'checkbox', 'other_sponsors' )
			    ->setDecorators( $this->_customDecorators )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setDescription( " Karik SEOP simu informasaun kona-ba atividade ne'e  hatan ona/atu hetan apoiu husi entidade seluk entaun status  labele simu(manuál)" )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Apoiu finanseiru sira seluk' );
	
	$elements[] = $this->createElement( 'checkbox', 'approved' )
			    ->setDecorators( $this->_customDecorators )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->removeDecorator( 'Label' )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Rekomendasaun' );
	
	$elements[] = $this->createElement( 'checkbox', 'amount' )
			    ->setDecorators( $this->_customDecorators )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Valór másimu' );
	
	$elements[] = $this->createElement( 'checkbox', 'previous_visit' )
			    ->setDecorators( $this->_customDecorators )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setLabel( 'Vizita Prévia' );
	
	$elements[] = $this->createElement( 'textarea', 'business_plan' )
			    ->setDecorators( $this->_customDecorators )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setLabel( 'Planu Negósiu nian' );
	
	$elements[] = $this->createElement( 'textarea', 'viability' )
			    ->setDecorators( $this->_customDecorators )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'rows', 3 )
			    ->setLabel( 'Viabilidade' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

    /**
     * 
     * @param array $fields
     */
    public function addAutomaticFields( $fields )
    {
	$elements = array();
	foreach ( $fields as $field => $value ) {
	    
	    $element = $this->createElement( 'checkbox', $field )
			    ->setDecorators( $this->_customDecorators )
			    ->setCheckedValue( 1 )
			    ->setUncheckedValue( 0 )
			    ->setValue( $value['value'] )
			    ->setAttrib( 'class', 'toggle-check' )
			    ->setAttrib( 'disabled', true )
			    ->setLabel( $value['label'] );
	    
	    if ( !empty( $value['description'] ) )
		$element->setDescription( $value['description'] );
	    
	    $elements[] = $element;
	}
	
	$this->addDisplayGroup( $elements, 'automatic_fields' );
    }
}