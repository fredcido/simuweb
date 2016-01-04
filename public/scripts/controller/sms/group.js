Sms = window.Sms || {};

Sms.Group = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status )
			General.go( '/sms/group/edit/id/' + response.id );
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );
	General.setTabsAjax( '.tabbable', this.configForm );
	this.loadGroups();
    },
    
    loadGroups: function()
    {
	General.loadTable( '#group-list', '/sms/group/list' );
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Sms.Group[method], pane );
    },
    
    configClient: function()
    {
	General.loadTable( '#client-group-list', '/sms/group/list-client/id/' + $( '#id_sms_group' ).val() );
    },
    
    configEnterprise: function()
    {
	General.loadTable( '#enterprise-group-list', '/sms/group/list-enterprise/id/' + $( '#id_sms_group' ).val() );
    },
    
    configInstitute: function()
    {
	General.loadTable( '#institute-group-list', '/sms/group/list-institute/id/' + $( '#id_sms_group' ).val() );
    },
    
    addClient: function()
    {
	var settings = {
	    title: 'Buka Kliente',
	    url: '/sms/group/search-client/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Sms.Group.initFormSearchClient( modal );
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
		url: General.getUrl( '/sms/group/search-client-forward' ),
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
				Sms.Group.saveClient( $( this ).data( 'id' ), form, modal );
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
    
    saveClient: function( id, form, modal )
    {
	$.ajax({
	    type: 'POST',
	    data: {
		client: id,
		group: $( '#id_sms_group' ).val()
	    },
	    dataType: 'json',
	    url: General.getUrl( '/sms/group/save-client' ),
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
		    
		    Message.msgSuccess( 'Kliente iha grupu.', form );
		    Sms.Group.configClient();
		} else
		    Message.msgError( response.description.message, form );
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', form );
	    }
	});
    },
    
    addEnterprise: function()
    {
	var settings = {
	    title: 'Buka Empreza',
	    url: '/sms/group/search-enterprise/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Sms.Group.initFormSearchEnterprise( modal );
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
		url: General.getUrl( '/sms/group/search-enterprise-forward' ),
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
				Sms.Group.saveEnterprise( $( this ).data( 'id' ), form, modal );
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
    
    saveEnterprise: function( id, form, modal )
    {
	$.ajax({
	    type: 'POST',
	    data: {
		enterprise: id,
		group: $( '#id_sms_group' ).val()
	    },
	    dataType: 'json',
	    url: General.getUrl( '/sms/group/save-enterprise' ),
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
		    
		    Message.msgSuccess( 'Empreza iha grupu.', form );
		    Sms.Group.configEnterprise();
		} else
		    Message.msgError( response.description.message, form );
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', form );
	    }
	});
    },
    
    addInstitute: function()
    {
	var settings = {
	    title: 'Buka Inst. Ensinu',
	    url: '/sms/group/search-institute/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Sms.Group.initFormSearchInstitute( modal );
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
		url: General.getUrl( '/sms/group/search-institute-forward' ),
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
				Sms.Group.saveInstitute( $( this ).data( 'id' ), form, modal );
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
	}
    
	Form.addValidate( form, submit );
    },
    
    saveInstitute: function( id, form, modal )
    {
	$.ajax({
	    type: 'POST',
	    data: {
		institute: id,
		group: $( '#id_sms_group' ).val()
	    },
	    dataType: 'json',
	    url: General.getUrl( '/sms/group/save-institute' ),
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
		    
		    Message.msgSuccess( 'Inst. Ensinu iha grupu.', form );
		    Sms.Group.configInstitute();
		    
		} else
		    Message.msgError( response.description.message, form );
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', form );
	    }
	});
    },
    
    removeItem: function( link, field )
    {
	remove = function()
	{
	    id = $( link ).data( 'id' );
	    container = $( link ).closest( '.tab-pane' );
	    
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/sms/group/remove-item/' ),
		    data: {
			id: id,
			field: field,
			group: $( '#id_sms_group' ).val()
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
			  
			    Sms.Group.configClient();
			    Sms.Group.configEnterprise();
			    Sms.Group.configInstitute();
			    
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
	
	General.confirm( 'Ita hakarak hamoos item ida ne\'e ?', 'Hamoos item', remove );
    }
};

$( document ).ready(
    function()
    {
	Sms.Group.init();
    }
);