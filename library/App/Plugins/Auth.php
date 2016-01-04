<?php

class App_Plugins_Auth extends Zend_Controller_Plugin_Abstract
{

    /**
     * 
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * 
     * @var Zend_Auth
     */
    protected $_auth;
    
    /**
     *
     * @var Zend_Config
     */
    protected $_config;
    
    /**
     *
     * @var array
     */
    protected $_resources = array();
    
    /**
     *
     * @var array
     */
    protected $_releasedModules = array( 'report', 'cron', 'external' );
    
    /**
     *
     * @var array
     */
    protected $_releasedControllers = array( 
					'default' => array(
							'index', 
							'auth',
							'note',
							'form',
							'statistics'
						    )
				      );
    
    /**
     *
     * @var array
     */
    protected $_session;

    /**
     * 
     * @var array
     */
    protected $_noAuth = array(
	'module'     => 'default',
	'controller' => 'auth',
	'action'     => 'index'
    );
    
    /**
     * 
     * @var array
     */
    protected $_noAccess = array(
	'module'     => 'default',
	'controller' => 'error',
	'action'     => 'access'
    );
    
    /**
     * 
     */
    public function __construct()
    {
	$this->_auth = Zend_Auth::getInstance();
	$this->_config = Zend_Registry::get( 'config' );

	//Namespace de autenticacao da aplicacao
	$namespace = 'Auth_Admin_' . ucfirst( $this->_config->general->appid );

	//Define storage da aplicacao
	$this->_auth->setStorage( new Zend_Auth_Storage_Session( $namespace ) );
	
	$this->_session = new Zend_Session_Namespace( $this->_config->general->appid );
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::dispatchLoopStartup()
     */
    public function dispatchLoopStartup( Zend_Controller_Request_Abstract $request )
    {
	$this->_request = $request;
	
	switch ( true ) {
	    case !$this->_checkAuth():
		$this->_routeNoAuth();
		break;
	    case !$this->_checkAccess():
		$this->_routeNoAccess();
		break;
	}

	return true;
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _checkAuth()
    {
	if ( !$this->_auth->hasIdentity() && 
	     'auth' !== $this->_request->getControllerName() && 
	     'cron' !== $this->_request->getModuleName() )
	    return false;
	
	if ( !empty( $this->_session->client ) && 'external' !== $this->_request->getModuleName() )
	    return false;
	
	return true;
    }
    
    /**
     * 
     * @return boolean
     */
    protected function _checkAccess()
    {
	$this->_mapResources();
	
	$module = $this->_request->getModuleName();
	$controller = $this->_request->getControllerName();
	
	if ( in_array( $module, $this->_releasedModules ) )
	    return true;
	
	if ( !empty( $this->_releasedControllers[$module] ) && in_array( $controller, $this->_releasedControllers[$module] ) )
	    return true;
	    
	if ( empty( $this->_resources[$module][$controller] ) )
	    return false;
	
	$id = $this->_resources[$module][$controller];
	
	if ( is_bool( $id ) && !empty( $id ) )
	    return true;
	
	$permissions = $this->_session->permissions;
	
	return !empty( $permissions[$id] );
    }
    
    /**
     * 
     */
    protected function _routeNoAccess()
    {
	$this->_setRouter( $this->_noAccess );
    }
    
    /**
     * 
     */
    protected function _mapResources()
    {
	
	$this->_resources = array(
	    'admin' => array (
		'access'	    => Admin_Form_UserForm::ID,
		'form'		    => Admin_Form_Form::ID,
		'audit'		    => Admin_Form_Audit::ID,
		'module'	    => Admin_Form_Module::ID,
		'user'		    => Admin_Form_User::ID,
		'department'	    => Admin_Form_Department::ID,
		'sms-config'	    => Admin_Form_SmsConfig::ID,
		'sms-credit'	    => Admin_Form_SmsCredit::ID,
		'group'		    => Admin_Form_Group::ID,
		'user-business'	    => Admin_Form_UserBusiness::ID,
		'index'		    => true
	    ),
	    'sms' => array (
		'group'		    => Sms_Form_Group::ID,
		'campaign-type'	    => Sms_Form_CampaignType::ID,
		'campaign'	    => Sms_Form_Campaign::ID,
		'index'		    => true
	    ),
	    'register' => array (
		'group'			    => Register_Form_Group::ID,
		'budget-category'	    => Register_Form_BudgetCategory::ID,
		'international-occupation'  => Register_Form_InternationalOccupation::ID,
		'isic-class'		    => Register_Form_IsicClass::ID,
		'isic-division'		    => Register_Form_IsicDivision::ID,
		'isic-group'		    => Register_Form_IsicGroup::ID,
		'isic-section'		    => Register_Form_IsicSection::ID,
		'isic-timor'		    => Register_Form_IsicTimor::ID,
		'isic-subsector'	    => Register_Form_IsicSubsector::ID,
		'mini-group'		    => Register_Form_MiniGroup::ID,
		'occupation-timor'	    => Register_Form_OccupationTimor::ID,
		'sub-group'		    => Register_Form_SubGroup::ID,
		'type-scholarity'	    => Register_Form_TypeScholarity::ID,
		'barrier-type'		    => Register_Form_BarrierType::ID,
		'appointment-objective'	    => Register_Form_AppointmentObjective::ID,
		'barrier'		    => Register_Form_Barrier::ID,
		'barrier-intervention'	    => Register_Form_BarrierIntervention::ID,
		'area-scholarity'	    => Register_Form_AreaScholarity::ID,
		'scholarity'		    => Register_Form_Scholarity::ID,
		'nation'		    => Register_Form_Nation::ID,
		'district'		    => Register_Form_District::ID,
		'sub-district'		    => Register_Form_SubDistrict::ID,
		'suku'			    => Register_Form_Suku::ID,
		'ceop'			    => Register_Form_Ceop::ID,
		'bank'			    => Register_Form_Bank::ID,
		'type-bank-account'	    => Register_Form_TypeBankAccount::ID,
		'education-institution'	    => Register_Form_EducationInstitutionInformation::ID,
		'enterprise'		    => Register_Form_EnterpriseInformation::ID,
		'index'			    => true
	    ),
	    'client' => array(
		'index'		=> true,
		'list-evidence'	=> Client_Form_ListEvidence::ID,
		'client'	=> Client_Form_ClientInformation::ID,
		'case'		=> Client_Form_ActionPlan::ID,
		'case-group'	=> Client_Form_CaseGroup::ID,
		'document'	=> true
	    ),
	    'job' => array(
		'index'	    => true,
		'vacancy'   => Job_Form_VacancyInformation::ID,
		'match'	    => Job_Form_Match::ID
	    ),
	    'fefop' => array(
		'index'			 => true,
		'rule'			 => Fefop_Form_Rule::ID,
		'expense-type'		 => Fefop_Form_ExpenseType::ID,
		'type-transaction'	 => Fefop_Form_TypeTransaction::ID,
		'financial'		 => Fefop_Form_Financial::ID,
		'bank-statement'	 => Fefop_Form_BankStatement::ID,
		'bank-consolidate'	 => Fefop_Form_BankConsolidate::ID,
		'expense'		 => Fefop_Form_Expense::ID,
		'fund'			 => Fefop_Form_Fund::ID,
		'beneficiary-blacklist'  => Fefop_Form_BeneficiaryBlacklist::ID,
		'unit-cost'		 => Fefop_Form_UnitCost::ID,
		'fp-annual-planning'	 => Fefop_Form_FPAnnualPlanning::ID,
		'fp-contract'		 => Fefop_Form_FPContract::ID,
		'ri-contract'		 => Fefop_Form_RIContract::ID,
		'followup'		 => Fefop_Form_Followup::ID,
		'document'		 => Fefop_Form_Document::ID,
		'fe-contract'		 => Fefop_Form_FEContract::ID,
		'fe-registration'	 => Fefop_Form_FERegistration::ID,
		'drh-training-plan'	 => Fefop_Form_DRHTrainingPlan::ID,
		'drh-contract'		 => Fefop_Form_DRHContract::ID,
		'pce-contract'		 => Fefop_Form_PCEContract::ID,
		'pce-fase'		 => Fefop_Form_PceFaseContract::ID,
		'edc-contract'		 => Fefop_Form_EDCContract::ID,
		'etc-contract'		 => Fefop_Form_ETCContract::ID
	    ),
	    'student-class' => array(
		'index'		=> true,
		'register'	=> StudentClass_Form_RegisterInformation::ID,
		'job-training'  => StudentClass_Form_JobTrainingInformation::ID
	    )
	);
    }

    /**
     * @access protected
     * @return void
     */
    protected function _routeNoAuth()
    {
	$this->_auth->clearIdentity();
	$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
	
	
	if ( !$this->_request->isXMLHttpRequest() )
	    $this->_session->triedroute = str_replace( $baseUrl, '', $this->_request->getRequestUri() );
	else {
	    
	    $helperBroker = Zend_Controller_Action_HelperBroker::getStaticHelper( 'json' );
	    $helperBroker->direct( array( 'error' => true, 'status' => false, 'logout' => true ) );
	}
	
	$this->_setRouter( $this->_noAuth );
    }

    /**
     * 
     * @access 	protected
     * @param 	array $router
     * @return 	void
     */
    protected function _setRouter( array $router )
    {
	$this->_request->setModuleName( $router['module'] );
	$this->_request->setControllerName( $router['controller'] );
	$this->_request->setActionName( $router['action'] );
    }

}