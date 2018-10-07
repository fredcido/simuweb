<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_CaseTimeline extends App_Form_Default
{
    public function init()
    {
        $this->setAttrib('class', 'horizontal-form');

        $elements = array();

        $elements[] = $this->createElement('hidden', 'id_action_plan_timeline')->setDecorators(array('ViewHelper'));
        $elements[] = $this->createElement('hidden', 'fk_id_action_plan')->setDecorators(array('ViewHelper'))->setAttrib('class', 'no-clear');

        $elements[] = $this->createElement('textarea', 'description')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addFilter('StringTrim')
            ->addFilter('StringToUpper')
            ->setAttrib('rows', 2)
            ->setRequired(true)
            ->setAttrib('class', 'm-wrap span12')
						->setLabel('Deskrisaun');
						
				$elements[] = $this->createElement( 'text', 'institution' )
						->setDecorators( $this->getDefaultElementDecorators() )
						->setAttrib( 'maxlength', 120 )
						->addFilter( 'StringTrim' )
						->addFilter( 'StringToUpper' )
						->setAttrib( 'class', 'm-wrap span12' )
						->setLabel( 'Instituisaun' );

				$elements[] = $this->createElement( 'text', 'date_start' )
						->setDecorators( $this->getDefaultElementDecorators() )
						->setAttrib( 'maxlength', 10 )
						->setRequired( true )
						->setAttrib( 'class', 'm-wrap span12 date-mask date' )
						->setLabel( 'Loron Inisiu' );
		
				$elements[] = $this->createElement( 'text', 'date_end' )
						->setDecorators( $this->getDefaultElementDecorators() )
						->setAttrib( 'maxlength', 10 )
						->setAttrib( 'class', 'm-wrap span12 date-mask date' )
						->setLabel( 'Loron Planu Remata' );

        App_Form_Toolbar::build($this, Client_Form_ActionPlan::ID);
        $this->addElements($elements);
    }
}
