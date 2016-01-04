<?php

/**
 * 
 * @version $Id: TrainingCountry.php 412 2014-10-10 09:55:37Z frederico $
 */
class Report_Form_TrainingCountry extends Report_Form_StandardSearchFefop
{
    /**
     * @var string
     */
    const PATH = 'fefop/training-country-report';
    
    /**
     * @var string
     */
    const TITLE = 'Relatoriu: Formasaun Husi nasaun';
    
    /**
     * @access public
     * @return void
     */
    public function init()
    {
        parent::init();
        
        $this->removeElement('id_fefop_programs');
        $this->removeElement('id_fefop_modules');
        $this->removeElement('id_adddistrict');
        $this->removeElement('id_fefop_status');
        
        //Área de Formação
        $rows = App_Model_DbTable_Factory::get('ScholarityArea')->fetchAll();
        
        $optScholarityArea = array();
        
        array_unshift($optScholarityArea, '');
        
        foreach ($rows as $row) {
        	$optScholarityArea[$row->id_scholarity_area] = (empty($row->acronym) ? '' : $row->acronym . ' - ') . $row->scholarity_area;
        }
        
        $elements[] = $this->createElement('select', 'id_scholarity_area')
            ->setLabel('Area Formasaun')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optScholarityArea);
        
        //Ocupação
        $dbPROFOcupationTimor = App_Model_DbTable_Factory::get('PROFOcupationTimor');
        $dbDRHTrainingPlan = App_Model_DbTable_Factory::get('DRHTrainingPlan');
        
        $select = $dbPROFOcupationTimor->select()
            ->setIntegrityCheck(false)
            ->from(
                $dbPROFOcupationTimor->__toString(),
                array('id_profocupationtimor', 'acronym', 'ocupation_name_timor')
            )
            ->join(
                $dbDRHTrainingPlan->__toString(),
                'DRH_TrainingPlan.fk_id_profocupationtimor = PROFOcupationTimor.id_profocupationtimor',
                array()
            );
        
        $rows = $dbPROFOcupationTimor->fetchAll($select);
        
        $optOccupation = array();
        
        array_unshift($optOccupation, '');
        
        foreach ($rows as $row) {
        	$optOccupation[$row->id_profocupationtimor] = $row->acronym . ' - ' . $row->ocupation_name_timor;
        }
        
        $elements[] = $this->createElement('select', 'id_profocupationtimor')
            ->setLabel('Okupasaun')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optOccupation);
        
        $this->addElements($elements);
        
        //Instituição
        $dbFefpEduInstitution = App_Model_DbTable_Factory::get('FefpEduInstitution');
        
        $select = $dbFefpEduInstitution->select()
            ->setIntegrityCheck(false)
            ->from(
                $dbFefpEduInstitution->__toString(),
                array('id_fefpeduinstitution', 'institution')
            )
            ->join(
                $dbDRHTrainingPlan->__toString(),
                'DRH_TrainingPlan.fk_id_fefpeduinstitution = FefpEduInstitution.id_fefpeduinstitution',
                array()
            );
        
        $rows = $dbFefpEduInstitution->fetchAll($select);
        
        $optInstitution = array();
        
        array_unshift($optInstitution, '');
        
        foreach ($rows as $row) {
        	$optInstitution[$row->id_fefpeduinstitution] = $row->institution;
        }
        
        $elements[] = $this->createElement('select', 'id_fefpeduinstitution')
            ->setLabel('Instituisaun')
            ->setRegisterInArrayValidator(false)
            ->setAttrib('class', 'm-wrap span12 chosen')
            ->setDecorators($this->getDefaultElementDecorators())
            ->addMultiOptions($optInstitution);
        
        $this->addElements($elements);
        
        $this->getElement('path')->setValue(self::PATH);
        $this->getElement('title')->setValue(self::TITLE);
    }
}