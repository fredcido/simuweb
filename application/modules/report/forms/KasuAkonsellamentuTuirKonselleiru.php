<?php

class Report_Form_KasuAkonsellamentuTuirKonselleiru extends Report_Form_StandardSearch
{
    /**
     * @var string
     */
    const PATH = 'client/kasu-akonsellamentu-tuir-konselleiru-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Kasu Akonsellamentu Tuir Konselleiru';
    
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
    	
    	$optKonselleiru = array();
    	array_unshift($optKonselleiru, '');
    	
    	$dbSysUser = App_Model_DbTable_Factory::get('SysUser');
    	
    	$rows = $dbSysUser->fetchAll(array('active = ?' => 1));
    	
    	foreach ($rows as $row) {
    		$optKonselleiru[$row->id_sysuser] = $row->name;
    	}
    	
    	$elements[] = $this->createElement('multiselect', 'fk_id_counselor')
        	->setDecorators($this->getDefaultElementDecorators())
        	->setLabel('Konselleiru')
        	->addMultiOptions($optKonselleiru)
        	->setAttrib('class', 'm-wrap span12 chosen');
    	
    	$this->getElement('fk_id_dec')->setRequired(false);
    	
    	$this->addElements($elements);
    }
}