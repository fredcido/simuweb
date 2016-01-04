<?php

/**
 * 
 * @version $Id: FefopController.php 512 2014-12-04 13:52:34Z frederico $
 */
class Report_FefopController extends Zend_Controller_Action
{
    /**
     * 
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
    }
    
    /**
     * @access public
     * @return void
     */
    public function indexAction()
    {
        $this->_forward('beneficiary');
    }
    
    /**
     * @access public
     * @return void
     */
    public function beneficiaryAnalyticAction()
    {
        $this->view->breadcrumb()->addStep(array(
            'label' => Report_Form_BeneficiaryAnalytic::TITLE,
            'url'   => '/report/fefop/beneficiary-analytic'
        ));
        
        $this->view->title(Report_Form_BeneficiaryAnalytic::TITLE);
        
        $this->view->menu()->setActivePath('report/fefop/beneficiary-analytic');
        
        $form = new Report_Form_BeneficiaryAnalytic(array(
        	'action' => $this->_helper->url('output', 'general')
        ));
        
        $this->view->assign('form', $form);
    }
    
    /**
     * @access public
     * @return void
     */
    public function beneficiarySyntheticAction()
    {
        $this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_BeneficiarySynthetic::TITLE,
    		'url'   => '/report/fefop/beneficiary-synthetic'
        ));
        
        $this->view->title(Report_Form_BeneficiarySynthetic::TITLE);
        
        $this->view->menu()->setActivePath('report/fefop/beneficiary-synthetic');
        
        $form = new Report_Form_BeneficiarySynthetic(array(
    		'action' => $this->_helper->url('output', 'general')
        ));
        
        $this->view->assign('form', $form);
    }
    
    /**
     * Lista de Contratos
     * 
     * @access public
     * @return void
     */
    public function contractAction()
    {
        $this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_Contract::TITLE,
    		'url'   => '/report/fefop/contract'
        ));
        
        $this->view->title(Report_Form_Contract::TITLE);
        
        $this->view->menu()->setActivePath('report/fefop/contract');
        
        $form = new Report_Form_Contract(array(
    		'action' => $this->_helper->url('output', 'general')
        ));
        
        $this->view->assign('form', $form);
    }
    
    /**
     * Formação por país
     * 
     * @access public
     * @return void
     */
    public function trainingCountryAction()
    {
        $this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_TrainingCountry::TITLE,
    		'url'   => '/report/fefop/training-country'
        ));
        
        $this->view->title(Report_Form_TrainingCountry::TITLE);
        
        $this->view->menu()->setActivePath('report/fefop/training-country');
        
        $form = new Report_Form_TrainingCountry(array(
    		'action' => $this->_helper->url('output', 'general')
        ));
        
        $this->view->assign('form', $form);
    }
    
    /**
     * Lista negra de beneficiários
     * 
     * @access public
     * @return void
     */
    public function blackListAction()
    {
        $title = 'Relatoriu: Benefisiariu la Kumpridor';
        
        $this->view->breadcrumb()->addStep(array(
    		'label' => $title,
    		'url'   => '/report/fefop/black-list'
        ));
        
        $this->view->title($title);
        
        $this->view->menu()->setActivePath('report/fefop/black-list');
        
        $form = new Fefop_Form_BeneficiaryBlacklistSearch(array(
            'action' => $this->_helper->url('output', 'general')
        ));
        
        $form->addElement(
            'hidden', 
            'path',
            array(
                'class' => 'no-clear',
                'decorators' => array('ViewHelper'),
                'value' => 'fefop/black-list-report',
            )
        );
        
        $form->addElement(
            'hidden', 
            'title',
            array(
                'class' => 'no-clear',
                'decorators' => array('ViewHelper'),
                'value' => $title,
            )
        );
        
        $this->view->assign('form', $form);
    }
    
    /**
     * Lançamento por contrato
     * 
     * @access public
     * @return void
     */
    public function financialContractAction()
    {
        $title = 'Relatoriu: Lansamentu Kontratu';
        
        $this->view->breadcrumb()->addStep(array(
    		'label' => $title,
    		'url'   => '/report/fefop/financial-contract'
        ));
        
        $this->view->title($title);
        
        $this->view->menu()->setActivePath('report/fefop/financial-contract');
        
        $form = new Fefop_Form_ContractSearch(array(
    		'action' => $this->_helper->url('output', 'general')
        ));
        
        $form->addElement(
    		'hidden',
    		'path',
    		array(
    			'class' => 'no-clear',
    			'decorators' => array('ViewHelper'),
    			'value' => 'fefop/financial-contract-report',
    		)
        );
        
        $form->addElement(
    		'hidden',
    		'title',
    		array(
				'class' => 'no-clear',
				'decorators' => array('ViewHelper'),
				'value' => $title,
    		)
        );
        
        $this->view->assign('form', $form);
    }
    
    /**
     * @access public
     * @return void
     */
    public function costAction()
    {
        $this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_Cost::TITLE,
    		'url'   => '/report/fefop/cost'
        ));
        
        $this->view->title(Report_Form_Cost::TITLE);
        
        $this->view->menu()->setActivePath('report/fefop/cost');
        
        $form = new Report_Form_Cost(array(
    		'action' => $this->_helper->url('output', 'general')
        ));
        
        $this->view->assign('form', $form);
    }
    
    /**
     * @access public
     * @return void
     */
    public function balanceSourceAction()
    {
        $this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_BalanceSource::TITLE,
    		'url'   => '/report/fefop/balance-source'
        ));
        
        $this->view->title(Report_Form_BalanceSource::TITLE);
        
        $this->view->menu()->setActivePath('report/fefop/balance-source');
        
        $form = new Report_Form_BalanceSource(array(
    		'action' => $this->_helper->url('output', 'general')
        ));
        
        $this->view->assign('form', $form);
    }
    
    /**
     * Financiamento por Contrato/Componente
     * 
     * @access public
     * @return void
     */
    public function contractComponentAction()
    {
        $this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_ContractComponent::TITLE,
    		'url'   => '/report/fefop/contract-component'
        ));
        
        $this->view->title(Report_Form_ContractComponent::TITLE);
        
        $this->view->menu()->setActivePath('report/fefop/contract-component');
        
        $form = new Report_Form_ContractComponent(array(
    		'action' => $this->_helper->url('output', 'general')
        ));
        
        $this->view->assign('form', $form);
    }
    
    /**
     * Financiamento por Fundos
     *
     * @access public
     * @return void
     */
    public function fundAction()
    {
    	$this->view->breadcrumb()->addStep(array(
			'label' => Report_Form_Fund::TITLE,
			'url'   => '/report/fefop/fund'
    	));
    
    	$this->view->title(Report_Form_Fund::TITLE);
    
    	$this->view->menu()->setActivePath('report/fefop/fund');
    
    	$form = new Report_Form_Fund(array(
			'action' => $this->_helper->url('output', 'general')
    	));
    
    	$this->view->assign('form', $form);
    }
    
    /**
     * @access public
     * @return void
     */
    public function repaymentsAction()
    {
        $this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_Repayment::TITLE,
    		'url'   => '/report/fefop/repayments'
        ));
        
        $this->view->title(Report_Form_Repayment::TITLE);
        
        $this->view->menu()->setActivePath('report/fefop/repayments');
        
        $form = new Report_Form_Repayment(array(
    		'action' => $this->_helper->url('output', 'general')
        ));
        
        $this->view->assign('form', $form);
    }
    
    /**
     * 
     */
    public function increasedAction()
    {
    	$this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_Increased::TITLE,
    		'url'   => '/report/fefop/increased'
    	));
    	 
    	$this->view->title(Report_Form_Increased::TITLE);
    	 
    	$this->view->menu()->setActivePath('report/fefop/increased');
    	 
    	$form = new Report_Form_Increased(array(
    		'action' => $this->_helper->url('output', 'general')
    	));
    	 
    	$this->view->assign('form', $form);
    }
    
    /**
     * 
     */
    public function financialIncreasedAction()
    {
    	$this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_FinancialIncreased::TITLE,
    		'url'   => '/report/fefop/financial-increased'
    	));
    	
    	$this->view->title(Report_Form_FinancialIncreased::TITLE);
    	
    	$this->view->menu()->setActivePath('report/fefop/financial-increased');
    	
    	$form = new Report_Form_FinancialIncreased(array(
    		'action' => $this->_helper->url('output', 'general')
    	));
    	
    	$this->view->assign('form', $form);
    }
    
     /**
     * @access public
     * @return void
     */
    public function feFormationAction()
    {
    	$this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_FEFormation::TITLE,
    		'url'   => '/report/fefop/fe-formation'
    	));
    	
    	$this->view->title(Report_Form_FEFormation::TITLE);
    	
    	$this->view->menu()->setActivePath('report/fefop/fe-formation');
    	
    	$form = new Report_Form_FEFormation(array(
    		'action' => $this->_helper->url('output', 'general')
    	));
    	
    	$this->view->assign('form', $form);
    }
    
    /**
     * @access public
     * @return void
     */
    public function totalizerAction()
    {
    	$this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_Totalizer::TITLE,
    		'url'   => '/report/fefop/totalizer'
    	));
    	
    	$this->view->title(Report_Form_Totalizer::TITLE);
    	
    	$this->view->menu()->setActivePath('report/fefop/totalizer');
    	
    	$form = new Report_Form_Totalizer(array(
    		'action' => $this->_helper->url('output', 'general')
    	));
    	
    	$this->view->assign('form', $form);
    }
    
    public function donorContractCostAction()
    {
    	$this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_DonorContractCost::TITLE,
    		'url'   => '/report/fefop/donor-contract-cost'
    	));
    	 
    	$this->view->title(Report_Form_DonorContractCost::TITLE);
    	 
    	$this->view->menu()->setActivePath('report/fefop/donor-contract-cost');
    	 
    	$form = new Report_Form_DonorContractCost(array(
    		'action' => $this->_helper->url('output', 'general')
    	));
    	 
    	$this->view->assign('form', $form);
    }    
    
    public function bankTransactionAction()
    {
    	$this->view->breadcrumb()->addStep(array(
    		'label' => Report_Form_BankTransaction::TITLE,
    		'url'   => '/report/fefop/bank-transaction'
    	));
    	 
    	$this->view->title(Report_Form_BankTransaction::TITLE);
    	 
    	$this->view->menu()->setActivePath('report/fefop/bank-transaction');
    	 
    	$form = new Report_Form_BankTransaction(array(
    		'action' => $this->_helper->url('output', 'general')
    	));
    	 
    	$this->view->assign('form', $form);
    }
}