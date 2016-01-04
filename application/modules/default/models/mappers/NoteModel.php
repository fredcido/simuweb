<?php

class Default_Model_Mapper_NoteModel extends App_Model_Abstract
{
    
    const CLASS_CANDIDATE = 'KLIENTE %s SELECIONADO BA KLASE FORMASAUN NUMERU: %s';
    
    const CLASS_GRADUATED = 'KLIENTE %s GRADUADU IHA KLASE FORMASAUN %s';
    
    const CLASS_EXPIRED = 'KLASE %s LORON REMATA LIU TIHA ONA. FAVOR ATUALIZA NO TAKA KLASE.';

    const CLASS_SHORTLIST = 'KLIENTE %s REFERE BA LISTA BADAK BA KLASE: %s';
    
    const JOB_SERVICE = 'KLIENTE %s HETAN SERVISU IHA VAGA %s';
    
    const JOB_EXPIRED = 'VAGA EMPREGU %s LORON TAKA LIU TIHA ONA. FAVOR ATUALIZA NO TAKA VAGA EMPREGU.';
    
    const JOB_TRAINING = 'KLIENTE %s SELECIONADO BA JOB TRAINING %s';
    
    const JOB_TRAINING_EXPIRED = 'VAGA ESTAJIU %s LORON TAKA LIU TIHA ONA. FAVOR ATUALIZA INFORMASAUN HOSI VAGA.';
    
    const JOB_TRAINING_GRADUATED = 'KLIENTE %s REMATA JOB TRAINING %s';
    
    const JOB_SHORTLIST = 'KLIENTE %s HO KAZU %s HOSI CEOP: %s NO KONSELLEIRU: %s REFERE BA VAGA EMPREGU %s HOSI CEOP: %s.';
    
    const APPOINTMENT_CASE_GRADUATED = 'KLIENTE %s HO KAZU %s CEOP: %s KONSELLEIRU: %s IHA AUDIENSIA KAZU LIU TIHA ONA. DATA %s';
    
    const DEPARTMENT_CREDIT = 'DEPARTAMENTU %s NIA PULSA HOTU ONA';
    
    const SMS_RECEIVED = 'VERIFIKA KAMPANHA %s IHA HAKAT SIMU SMS';
    
    const CAMPAIGN_FINISHED = 'KAMPANHA %s REMATA HARUKA SMS TIHA ONA';
    
    const CASE_FOLLOW_UP = 'HALO KONTATO HO KLIENTE: %s, TELEMOVEL: %s NO EMAIL: %s';
    
    const JOB_FOLLOW_UP = 'HALO KONTATU AKOMPAÑAMENTU BA KLIENTE: %s, TELEMOVEL: %s NO EMAIL: %s NEEBE HETAN SERBISU';
    
    const DRH_GREATER = 'DRH TRAINING PLAN NUMERU %s FOLIN HIRA LIU %s, HO FOLIN HIRA: %s';
    
    const RI_AMOUNT_GREATER = 'RI KONTRATU NUMERU %s FOLIN HIRA LIU %s, HO FOLIN HIRA: %s';
    
    const RI_DURATION_GREATER = 'RI KONTRATU NUMERU %s TEMPU DURASAUN LIU %s, HO TEMPU DURASAUN: %s';
    
    const FE_DURATION_GREATER = 'FE KONTRATU NUMERU %s TEMPU DURASAUN LIU %s, HO TEMPU DURASAUN: %s';
    
    const FE_GRADUATION = 'FE KONTRATU NUMERU %s - BENEFISIARIU: %s LA IHA FORMASAUN TEKINIKA PROFISIONAL LEVEL 2 KA GRADUASAUN SUPERIOR';
    
    
    /**
     *
     * @param int $client
     * @param int $class
     * @return string 
     */
    public function getClassCandidate( $client, $class )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	
	$client = $mapperClient->detailClient( $client );
	$class = $mapperStudentClass->detailStudentClass( $class );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	
	$clientName = Client_Model_Mapper_Client::buildNumRow( $client ) . ' - ' . Client_Model_Mapper_Client::buildName( $client );
	$className = str_pad( $class->id_fefpstudentclass, 5, '0', STR_PAD_LEFT ) . ' - ' . $class->class_name;
	
	$aClient = sprintf( $link, $view->baseUrl( '/client/client/print/id/' . $client->id_perdata ), $clientName );
	$aClass = sprintf( $link, $view->baseUrl( '/student-class/register/edit/id/' . $class->id_fefpstudentclass ), $className );
	
	return sprintf( self::CLASS_CANDIDATE, $aClient, $aClass );
    }
    
    /**
     *
     * @param int $client
     * @param int $class
     * @return string 
     */
    public function getClassGraduated( $client, $class )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	
	$client = $mapperClient->detailClient( $client );
	$class = $mapperStudentClass->detailStudentClass( $class );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	
	$clientName = Client_Model_Mapper_Client::buildNumRow( $client ) . ' - ' . Client_Model_Mapper_Client::buildName( $client );
	$className = str_pad( $class->id_fefpstudentclass, 5, '0', STR_PAD_LEFT ) . ' - ' . $class->class_name;
	
	$aClient = sprintf( $link, $view->baseUrl( '/client/client/print/id/' . $client->id_perdata ), $clientName );
	$aClass = sprintf( $link, $view->baseUrl( '/student-class/register/edit/id/' . $class->id_fefpstudentclass ), $className );
	
	return sprintf( self::CLASS_GRADUATED, $aClient, $aClass );
    }
    
    /**
     *
     * @param int $client
     * @param int $vacancy
     * @return string 
     */
    public function geJobService( $client, $vacancy )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$mapperJobVacancy = new Job_Model_Mapper_JobVacancy();
	
	$client = $mapperClient->detailClient( $client );
	$vacancy = $mapperJobVacancy->detailVacancy( $vacancy );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	
	$clientName = Client_Model_Mapper_Client::buildNumRow( $client ) . ' - ' . Client_Model_Mapper_Client::buildName( $client );
	$vacancyName = str_pad( $vacancy->id_jobvacancy, 5, '0', STR_PAD_LEFT ) . ' - ' . $vacancy->vacancy_titule;
	
	$aClient = sprintf( $link, $view->baseUrl( '/client/client/print/id/' . $client->id_perdata ), $clientName );
	$aVacancy = sprintf( $link, $view->baseUrl( '/job/vacancy/view/id/' . $vacancy->id_jobvacancy ), $vacancyName );
	
	return sprintf( self::JOB_SERVICE, $aClient, $aVacancy );
    }
    
    /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getJobTrainingCandidate( $client, $idJobTraining )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	
	$client = $mapperClient->detailClient( $client );
	$jobTraining = $mapperJobTraining->detailJobTraining( $idJobTraining );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	
	$clientName = Client_Model_Mapper_Client::buildNumRow( $client ) . ' - ' . Client_Model_Mapper_Client::buildName( $client );
	$jobTrainingTile = str_pad( $idJobTraining, 5, '0', STR_PAD_LEFT ) . ' - ' . $jobTraining->title;
	
	$aClient = sprintf( $link, $view->baseUrl( '/client/client/print/id/' . $client->id_perdata ), $clientName );
	$aJobTraining = sprintf( $link, $view->baseUrl( '/student-class/job-training/print/id/' . $idJobTraining ), $jobTrainingTile );
	
	return sprintf( self::JOB_TRAINING, $aClient, $aJobTraining );
    }
    
    /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getJobTrainingGraduated( $client, $idJobTraining )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	
	$client = $mapperClient->detailClient( $client );
	$jobTraining = $mapperJobTraining->detailJobTraining( $idJobTraining );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	
	$clientName = Client_Model_Mapper_Client::buildNumRow( $client ) . ' - ' . Client_Model_Mapper_Client::buildName( $client );
	$jobTrainingTile = str_pad( $idJobTraining, 5, '0', STR_PAD_LEFT ) . ' - ' . $jobTraining->title;
	
	$aClient = sprintf( $link, $view->baseUrl( '/client/client/print/id/' . $client->id_perdata ), $clientName );
	$aJobTraining = sprintf( $link, $view->baseUrl( '/student-class/job-training/print/id/' . $idJobTraining ), $jobTrainingTile );
	
	return sprintf( self::JOB_TRAINING_GRADUATED, $aClient, $aJobTraining );
    }
    
    /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getClassExpired( $class )
    {
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$class = $mapperStudentClass->detailStudentClass( $class );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$className = str_pad( $class->id_fefpstudentclass, 5, '0', STR_PAD_LEFT ) . ' - ' . $class->class_name;
	$aClass = sprintf( $link, $view->baseUrl( '/student-class/register/edit/id/' . $class->id_fefpstudentclass ), $className );
	
	return sprintf( self::CLASS_EXPIRED, $aClass );
    }
    
    /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getJobExpired( $job )
    {
	$mapperJobVacancy = new Job_Model_Mapper_JobVacancy();
	$vacancy = $mapperJobVacancy->detailVacancy( $job );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$vacancyName = str_pad( $vacancy->id_jobvacancy, 5, '0', STR_PAD_LEFT ) . ' - ' . $vacancy->vacancy_titule;
	$aVacancy = sprintf( $link, $view->baseUrl( '/job/vacancy/view/id/' . $vacancy->id_jobvacancy ),  $vacancy->name_dec . ' - ' . $vacancyName );
	
	return sprintf( self::JOB_EXPIRED, $aVacancy );
    }
    
    /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getJobTrainingExpired( $job )
    {
	$mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	$jobTraining = $mapperJobTraining->detailJobTraining( $job );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$jobTrainingName = str_pad( $jobTraining->id_jobtraining, 5, '0', STR_PAD_LEFT ) . ' - ' . $jobTraining->title;
	$aJobTraining = sprintf( $link, $view->baseUrl( '/student-class/job-training/edit/id/' . $jobTraining->id_jobtraining ),  $jobTraining->name_dec . ' - ' . $jobTrainingName );
	
	return sprintf( self::JOB_TRAINING_EXPIRED, $aJobTraining );
    }
    
    /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getAppointmentExpired( $appointment )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	
	$clientName = Client_Model_Mapper_Client::buildNumRow( $appointment ) . ' - ' . Client_Model_Mapper_Client::buildName( $appointment );
	$idCase = str_pad( $appointment->id_action_plan, 5, '0', STR_PAD_LEFT );
	$aClient = sprintf( $link, $view->baseUrl( '/client/client/print/id/' . $appointment->id_perdata ), $clientName );
	
	$date = new Zend_Date( $appointment->date_appointment );
	
	return sprintf( self::APPOINTMENT_CASE_GRADUATED, 
			$aClient, 
			$idCase, 
			$appointment->ceop_case, 
			$appointment->counselor,
			$date->toString( 'dd/MM/yyyy HH:mm')
		);
    }
    
    
    /**
     *
     * @param array $data
     * @return string
     */
    public function geJobShortlist( $data )
    {
	$mapperJobVacancy = new Job_Model_Mapper_JobVacancy();
	$mapperClient = new Client_Model_Mapper_Client();
	$mapperCase = new Client_Model_Mapper_Case();
	
	$vacancy = $mapperJobVacancy->detailVacancy( $data['vacancy'] );
	$client = $mapperClient->detailClient( $data['client'] );
	$case = $mapperCase->detailCase( $data['case'] );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	
	$vacancyName = str_pad( $vacancy->id_jobvacancy, 5, '0', STR_PAD_LEFT ) . ' - ' . $vacancy->vacancy_titule;
	$clientName = Client_Model_Mapper_Client::buildNumRow( $client ) . ' - ' . Client_Model_Mapper_Client::buildName( $client );
	
	$aVacancy = sprintf( $link, $view->baseUrl( '/job/vacancy/view/id/' . $vacancy->id_jobvacancy ),  $vacancy->name_dec . ' - ' . $vacancyName );
	$aClient = sprintf( $link, $view->baseUrl( '/client/client/print/id/' . $client->id_perdata ), $clientName );
	$idCase = str_pad( $case->id_action_plan, 5, '0', STR_PAD_LEFT );
	
	return sprintf( 
		    self::JOB_SHORTLIST, 
		    $aClient,
		    $idCase,
		    $case->name_dec,
		    $case->name,
		    $aVacancy,
		    $vacancy->name_dec
		);
    }
    
    /**
     *
     * @param array $data
     * @return string 
     */
    public function geClassShortlist( $data )
    {
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$mapperClient = new Client_Model_Mapper_Client();
	$mapperCase = new Client_Model_Mapper_Case();
	
	$class = $mapperStudentClass->detailStudentClass( $data['class'] );
	$client = $mapperClient->detailClient( $data['client'] );
	$case = $mapperCase->detailCase( $data['case'] );
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	
	$className = str_pad( $class->id_fefpstudentclass, 5, '0', STR_PAD_LEFT ) . ' - ' . $class->class_name;
	$clientName = Client_Model_Mapper_Client::buildNumRow( $client ) . ' - ' . Client_Model_Mapper_Client::buildName( $client );
	
	$aClass = sprintf( $link, $view->baseUrl( '/student-class/register/edit/id/' . $class->id_fefpstudentclass ),  $class->name_dec . ' - ' . $className );
	$aClient = sprintf( $link, $view->baseUrl( '/client/client/print/id/' . $client->id_perdata ), $clientName );
	$idCase = str_pad( $case->id_action_plan, 5, '0', STR_PAD_LEFT );
	
	return sprintf( 
		    self::JOB_SHORTLIST, 
		    $aClient,
		    $idCase,
		    $case->name_dec,
		    $case->name,
		    $aClass,
		    $class->name_dec
		);
    }
    
    /**
     * 
     * @param array $department
     * @return string
     */
    public function getDepartmentCredit( $department )
    {
	$departmentName = str_pad( $department['id_department'], 5, '0', STR_PAD_LEFT ) . ' - ' . $department['name'];
	return sprintf( self::DEPARTMENT_CREDIT, $departmentName );
    }
    
    /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getCampaignSmsReceived( $campaign )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$campaignName = str_pad( $campaign->id_campaign, 5, '0', STR_PAD_LEFT ) . ' - ' . $campaign->campaign_title;
	$aCampaign = sprintf( $link, $view->baseUrl( '/sms/campaign/edit/id/' . $campaign->id_campaign ), $campaignName );
	
	return sprintf( self::SMS_RECEIVED, $aCampaign );
    }
    
     /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getCampaignFinished( $campaign )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$campaignName = str_pad( $campaign->id_campaign, 5, '0', STR_PAD_LEFT ) . ' - ' . $campaign->campaign_title;
	$aCampaign = sprintf( $link, $view->baseUrl( '/sms/campaign/edit/id/' . $campaign->id_campaign ), $campaignName );
	
	return sprintf( self::CAMPAIGN_FINISHED, $aCampaign );
    }
    
    /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getFollowUpCase( $client )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $client );
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$clientName = Client_Model_Mapper_Client::buildNumRow( $client ) . ' - ' . Client_Model_Mapper_Client::buildName( $client );
	$aClient = sprintf( 
			    $link, 
			    $view->baseUrl( '/client/client/view/id/' . $client->id_perdata ), 
			    $clientName
		    );
	
	return sprintf( 
		    self::CASE_FOLLOW_UP, 
		    $aClient,
		    $client->client_fone,
		    $client->email
		);
    }
    
    /**
     *
     * @param int $client
     * @param int $idJobTraining
     * @return string 
     */
    public function getFollowUpJob( $client )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $client );
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$clientName = Client_Model_Mapper_Client::buildNumRow( $client ) . ' - ' . Client_Model_Mapper_Client::buildName( $client );
	$aClient = sprintf( 
			    $link, 
			    $view->baseUrl( '/client/client/view/id/' . $client->id_perdata ), 
			    $clientName
		    );
	
	return sprintf( 
		    self::JOB_FOLLOW_UP, 
		    $aClient,
		    $client->client_fone,
		    $client->email
		);
    }
    
    /**
     * 
     * @param array $trainingProviders
     * @return string
     */
    public function getTrainingProviderNotFound( $trainingProviders )
    {
	$message = 'Sentru Formasaun sira ne\'e seidauk rejistu iha sistema INDMO: <br /><p>';
	
	$mapperEduInstitution = new Register_Model_Mapper_EducationInstitute();
	$view = Zend_Layout::getMvcInstance()->getView();
	$link = '<a href="%s" target="_blank">%s</a>';
	
	$namesTraining = array();
	foreach ( $trainingProviders as $id ) {
	    
	    $provider = $mapperEduInstitution->detailEducationInstitution( $id );
	    $namesTraining[] = sprintf( $link, $view->baseUrl( '/register/education-institution/edit/id/' . $id ), $provider['institution'] );
	}
	
	$message .= '<ul><li>' . implode( '</li><li>', $namesTraining ) . '</li></ul></p>';
	return $message;
    }
    
    /**
     *
     * @param int $drhTraining
     * @return string 
     */
    public function getDrhTrainingPlan( $drhTraining )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$DRHNum = Fefop_Model_Mapper_DRHTrainingPlan::buildNum( $drhTraining->id_drh_trainingplan );
	$drhLink = sprintf( 
			    $link, 
			    $view->baseUrl( '/fefop/drh-training-plan/edit/id/' . $drhTraining->id_drh_trainingplan ), 
			    $DRHNum
		    );

	
	$currency = new Zend_Currency();
	
	return sprintf( 
		    self::DRH_GREATER, 
		    $drhLink,
		    $currency->setValue( Fefop_Model_Mapper_DRHTrainingPlan::LIMIT_AMOUNT )->toCurrency(),
		    $currency->setValue( $drhTraining->amount )->toCurrency()
		);
    }
    
    /**
     *
     * @param int $riContract
     * @return string 
     */
    public function getRIGreaterAmount( $riContract )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$riContractNum = Fefop_Model_Mapper_Contract::buildNumRow( $riContract );
	$riLink = sprintf( 
			    $link, 
			    $view->baseUrl( '/fefop/ri-contract/edit/id/' . $riContract->id_ri_contract ), 
			    $riContractNum
		    );

	
	$currency = new Zend_Currency();
	
	return sprintf( 
		    self::RI_AMOUNT_GREATER, 
		    $riLink,
		    $currency->setValue( Fefop_Model_Mapper_RIContract::LIMIT_AMOUNT )->toCurrency(),
		    $currency->setValue( $riContract->amount )->toCurrency()
		);
    }
    
    /**
     *
     * @param int $riContract
     * @return string 
     */
    public function getRIGreaterDuration( $riContract )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$riContractNum = Fefop_Model_Mapper_Contract::buildNumRow( $riContract );
	$riLink = sprintf( 
			    $link, 
			    $view->baseUrl( '/fefop/ri-contract/edit/id/' . $riContract->id_ri_contract ), 
			    $riContractNum
		    );
	
	//'RI KONTRATU NUMERU %s TEMPU DURASAUN LIU %s, HO TEMPU DURASAUN: %s'
	
	$measure = new Zend_Measure_Time( Fefop_Model_Mapper_RIContract::MOUNTH_LIMIT, Zend_Measure_Time::MONTH );
	$diffMonths = $measure->convertTo( Zend_Measure_Time::YEAR, 0 );
	
	$dateInit = new Zend_Date( $riContract->date_start );
	$dateFinish = new Zend_Date( $riContract->date_finish );
	
	$diff = $dateFinish->sub( $dateInit );
	
	$measure = new Zend_Measure_Time( $diff->toValue(), Zend_Measure_Time::SECOND );
	$diffYear = $measure->convertTo( Zend_Measure_Time::YEAR, 0 );

	return sprintf( 
		    self::RI_DURATION_GREATER, 
		    $riLink,
		    $diffMonths,
		    $diffYear
		);
    }
    
    /**
     *
     * @param int $feContract
     * @return string 
     */
    public function getFEGreaterDuration( $feContract )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$feContractNum = Fefop_Model_Mapper_Contract::buildNumRow( $feContract );
	$feLink = sprintf( 
			    $link, 
			    $view->baseUrl( '/fefop/fe-contract/edit/id/' . $feContract->id_fe_contract ), 
			    $feContractNum
		    );
	
	$measure = new Zend_Measure_Time( Fefop_Model_Mapper_FEContract::MOUNTH_LIMIT, Zend_Measure_Time::MONTH );
	$diffMonths = $measure->convertTo( Zend_Measure_Time::MONTH, 0 );
	
	$dateInit = new Zend_Date( $feContract->date_start );
	$dateFinish = new Zend_Date( $feContract->date_finish );
	
	$diff = $dateFinish->sub( $dateInit );
	
	$measure = new Zend_Measure_Time( $diff->toValue(), Zend_Measure_Time::SECOND );
	$diiffMonth = $measure->convertTo( Zend_Measure_Time::MONTH, 0 );

	return sprintf( 
		    self::FE_DURATION_GREATER, 
		    $feLink,
		    $diffMonths,
		    $diiffMonth
		);
    }
    
    /**
     *
     * @param int $feContract
     * @return string 
     */
    public function getFEGraduation( $feContract )
    {
	$view = Zend_Layout::getMvcInstance()->getView();
	
	$link = '<a href="%s" target="_blank">%s</a>';
	$feContractNum = Fefop_Model_Mapper_Contract::buildNumRow( $feContract );
	
	$feLink = sprintf( 
			    $link, 
			    $view->baseUrl( '/fefop/fe-contract/edit/id/' . $feContract->id_fe_contract ), 
			    $feContractNum
		    );
	
	$clientLink = sprintf( 
			    $link, 
			    $view->baseUrl( '/client/client/view/id/' . $feContract->fk_id_perdata ), 
			    $feContract->beneficiary
		    );
	
	return sprintf( 
		    self::FE_GRADUATION, 
		    $feLink,
		    $clientLink
		);
    }
}