Fefop = window.Fefop || {};

Fefop.FERegistration = {
    
    init: function()
    {
	this.initForm();
	this.initFormSearch();
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
		    $( '#fe-registration-list tbody' ).empty();
	     
		    oTable = $( '#fe-registration-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#fe-registration-list tbody' ).html( response );
		    
		    General.drawTables( '#fe-registration-list' );
		    General.scrollTo( '#fe-registration-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
	
	Form.addValidate( form, submit );
    },
    
    initForm: function()
    {
	var form  = $( 'form#fefopformferegistration' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    if ( !$( '#entity-list tbody tr').length ) {
		
		Message.msgError('Tenki hili Entidade ida', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( General.empty( $( '#id_fe_registration' ).val() ) ) {
			
			    $( form ).find( '#id_fe_registration' ).val( response.id );
			    window.history.replaceState( {}, "FE Registration Edit", BASE_URL + "/fefop/fe-registration/edit/id/" + response.id );

			    Fefop.FERegistration.blockEditing();
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	this.blockEditing();
    },
    
    searchClient: function()
    {
	var settings = {
	    title: 'Buka Kliente',
	    url: '/fefop/fe-registration/search-client/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.FERegistration.initFormSearchClient( modal );
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
		url: General.getUrl( '/fefop/fe-registration/search-client-forward' ),
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
				Fefop.FERegistration.setClient( $( this ).data( 'id' ), modal );
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
	};
    
	Form.addValidate( form, submit );
	Form.handleClientSearch( form );
    },
    
    setClient: function( id, modal )
    {
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fe-registration/fetch-client/' ),
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
		    $( 'form' ).populate( response, {resetForm: false} );
		    General.scrollTo( '#breadcrumb' );
		    
		    $( '#scholarity-list tbody' ).empty().load(
			General.getUrl( '/fefop/fe-registration/list-scholarity/id/' + response.fk_id_perdata + '/type/2' )
		    );
		    $( '#formal-scholarity-list tbody' ).empty().load(
			General.getUrl( '/fefop/fe-registration/list-scholarity/id/' + response.fk_id_perdata + '/type/1' )
		    );
		    
		    modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    blockEditing: function()
    {
	if ( General.empty( $( '#id_fe_registration' ).val() ) )
	    return false;
	
	$( '#btn-search-client' ).attr( 'disabled', true ).addClass( 'disabled' );
	$( '#btn-print-registration' ).removeAttr( 'disabled' );
    },
    
    searchInstitute: function()
    {	
	var settings = {
	    title: 'Buka Inst. Ensinu',
	    url: '/fefop/fe-registration/search-institute/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.FERegistration.initFormSearchInstitute( modal );
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
		url: General.getUrl( '/fefop/fe-registration/search-institute-forward' ),
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
				Fefop.FERegistration.setInstitute( $( this ).data( 'id' ), modal );
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
		url: General.getUrl( '/fefop/fe-registration/fetch-institute/' ),
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
		    var data = response;
		    data.type = 'institute';
		    Fefop.FERegistration.addEntitiy( data, modal );
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
	    url: '/fefop/fe-registration/search-enterprise/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.FERegistration.initFormSearchEnterprise( modal );
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
		url: General.getUrl( '/fefop/fe-registration/search-enterprise-forward' ),
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
				Fefop.FERegistration.setEnterprise( $( this ).data( 'id' ), modal );
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
		url: General.getUrl( '/fefop/fe-registration/fetch-enterprise/' ),
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
		    var data = response;
		    data.type = 'enterprise';
		    Fefop.FERegistration.addEntitiy( data, modal );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    addEntitiy: function( data, modal )
    {
	var form = $( 'form' );
	
	if ( $( '#entity-list .entity.' + data.type ).filter(function(){ return $(this).val() == data.id; }).length ) {
	    
	    Message.msgError( 'Iha ' + data.type + ' tiha ona', modal.find( '.modal-body' ) );
	    return false;
	}
	
	$.ajax({
	    type: 'POST',
	    dataType: 'text',
	    data: {
		row: data
	    },
	    url: General.getUrl( '/fefop/fe-registration/add-entity/' ),
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
		$( '#entity-list' ).find( 'tbody' ).append( response );
		General.scrollTo( $( '#entity-list' ), 800 );
		modal.modal( 'hide' );
	    },
	    error: function ()
	    {
		Message.msgError( 'Erro ao executar operação', form );
	    }
	});
    },
    
    removeItem: function( link )
    {
	remove = function()
	{
	    tr = $( link ).closest( 'tr' );
	    tr.remove();
	};
	
	General.confirm( 'Ita hakarak hamoos item ida ne\'e ?', 'Hamoos item', remove );
    },
    
    printRegistration: function()
    {
	id = $( '#id_fe_registration' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/fe-registration/print/id/' + id ), 'Imprime Fixa Rejistrasaun' );
    }
};

$( document ).ready(
    function()
    {
	Fefop.FERegistration.init();
    }
);