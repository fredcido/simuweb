StudentClass = window.StudentClass || {};

StudentClass.JobTraining = {
    
    init: function()
    {
	this.initFormSearch();
	this.initForm();
    },
   
    initFormSearch: function()
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
		    $( '#job-training-list tbody' ).empty();
	     
		    oTable = $( '#job-training-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#job-training-list tbody' ).html( response );
		    
		    General.drawTables( '#job-training-list' );
		    General.scrollTo( '#job-training-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	}
    
	Form.addValidate( form, submit );
    },
    
    initForm: function()
    {
	if ( !$( '#container-job-training' ).length )
	    return false;
	
	Portlet.init( '#container-job-training' );
	
	this.configInformation();
    },
    
    configInformation: function()
    {
	var form  = $( '#information form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( General.empty( $( '#id_jobtraining' ).val() ) ) {
			
			    $( form ).find( '#id_jobtraining' ).val( response.id );

			    window.history.replaceState( {}, "Job Training Edit", BASE_URL + "/student-class/job-training/edit/id/" + response.id );

			    $( '#container-job-training .dynamic-portlet' ).each(
				function()
				{
				    dataUrl = $( this ).attr( 'data-url' );
				    $( this ).attr( 'data-url', dataUrl + response.id );
				}
			    );
				
			    // Release all the steps and go to step 1
			    Portlet.releaseSteps( 1, true );
			    
			    $( '#fk_id_fefpenterprise' ).attr( 'disabled', true ).trigger( 'liszt:updated' );
			    $( '#fk_id_dec' ).attr( 'disabled', true ).trigger( 'liszt:updated' );
			    
			    $( '#container-print' ).show();
			}
		    }
		    
		    StudentClass.JobTraining.reloadFinish();
		}
	    };

	    Form.submitAjax( form, obj );
	    
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	form.find( '#date_start, #date_finish' ).blur(
	    function()
	    {
		if ( General.empty( $( '#date_start' ).val() ) || General.empty( $( '#date_finish' ).val() ) ) {
		    
		    form.find( '#duration' ).val( '' );
		    return false;
		}
		
		$.ajax(
		    {
			type: 'POST',
			dataType: 'json',
			url: General.getUrl( '/student-class/job-training/calculate-month' ),
			data: {
			    data_ini: form.find( '#date_start' ).val(),
			    date_fim: form.find( '#date_finish' ).val()
			},
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
			    form.find( '#duration' ).val( response.duration );
			},
			error: function ()
			{
			    Message.msgError( 'Operasaun la diak', form );
			    form.find( '#duration' ).val( '' );
			}
		    }
		);
	    }
	);
	
	StudentClass.JobTraining.configCalcTotalStudents();
    },
    
    configCalcTotalStudents: function()
    {
	$( '#total_man, #total_woman' ).change(
	    function()
	    {
		men = General.getFieldFloatValue( '#total_man' );
		women = General.getFieldFloatValue( '#total_woman' );
		
		$( '#total_participants' ).val( men + women );
	    }
	);
    },
    
    configCourse: function( pane )
    {
	var form  = $( '#course form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#course #clear' ).trigger( 'click' );
			StudentClass.JobTraining.loadCourse();
		    }
		    
		    StudentClass.JobTraining.reloadFinish();
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	StudentClass.JobTraining.loadCourse();
	StudentClass.JobTraining.configCategory();
    },
    
    loadCourse: function()
    {
	General.loadTable( '#course-list', '/student-class/job-training/list-course/id/' + $( '#id_jobtraining' ).val() );
    },

    removeCourse: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'course' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/student-class/job-training/delete-course/' ),
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#course-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#course-list' );
		    },
		    success: function ( response )
		    {
			StudentClass.JobTraining.loadCourse();
			StudentClass.JobTraining.reloadFinish();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#course' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos kursu ida ne\'e ?', 'Hamoos Kursu', remove );
    },
    
    configCategory: function()
    {
	$( '#category' ).change(
	    function()
	    {
		var category = $( this ).val();
		if ( General.empty( category ) )
		    return false;
		
		url = '/student-class/job-training/search-course/type/2/category/' + $( this ).val();
		General.loadCombo( url, 'fk_id_perscholarity' );
	    }
	);
    },
    
    configCandidate: function()
    {
	General.setTabsAjax( '#candidate .tabbable', StudentClass.JobTraining.configFormCandidate );
    },
    
    configFormCandidate: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( StudentClass.JobTraining[method], pane );
    },
    
    configMatch: function( pane )
    {
	var form  = pane.find( 'form' );
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
		success: function( response )
		{
		    pane.find( '#match-list tbody' ).empty();

		    oTable = pane.find( '#match-list' ).dataTable();
		    oTable.fnDestroy(); 

		    pane.find( '#match-list tbody' ).html( response );

		    General.drawTables( pane.find( '#match-list' ) );
		    General.scrollTo( pane.find( '#match-list' ), 800 );
		    
		    App.initUniform();
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
    
    configCandidates: function()
    {
	callback = function()
	{
	    if ( !$( '#candidate-list tbody tr' ).length )
		$( '#info-class-list' ).show();
	    else
		$( '#info-class-list' ).hide();
	    
	    App.initUniform();
	};
	
	General.loadTable( '#candidate-list', '/student-class/job-training/list-candidate/id/' + $( '#id_jobtraining' ).val(), callback );
	return false;
    },
    
    
    addListCandidate: function( button )
    {
	container = $( button ).closest( 'form' );
	Message.clearMessages( container );
	
	var clients = [];
	if ( $( '#match-list' ).dataTable ) {
	    
	    dataTable = $( '#match-list' ).dataTable();
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
	
	var data = $( container ).serializeArray();
	for ( i in clients )
	    data.push( {name: 'clients[]', value: clients[i]} );
	
	$.ajax(
	    {
		type: 'POST',
		data: $.param( data ),
		dataType: 'json',
		url: General.getUrl( '/student-class/job-training/add-list/' ),
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

			var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
			Message.msgError( msg, container );

		    } else {

			container.find( '.table' ).dataTable().fnClearTable();		
			container[0].reset();
			Message.msgSuccess( 'Kliente sira iha lista.', container );
			
			StudentClass.JobTraining.configCandidates();
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
	
	return false;
    },
    
    saveTrainee: function()
    {
	container = $( '#candidate-list' ).parents( '.box-content' );
	Message.clearMessages( container );
	
	var clients = [];
	if ( $( '#candidate-list' ).dataTable ) {
	    
	    dataTable = $( '#candidate-list' ).dataTable();
	    $( 'input:not(:disabled):checked', dataTable.fnGetNodes() ).each(
		function()
		{
		    clients.push( $( this ).val() );
		}
	    );
	}
	
	if ( !clients.length ) {
	    
	    Message.msgError( 'Tenke hili kandidatu ba halo lista Partisipante.', container );
	    return false;
	}
	
	$.ajax(
	    {
		type: 'POST',
		data: {
		    id_jobtraining: $( '#id_jobtraining' ).val(),
		    clients: clients
		},
		dataType: 'json',
		url: General.getUrl( '/student-class/job-training/save-trainee/' ),
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

			var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
			Message.msgError( msg, container );

		    } else {
			
			StudentClass.JobTraining.configCandidates();
			StudentClass.JobTraining.configTrainee();
			StudentClass.JobTraining.reloadFinish();
			
			Message.msgSuccess( 'Kandidatu sira iha lista Partisipante.', container );
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
	
	return false;
    },
    
    configTrainee: function()
    {
	StudentClass.JobTraining.loadClient();
    },
    
    loadClient: function()
    {
	callback = function()
	{
	    if ( !$( '#client-list tbody tr' ).length ) {
		
		$( '#info-job-training-client' ).show();
		
	    } else {
		
		$( '#info-job-training-client' ).hide();
	    }
	    
	    App.initUniform();
	};
	
	General.loadTable( '#client-list', '/student-class/job-training/list-client/id/' + $( '#id_jobtraining' ).val(), callback );
    },
    
    editTrainee: function( id )
    {
	var settings = {
	    title: 'Edita Partisipante',
	    data: {
		trainee: id,
		id: $( '#id_jobtraining' ).val()
	    },
	    url: '/student-class/job-training/edit-trainee/',
	    callback: function( modal )
	    {
		var form  = modal.find( 'form' );
		submit = function()
		{
		    App.blockUI( form );
		    var obj = {
			callback: function( response )
			{
			    App.unblockUI( form );
			    
			    if ( response.status ) {
				
				if ( !General.empty( modal.find( '#contract' ).val() ) )
				    StudentClass.JobTraining.editExperience( modal.find( '#fk_id_perdata' ).val() );
				
				modal.modal( 'hide' );
			    }

			    StudentClass.JobTraining.loadClient();
			}
		    };

		    Form.submitAjax( form, obj );		    
		    return false;
		};
		
		form.find( '#date_start, #date_finish' ).change(
		    function()
		    {
			if ( General.empty( $( this ).val() ) ) {

			    form.find( '#duration' ).val( '' );
			    return false;
			}

			$.ajax(
			    {
				type: 'POST',
				dataType: 'json',
				url: General.getUrl( '/student-class/job-training/calculate-month' ),
				data: {
				    data_ini: form.find( '#date_start' ).val(),
				    date_fim: form.find( '#date_finish' ).val()
				},
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
				    form.find( '#duration' ).val( response.duration );
				},
				error: function ()
				{
				    Message.msgError( 'Operasaun la diak', form );
				    form.find( '#duration' ).val( '' );
				}
			    }
			);
		    }
		);

		Form.addValidate( form, submit );
		Form.init();
	    }
	};

	General.ajaxModal( settings );
    },
    
    editExperience: function( id )
    {
	var settings = {
	    title: 'Esperiensia Profisional',
	    url: '/client/client/experience/id/' + id,
	    callback: function( modal )
	    {
		Client.Client.configExperience( modal );
		Form.init();
	    }
	};

	General.ajaxModal( settings );
    },
    
    reloadFinish: function()
    {
	General.loading( true );
	
	$( '#finish-list tbody' ).load(
	    General.getUrl( '/student-class/job-training/list-finish/id/' + $( '#id_jobtraining' ).val() ),
	    {},
	    function()
	    {
		if ( $( '#button-finish' ).hasClass( 'disabled' ) || $( '#finish-list td i.icon-warning-sign' ).length )
		    $( '#button-finish' ).attr( 'disabled', true );
		else
		    $( '#button-finish' ).removeAttr( 'disabled' );
		
		General.loading( false );
	    }
	);
    },
    
    finishJobTraining: function()
    {
	var container = $( '#finish .box-content' );
	if ( $( '#finish-list td i.icon-warning-sign' ).length ) {
	    
	    Message.msgError( 'Erro: La bele remata job training! Haree kriterio sira.', container );
	    return false;
	}

	$.ajax(
	    {
		type: 'POST',
		data: {
		    id: $( '#id_jobtraining' ).val()
		},
		dataType: 'json',
		url: General.getUrl( '/student-class/job-training/finish-job-training/' ),
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
    
    searchInstitute: function()
    {	
	var settings = {
	    title: 'Buka Inst. Ensinu',
	    url: '/student-class/job-training/search-institute/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		StudentClass.JobTraining.initFormSearchInstitute( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormSearchInstitute: function( modal )
    {
	var form  = modal.find( 'form' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    var data = $( form ).serializeArray();
	    data.push( {name: 'list-ajax', value: 1} );
	    
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: data,
		dataType: 'text',
		url: General.getUrl( '/student-class/job-training/search-institute-forward' ),
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
		    $( '#education-institute-list tbody' ).empty();
	     
		    oTable = $( '#education-institute-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#education-institute-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#education-institute-list tbody a.action-ajax' ).click(
			    function()
			    {
				StudentClass.JobTraining.setInstitute( $( this ).data( 'id' ), modal );
			    }
			);
		    };
		    
		    General.drawTables( '#education-institute-list', callbackClick );
		    General.scrollTo( '#education-institute-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
    },
    
    setInstitute: function( id, modal )
    { 
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/student-class/job-training/fetch-institute/' ),
		data: {id: id},
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
		   $( '#fk_id_fefpeduinstitution, #fk_id_fefpenterprise' ).val( '' );
		   $( 'form' ).populate( response, {resetForm: false} );
		   
		   General.scrollTo( '#breadcrumb' );
		   
		   $( '#entity' ).trigger( 'change' );
		   modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    searchEnterprise: function()
    {
	var settings = {
	    title: 'Buka Empreza',
	    url: '/student-class/job-training/search-enterprise/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		StudentClass.JobTraining.initFormSearchEnterprise( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormSearchEnterprise: function( modal )
    {
	var form  = modal.find( 'form' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    var data = $( form ).serializeArray();
	    data.push( {name: 'list-ajax', value: 1} );
	    
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: data,
		dataType: 'text',
		url: General.getUrl( '/student-class/job-training/search-enterprise-forward' ),
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
		    $( '#enterprise-list tbody' ).empty();
	     
		    oTable = $( '#enterprise-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#enterprise-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#enterprise-list tbody a.action-ajax' ).click(
			    function()
			    {
				StudentClass.JobTraining.setEnterprise( $( this ).data( 'id' ), modal );
			    }
			);
		    };
		    
		    General.drawTables( '#enterprise-list', callbackClick );
		    General.scrollTo( '#enterprise-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	}
    
	Form.addValidate( form, submit );
    },
    
    setEnterprise: function( id, modal )
    {
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/student-class/job-training/fetch-enterprise/' ),
		data: {id: id},
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
		   $( '#fk_id_fefpeduinstitution, #fk_id_fefpenterprise' ).val( '' );
		   $( 'form' ).populate( response, {resetForm: false} );
		   General.scrollTo( '#breadcrumb' );
		   modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    printStudentClass: function()
    {
	General.newWindow( General.getUrl( '/student-class/job-training/print/id/' + $( '#id_jobtraining' ).val() ), 'Imprime Job Training' );
    }
};

$( document ).ready(
    function()
    {
	StudentClass.JobTraining.init();
    }
);