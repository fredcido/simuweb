<?php

class Zend_View_Helper_Menu extends Zend_View_Helper_Abstract
{

    /**
     *
     * @var array
     */
    protected $_permissions;

    /**
     *
     * @var DOMDocument
     */
    protected $_dom;

    /**
     */
    protected $_requestedUri;

    /**
     *
     * @var DomElement
     */
    protected $_separator;

    /**
     *
     * @var boolean
     */
    protected $_active = false;

    /**
     *
     * @var string
     */
    protected $_activePath;

    /**
     *
     * @var array
     */
    protected $_menus = array(
        array(
            'label' => 'Home',
            'icon' => 'icon-home',
            'url' => '/'
        ),
        array(
            'label' => 'Formulariu',
            'icon' => 'icon-hdd',
            'url' => 'default/form'
        ),
        array(
            'label' => 'Indikador Xave Ekonomia',
            'icon' => 'icon-bar-chart',
            'url' => 'default/statistics'
        ),
        array(
            'label' => 'Admin',
            'url' => 'admin/index',
            'icon' => 'icon-cogs',
            'children' => array(
                array(
                    'label' => 'Index',
                    'url' => 'admin/index'
                ),
                array(
                    'label' => 'Usuariu Sistema',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Usuariu',
                            'url' => 'admin/user',
                            'id' => Admin_Form_User::ID
                        ),
                        array(
                            'label' => 'Rejistu Grupu',
                            'url' => 'admin/group',
                            'id' => Admin_Form_Group::ID
                        ),
                        array(
                            'label' => 'Controle de Permissões',
                            'url' => 'admin/access',
                            'id' => Admin_Form_UserForm::ID
                        ),
                        array(
                            'label' => 'Atividades dos Usuários',
                            'url' => 'admin/audit',
                            'id' => Admin_Form_Audit::ID
                        ),
                        array(
                            'label' => 'Usuariu Planu Negosiu',
                            'url' => 'admin/user-business',
                            'id' => Admin_Form_UserBusiness::ID
                        )
                    )
                ),
                array(
                    'label' => 'Formulariu Sistema',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Formulariu',
                            'url' => 'admin/form',
                            'id' => Admin_Form_Form::ID
                        ),
                        array(
                            'label' => 'Rejistu Modulu',
                            'url' => 'admin/module',
                            'id' => Admin_Form_Module::ID
                        )
                    )
                ),
                array(
                    'label' => 'SMS',
                    'children' => array(
                        array(
                            'label' => 'Departamentu',
                            'url' => 'admin/department',
                            'id' => Admin_Form_Department::ID
                        ),
                        array(
                            'label' => 'Sms Config',
                            'url' => 'admin/sms-config',
                            'id' => Admin_Form_SmsConfig::ID
                        ),
                        array(
                            'label' => 'Sms Pulsa',
                            'url' => 'admin/sms-credit',
                            'id' => Admin_Form_SmsCredit::ID
                        )
                    )
                )
            )
        ),
        array(
            'label' => 'Sms',
            'url' => 'Sms/index',
            'icon' => 'icon-envelope',
            'children' => array(
                array(
                    'label' => 'Index',
                    'url' => 'sms/index'
                ),
                array(
                    'label' => 'Grupu Sms',
                    'url' => 'sms/group',
                    'id' => Sms_Form_Group::ID
                ),
                array(
                    'label' => 'Tipu kampanha',
                    'url' => 'sms/campaign-type',
                    'id' => Sms_Form_CampaignType::ID
                ),
                array(
                    'label' => 'Kampanha',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Kampanha',
                            'url' => 'sms/campaign',
                            'id' => Sms_Form_Campaign::ID
                        ),
                        array(
                            'label' => 'Buka Kampanha',
                            'url' => 'sms/campaign/list',
                            'id' => Sms_Form_CampaignSearch::ID
                        )
                    )
                )
            )
        ),
        array(
            'label' => 'Rejistu Jeral',
            'url' => 'register/index',
            'icon' => 'icon-globe',
            'children' => array(
                array(
                    'label' => 'Index',
                    'url' => 'register/index'
                ),
                array(
                    'label' => 'Ocupasaun ISCO-08',
                    'children' => array(
                        array(
                            'label' => 'Grupo Okupasaun',
                            'url' => 'register/group',
                            'id' => Register_Form_Group::ID
                        ),
                        array(
                            'label' => 'Sub-Grupo Okupasaun',
                            'url' => 'register/sub-group',
                            'id' => Register_Form_SubGroup::ID
                        ),
                        array(
                            'label' => 'Mini-Grupo Okupasaun',
                            'url' => 'register/mini-group',
                            'id' => Register_Form_MiniGroup::ID
                        ),
                        array(
                            'label' => 'Okupasaun Internasional',
                            'url' => 'register/international-occupation',
                            'id' => Register_Form_InternationalOccupation::ID
                        ),
                        array(
                            'label' => 'Okupasaun Timor',
                            'url' => 'register/occupation-timor',
                            'id' => Register_Form_OccupationTimor::ID
                        )
                    )
                ),
                array(
                    'label' => 'Setor Industria',
                    'children' => array(
                        array(
                            'label' => 'Sesaun ISIC',
                            'url' => 'register/isic-section',
                            'id' => Register_Form_IsicSection::ID
                        ),
                        
                        array(
                            'label' => 'Divisaun ISIC',
                            'url' => 'register/isic-division',
                            'id' => Register_Form_IsicDivision::ID
                        ),
                        array(
                            'label' => 'Grupu ISIC',
                            'url' => 'register/isic-group',
                            'id' => Register_Form_IsicGroup::ID
                        ),
                        array(
                            'label' => 'Klase ISIC',
                            'url' => 'register/isic-class',
                            'id' => Register_Form_IsicClass::ID
                        ),
                        array(
                            'label' => 'Index Timor',
                            'url' => 'register/isic-timor',
                            'id' => Register_Form_IsicTimor::ID
                        ),
			/*
			array(
			    'label' =>	'Subsektor',
			    'url'   =>	'register/isic-subsector',
			    'id'    =>	Register_Form_IsicSubsector::ID
			)
			*/
		    )
                ),
                array(
                    'label' => 'Kursu',
                    'children' => array(
                        array(
                            'label' => 'Tipu Kursu',
                            'url' => 'register/type-scholarity',
                            'id' => Register_Form_TypeScholarity::ID
                        ),
                        array(
                            'label' => 'Area Kursu',
                            'url' => 'register/area-scholarity',
                            'id' => Register_Form_AreaScholarity::ID
                        ),
                        array(
                            'label' => 'Kursu',
                            'url' => 'register/scholarity',
                            'id' => Register_Form_Scholarity::ID
                        )
                    )
                ),
                array(
                    'label' => 'CEOP',
                    'url' => 'register/ceop',
                    'id' => Register_Form_Ceop::ID
                ),
                array(
                    'label' => 'Banku',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Banku',
                            'url' => 'register/bank',
                            'id' => Register_Form_Bank::ID
                        ),
                        array(
                            'label' => 'Tipu Konta Banku',
                            'url' => 'register/type-bank-account',
                            'id' => Register_Form_TypeBankAccount::ID
                        )
                    )
                ),
                array(
                    'label' => 'Instituisaun Ensinu',
                    'children' => array(
                        array(
                            'label' => 'Instituisaun Ensinu',
                            'url' => 'register/education-institution',
                            'id' => Register_Form_EducationInstitutionInformation::ID
                        ),
                        array(
                            'label' => 'Buka Inst. Ensinu',
                            'url' => 'register/education-institution/list',
                            'id' => Register_Form_EducationInstitutionSearch::ID
                        )
                    )
                ),
                array(
                    'label' => 'Empreza',
                    'children' => array(
                        array(
                            'label' => 'Empreza',
                            'url' => 'register/enterprise',
                            'id' => Register_Form_EnterpriseInformation::ID
                        ),
                        array(
                            'label' => 'Buka Empreza',
                            'url' => 'register/enterprise/list',
                            'id' => Register_Form_EnterpriseSearch::ID
                        )
                    )
                ),
                array(
                    'label' => 'Hela Fatin',
                    'children' => array(
                        array(
                            'label' => 'Nasaun',
                            'url' => 'register/nation',
                            'id' => Register_Form_Nation::ID
                        ),
                        array(
                            'label' => 'Distritu',
                            'url' => 'register/district',
                            'id' => Register_Form_District::ID
                        ),
                        array(
                            'label' => 'Sub-Distritu',
                            'url' => 'register/sub-district',
                            'id' => Register_Form_SubDistrict::ID
                        ),
                        array(
                            'label' => 'Suku',
                            'url' => 'register/suku',
                            'id' => Register_Form_Suku::ID
                        )
                    )
                ),
                array(
                    'label' => 'Barreira',
                    'children' => array(
                        array(
                            'label' => 'Tipu Barreira',
                            'url' => 'register/barrier-type',
                            'id' => Register_Form_BarrierType::ID
                        ),
                        array(
                            'label' => 'Barreira',
                            'url' => 'register/barrier',
                            'id' => Register_Form_Barrier::ID
                        ),
                        array(
                            'label' => 'Intervensaun',
                            'url' => 'register/barrier-intervention',
                            'id' => Register_Form_BarrierIntervention::ID
                        )
                    )
                ),
                array(
                    'label' => 'Intensaun ba Audiensia',
                    'url' => 'register/appointment-objective',
                    'id' => Register_Form_AppointmentObjective::ID
                )
            )
        ),
        array(
            'label' => 'Jestaun Kasu',
            'icon' => 'icon-user',
            'url' => 'client/index',
            'children' => array(
                array(
                    'label' => 'Index',
                    'url' => 'client/index'
                ),
                array(
                    'label' => 'Kliente',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Kliente',
                            'url' => 'client/client',
                            'id' => Client_Form_ClientInformation::ID
                        ),
                        array(
                            'label' => 'Buka Kliente',
                            'url' => 'client/client/list',
                            'id' => Client_Form_ClientSearch::ID
                        )
                    )
                ),
                array(
                    'label' => 'Lista Kartaun Evidensia',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Lista',
                            'url' => 'client/list-evidence',
                            'id' => Client_Form_ListEvidence::ID
                        ),
                        array(
                            'label' => 'Buka Lista',
                            'url' => 'client/list-evidence/list',
                            'id' => Client_Form_ListEvidence::ID
                        )
                    )
                ),
                array(
                    'label' => 'Kazu',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Kazu Grupu',
                            'url' => 'client/case-group',
                            'id' => Client_Form_CaseGroup::ID
                        ),
                        array(
                            'label' => 'Buka Kazu',
                            'url' => 'client/case/list',
                            'id' => Client_Form_ActionPlan::ID
                        )
                    )
                ),
            )
        ),
        array(
            'label' => 'Empregu',
            'icon' => 'icon-briefcase',
            'url' => 'job/index',
            'children' => array(
                array(
                    'label' => 'Index',
                    'url' => 'job/index'
                ),
                array(
                    'label' => 'Rejistu Vaga Empregu',
                    'url' => 'job/vacancy',
                    'id' => Job_Form_VacancyInformation::ID
                ),
                array(
                    'label' => 'Buka Vaga Empregu',
                    'url' => 'job/vacancy/list',
                    'id' => Job_Form_VacancySearch::ID
                ),
                array(
                    'label' => 'Buka Kliente Sira',
                    'url' => 'job/vacancy/search-client',
                    'id' => Job_Form_SearchClient::ID
                )
            )
        ),
        array(
            'label' => 'FEFOP',
            'icon' => 'icon-dollar',
            'children' => array(
		array(
                    'label' => 'Konfigurasaun',
                    'children' => array(
			array(
                            'label' => 'Regra',
                            'url' => 'fefop/rule/',
                            'id' => Fefop_Form_Rule::ID
                        ),
			array(
                            'label' => 'Tipu Transasaun',
                            'url' => 'fefop/type-transaction/',
                            'id' => Fefop_Form_TypeTransaction::ID
                        ),
                        array(
                            'label' => 'Komponente',
                            'url' => 'fefop/expense-type/',
                            'id' => Fefop_Form_ExpenseType::ID
                        ),
                        array(
                            'label' => 'Rubrika',
                            'url' => 'fefop/expense/',
                            'id' => Fefop_Form_Expense::ID
                        ),
                        array(
                            'label' => 'Fundu',
                            'url' => 'fefop/fund/',
                            'id' => Fefop_Form_Fund::ID
                        ),
                        array(
                            'label' => 'Benefisariu la kumpridor',
                            'url' => 'fefop/beneficiary-blacklist/',
                            'id' => Fefop_Form_BeneficiaryBlacklist::ID
                        ),
                        array(
                            'label' => 'Kustu Unitariu',
                            'url' => 'fefop/unit-cost/',
                            'id' => Fefop_Form_UnitCost::ID
                        )
                    )
                ),
                array(
                    'label' => 'PFPCI',
                    'children' => array(
                        array(
                            'label' => 'DRH',
                            'children' => array(
                                array(
                                    'label' => 'Plano Formasaun',
                                    'url' => 'fefop/drh-training-plan',
                                    'id' => Fefop_Form_DRHTrainingPlan::ID
                                ),
                                array(
                                    'label' => 'Buka Plano',
                                    'url' => 'fefop/drh-training-plan/list',
                                    'id' => Fefop_Form_DRHTrainingPlan::ID
                                ),
                                array(
                                    'label' => 'Kontratu',
                                    'url' => 'fefop/drh-contract',
                                    'id' => Fefop_Form_DRHContract::ID
                                ),
                                array(
                                    'label' => 'Kontratu Sira',
                                    'url' => 'fefop/drh-contract/contracts',
                                    'id' => Fefop_Form_DRHBulkContract::ID
                                ),
                                array(
                                    'label' => 'Buka Kontratu',
                                    'url' => 'fefop/drh-contract/list',
                                    'id' => Fefop_Form_DRHContract::ID
                                )
                            )
                        ),
                        array(
                            'label' => 'FP',
                            'children' => array(
                                array(
                                    'label' => 'Planeamentu ba Tinan',
                                    'url' => 'fefop/fp-annual-planning/',
                                    'id' => Fefop_Form_FPAnnualPlanning::ID
                                ),
                                array(
                                    'label' => 'Rejistu Kontraktu',
                                    'url' => 'fefop/fp-contract',
                                    'id' => Fefop_Form_FPContract::ID
                                ),
                                array(
                                    'label' => 'Buka Kontraktu',
                                    'url' => 'fefop/fp-contract/list',
                                    'id' => Fefop_Form_FPContract::ID
                                )
                            )
                        ),
                        array(
                            'label' => 'RI',
                            'children' => array(
                                array(
                                    'label' => 'Rejistu Kontraktu',
                                    'url' => 'fefop/ri-contract',
                                    'id' => Fefop_Form_RIContract::ID
                                ),
                                array(
                                    'label' => 'Buka Kontraktu',
                                    'url' => 'fefop/ri-contract/list',
                                    'id' => Fefop_Form_RIContract::ID
                                )
                            )
                        )
                    )
                ),
                array(
                    'label' => 'PISE',
                    'children' => array(
                        array(
                            'label' => 'FE',
                            'children' => array(
				array(
                                    'label' => 'Rejistu Inskrisaun',
                                    'url' => 'fefop/fe-registration',
                                    'id' => Fefop_Form_FERegistration::ID
                                ),
                                array(
                                    'label' => 'Buka Inskrisaun',
                                    'url' => 'fefop/fe-registration/list',
                                    'id' => Fefop_Form_FERegistration::ID
                                ),
                                array(
                                    'label' => 'Rejistu Kontraktu',
                                    'url' => 'fefop/fe-contract',
                                    'id' => Fefop_Form_FEContract::ID
                                ),
                                array(
                                    'label' => 'Buka Kontraktu',
                                    'url' => 'fefop/fe-contract/list',
                                    'id' => Fefop_Form_FEContract::ID
                                )
                            )
                        )
                    )
                ),
		array(
                    'label' => 'PCE',
                    'children' => array(
			array(
			    'label' => 'Kontratu Formasaun',
			    'children' => array(
				array(
				    'label' => 'Kontratu',
				    'url'   => 'fefop/pce-fase/',
				    'id'    => Fefop_Form_PceFaseContract::ID
				),
				array(
                                    'label' => 'Buka Kontraktu',
                                    'url'   => 'fefop/pce-fase/list',
                                    'id'    => Fefop_Form_PceFaseContract::ID
                                )
			    )
			),
                        array(
			    'label' => 'Buka Planu Negosiu',
			    'url'   => 'fefop/pce-contract/list/',
			    'id'    => Fefop_Form_PCEContract::ID
			),
                    )
                ),
		array(
                    'label' => 'PER',
                    'children' => array(
			array(
			    'label' => 'EDC',
			    'children' => array(
				array(
				    'label' => 'Kontratu',
				    'url'   => 'fefop/edc-contract/',
				    'id'    => Fefop_Form_EDCContract::ID
				),
				array(
                                    'label' => 'Buka Kontraktu',
                                    'url'   => 'fefop/edc-contract/list',
                                    'id'    => Fefop_Form_EDCContract::ID
                                )
			    )
			),
			array(
			    'label' => 'ETC',
			    'children' => array(
				array(
				    'label' => 'Kontratu',
				    'url'   => 'fefop/etc-contract/',
				    'id'    => Fefop_Form_ETCContract::ID
				),
				array(
                                    'label' => 'Buka Kontraktu',
                                    'url'   => 'fefop/etc-contract/list',
                                    'id'    => Fefop_Form_ETCContract::ID
                                )
			    )
			),
                    )
                ),
		array(
		    'label' => 'Financeiro',
		    'children' => array(
			array(
			    'label' => 'Financeiro',
			    'url' => 'fefop/financial/',
			    'id' => Fefop_Form_Financial::ID
			),
			array(
			    'label' => 'Bankariu',
			    'children' => array(
				array(
				    'label' => 'Extratu',
				    'url' => 'fefop/bank-statement/',
				    'id' => Fefop_Form_BankStatement::ID
				),
				array(
				    'label' => 'Konsiliasaun',
				    'url'   => 'fefop/bank-consolidate/',
				    'id'    => Fefop_Form_BankConsolidate::ID
				)
			    )
			),
		    )
		)
            ),
        ),
        array(
            'label' => 'Formasaun',
            'icon' => 'icon-book',
            'url' => 'student-class/index',
            'children' => array(
                array(
                    'label' => 'Index',
                    'url' => 'student-class/index'
                ),
                array(
                    'label' => 'Turma',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Klase Formasaun',
                            'url' => 'student-class/register',
                            'id' => StudentClass_Form_RegisterInformation::ID
                        ),
                        array(
                            'label' => 'Buka - Atualiza',
                            'url' => 'student-class/register/list',
                            'id' => StudentClass_Form_RegisterSearch::ID
                        )
                    )
                ),
                array(
                    'label' => 'Job Training',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Job Training',
                            'url' => 'student-class/job-training',
                            'id' => StudentClass_Form_JobTrainingInformation::ID
                        ),
                        array(
                            'label' => 'Buka - Atualiza',
                            'url' => 'student-class/job-training/list',
                            'id' => StudentClass_Form_RegisterSearch::ID
                        )
                    )
                )
            )
        ),
        array(
            'label' => 'Relatoriu',
            'icon' => 'icon-bar-chart',
            'url' => 'report/index',
            'children' => array(
                array(
                    'label' => 'Admin',
                    'children' => array(
                        array(
                            'label' => 'Uzuariu',
                            'url' => 'report/admin/user'
                        )
                    )
                ),
                array(
                    'label' => 'SMS',
                    'children' => array(
                        array(
                            'label' => 'Kampanha',
                            'url' => 'report/sms/campaign'
                        ),
                        array(
                            'label' => 'Pulsa',
                            'url' => 'report/sms/credit'
                        ),
                        array(
                            'label' => 'Total Pulsa',
                            'url' => 'report/sms/balance'
                        ),
                        array(
                            'label' => 'Enviu sira',
                            'url' => 'report/sms/sending'
                        ),
                        array(
                            'label' => 'Simu hotu',
                            'url' => 'report/sms/incoming'
                        )
                    )
                ),
                array(
                    'label' => 'Rejistu Jeral',
                    'children' => array(
                        array(
                            'label' => 'Kursu',
                            'url' => 'report/register/course'
                        ),
                        array(
                            'label' => 'Inst. Ensinu',
                            'url' => 'report/register/institution'
                        ),
                        array(
                            'label' => 'Empreza',
                            'url' => 'report/register/enterprise'
                        )
                    )
                ),
                array(
                    'label' => 'Jestaun Kazu',
                    'children' => array(
                        array(
                            'label' => 'Rejistu Kliente',
                            'url' => 'report/client/register'
                        ),
                        array(
                            'label' => 'Kliente CEOP / Tinan',
                            'url' => 'report/client/ceop-year'
                        ),
                        array(
                            'label' => 'Kliente CEOP / Quarter',
                            'url' => 'report/client/ceop-quarter'
                        ),
                        array(
                            'label' => 'Grupu Idade / Tinan',
                            'url' => 'report/client/age-group-year'
                        ),
                        array(
                            'label' => 'Grupu Idade / Quarter',
                            'url' => 'report/client/age-group-quarter'
                        ),
                        array(
                            'label' => 'Nivel Eskola / Tinan',
                            'url' => 'report/client/school-year'
                        ),
                        array(
                            'label' => 'Nivel Eskola / Quarter',
                            'url' => 'report/client/school-quarter'
                        ),
                        array(
                            'label' => 'Kliente husi Distritu',
                            'url' => 'report/client/district'
                        ),
                        array(
                            'label' => 'Objetivu Vizita',
                            'url' => 'report/client/visit-purpose'
                        ),
                        array(
                    		'label' => 'Kasu Akonsellamentu Tuir Konselleiru',
                    		'url' => 'report/client/kasu-akonsellamentu-tuir-konselleiru',
                        ),
                        array(
                    		'label' => 'Audiensia Akonsellamentu Tuir Konselleiru',
                    		'url' => 'report/client/audiensia-akonsellamentu-tuir-konselleiru'
                        ),
                        array(
                    		'label' => 'Numeru Planu Tuir Asaun CEOP',
                    		'url' => 'report/client/numeru-planu-tuir-asaun-ceop'
                        ),
                    )
                ),
                array(
                    'label' => 'Empregu',
                    'children' => array(
                        array(
                            'label' => 'Hetan Servisu',
                            'url' => 'report/job/placement'
                        ),
                        array(
                            'label' => 'Hetan Servisu Rai liu',
                            'url' => 'report/job/placement-overseas'
                        ),
                        array(
                            'label' => 'Hetan Servisu Konsolidadu',
                            'url' => 'report/job/placement-consolidated'
                        ),
                        array(
                            'label' => 'Indikador juventude nian',
                            'url' => 'report/job/youth-indicator'
                        ),
                        array(
                            'label' => 'Refere Shortlist',
                            'url' => 'report/job/shortlisted'
                        ),
                        array(
                            'label' => 'Lista Shortlist',
                            'url' => 'report/job/list-shortlist'
                        ),
                        array(
                            'label' => 'Lista Vagas Rejista',
                            'url' => 'report/job/register'
                        ),
                        array(
                            'label' => 'Buka servisu / Nivel Edukasaun',
                            'url' => 'report/job/education'
                        )
                    )
                ),
                array(
                    'label' => 'Formasaun Profisional',
                    'children' => array(
                        array(
                            'label' => 'Liu Husi Area Kursu',
                            'url' => 'report/student-class/area'
                        ),
                        array(
                            'label' => 'Liu Husi Kursu',
                            'url' => 'report/student-class/course'
                        ),
                        array(
                            'label' => 'Liu Husi Nivel Edukasaun',
                            'url' => 'report/student-class/school'
                        ),
                        array(
                            'label' => 'Liu Husi Grupu Idade',
                            'url' => 'report/student-class/age-group'
                        ),
                        array(
                            'label' => 'Liu Husi Distritu',
                            'url' => 'report/student-class/district'
                        ),
			array(
                            'label' => 'Lista Graduadu',
                            'url' => 'report/student-class/list-graduate'
                        )
                    )
                ),
                array(
                    'label' => 'FEFOP',
                    'children' => array(
                        array(
                            'label' => 'Benefisiariu - Analitiku',
                            'url' => 'report/fefop/beneficiary-analytic',
                        ),
                        array(
                        	'label' => 'Benefisiariu - Sintetitku',
                        	'url' => 'report/fefop/beneficiary-synthetic',
                        ),
                        array(
                        	'label' => 'Lista Kontratu',
                        	'url' => 'report/fefop/contract',
                        ),
                        array(
                        	'label' => 'Formasaun hodi Nasaun',
                        	'url' => 'report/fefop/training-country',
                        ),
                        array(
                        	'label' => 'Benefisiariu la kumpridor',
                        	'url' => 'report/fefop/black-list',
                        ),
                        array(
                        	'label' => 'Lansamentu Kontratu',
                        	'url' => 'report/fefop/financial-contract',
                        ),
                        array(
                        	'label' => 'Rúbrica Kustu',
                        	'url' => 'report/fefop/cost',
                        ),
			array(
                        	'label' => 'Finansiamentu Formasaun',
                        	'url' => 'report/fefop/fe-formation',
                        ),
                        array(
                        	'label' => 'Previsaun Osan husi Fonte',
                        	'url' => 'report/fefop/balance-source',
                        ),
                        array(
                        	'label' => 'Finansiamentu Kontratu x Komponente',
                        	'url' => 'report/fefop/contract-component',
                        ),
                        array(
                        	'label' => 'Finansiamentu husi Fundu',
                        	'url' => 'report/fefop/fund',
                        ),
                        array(
                        	'label' => 'Finansiamentu Devolusaun',
                        	'url' => 'report/fefop/repayments',
                        ),
                        array(
                        	'label' => 'Kustos extras',
                        	'url' => 'report/fefop/increased',
                        ),
                        array(
                        	'label' => 'Finansiamentu Kustos extras',
                        	'url' => 'report/fefop/financial-increased',
                        ),
                        array(
                        	'label' => 'Finansiamentu total Kontratu',
                        	'url' => 'report/fefop/totalizer',
                        ),
                        array(
                        	'label' => 'Totais Projetu',
                        	'url' => 'report/fefop/donor-contract-cost',
                        ),                        
                        array(
                        	'label' => 'Movimentu Bankariu',
                        	'url' => 'report/fefop/bank-transaction',
                        ),                        
                    ),
                ),
            )
        )
    );

    /**
     */
    public function __construct()
    {
        $this->_dom = new DOMDocument();
        
        $this->_initRoute();
    }

    /**
     *
     * @return \Zend_View_Helper_Menu
     */
    public function menu()
    {
        return $this;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     *
     * @return string
     */
    public function render()
    {
        $ulMenu = $this->_dom->createElement('ul');
        $ulMenu->setAttribute('class', 'page-sidebar-menu');
        
        // Create toogle icon
        $liToogle = $this->_dom->createElement('li');
        $toogle = $this->_dom->createElement('div');
        $toogle->setAttribute('class', 'sidebar-toggler hidden-phone');
        $liToogle->appendChild($toogle);
        $ulMenu->appendChild($liToogle);
        
        // Create all the another itens
        foreach ($this->_menus as $menu)
            $this->_addChildMenu($menu, $ulMenu, true);
            
            // Is there is no item active, define home
        $liHome = $ulMenu->childNodes->item(1);
        
        // Home first item in the menu
        $liHome->setAttribute('class', 'start');
        
        if (! $this->_active)
            $this->_setActive($liHome, true);
        
        $this->_dom->appendChild($ulMenu);
        return $this->_dom->saveHTML();
    }

    /**
     *
     * @param array $item            
     * @param DomElement $parentContainer            
     * @return bool
     */
    protected function _addChildMenu($item, $parentContainer, $root = false)
    {
        // Check if it has permission
        if (! empty($item['id']) && ! $this->view->access($item['id']))
            return false;
        
        $active = false;
        
        $li = $this->_dom->createElement('li');
        
        // Check if the item has an url and it is active
        if (array_key_exists('url', $item) && $this->_checkActive($item['url']))
            $active = true;
        
        $a = $this->_dom->createElement('a');
        
        if (! empty($item['url'])) {
            $a->setAttribute('href', $this->view->baseUrl($item['url']));
        } else {
            
            $a->setAttribute('href', 'javascript:;');
            $a->setAttribute('class', 'just-parent');
        }
        
        // If there is an icon
        if (! empty($item['icon'])) {
            
            $i = $this->_dom->createElement('i');
            $i->setAttribute('class', $item['icon']);
            $a->appendChild($i);
        }
        
        if ($root) {
            
            $spanTitle = $this->_dom->createElement('span');
            $spanTitle->setAttribute('class', 'title');
            $spanTitle->appendChild($this->_dom->createTextNode($item['label']));
            $a->appendChild($spanTitle);
        } else
            $a->appendChild($this->_dom->createTextNode($item['label']));
        
        $li->appendChild($a);
        
        // If there are children in the item
        if (! empty($item['children'])) {
            
            $spanArrow = $this->_dom->createElement('span');
            $spanArrow->setAttribute('class', 'arrow');
            
            $a->appendChild($spanArrow);
            
            $ulContainer = $this->_dom->createElement('ul');
            $ulContainer->setAttribute('class', 'sub-menu');
            
            // Attach all the children itens
            foreach ($item['children'] as $child) {
                
                $activeTest = $this->_addChildMenu($child, $ulContainer);
                if ($activeTest)
                    $active = $activeTest;
            }
            
            if ($ulContainer->childNodes->length < 1)
                return false;
            
            $li->appendChild($ulContainer);
        }
        
        // Check if it's active
        if ($active)
            $this->_setActive($li, $root);
        
        $parentContainer->appendChild($li);
        
        return $active;
    }

    /**
     *
     * @param DOMElement $li            
     * @param bool $root            
     */
    protected function _setActive($li, $root = false)
    {
        $currentClass = $li->getAttribute('class');
        $li->setAttribute('class', 'active ' . $currentClass);
        
        if ($root) {
            
            $span = $this->_dom->createElement('span');
            $span->setAttribute('class', 'selected');
            $li->childNodes->item(0)->appendChild($span);
        }
        
        $this->_active = true;
    }

    /**
     *
     * @param type $url            
     * @return boolean
     */
    protected function _checkActive($url)
    {
        $url = trim($url, '/');
        
        if (! empty($this->_activePath))
            return $this->_activePath == $url;
        else
            return $this->_currentRoute == $url;
    }

    /**
     *
     * @param string $path            
     */
    public function setActivePath($path)
    {
        $this->_activePath = $path;
    }

    /**
     */
    protected function _initRoute()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
        $module = $request->getModuleName();
        if ('' == $module)
            $module = 'default';
        
        $controller = $request->getControllerName();
        if ('' == $controller)
            $controller = 'index';
        
        $route = array(
            $module,
            $controller
        );
        
        $this->_currentRoute = trim(implode('/', $route), '/');
    }
}