<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_ListGraduate extends Report_Form_StandardSearch
{
    /**
     *
     */
    public function init()
    {
        parent::init();
    
        $elements = array();
    
        $elements[] = $this->createElement('hidden', 'path')
                ->setValue('student-class/list-graduate-report')
                ->setAttrib('class', 'no-clear')
                ->setDecorators(array( 'ViewHelper' ));
    
        $elements[] = $this->createElement('hidden', 'title')
                ->setValue('Relatoriu: Lista Graduadu')
                ->setAttrib('class', 'no-clear')
                ->setDecorators(array( 'ViewHelper' ));
    
        $elements[] = $this->createElement('hidden', 'orientation')
                ->setValue('landscape')
                ->setAttrib('class', 'no-clear')
                ->setDecorators(array( 'ViewHelper' ));
    
        $mapperEducationInsitute = new Register_Model_Mapper_EducationInstitute();
        $rows = $mapperEducationInsitute->listByFilters();
    
        $optEducationInstitute[''] = '';
        foreach ($rows as $row) {
            $optEducationInstitute[$row->id_fefpeduinstitution] = $row->institution;
        }
    
        $elements[] = $this->createElement('select', 'fk_id_fefpeduinstitution')
                ->setDecorators($this->getDefaultElementDecorators())
                ->setLabel('Instituisaun Ensinu')
                ->addMultiOptions($optEducationInstitute)
                ->setRegisterInArrayValidator(false)
                ->setAttrib('class', 'm-wrap span12 chosen');
    
        $mapperScholarityArea = new Register_Model_Mapper_ScholarityArea();
        $sections = $mapperScholarityArea->fetchAll();
    
        $optScholarityArea[''] = '';
        foreach ($sections as $section) {
            $optScholarityArea[$section['id_scholarity_area']] = $section['scholarity_area'];
        }
    
        $elements[] = $this->createElement('select', 'fk_id_scholarity_area')
                ->setDecorators($this->getDefaultElementDecorators())
                ->setAttrib('class', 'm-wrap span12 chosen focused')
                ->setLabel('Area Kursu')
                ->addMultiOptions($optScholarityArea);
    
        $filters = array(
			'type'	=> Register_Model_Mapper_PerTypeScholarity::NON_FORMAL
		);
    
        $mapperScholarity = new Register_Model_Mapper_PerScholarity();
        $optScholarity = $mapperScholarity->getOptionsScholarity($filters);
    
        $elements[] = $this->createElement('select', 'fk_id_perscholarity')
                ->setDecorators($this->getDefaultElementDecorators())
                ->setRegisterInArrayValidator(false)
                ->addMultiOptions($optScholarity)
                ->setAttrib('class', 'm-wrap span12 chosen')
				->setLabel('Kursu');

		$optGender[''] = '';
		$optGender['MANE'] = 'MANE';
		$optGender['FETO'] = 'FETO';
				
		$elements[] = $this->createElement('select', 'gender')
                ->setDecorators($this->getDefaultElementDecorators())
                ->setRegisterInArrayValidator(false)
                ->addMultiOptions($optGender)
                ->setAttrib('class', 'm-wrap span12 chosen')
				->setLabel('Seksu');
    
        $this->addElements($elements);
    }
}
