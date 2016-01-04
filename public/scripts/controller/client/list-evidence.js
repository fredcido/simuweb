Client = window.Client || {};

Client.ListEvidence = {
    
    init: function()
    {
	this.initFormSearch();
	this.initForm();
    },
    
    initForm: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    if ( !$( '#client-list-evidence tbody tr td:not(.dataTables_empty)' ).length ) {
		
		Message.msgError( 'Tenki tau Kliente sira.', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status )
			General.go( '/client/list-evidence/edit/id/' + response.id );
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );
	this.listClient();
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
		    $( '#list-evidence-list tbody' ).empty();
	     
		    oTable = $( '#list-evidence-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#list-evidence-list tbody' ).html( response );
		    
		    General.drawTables( '#list-evidence-list' );
		    General.scrollTo( '#list-evidence-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
    },
    
    
    addClient: function()
    {
	var settings = {
	    title: 'Buka Kliente',
	    url: '/client/list-evidence/search-client/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Client.ListEvidence.initFormSearchClient( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormSearchClient: function( modal )
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
		url: General.getUrl( '/client/list-evidence/search-client-forward' ),
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
		    $( '#client-list tbody' ).empty();
	     
		    oTable = $( '#client-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#client-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#client-list tbody a.action-ajax' ).click(
			    function()
			    {
				Client.ListEvidence.saveClient( $( this ), form, modal );
			    }
			);
		    };
		    
		    General.drawTables( '#client-list', callbackClick );
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
    
    saveClient: function( link, form, modal )
    {
	if ( $( link ).hasClass( 'grey' ) )
	    return false;
	
	$.ajax({
	    type: 'POST',
	    data: {
		client: $( link ).data( 'id' ),
		list: $( '#id_job_list' ).val()
	    },
	    dataType: 'json',
	    url: General.getUrl( '/client/list-evidence/save-client' ),
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
		if ( response.status ) {
		    
		    Client.ListEvidence.listClient();
		    $( link ).addClass( 'grey' ).removeClass( 'red' );
		    
		} else
		    Message.msgError( response.description.message, form );
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', form );
	    }
	});
    },
    
    listClient: function()
    {
	General.loadTable( '#client-list-evidence', '/client/list-evidence/list-client/id/' + $( '#id_job_list' ).val() );
    },
    
    removeClient: function( id )
    {
	container = $( 'form' );
	
	if ( !General.empty( $( '#id_job_list' ).val() ) ) {
	    
	    if ( $( '#client-list-evidence tbody tr' ).length == 1 ) {
		
		Message.msgError( 'La bele hasai kliente hotu.', container );
		return false;
	    }
	}
	
	remove = function()
	{   
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/list-evidence/remove-client/' ),
		    data: {
			id: id,
			list: $( '#id_job_list' ).val()
		    },
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
			if ( response.status ) {
			  
			    Client.ListEvidence.listClient();
			    
			} else
			    Message.msgError( response.description.message, container );
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', container );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos kliente ida ne\'e ?', 'Hamoos item', remove );
    },
    
    printList: function()
    {
	var dataTable = $( '#client-list-evidence' ).closest( 'table' ).dataTable();
	
	clients = [];
	$( 'input:checked', dataTable.fnGetNodes() ).each(
	    function()
	    {
		clients.push( $( this ).val() );
	    }
	);

	if ( !clients.length ) {
	    
	    Message.msgError( 'Tenki hili kliente ba print lista.', $( 'form' ) );
	    return false;
	}
	
	var url = General.getUrl( '/client/list-evidence/print/id/' + $( '#id_job_list' ).val() + '/clients/' + clients.join( ',' ) )
	General.newWindow( url, 'Lista Kartaun Evidensia' );
	
	setTimeout( function(){ history.go( 0 ); }, 1500 );
    }
};

$( document ).ready(
    function()
    {
	Client.ListEvidence.init();
    }
);