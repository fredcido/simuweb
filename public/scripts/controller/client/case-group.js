Client = window.Client || {};

Client.CaseGroup = {
    
    interventionSelected: null,
    modalCurrent: null,
    vacancySelected: null,
    classSelected: null,
    jobTrainingSelected: null,
   
   init: function()
   {
       if ( !$( '#container-case-group' ).length )
	    return false;
	
	Portlet.init( '#container-case-group' );
	
	this.configInformation();
   },
   
   configInformation: function()
   {
       var form  = $( '#information form' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( General.empty( $( '#id_action_plan_group' ).val() ) ) {
			    
			    $( '#information form' ).find( '#id_action_plan_group' ).val( response.id );
			    window.history.replaceState( {}, "Case Group Edit", General.getUrl( "/client/case-group/edit/id/" + response.id ) );

			    $( '#container-case-group .dynamic-portlet' ).each(
				function()
				{
				    dataUrl = $( this ).attr( 'data-url' );
				    $( this ).attr( 'data-url', dataUrl + response.id );
				}
			    );

			    // Release the next step
			    Portlet.releaseStepByIndex( 1, true );
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
   },
   
   configClient: function()
   {
       Client.CaseGroup.initFormSearchClient();
       Client.CaseGroup.listClientGroup();
   },
   
   initFormSearchClient: function()
   {
        var form  = $( 'form#search' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    var pars = $( form ).serialize();
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: pars,
		dataType: 'text',
		url: form.attr( 'action' ),
		beforeSend: function()
		{
		    General.loading( true );
		},
		complete: function()
		{
		    General.loading( false );
		},
		success: function ( response )
		{
		    $( '#client-list tbody' ).empty();
	     
		    oTable = $( '#client-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#client-list tbody' ).html( response );
		    
		    General.drawTables( '#client-list' );
		    General.scrollTo( '#client-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	}
    
	Form.addValidate( form, submit );
	Form.handleClientSearch( form );
   },
   
   setRequiredGroup: function ( value, flag )
   {
       if ( General.empty( value ) )
	   return false;
       
       if ( 'C' == flag ) {
	   
	   Form.makeRequired( '#fk_id_addcountry', true );
	   Form.makeRequired( '#fk_id_fefpeduinstitution', false );
	   $( '#fk_id_fefpeduinstitution' ).val( '' ).trigger( 'change' );
	   
       } else {
       
	   Form.makeRequired( '#fk_id_fefpeduinstitution', true );
	   Form.makeRequired( '#fk_id_addcountry', false );
	   $( '#fk_id_addcountry' ).val( '' ).trigger( 'change' );
       }
   },
   
   addClients: function( button )
   {
	container = $( button ).closest( 'form' );
	Message.clearMessages( container );
	
        var clients = [];
	if ( $( '#client-list' ).dataTable ) {
	    
	    dataTable = $( '#client-list' ).dataTable();
	    $( 'input:not(:disabled):checked', dataTable.fnGetNodes() ).each(
		function()
		{
		    clients.push( $( this ).val() );
		}
	    );
	}
	
	if ( !clients.length ) {
	    
	    Message.msgError( 'Tenke hili kliente ba hatama.', container );
	    return false;
	}
	
	if ( General.empty( $( '#fk_id_profocupationtimor' ).val() ) ) {
	    
	    Message.msgError( 'Tenke hili Meta Empregu.', container );
	    $( '#fk_id_profocupationtimor' ).closest( '.control-group' ).addClass( 'error' );
	    
	    return false;
	} else
	    $( '#fk_id_profocupationtimor' ).closest( '.control-group' ).removeClass( 'error' );
	 
	
	$.ajax(
	    {
		type: 'POST',
		data: {
		    occupation: $( '#fk_id_profocupationtimor' ).val(),
		    case_group: $( '#id_action_plan_group' ).val(),
		    clients: clients
		},
		dataType: 'json',
		url: General.getUrl( '/client/case-group/add-client/' ),
		beforeSend: function()
		{
		    App.blockUI( '#client-list' );
		    General.scrollTo( '#client-list' );
		},
		complete: function()
		{
		    App.unblockUI( '#client-list' );
		},
		success: function ( response )
		{
		    if ( !response.status ) {

			var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
			Message.msgError( msg, container );

		    } else {

			$( '#client-list' ).dataTable().fnClearTable();		
			container.find( '#clear' ).trigger( 'click' );
			Message.msgSuccess( 'Kliente sira iha lista.', container );
			
			Client.CaseGroup.listClientGroup();
			General.scrollTo( '#client-group-list' );
			
			// Release the next step
			Portlet.releaseStepByIndex( 2 );
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
   },
   
   listClientGroup: function()
   {
       General.loadTable( '#client-group-list', '/client/case-group/list-client-group/id/' + $( '#id_action_plan_group' ).val() );
   },
   
   documentsCase: function( idCase, client)
   {
	var data = {
	    client: client,
	    'case': idCase
	};

	File.manager( data );
    },
    
   configActionPlan: function()
   {
       var form  = $( '#action-plan form' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    if ( !$( '#barrier-list tbody tr' ).length ) {
		
		Message.msgError( 'Tenki tau Barreiras ho Intervensaun sira.', $( '#action-plan form') );
		return false;
	    }
	    
	    var valid = true;
	    var thereIs = false;
	    
	    $( '#barrier-list .control-group' ).removeClass( 'error' );
	    $( '#barrier-list select' ).each(
		function()
		{
		    thereIs = true;
		    if ( General.empty( $( this ).val() ) ) {
			
			valid = false;
			$( this ).closest( '.control-group' ).addClass( 'error' );
		    }
		}
	    );
		
	    if ( !valid ) {
		
		Message.msgError( 'Tenki tau dadus hotu-hotu ba iha tabela kraik.', $( '#action-plan form') );
		return false;
	    }
	    
	    if ( !thereIs ) {
		
		Message.msgError( 'la iha intervensaun foun.', $( '#action-plan form') );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			Client.CaseGroup.listBarriers();
			Client.CaseGroup.listClientGroup();
			// Release the next step
			Portlet.releaseStepByIndex( 3 );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	Client.CaseGroup.listBarriers();
   },
   
   listBarriers: function()
   {
       General.loadTable( '#barrier-list', '/client/case-group/list-barriers/id/' + $( '#id_action_plan_group' ).val() );
   },
   
   setResultBarrier: function ( item )
   {
       var data = $( item ).closest( 'tr' ).data( 'row' );
       
       var settings = {
	    title: 'Rezultadu Barreira',
	    data: $.param( data ),
	    url: '/client/case-group/result-barrier/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Client.CaseGroup.listCaseResult( data );
		Client.CaseGroup.configFormResult( modal, data );
	    }
	};

	General.ajaxModal( settings );
   },
   
   printBarrier: function ( item, all )
   {
       var data = $( item ).closest( 'tr' ).data( 'row' );
       
       General.newWindow( General.getUrl( '/client/case-group/print-barrier/id/' + $( '#id_action_plan_group' ).val() + '/all/' + all + '?' + $.param( data ) ), 'Imprime Barreira Jestaun Kazu Grupu' );
   },
   
   configFormResult: function( modal, data )
   {
	var form  = modal.find( 'form' );
	if ( !form.length )
	    return false;
	
	submit = function()
	{   
	    var status = [];
	    dataTable = $( '#result-client-list' ).dataTable();
	    $( 'select:not(:disabled)', dataTable.fnGetNodes() ).each(
		function()
		{
		    status.push( {name: 'status['+ $( this ).attr( 'id' ) + ']', value: $( this ).val()} );
		}
	    );
		
	    if ( !status.length ) {
		
		Message.msgError( 'La iha kliente atu atualiza rezultadu.', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			Client.CaseGroup.listCaseResult( data );
			Client.CaseGroup.listClientGroup();
			Client.CaseGroup.reloadFinish();
		    }
		},
		data: status
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
   },
   
   listCaseResult: function( data )
   {
       $.ajax(
	    {
		type: 'POST',
		data: data,
		dataType: 'text',
		url: General.getUrl( '/client/case-group/list-case-result/' ),
		beforeSend: function()
		{
		    App.blockUI( '#result-client-list' );
		},
		complete: function()
		{
		    App.unblockUI( '#result-client-list' );
		},
		success: function ( response )
		{
		    oTable = $( '#result-client-list' ).dataTable();
		    if ( oTable )
			oTable.fnDestroy(); 

		    $( '#result-client-list' ).find( 'tbody' ).empty().html( response );
		    General.drawTables( '#result-client-list' );
		},
		error: function()
		{
		    console.log( arguments );
		}
	    }
	);
   },
   
   searchJob: function( barrier )
   {
	this.interventionSelected = barrier;
	
	var settings = {
	    title: 'Buka Empregu',
	    url: '/client/case-group/job-barrier/barrier/' + barrier + '/id/' + $( '#id_action_plan_group' ).val(),
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Client.CaseGroup.listJobBarrier();
		Client.CaseGroup.modalCurrent = modal;
		General.setTabsAjax( modal.find( '.tabbable' ), Client.CaseGroup.configSearchJob );
	    }
	};

	General.ajaxModal( settings );
    },
    
    configSearchJob: function( pane )
    {
	form = pane.find( 'form' );
	
	submit = function()
	{
	    var data = $( form ).serializeArray();
	    data.push( {name: 'list-ajax', value: 1} );
	    
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: $.param( data ),
		dataType: 'text',
		url: form.attr( 'action' ),
		beforeSend: function()
		{
		    App.blockUI( form );
		},
		complete: function()
		{
		    App.unblockUI( form );
		},
		success: function ( response )
		{
		    $( '#vacancy-list tbody' ).empty();
	     
		    oTable = $( '#vacancy-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#vacancy-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#vacancy-list tbody a.action-ajax' ).click(
			    function()
			    {
				Client.CaseGroup.vacancyClients( $( this ).data( 'id' ) );
			    }
			);
		    };
		    
		    General.drawTables( '#vacancy-list', callbackClick );
		    General.scrollTo( '#vacancy-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	}
    
	Form.addValidate( form, submit );
	
	$( '#open_date' ).daterangepicker(
	    {
                format: 'dd/MM/yyyy',
                separator: ' to\'o '
            },
	    function( start, end )
	    {
		$( '#open_date' ).val( start.toString( 'dd/MM/yyyy' ) );
		$( '#close_date' ).val( end.toString( 'dd/MM/yyyy' ) );
	    }
        );
    },
    
    listJobBarrier: function()
    {
	General.loadTable( '#job-barrier-list', '/client/case-group/list-job-barrier-rows/intervention/' + Client.CaseGroup.interventionSelected + '/case/' + $( '#id_action_plan_group' ).val() );
    },
    
    vacancyClients: function( vacancy )
    {
	this.vacancySelected = vacancy;
	Client.CaseGroup.modalCurrent.modal( 'hide' );
	
	var settings = {
	    title: 'Vaga Empregu',
	    url: '/client/case-group/job-client/vacancy/' + vacancy + '/id/' + $( '#id_action_plan_group' ).val(),
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Client.CaseGroup.modalCurrent = modal;
		Client.CaseGroup.listClientVacancy( vacancy );
	    }
	};

	General.ajaxModal( settings );
    },
    
    listClientVacancy: function( vacancy )
    {
	General.loadTable( '#client-vacancy-list', '/client/case-group/list-client-vacancy/vacancy/' + vacancy + '/case/' + $( '#id_action_plan_group' ).val() );
    },
    
    clientsToVacancy: function( button )
    {
	var cases = [];
	var container = $( button ).closest( '.modal-body' );
	
	dataTable = $( '#client-vacancy-list' ).dataTable();
	$( 'input:not(:disabled):checked', dataTable.fnGetNodes() ).each(
	    function()
	    {
		cases.push( $( this ).val() );
	    }
	);
	    
	if ( General.empty( cases ) ) {

	    Message.msgError( 'Tenki hili kliente atu haruka ba lista kandidatu', container );
	    return false;
	}
	
	$.ajax({
	    type: 'POST',
	    data: {
		vacancy: Client.CaseGroup.vacancySelected,
		case_id: $( '#id_action_plan_group' ).val(),
		cases: cases,
		intervention: Client.CaseGroup.interventionSelected
	    },
	    dataType: 'json',
	    url: General.getUrl( '/client/case-group/client-to-vacancy/' ),
	    beforeSend: function()
	    {
		App.blockUI( container );
	    },
	    complete: function()
	    {
		App.unblockUI( container );
	    },
	    success: function ( response )
	    {
		if ( !response.status ) {

		    var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
		    Message.msgError( msg, container );

		} else {

		    Message.msgSuccess( 'Kliente sira iha lista kandidatu.', container );
		    Client.CaseGroup.listClientVacancy( Client.CaseGroup.vacancySelected );
		}
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', container );
	    }
	});
    },
    
    searchJobTraining: function( barrier )
    {
	this.interventionSelected = barrier;
	
	var settings = {
	    title: 'Buka Job Training',
	    url: '/client/case-group/job-training-barrier/barrier/' + barrier + '/id/' + $( '#id_action_plan_group' ).val(),
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Client.CaseGroup.listJobTrainingBarrier();
		Client.CaseGroup.modalCurrent = modal;
		General.setTabsAjax( modal.find( '.tabbable' ), Client.CaseGroup.configSearchJobTraining );
	    }
	};

	General.ajaxModal( settings );
    },
    
    configSearchJobTraining: function( pane )
    {
	form = pane.find( 'form' );
	
	submit = function()
	{
	    var data = $( form ).serializeArray();
	    data.push( {name: 'list-ajax', value: 1} );
	    
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: $.param( data ),
		dataType: 'text',
		url: form.attr( 'action' ),
		beforeSend: function()
		{
		    App.blockUI( form );
		},
		complete: function()
		{
		    App.unblockUI( form );
		},
		success: function ( response )
		{
		    $( '#job-training-list tbody' ).empty();
	     
		    oTable = $( '#job-training-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#job-training-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#job-training-list tbody a.action-ajax' ).click(
			    function()
			    {
				Client.CaseGroup.jobTrainingClients( $( this ).data( 'id' ) );
			    }
			);
		    };
		    
		    General.drawTables( '#job-training-list', callbackClick );
		    General.scrollTo( '#job-training-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
	
	Form.addValidate( form, submit );
    },
    
    listJobTrainingBarrier: function()
    {
	General.loadTable( '#job-training-barrier-list', '/client/case-group/list-job-training-barrier-rows/intervention/' + Client.CaseGroup.interventionSelected + '/case/' + $( '#id_action_plan_group' ).val() );
    },
    
    jobTrainingClients: function( id_job_training )
    {
	this.jobTrainingSelected = id_job_training;
	Client.CaseGroup.modalCurrent.modal( 'hide' );
	
	var settings = {
	    title: 'Job Training',
	    url: '/client/case-group/job-training-client/job-training/' + id_job_training + '/id/' + $( '#id_action_plan_group' ).val(),
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Client.CaseGroup.modalCurrent = modal;
		Client.CaseGroup.listClientJobTraining( id_job_training );
	    }
	};

	General.ajaxModal( settings );
    },
    
    listClientJobTraining: function( id_job_training )
    {
	General.loadTable( '#client-job-training-list', '/client/case-group/list-client-job-training/job-training/' + id_job_training + '/case/' + $( '#id_action_plan_group' ).val() );
    },
    
    clientsToJobTraining: function( button )
    {
	var cases = [];
	var container = $( button ).closest( '.modal-body' );
	
	dataTable = $( '#client-job-training-list' ).dataTable();
	$( 'input:not(:disabled):checked', dataTable.fnGetNodes() ).each(
	    function()
	    {
		cases.push( $( this ).val() );
	    }
	);
	    
	if ( General.empty( cases ) ) {

	    Message.msgError( 'Tenki hili kliente atu haruka ba lista kandidatu', container );
	    return false;
	}
	
	$.ajax({
	    type: 'POST',
	    data: {
		idJobTraining: Client.CaseGroup.jobTrainingSelected,
		case_id: $( '#id_action_plan_group' ).val(),
		cases: cases,
		intervention: Client.CaseGroup.interventionSelected
	    },
	    dataType: 'json',
	    url: General.getUrl( '/client/case-group/client-to-job-training/' ),
	    beforeSend: function()
	    {
		App.blockUI( container );
	    },
	    complete: function()
	    {
		App.unblockUI( container );
	    },
	    success: function ( response )
	    {
		if ( !response.status ) {

		    var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
		    Message.msgError( msg, container );

		} else {

		    Message.msgSuccess( 'Kliente sira iha lista kandidatu.', container );
		    Client.CaseGroup.listClientJobTraining( Client.CaseGroup.jobTrainingSelected );
		}
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', container );
	    }
	});
    },
    
    searchClass: function( barrier )
    {
	this.interventionSelected = barrier;
	
	var settings = {
	    title: 'Buka Klase Formasaun',
	    url: '/client/case-group/class-barrier/barrier/' + barrier + '/id/' + $( '#id_action_plan_group' ).val(),
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Client.CaseGroup.listClassBarrier();
		Client.CaseGroup.modalCurrent = modal;
		General.setTabsAjax( modal.find( '.tabbable' ), Client.CaseGroup.configSearchClass );
	    }
	};

	General.ajaxModal( settings );
    },
    
    configSearchClass: function( pane )
    {
	form = pane.find( 'form' );
	
	submit = function()
	{
	    var data = $( form ).serializeArray();
	    data.push( {name: 'list-ajax', value: 1} );
	    
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: $.param( data ),
		dataType: 'text',
		url: form.attr( 'action' ),
		beforeSend: function()
		{
		    App.blockUI( form );
		},
		complete: function()
		{
		    App.unblockUI( form );
		},
		success: function ( response )
		{
		    $( '#class-list tbody' ).empty();
	     
		    oTable = $( '#class-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#class-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#class-list tbody a.action-ajax' ).click(
			    function()
			    {
				Client.CaseGroup.classClients( $( this ).data( 'id' ) );
			    }
			);
		    };
		    
		    General.drawTables( '#class-list', callbackClick );
		    General.scrollTo( '#class-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
	
	Form.addValidate( form, submit );
	
	$( '#open_date' ).daterangepicker(
	    {
                format: 'dd/MM/yyyy',
                separator: ' to\'o '
            },
	    function( start, end )
	    {
		$( '#open_date' ).val( start.toString( 'dd/MM/yyyy' ) );
		$( '#close_date' ).val( end.toString( 'dd/MM/yyyy' ) );
	    }
        );
    },
    
    listClassBarrier: function()
    {
	General.loadTable( '#class-barrier-list', '/client/case-group/list-class-barrier-rows/intervention/' + Client.CaseGroup.interventionSelected + '/case/' + $( '#id_action_plan_group' ).val() );
    },
    
    classClients: function( id_class )
    {
	this.classSelected = id_class;
	Client.CaseGroup.modalCurrent.modal( 'hide' );
	
	var settings = {
	    title: 'Klase Formasaun',
	    url: '/client/case-group/class-client/class/' + id_class + '/id/' + $( '#id_action_plan_group' ).val(),
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Client.CaseGroup.modalCurrent = modal;
		Client.CaseGroup.listClientClass( id_class );
	    }
	};

	General.ajaxModal( settings );
    },
    
    listClientClass: function( id_class )
    {
	General.loadTable( '#client-class-list', '/client/case-group/list-client-class/class/' + id_class + '/case/' + $( '#id_action_plan_group' ).val() );
    },
    
    clientsToClass: function( button )
    {
	var cases = [];
	var container = $( button ).closest( '.modal-body' );
	
	dataTable = $( '#client-class-list' ).dataTable();
	$( 'input:not(:disabled):checked', dataTable.fnGetNodes() ).each(
	    function()
	    {
		cases.push( $( this ).val() );
	    }
	);
	    
	if ( General.empty( cases ) ) {

	    Message.msgError( 'Tenki hili kliente atu haruka ba lista kandidatu', container );
	    return false;
	}
	
	$.ajax({
	    type: 'POST',
	    data: {
		idClass: Client.CaseGroup.classSelected,
		case_id: $( '#id_action_plan_group' ).val(),
		cases: cases,
		intervention: Client.CaseGroup.interventionSelected
	    },
	    dataType: 'json',
	    url: General.getUrl( '/client/case-group/client-to-class/' ),
	    beforeSend: function()
	    {
		App.blockUI( container );
	    },
	    complete: function()
	    {
		App.unblockUI( container );
	    },
	    success: function ( response )
	    {
		if ( !response.status ) {

		    var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
		    Message.msgError( msg, container );

		} else {

		    Message.msgSuccess( 'Kliente sira iha lista kandidatu.', container );
		    Client.CaseGroup.listClientClass( Client.CaseGroup.classSelected );
		}
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', container );
	    }
	});
    },
    
    reloadFinish: function()
    {
	General.loading( true );
	
	$( '#finish-list tbody' ).load(
	    General.getUrl( '/client/case-group/list-finish/id/' + $( '#id_action_plan_group' ).val() ),
	    {},
	    function()
	    {
		if ( $( '#button-finish' ).hasClass( 'disabled' ) 
		     || $( '#finish-list td i.icon-warning-sign' ).length 
		     || !$( '#finish-list td i' ).length 
		    )
		    $( '#button-finish' ).attr( 'disabled', true );
		else
		    $( '#button-finish' ).removeAttr( 'disabled' );
		
		General.loading( false );
	    }
	);
    },
    
    finishCase: function()
    {
	var container = $( '#finish .box-content' );
	if ( $( '#finish-list td i.icon-warning-sign' ).length ) {
	    
	    Message.msgError( 'Erro: La bele remata kazu grupu! Haree kriterio sira.', container );
	    return false;
	}

	$.ajax(
	    {
		type: 'POST',
		data: {
		    id: $( '#id_action_plan_group' ).val()
		},
		dataType: 'json',
		url: General.getUrl( '/client/case-group/finish-case/' ),
		beforeSend: function()
		{
		    General.loading( true );
		},
		complete: function()
		{
		    General.loading( false );
		},
		success: function ( response )
		{
		    if ( !response.status ) {

			var msg = response.message.length ? response.message[0].message : 'Operasaun la diak';
			Message.msgError( msg, container );

		    } else
			history.go( 0 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
	
	return false;
    },
    
    printCase: function()
    {
	General.newWindow( General.getUrl( '/client/case-group/print/id/' + $( '#id_action_plan_group' ).val() ), 'Imprime Jestaun Kazu Grupu' );
    }
};

$( document ).ready(
    function()
    {
	Client.CaseGroup.init();
    }
);