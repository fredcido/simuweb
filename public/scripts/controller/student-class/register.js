StudentClass = window.StudentClass || {};

StudentClass.Register = {
    
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
		    $( '#class-list tbody' ).empty();
	     
		    oTable = $( '#class-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#class-list tbody' ).html( response );
		    
		    General.drawTables( '#class-list' );
		    General.scrollTo( '#class-list', 800 );
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
	if ( !$( '#container-register' ).length )
	    return false;
	
	Portlet.init( '#container-register' );
	
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
			
			if ( General.empty( $( '#id_fefpstudentclass' ).val() ) ) {
			
			    $( form ).find( '#id_fefpstudentclass' ).val( response.id );

			    window.history.replaceState( {}, "Student Class Edit", BASE_URL + "/student-class/register/edit/id/" + response.id );

			    $( '#container-register .dynamic-portlet' ).each(
				function()
				{
				    dataUrl = $( this ).attr( 'data-url' );
				    $( this ).attr( 'data-url', dataUrl + response.id );
				}
			    );
				
			    // Release all the steps and go to step 1
			    Portlet.releaseSteps( 1, true );
			    
			    $( '#fk_id_fefpeduinstitution' ).attr( 'disabled', true ).trigger( 'liszt:updated' );
			    $( '#fk_id_dec' ).attr( 'disabled', true ).trigger( 'liszt:updated' );
			    
			    $( '#container-print' ).show();
			}
		    }
		    
		    StudentClass.Register.reloadFinish();
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	form.find( '#fk_id_perscholarity' ).change(
	    function()
	    {
		var className = form.find( '#class_name' ).eq( 0 ).val();
		if ( !General.empty( className ) )
		    return false;
		
		var scholarityName = $( this ).find( 'option:selected' ).text();
		form.find( '#class_name' ).eq( 0 ).val( scholarityName );
	    }
	);
	
	StudentClass.Register.configCalcTotalStudents();
	StudentClass.Register.configSearchCourses();
    },
    
    configCalcTotalStudents: function()
    {
	$( '#num_women_student, #num_men_student' ).change(
	    function()
	    {
		men = General.getFieldFloatValue( '#num_men_student' );
		women = General.getFieldFloatValue( '#num_women_student' );
		
		$( '#num_total_student' ).val( men + women );
	    }
	);
    },
    
    configSearchCourses: function()
    {
	$( '#fk_id_fefpeduinstitution' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_perscholarity' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/student-class/register/search-course/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_perscholarity' );
		return true;
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
			StudentClass.Register.loadCourse();
		    }
		    
		    StudentClass.Register.reloadFinish();
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	StudentClass.Register.loadCourse();
	StudentClass.Register.configCategory();
    },
    
    loadCourse: function()
    {
	General.loadTable( '#course-list', '/student-class/register/list-course/id/' + $( '#id_fefpstudentclass' ).val() );
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
		    url: General.getUrl( '/student-class/register/delete-course/' ),
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
			StudentClass.Register.loadCourse();
			StudentClass.Register.reloadFinish();
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
		
		url = '/student-class/register/search-course/type/2/category/' + $( this ).val();
		General.loadCombo( url, 'fk_id_perscholarity' );
	    }
	);
    },
    
    configCandidate: function()
    {
	General.setTabsAjax( '#candidate .tabbable', StudentClass.Register.configFormCandidate );
    },
    
    configFormCandidate: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( StudentClass.Register[method], pane );
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
	
	General.loadTable( '#candidate-list', '/student-class/register/list-candidate/id/' + $( '#id_fefpstudentclass' ).val(), callback );
	return false;
    },
    
    checkAll: function( master )
    {
	var dataTable = $( master ).closest( 'table' ).dataTable();
	
	$( 'input:not(:disabled)', dataTable.fnGetNodes() ).each(
	    function()
	    {
		$( this ).attr( 'checked', !!($( master ).attr( 'checked' )) );
		$.uniform.update( $( this ) );
	    }
	);
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
		url: General.getUrl( '/student-class/register/add-list/' ),
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
			
			StudentClass.Register.configCandidates();
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
    
    doShortlist: function()
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
	    
	    Message.msgError( 'Tenke hili kandidatu ba halo Shortlist.', container );
	    return false;
	}
	
	$.ajax(
	    {
		type: 'POST',
		data: {
		    id_fefpstudentclass: $( '#id_fefpstudentclass' ).val(),
		    clients: clients
		},
		dataType: 'json',
		url: General.getUrl( '/student-class/register/save-shortlist/' ),
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
			
			StudentClass.Register.configCandidates();
			StudentClass.Register.configShortlist();
			
			Message.msgSuccess( 'Kandidatu sira iha Shortlist.', container );
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
    
    configShortlist: function()
    {
	callback = function()
	{
	    if ( !$( '#shortlist-list tbody tr' ).length )
		$( '#info-class-shortlist' ).show();
	    else
		$( '#info-class-shortlist' ).hide();
	    
	    App.initUniform();
	};
	
	General.loadTable( '#shortlist-list', '/student-class/register/list-shortlist/id/' + $( '#id_fefpstudentclass' ).val(), callback );
    },
    
    removeShortlist: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'client' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/student-class/register/delete-shortlist/' ),
		    data: {
			id: id,
			id_studentclass: $( '#id_fefpstudentclass' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( '#shortlist-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#shortlist-list' );
		    },
		    success: function ()
		    {
			StudentClass.Register.configShortlist();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#shortlist' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos kliente ida ne\'e ?', 'Hamoos Kliente', remove );
    },
    
    saveClass: function()
    {
	container = $( '#shortlist .box-content' );
	Message.clearMessages( container );
	
	var clients = [];
	if ( $( '#shortlist-list' ).dataTable ) {
	    
	    dataTable = $( '#shortlist-list' ).dataTable();
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
	
	$.ajax(
	    {
		type: 'POST',
		data: {
		    id_fefpstudentclass: $( '#id_fefpstudentclass' ).val(),
		    clients: clients
		},
		dataType: 'json',
		url: General.getUrl( '/student-class/register/save-class/' ),
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
			
			StudentClass.Register.configShortlist();
			StudentClass.Register.loadClient();
			Message.msgSuccess( 'Kandidatu sira iha Klase Formasaun.', container );
		    }
		    
		    StudentClass.Register.reloadFinish();
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
	
	return false;
    },
    
    configClient: function()
    {
	var form  = $( '#client form' );
	submit = function()
	{
	    if ( !$( '#client-list' ).dataTable().fnGetData().length ) {
		 
		 Message.msgError( 'Erro: La bele atualiza aluno. Klase ida ne\'e seidauk iha partisipante.', $( '#client .box-content' ) );
		 return false;
	    }
	    
	    var data = $( form ).serializeArray();
	    dataTable = $( '#client-list' ).dataTable();
	    
	    $( 'select', dataTable.fnGetNodes() ).each(
		function()
		{
		    data.push( { name: 'status[' + $( this ).attr( 'id' ).replace( /[^0-9]/g, '' ) + ']', value: $( this ).val() } );
		}
	    );
    
	    var valid = true;
	    $( 'input.date-drop', dataTable.fnGetNodes() ).each(
		function()
		{
		    if ( $( this ).hasClass( 'required' ) && General.empty( $( this ).val() ) ) {
			
			valid = false;
			$( this ).closest( '.control-group' ).addClass( 'error' );
		    } else
			$( this ).closest( '.control-group' ).removeClass( 'error' );
		    
		    data.push( { name: 'date_drop[' + $( this ).attr( 'name' ).replace( /[^0-9]/g, '' ) + ']', value: $( this ).val() } );
		}
	    );
    
	    if ( !valid ) {
		
		Message.msgError( 'Erro: Tenki preensi Data Retira ba alunu mak Retira.', $( '#client .box-content' ) );
		 return false;
	    }

	    $.ajax({
		type: 'POST',
		data: data,
		dataType: 'json',
		url: $( form ).attr( 'action' ),
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
		    if ( response.status )
			StudentClass.Register.reloadFinish();
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	    
	    return false;
	};
	
	Form.addValidate( form, submit );
	StudentClass.Register.loadClient();
    },
    
    loadClient: function()
    {
	callback = function()
	{
	    if ( !$( '#client-list tbody tr' ).length ) {
		
		$( '#info-class-client' ).show();
		$( '#fk_id_perscholarity' ).removeAttr( 'disabled' ).trigger( 'liszt:updated' );
		
	    } else {
		
		$( '#info-class-client' ).hide();
		$( '#fk_id_perscholarity' ).attr( 'disabled', true ).trigger( 'liszt:updated' );
	    }
	    
	    App.initUniform();
	    Form.init();
	};
	
	General.loadTable( '#client-list', '/student-class/register/list-client/id/' + $( '#id_fefpstudentclass' ).val(), callback );
    },
    
    setDropOut: function( select )
    {
	var status = $( select ).val();
	var field = $( select ).closest( 'tr' ).find( '.date-drop' );
	
	if ( 'D' === status )
	    field.removeAttr( 'disabled' ).focus().addClass( 'required' );
	else {
	    field.attr( 'disabled', '' ).val( '' ).removeClass( 'required' );
	    field.closest( '.control-group' ).removeClass( 'error' ).find( '.help-block' ).remove();
	}
    },
    
    setDropOutCompentency: function( select, table )
    {
	dataTable = $( table ).dataTable();
	var oneDropped = false;
	$( 'select', dataTable.fnGetNodes() ).each(
	    function()
	    {
		if ( 'D' == $( this ).val() ) {
		    oneDropped = true;
		    return false;
		}
	    }
	);

	var field = $( '#date_drop_out' );
	if ( oneDropped )
	    field.removeAttr( 'disabled' ).rules( 'add', 'required' );
	else
	    field.attr( 'disabled', '' ).val( '' ).rules( 'remove', 'required' );
    },
    
    setResult: function ( flag, item )
    {
	var table = $( item ).closest( 'table' );
	if ( $( table ).dataTable ) {
	    
	    dataTable = $( table ).dataTable();
	    nodes = $( 'select:not(:disabled)', dataTable.fnGetNodes() );
	    
	} else
	    nodes = $( table ).closest( 'table' ).find( 'tbody select:not(:disabled)' );
	
	$( nodes ).each(
	    function()
	    {
		$( this ).val( flag );//.trigger( 'change' );
	    }
	);
    },
    
    reloadFinish: function()
    {
	General.loading( true );
	
	$( '#finish-list tbody' ).load(
	    General.getUrl( '/student-class/register/list-finish/id/' + $( '#id_fefpstudentclass' ).val() ),
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
    
    finishClass: function()
    {
	var container = $( '#finish .box-content' );
	if ( $( '#finish-list td i.icon-warning-sign' ).length ) {
	    
	    Message.msgError( 'Erro: La bele remata klase! Haree kriterio sira.', container );
	    return false;
	}

	$.ajax(
	    {
		type: 'POST',
		data: {
		    id: $( '#id_fefpstudentclass' ).val()
		},
		dataType: 'json',
		url: General.getUrl( '/student-class/register/finish-class/' ),
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
    
    resultCompetencies: function( client )
    {
	var settings = {
	    title: 'Rezultadu Kompetensia Sira',
	    url: '/student-class/register/competencies/client/' + client + '/id/' + $( '#id_fefpstudentclass' ).val(),
	    buttons: [
		{
		    css: 'red',
		    text: 'Atualiza',
		    click: function( modal )
		    {
			modal.find( 'form' ).submit();
		    }
		}
	    ],
	    callback: function( modal )
	    {
		var form  = modal.find( 'form' );
		submit = function()
		{
		    var obj = {
			callback: function( response )
			{
			    if ( response.status ) {

				modal.modal( 'hide' );
				StudentClass.Register.reloadFinish();
				StudentClass.Register.loadClient();
			    }
			}
		    };
		    
		    var data = [];
		    var valid = true;
		    dataTable = form.find( 'table' ).dataTable();
		    $( 'select', dataTable.fnGetNodes() ).each(
			function()
			{
			    var statusFinal = $( this ).val();
			    if ( 'D' === statusFinal && General.empty( form.find( '#date_drop_out' ).val() ) )
				valid = false;
			    
			    data.push( { name: 'status[' + $( this ).attr( 'id' ).replace( /[^0-9]/g, '' ) + ']', value: $( this ).val() } );
			}
		    );
	    
		    if ( !valid ) {
		
			Message.msgError( 'Erro: Tenki preensi Data Retira.', form );
			form.find( '#date_drop_out' ).closest( '.control-group' ).addClass( 'error' );
			return false;
			
		    } else
			form.find( '#date_drop_out' ).closest( '.control-group' ).removeClass( 'error' );
	    
		    obj.data = data;

		    Form.submitAjax( form, obj );
		    return false;
		};

		Form.addValidate( form, submit );
		Form.init();
		General.drawTables( modal.find( 'table' ) );
	    }
	};

	General.ajaxModal( settings );
    },
    
    printStudentClass: function()
    {
	General.newWindow( General.getUrl( '/student-class/register/print/id/' + $( '#id_fefpstudentclass' ).val() ), 'Imprime Klase Formasaun' );
    },
    
    printShortList: function()
    {
	General.newWindow( General.getUrl( '/student-class/register/print-shortlist/id/' + $( '#id_fefpstudentclass' ).val() ), 'Imprime Lista Badak' );
    },
    
    cancelClass: function( id )
    {
	cancel = function()
	{
	    var settings = {
		title: 'Kansela Klase',
		url: '/student-class/register/cancel/id/' + $( '#id_fefpstudentclass' ).val(),
		buttons: [
		    {
			css: 'blue',
			text: 'Halot',
			click: function( modal )
			{
			    modal.find( 'form' ).submit();
			}
		    }
		],
		callback: function( modal )
		{
		    var form  = modal.find( 'form' );
		    submit = function()
		    {
			var obj = {
			    callback: function( response )
			    {
				if ( response.status ) {

				    modal.modal( 'hide' );
				    history.go( 0 );
				}
			    }
			};

			Form.submitAjax( form, obj );
			return false;
		    }

		    Form.addValidate( form, submit );
		}
	    };
	    
	    General.ajaxModal( settings );
	};
	
	General.confirm( 'Ita hakarak kansela klase ida ne\'e ?', 'Kansela Klase', cancel );
    }
};

$( document ).ready(
    function()
    {
	StudentClass.Register.init();
    }
);