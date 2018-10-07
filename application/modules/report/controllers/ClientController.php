<?php

class Report_ClientController extends Zend_Controller_Action
{

    /**
     *
     * @var Report_Model_Mapper_Client
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
        $this->_mapper = new Report_Model_Mapper_Client();
        $this->_config = Zend_Registry::get('config');

        $stepBreadCrumb = array(
            'label' => 'Jestaun Kazu',
            'url' => '/report/client/register',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
    }

    /**
     *
     */
    public function indexAction()
    {
        $this->_forward('register');
    }

    /**
     *
     */
    public function registerAction()
    {
        $stepBreadCrumb = array(
            'label' => 'Rejistu Kliente',
            'url' => '/report/client/register',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Rejistu Kliente');
        $this->view->menu()->setActivePath('report/client/register');

        $form = new Report_Form_ClientRegister();
        $form->setAction($this->_helper->url('output', 'general'));

        $this->view->form = $form;
    }

    /**
     *
     */
    public function ceopYearAction()
    {
        $stepBreadCrumb = array(
            'label' => 'Kliente CEOP / Tinan',
            'url' => '/report/client/ceop-year',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Kliente CEOP / Tinan');
        $this->view->menu()->setActivePath('report/client/ceop-year');

        $form = new Report_Form_ClientCeopYear();
        $form->setAction($this->_helper->url('output', 'general'));

        $this->view->form = $form;
    }

    /**
     *
     */
    public function ceopQuarterAction()
    {
        $stepBreadCrumb = array(
            'label' => 'Kliente CEOP / Quarter',
            'url' => '/report/client/ceop-quarter',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Kliente CEOP / Quarter');
        $this->view->menu()->setActivePath('report/client/ceop-quarter');

        $form = new Report_Form_ClientCeopQuarter();
        $form->setAction($this->_helper->url('output', 'general'));

        $this->view->form = $form;
    }

    /**
     *
     */
    public function ageGroupYearAction()
    {
        $stepBreadCrumb = array(
            'label' => 'Grupu Idade / Tinan',
            'url' => '/report/client/age-group-year',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Grupu Idade / Tinan');
        $this->view->menu()->setActivePath('report/client/age-group-year');

        $form = new Report_Form_ClientAgeGroupYear();
        $form->setAction($this->_helper->url('output', 'general'));

        $this->view->form = $form;
    }

    /**
     *
     */
    public function ageGroupQuarterAction()
    {
        $stepBreadCrumb = array(
            'label' => 'Grupu Idade / Quarter',
            'url' => '/report/client/age-group-quarter',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Grupu Idade / Quarter');
        $this->view->menu()->setActivePath('report/client/age-group-quarter');

        $form = new Report_Form_ClientAgeGroupQuarter();
        $form->setAction($this->_helper->url('output', 'general'));

        $this->view->form = $form;
    }

    /**
     *
     */
    public function schoolYearAction()
    {
        $stepBreadCrumb = array(
            'label' => 'Nivel Eskola / Tinan',
            'url' => '/report/client/school-year',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Nivel Eskola / Tinan');
        $this->view->menu()->setActivePath('report/client/school-year');

        $form = new Report_Form_ClientSchoolYear();
        $form->setAction($this->_helper->url('output', 'general'));

        $this->view->form = $form;
    }

    /**
     *
     */
    public function schoolQuarterAction()
    {
        $stepBreadCrumb = array(
            'label' => 'Nivel Eskola / Quarter',
            'url' => '/report/client/school-quarter',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Nivel Eskola / Quarter');
        $this->view->menu()->setActivePath('report/client/school-quarter');

        $form = new Report_Form_ClientSchoolQuarter();
        $form->setAction($this->_helper->url('output', 'general'));

        $this->view->form = $form;
    }

    /**
     *
     */
    public function districtAction()
    {
        $stepBreadCrumb = array(
            'label' => 'Kliente husi Distritu',
            'url' => '/report/client/district',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Kliente husi Distritu');
        $this->view->menu()->setActivePath('report/client/district');

        $form = new Report_Form_ClientDistrict();
        $form->setAction($this->_helper->url('output', 'general'));

        $this->view->form = $form;
    }

    /**
     *
     */
    public function visitPurposeAction()
    {
        $stepBreadCrumb = array(
            'label' => 'Objetivu Vizita',
            'url' => '/report/client/visit-purpose',
        );

        $this->view->breadcrumb()->addStep($stepBreadCrumb);
        $this->view->title('Relatorio: Objetivu Vizita');
        $this->view->menu()->setActivePath('report/client/visit-purpose');

        $form = new Report_Form_ClientVisitPurpose();
        $form->setAction($this->_helper->url('output', 'general'));

        $this->view->form = $form;
    }

    /**
     * @return void
     */
    public function kasuAkonsellamentuTuirKonselleiruAction()
    {
        $this->view->breadcrumb()->addStep(array(
            'label' => Report_Form_KasuAkonsellamentuTuirKonselleiru::TITLE,
            'url' => '/report/client/kasu-akonsellamentu-tuir-konselleiru',
        ));

        $this->view->title(Report_Form_KasuAkonsellamentuTuirKonselleiru::TITLE);

        $this->view->menu()->setActivePath('report/client/kasu-akonsellamentu-tuir-konselleiru');

        $form = new Report_Form_KasuAkonsellamentuTuirKonselleiru(array(
            'action' => $this->_helper->url('output', 'general'),
        ));

        $this->view->assign('form', $form);
    }

    /**
     * @return void
     */
    public function audiensiaAkonsellamentuTuirKonselleiruAction()
    {
        $this->view->breadcrumb()->addStep(array(
            'label' => Report_Form_AudiensiaAkonsellamentuTuirKonselleiru::TITLE,
            'url' => '/report/client/audiensia-akonsellamentu-tuir-konselleiru',
        ));

        $this->view->title(Report_Form_AudiensiaAkonsellamentuTuirKonselleiru::TITLE);

        $this->view->menu()->setActivePath('report/client/audiensia-akonsellamentu-tuir-konselleiru');

        $form = new Report_Form_AudiensiaAkonsellamentuTuirKonselleiru(array(
            'action' => $this->_helper->url('output', 'general'),
        ));

        $this->view->assign('form', $form);
    }

    /**
     * @return void
     */
    public function numeruPlanuTuirAsaunCeopAction()
    {
        $this->view->breadcrumb()->addStep(array(
            'label' => Report_Form_NumeruPlanuTuirAsaunCeop::TITLE,
            'url' => '/report/client/numeru-planu-tuir-asaun-ceop',
        ));

        $this->view->title(Report_Form_NumeruPlanuTuirAsaunCeop::TITLE);

        $this->view->menu()->setActivePath('report/client/numeru-planu-tuir-asaun-ceop');

        $form = new Report_Form_NumeruPlanuTuirAsaunCeop(array(
            'action' => $this->_helper->url('output', 'general'),
        ));

        $this->view->assign('form', $form);
    }
}
