Job = window.Job || {};

Job.Vacancy = {
    
    TIMOR_LESTE: 1,
    
    clientsList: [],
    closeList: [],
    
    init: function()
    {
	this.initFormSearch();
	this.initForm();
	this.initFormClose();
	this.initFormSearchClient();
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
		    $( '#vacancy-list tbody' ).empty();
	     
		    oTable = $( '#vacancy-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#vacancy-list tbody' ).html( response );
		    
		    General.drawTables( '#vacancy-list' );
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
    
    initFormSearchClient: function()
    {
	var form  = $( 'form#searchclient' );
	
	if ( !form.length )
	    return false;
	
	$( '#fk_id_adddistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsubdistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/job/vacancy/search-sub-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsubdistrict' );
		return true;
	    }
	);
	    
	$( '#max_level' ).change(
	    function()
	    {
		level = parseInt( $( this ).val() );
		combo =  $( '#fk_id_perscholarity' );
		
		if ( level >= 6 )
		    combo.removeAttr( 'disabled' );
		else
		    combo.attr( 'disabled', true );
		
		combo.val( '' ).trigger( 'change' ).trigger( 'liszt:updated' );
	    }
	);
	    
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
		    
		    App.initUniform();
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	}

	Form.addValidate( form, submit );
	Job.Vacancy._configCategoryScholarity( form, 1 );
	
	$( '#slider-age' ).slider(
	    {
		range: true,
		min: 15,
		max: 90,
		values: [20, 40],
		slide: function( event, ui ) 
		{
		    $( '#slider-age-amount' ).text( ui.values[0] + ' - ' + ui.values[1] );
		    $( '#minimum_age' ).val( ui.values[0] );
		    $( '#maximum_age' ).val( ui.values[1] );
		}
	    }
	);
    },
    
    saveListClients: function( form )
    {
	if ( !Job.Vacancy.clientsList.length ) {
		
	    Message.msgError( 'Tenke hili kliente ba halo lista.', $( '.form-actions' ) );
	    return false;
	}
	
	if ( General.empty( $( '#fk_id_fefpenterprise' ).val() ) ) {
	    
	    Message.msgError( 'Tenke hili empreza ida.', $( '.form-actions' ) );
	    return false;
	}

	var data = $( form ).serializeArray();
	var clients = Job.Vacancy.clientsList;
	for ( i in clients )
	    data.push( { name: 'clients[]', value: clients[i] } );

	$.ajax({
	    type: 'POST',
	    data: data,
	    dataType: 'json',
	    url: General.getUrl( '/job/vacancy/save-client-list/' ),
	    beforeSend: function()
	    {
		General.loading( true );
	    },
	    complete: function()
	    {
		General.loading( false );
	    },
	    async:   false,
	    success: function ( response )
	    {
		if ( response.status ) {
		    
		    oTable = $( '#client-list' ).dataTable();
		    oTable.fnDestroy(); 
		    
		    $( '#client-list tbody' ).empty();

		    General.drawTables( '#client-list' );
		    
		    Job.Vacancy.clientsList = [];
		    
		    General.newWindow( General.getUrl( '/job/vacancy/print-list/id/' + response.id ), 'Imprime Lista Kliente' );
		}
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', form );
	    }
	});
    },
    
    initFormClose: function()
    {
	var form  = $( 'form#close' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    clients = Job.Vacancy.closeList;
	    
	    // Check clients were selected
	    if ( !clients.length ) {

		Message.msgError( 'Tenke hili kandidatu ba Taka vaga.', form );
		return false;
	    }
	    
	    // Check if clients selected are the same total defined in the vacany
	    if ( clients.length != parseInt( $( '#num_position' ).val() ) ) {
		
		Message.msgError( 'Tenki seleciona Kliente ' + $( '#num_position' ).val() + ' deit ba Kontratasaun!', form );
		return false;
	    }
	    
	    var data = [];
	    for ( i in clients )
		data.push( { name: 'clients[]', value: clients[i] } );
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status )
			history.go( 0 );
		},
		data: data
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
	General.drawTables( '#shortlist-list' );
    },
    
    initForm: function()
    {
	General.setTabsAjax( '.tabbable', this.configForm );
	this.configInformation();
    },
    
    checkAll: function( master )
    {
	var dataTable = $( master ).closest( 'table' ).dataTable();
	var inputs = $( 'input:not(:disabled)', dataTable.fnGetNodes() );
	inputs.attr( 'checked', !!( $( master ).attr( 'checked' ) ) ).trigger( 'change' );
	$.uniform.update( inputs );
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Job.Vacancy[method], pane );
    },
    
    configInformation: function()
    {
	var form  = $( '.tab-content #data form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( General.empty( $( '#id_jobvacancy' ).val() ) ) {
			
			    $( form ).find( '#id_jobvacancy' ).val( response.id );

			    window.history.replaceState( {}, "Vacancy Edit", BASE_URL + "/job/vacancy/edit/id/" + response.id );

			    $( '.nav-tabs a.ajax-tab' ).each(
				function()
				{
				    dataHref = $( this ).attr( 'data-href' );
				    $( this ).attr( 'data-href', dataHref + response.id );
				    $( this ).parent().removeClass( 'disabled' );
				}
			    );

			    $( '.nav-tabs a.ajax-tab' ).eq( 0 ).trigger( 'click' );
			   
			    a = $( '<a />' );
			    a.addClass( 'btn red' ).attr( 'href', General.getUrl( '/job/vacancy/view/id/' + response.id ) );

			    icon = $( '<i/>' );
			    icon.addClass( 'm-icon-swapleft m-icon-white' );

			    a.append( icon ).append( ' Fila ba Vaga' );
			    $( '#button-back' ).append( a ).closest( '.row-fluid' ).show();
			
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
    },
    
    configAddress: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#address #clear' ).trigger( 'click' );
			Job.Vacancy.loadAddress();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	
	Job.Vacancy.loadAddress();
	Job.Vacancy._configChangeCountry();
	Job.Vacancy._configChangeDistrict();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    _configChangeCountry: function()
    {
	$( '#fk_id_addcountry' ).change(
	    function()
	    {
		els = $( '#fk_id_adddistrict, #fk_id_addsubdistrict' );
		
		if ( $( this ).val() != Job.Vacancy.TIMOR_LESTE ) {
		    
		    els.each(
			function()
			{
			    $( this ).val( '' ).trigger( 'change' ).attr( 'disabled', true );
				    
			    var label = $( this ).parents( '.control-group' ).find( 'label' );	    
			    if ( label.hasClass( 'required' ) )
				label.attr( 'data-required', true ).removeClass( 'required' );
			}
		    );
		    
		} else {
		   
		    els.each(
			function()
			{
			    $( this ).val( '' ).trigger( 'change' ).removeAttr( 'disabled' );
				    
			    var label = $( this ).parents( '.control-group' ).find( 'label' );	    
			    if ( label.attr( 'data-required' ) )
				label.addClass( 'required' );
			}
		    );
		}
	    }
	);
    },
    
    _configChangeDistrict: function()
    {
	$( '#fk_id_adddistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsubdistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/job/vacancy/search-sub-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsubdistrict' );
	    }
	);
    },
    
    loadAddress: function()
    {
	General.loadTable( '#address-list', '/job/vacancy/list-address/id/' + $( '#fk_id_jobvacancy' ).val() );
    },
    
    removeAddress: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'address' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/job/vacancy/delete-address/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#address-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#address-list' );
		    },
		    success: function ( response )
		    {
			Job.Vacancy.loadAddress();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#address' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos fatin vaga ida ne\'e ?', 'Hamoos fatin vaga', remove );
    },
    
    configScholarity: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#scholarity #clear' ).trigger( 'click' );
			Job.Vacancy.loadScholarity();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	
	Job.Vacancy.loadScholarity();
	Job.Vacancy._configCategoryScholarity( pane, 1 );
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    _configCategoryScholarity: function( pane, type )
    {
	pane.find( '#category' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    pane.find( '#fk_id_perscholarity' ).val( '' ).attr( 'disabled', true ).trigger( 'liszt:updated' ).rules( 'remove', 'required' );
		    pane.find( '#fk_id_perscholarity' ).parents( '.control-group' ).find( 'label' ).removeClass( 'required' );
		    
		    return false;   
		}
		
		pane.find( '#fk_id_perscholarity' ).rules( 'add', 'required' );
		pane.find( '#fk_id_perscholarity' ).parents( '.control-group' ).find( 'label' ).addClass( 'required' );
		
		url = '/job/vacancy/search-scholarity/category/' + $( this ).val() + '/type/' + type;
		General.loadCombo( url, pane.find( '#fk_id_perscholarity' ) );
	    }
	);
    },
    
    loadScholarity: function()
    {
	General.loadTable( '#scholarity-list', '/job/vacancy/list-scholarity/id/' + $( '#fk_id_jobvacancy' ).val() );
    },
    
    removeScholarity: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'scholarity' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/job/vacancy/delete-scholarity/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#scholarity-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#scholarity-list' );
		    },
		    success: function ( response )
		    {
			Job.Vacancy.loadScholarity();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#scholarity' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Nivel Eskola ida ne\'e ?', 'Hamoos Nivel Eskola', remove );
    },
    
    configTraining: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#training #clear' ).trigger( 'click' );
			Job.Vacancy.loadTraining();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Job.Vacancy._configCategoryScholarity( pane, 2 );
	Job.Vacancy.loadTraining();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    loadTraining: function()
    {
	General.loadTable( '#training-list', '/job/vacancy/list-training/id/' + $( '#fk_id_jobvacancy' ).val() );
    },
    
    removeTraining: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'training' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/job/vacancy/delete-training/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#training-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#training-list' );
		    },
		    success: function ( response )
		    {
			Job.Vacancy.loadTraining();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#training' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Formasaun Profisional ida ne\'e ?', 'Hamoos Formasaun Profisional', remove );
    },
    
    configLanguage: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#language #clear' ).trigger( 'click' );
			Job.Vacancy.loadLanguage();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Job.Vacancy.loadLanguage();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    loadLanguage: function()
    {
	General.loadTable( '#language-list', '/job/vacancy/list-language/id/' + $( '#fk_id_jobvacancy' ).val() );
    },
    
    removeLanguage: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'language' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/job/vacancy/delete-language/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#language-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#language-list' );
		    },
		    success: function ( response )
		    {
			Job.Vacancy.loadLanguage();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#language' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Lian Fuan ida ne\'e ?', 'Hamoos Lian Fuan', remove );
    },
    
    configHandicapped: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#handicapped #clear' ).trigger( 'click' );
			Job.Vacancy.loadHandicapped();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Job.Vacancy.loadHandicapped();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    loadHandicapped: function()
    {
	General.loadTable( '#handicapped-list', '/job/vacancy/list-handicapped/id/' + $( '#fk_id_jobvacancy' ).val() );
    },
    
    removeHandicapped: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'handicapped' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/job/vacancy/delete-handicapped/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#handicapped-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#handicapped-list' );
		    },
		    success: function ( response )
		    {
			Job.Vacancy.loadHandicapped();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#handicapped' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Defisiensia ida ne\'e ?', 'Hamoos Defisiensia', remove );
    },
    
    editHandicapped: function( link )
    {
	id = $( link ).data( 'handicapped' );
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: BASE_URL + '/job/vacancy/fetch-handicapped/id/' + id,
		beforeSend: function()
		{
		    App.blockUI( '#handicapped form' );
		},
		complete: function()
		{
		    App.unblockUI( '#handicapped form' );
		},
		success: function ( response )
		{
		    $( '#handicapped form' ).populate( response, {resetForm: true} );
		    $( '#fk_id_typehandicapped' ).trigger( 'change' ); 
		    General.scrollTo( '#breadcrumb' );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', '#handicapped' );
		}
	    }
	);
    },
    
    printJob: function( id )
    {
	General.newWindow( General.getUrl( '/job/vacancy/print/id/' + id ), 'Imprime Vaga Empregu' );
    },
    
    printShortlist: function( id )
    {
	General.newWindow( General.getUrl( '/job/match/print/id/' + id ), 'Imprime Shortlist Vaga Empregu' );
    },
    
    cancelVacancy: function( id )
    {
	cancel = function()
	{
	    var settings = {
		title: 'Kansela vaga',
		url: '/job/vacancy/cancel/id/' + id,
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
	
	General.confirm( 'Ita hakarak kansela vaga ida ne\'e ?', 'Kansela Vaga', cancel );
    },
    
    setClientList: function( check )
    {
	if ( General.empty( Job.Vacancy.clientsList ) )
	    Job.Vacancy.clientsList = [];
	
	if ( $( check ).attr( 'checked' ) ) {
	    
	    if ( $.inArray( $( check ).val(), Job.Vacancy.clientsList ) < 0 )
		Job.Vacancy.clientsList.push( $( check ).val() )
	} else {
	    
	    index = $.inArray( $( check ).val(), Job.Vacancy.clientsList );
	    Job.Vacancy.clientsList.splice( index, 1 );
	}
    },
    
    setCloseList: function( check )
    {
	if ( General.empty( Job.Vacancy.closeList ) )
	    Job.Vacancy.closeList = [];
	
	if ( $( check ).attr( 'checked' ) ) {
	    
	    if ( $.inArray( $( check ).val(), Job.Vacancy.closeList ) < 0 )
		Job.Vacancy.closeList.push( $( check ).val() )
	} else {
	    
	    index = $.inArray( $( check ).val(), Job.Vacancy.closeList );
	    Job.Vacancy.closeList.splice( index, 1 );
	}
    }
};

$( document ).ready(
    function()
    {
	Job.Vacancy.init();
    }
);