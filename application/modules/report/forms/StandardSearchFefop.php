<?php

/**
 * 
 * @version $Id: StandardSearchFefop.php 504 2014-12-03 05:04:30Z frederico $
 */
class Report_Form_StandardSearchFefop extends Report_Form_StandardSearch
{
    /**
     * @see Report_Form_StandardSearch::init()
     */
    public function init()
    {
        parent::init();
                
        $elements = array();
        
        $elements[] = $this->createElement('hidden', 'path')
            ->setAttrib('class', 'no-clear')
            ->setDecorators(array('ViewHelper'));
        
        $elements[] = $this->createElement('hidden', 'title')
            ->setAttrib('class', 'no-clear')
            ->setDecorators(array('ViewHelper'));
                
        //CEOP
        $optCeop = $this->getElement('fk_id_dec')->getMultiOptions();
        
        $this->removeElement('fk_id_dec');
                
        $elements[] = $this->createElement('multiselect', 'fk_id_dec')
            ->setDecorators($this->getDefaultElementDecorators())
            ->setLabel('CEOP')
            ->addMultiOptions($optCeop)
            ->setRequired(true)
            ->setAttrib('class', 'm-wrap span12 chosen');
        
        //Programs
        $rows = App_Model_DbTable_Factory::get('FEFOPPrograms')->fetchAll();
        
        $optProgram = array();
        
        array_unshift($optProgram, '');
        
        foreach ($rows as $row) {
            $optProgram[$row->id_fefop_programs] = $row->acronym . ' - ' . $row->description;
        }
        
        $elements[] = $this->createElement('multiselect', 'id_fefop_programs')
            ->setLabel('Programa')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optProgram);
        
        //Modules
        $rows = App_Model_DbTable_Factory::get('FEFOPModules')->fetchAll();
        
        $optModule = array();
        
        array_unshift($optModule, '');
        
        foreach ($rows as $row) {
            $optModule[$row->id_fefop_modules] = $row->acronym . ' - ' . $row->description;
        }
        
        $elements[] = $this->createElement('multiselect', 'id_fefop_modules')
            ->setLabel('Modulu')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optModule);
        
        $this->addElements($elements);
        
        //Districts
        $rows = App_Model_DbTable_Factory::get('AddDistrict')->fetchAll();
        
        $optDistrito = array();
        
        array_unshift($optDistrito, '');
        
        foreach ($rows as $row) {
        	$optDistrito[$row->id_adddistrict] = $row->District;
        }
        
        $elements[] = $this->createElement('multiselect', 'id_adddistrict')
            ->setLabel('Distritu')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optDistrito);
        
        //Status
        $rows = App_Model_DbTable_Factory::get('FEFOPStatus')->fetchAll(null, array('order ASC'));
        
        $optStatus = array();
        array_unshift($optStatus, '');
        
        foreach ($rows as $row) {
        	$optStatus[$row->id_fefop_status] = $row->status_description;
        }
        
        $elements[] = $this->createElement('multiselect', 'id_fefop_status')
            ->setLabel('Status')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optStatus);
        
        $this->addElements($elements);
    }
}