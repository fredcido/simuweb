Job = window.Job || {};

Job.Match = {
    
    clientList: {},
    
    init: function()
    {
	this.initForm();
    },
    
    initForm: function()
    {
	General.setTabsAjax( '.tabbable', this.configForm );
	
	this.configShortlist();
	this.configList();
    },
    
    checkAll: function( master, pane )
    {
	var dataTable = $( master ).closest( 'table' ).dataTable();
	var id = pane || $( master ).closest( '.tab-pane' ).attr( 'id' )
	
	$( 'input:not(:disabled)', dataTable.fnGetNodes() ).each(
	    function()
	    {
		$( this ).attr( 'checked', !!($( master ).attr( 'checked' )) );
		$.uniform.update( $( this ) );
		
		Job.Match.setClientList( $( this ), id );
	    }
	);
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Job.Match[method], pane );
    },
    
    configList: function()
    {
	this.loadList();
    },
    
    loadList: function()
    {
	callback = function()
	{
	    if ( !$( '#list-list tbody tr' ).length )
		$( '#info-vacancy-list' ).show();
	    else
		$( '#info-vacancy-list' ).hide();
	    
	    App.initUniform();
	};
	
	General.loadTable( '#list-list', '/job/match/list-candidate/id/' + $( '#fk_id_jobvacancy' ).val(), callback );
	
	return false;
    },
    
    configShortlist: function()
    {
	this.loadShortlist();
    },
    
    loadShortlist: function()
    {
	callback = function()
	{
	    if ( !$( '#shortlist-list tbody tr' ).length )
		$( '#info-vacancy-shortlist' ).show();
	    else
		$( '#info-vacancy-shortlist' ).hide();
	    
	    General.scrollTo( '#shortlist-list' );
	};
	
	General.loadTable( '#shortlist-list', '/job/match/list-shortlist/id/' + $( '#fk_id_jobvacancy' ).val(), callback );
	
	return false;
    },
    
    listAutomatic: function()
    {
	callback = function()
	{
	    App.initUniform();
	    General.scrollTo( '#automatic #client-list' );
	};
	
	General.loadTable( '#automatic #client-list', '/job/match/list-automatic/id/' + $( '#fk_id_jobvacancy' ).val(), callback );
    },
    
    setClientList: function( check, id )
    {
	if ( General.empty( Job.Match.clientList[id] ))
	    Job.Match.clientList[id] = [];
	
	if ( $( check ).attr( 'checked' ) ) {
	    
	    if ( $.inArray( $( check ).val(), Job.Match.clientList[id] ) < 0 )
		Job.Match.clientList[id].push( $( check ).val() )
	} else {
	    
	    index = $.inArray( $( check ).val(), Job.Match.clientList[id] );
	    Job.Match.clientList[id].splice( index, 1 );
	}
    },
    
    addList: function( form )
    {
	container = $( form ).find( '.box-content' );
	Message.clearMessages( container );
	
	pane = $( form ).closest( '.tab-pane' );
	clients = General.empty( Job.Match.clientList[pane.attr( 'id' )] ) ? [] : Job.Match.clientList[pane.attr( 'id' )];
		       
	if ( !clients.length ) {
	    
	    Message.msgError( 'Tenke hili kandidatu ba halo Shortlist.', container );
	    return false;
	}
	
	var data = $( form ).serializeArray();
	for ( i in clients )
	    data.push( { name: 'clients[]', value: clients[i] } );
	
	$.ajax(
	    {
		type: 'POST',
		data: $.param( data ),
		dataType: 'json',
		url: General.getUrl( '/job/match/add-list/' ),
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

			portlet = $( '#list' ).closest( '.portlet' );
			
			Job.Match.openPorlet( portlet, true );
			Job.Match.loadList();
			General.scrollTo( portlet );
			
			container.find( '.table' ).dataTable().fnClearTable();
			
			Job.Match.clientList[pane.attr( 'id' )] = [];
			
			Message.msgSuccess( 'Kandidatu sira iha lista.', $( '#list' ) );
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
	container = $( '#list-list' ).parents( '.box-content' );
	Message.clearMessages( container );
	
	clients = General.empty( Job.Match.clientList.candidates ) ? [] : Job.Match.clientList.candidates;
		       
	if ( !clients.length ) {
	    
	    Message.msgError( 'Tenke hili kandidatu ba halo Shortlist.', container );
	    return false;
	}
	
	$.ajax(
	    {
		type: 'POST',
		data: {
		    id_jobvacancy: $( '#fk_id_jobvacancy' ).val(),
		    clients: clients
		},
		dataType: 'json',
		url: General.getUrl( '/job/match/save/' ),
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
			
			portlet = $( '#shortlist' ).closest( '.portlet' );
			
			General.scrollTo( portlet );
			Job.Match.openPorlet( portlet, true );
			
			Job.Match.clientList.candidates = [];

			Job.Match.loadList();
			Job.Match.loadShortlist();
			Message.msgSuccess( 'Kandidatu sira iha Shortlist.', $( '#shortlist' ) );
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
    
    openPorlet: function( portlet, open )
    {
	var body = $( portlet ).children( '.portlet-body' );
	var control = $( portlet ).find( '.portlet-title .tools a:not(.reload)' );
	
	if ( open ) {
	    
	    control.removeClass( 'expand' ).addClass( 'collapse' );
	    body.slideDown( 200 );
	    
	} else {
	    
	    control.removeClass( 'collapse' ).addClass( 'expand' );
	    body.slideUp( 200 ); 
	}
    },
    
    removeClient: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'client' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/job/match/remove-client/',
		    data: {
			client: id,
			id_jobvacancy: $( '#fk_id_jobvacancy' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( '#shortlist-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#shortlist-list' );
		    },
		    success: function ( response )
		    {
			Job.Match.loadShortlist();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#shortlist' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos kliente ida ne\'e ?', 'Hamoos kliente', remove );
    },
    
    configManual: function()
    {
	$( '#fk_id_adddistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsubdistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/job/match/search-sub-district/id/' + $( this ).val();
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
		
		combo.val( '' ).trigger( 'change' ).trigger( 'liszt:updated' ).trigger( 'chosen:updated' );
	    }
	);
	    
	var form  = $( '.tab-content #manual form' );
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
		    $( '#manual #client-list tbody' ).empty();

		    oTable = $( '#manual #client-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#manual #client-list tbody' ).html( response );

		    General.drawTables( '#manual #client-list' );
		    General.scrollTo( '#manual #client-list', 800 );
		    
		    App.initUniform();
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	}

	Form.addValidate( form, submit );
	Job.Match._configCategoryScholarity( form, 1 );
	
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
		
		url = '/job/match/search-scholarity/category/' + $( this ).val() + '/type/' + type;
		General.loadCombo( url, pane.find( '#fk_id_perscholarity' ) );
	    }
	);
    },
    
    configDirect: function()
    {
	var form  = $( '.tab-content #direct form' );
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
		    $( '#direct #client-list tbody' ).empty();

		    oTable = $( '#direct #client-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#direct #client-list tbody' ).html( response );

		    General.drawTables( '#direct #client-list' );
		    General.scrollTo( '#direct #client-list', 800 );
		    
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
    
    printShortlist: function( id )
    {
	General.newWindow( General.getUrl( '/job/match/print/id/' + id ), 'Imprime Shortlist Vaga Empregu' );
    }
};

$( document ).ready(
    function()
    {
	Job.Match.init();
    }
);