<?php

class Report_Form_NumeruPlanuTuirAsaunCeop extends Report_Form_StandardSearch
{
    /**
     * @var string
     */
    const PATH = 'client/numeru-planu-tuir-asaun-ceop-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Numeru Planu Tuir Asaun CEOP';
    
    /**
     * @access public
     * @return void
     */
    public function init()
    {
    	parent::init();
    	
    	$elements = array();
    	 
    	$elements[] = $this->createElement('hidden', 'path')
        	->setAttrib('class', 'no-clear')
        	->setValue(self::PATH)
        	->setDecorators(array('ViewHelper'));
    	 
    	$elements[] = $this->createElement('hidden', 'title')
        	->setAttrib('class', 'no-clear')
        	->setValue(self::TITLE)
        	->setDecorators(array('ViewHelper'));
    	
    	$this->getElement('fk_id_dec')->setRequired(false);
    	 
    	$this->addElements($elements);
    }
}