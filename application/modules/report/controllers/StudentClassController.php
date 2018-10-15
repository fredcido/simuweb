<?php

class Report_StudentClassController extends Zend_Controller_Action
{
    
    /**
     *
     * @var Report_Model_Mapper_StudentClass
     */
    protected $_mapper;
    
    /**
     *
     * @var Zend_Config
     */
    protected $_config;
    
    /**
     *
     */
    public function init()
    {
        $this->_mapper = new Report_Model_Mapper_StudentClass();
        $this->_config = Zend_Registry::get('config');
    
        $stepBreadCrumb = array(
        'label' => 'Formasaun Profisional',
        'url'   => '/report/student-class/area'
    );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
    }
    
    /**
     *
     */
    public function indexAction()
    {
        $this->_forward('area');
    }
    
    /**
     *
     */
    public function areaAction()
    {
        $stepBreadCrumb = array(
        'label' => 'Liu Husi Area',
        'url'   => '/report/student-class/area'
    );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Graduadu Liu Husi Area');
        $this->view->menu()->setActivePath('report/student-class/area');
    
        $form = new Report_Form_GraduatedArea();
        $form->setAction($this->_helper->url('output', 'general'));
    
        $this->view->form = $form;
    }
    
    /**
     *
     */
    public function courseAction()
    {
        $stepBreadCrumb = array(
        'label' => 'Liu Husi Kursu',
        'url'   => '/report/student-class/course'
    );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Graduadu Liu Husi Kursu');
        $this->view->menu()->setActivePath('report/student-class/course');
    
        $form = new Report_Form_GraduatedCourse();
        $form->setAction($this->_helper->url('output', 'general'));
    
        $this->view->form = $form;
    }
    
    /**
     *
     */
    public function schoolAction()
    {
        $stepBreadCrumb = array(
        'label' => 'Liu Husi Nivel Eskola',
        'url'   => '/report/student-class/school'
    );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Graduadu Liu Husi Nivel Eskola');
        $this->view->menu()->setActivePath('report/student-class/school');
    
        $form = new Report_Form_GraduatedSchool();
        $form->setAction($this->_helper->url('output', 'general'));
    
        $this->view->form = $form;
    }
    
    /**
     *
     */
    public function ageGroupAction()
    {
        $stepBreadCrumb = array(
        'label' => 'Liu Husi Grupu idade',
        'url'   => '/report/student-class/age-group'
    );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Graduadu Liu Husi Grupu idade');
        $this->view->menu()->setActivePath('report/student-class/age-group');
    
        $form = new Report_Form_GraduatedAgeGroup();
        $form->setAction($this->_helper->url('output', 'general'));
    
        $this->view->form = $form;
    }
    
    /**
     *
     */
    public function districtAction()
    {
        $stepBreadCrumb = array(
        'label' => 'Liu Husi Distritu',
        'url'   => '/report/student-class/district'
    );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Graduadu Liu Husi Distritu');
        $this->view->menu()->setActivePath('report/student-class/district');
    
        $form = new Report_Form_GraduatedDistrict();
        $form->setAction($this->_helper->url('output', 'general'));
    
        $this->view->form = $form;
    }
    
    /**
     *
     */
    public function courseInstitutionAction()
    {
        $stepBreadCrumb = array(
        'label' => 'Liu Husi Kursu/Inst. Ensinu',
        'url'   => '/report/student-class/course-institution'
    );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Graduadu Liu Husi Kursu/Inst. Ensinu');
        $this->view->menu()->setActivePath('report/student-class/course-institution');
    
        $form = new Report_Form_GraduatedCourseInstitution();
        $form->setAction($this->_helper->url('output', 'general'));
    
        $this->view->form = $form;
    }
    
    /**
     *
     */
    public function listGraduateAction()
    {
        $stepBreadCrumb = array(
			'label' => 'Lista Graduadu',
			'url'   => '/report/student-class/graduate'
		);

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Lista Graduadu');
        $this->view->menu()->setActivePath('report/student-class/list-graduate');
    
        $form = new Report_Form_ListGraduate();
        $form->setAction($this->_helper->url('output', 'general'));
    
        $this->view->form = $form;
    }
}
