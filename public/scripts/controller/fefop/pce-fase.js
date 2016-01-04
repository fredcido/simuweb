Fefop = window.Fefop || {};

Fefop.PceFaseContract = {
    
    init: function()
    {
	this.initForm();
	this.initFormSearch();
    },
    
    initForm: function()
    {
	General.setTabsAjax( '.tabbable', this.configForm );
	
	this.configInformation();
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
		    $( '#pce-fase-list tbody' ).empty();
	     
		    oTable = $( '#pce-fase-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#pce-fase-list tbody' ).html( response );
		    
		    General.drawTables( '#pce-fase-list' );
		    General.scrollTo( '#pce-fase-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
	
	$( '#slider-amount' ).slider(
	    {
		range: true,
		min: 0,
		max: 500000,
		values: [0, 100000],
		slide: function( event, ui ) 
		{
		    $( '#slider-age-amount' ).text( '$' + ( ui.values[0] ) + ' - ' + '$' + ( ui.values[1]  ));
		    $( '#minimum_amount' ).val( ui.values[0] );
		    $( '#maximum_amount' ).val( ui.values[1] );
		}
	    }
	);

	Form.addValidate( form, submit );
	this.configSearchIsicClass();
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Fefop.PceFaseContract[method], pane );
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
			
			window.setTimeout(
			    function()
			    {
				General.go( General.getUrl('/fefop/pce-fase/edit/id/' + response.id ) );
			    },
			    3000
			);
			
			if ( General.empty( $( '#id_pce_contract' ).val() ) ) {
			
			    $( form ).find( '#id_pce_contract' ).val( response.id );
			    window.history.replaceState( {}, "PCE Contract Edit", BASE_URL + "/fefop/pce-fase/edit/id/" + response.id );
			    
			    Fefop.PceFaseContract.fetchContract();
			    Fefop.PceFaseContract.blockEditing();
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	this.closePortlets();
	this.configFocusPortlet();
	this.blockEditing();
	this.configChangeModule();
	this.configSearchIsicClass();
	this.configChangeExpensesValues();
    },
    
    configFocusPortlet: function()
    {
	$( '#expense-list tbody tr' ).css( 'cursor', 'pointer' ).click(
	    function()
	    {
		var id = $( this ).find( '.cost-expense' ).eq(0).attr( 'id' ).replace( /[^0-9]/g, '' );
		
		var portlet = $( '.expense-portlet#expense-portlet-' + id );
		
		$( '.expense-portlet:not(#expense-portlet-' + id + ')' ).each(
		    function()
		    {
			if ( $( this ).find( '.portlet-body' ).is( ':visible' ) )
			    $( this ).find( '.portlet-title' ).trigger( 'click' );
		    }
		);
	
		if ( !portlet.find( '.portlet-body' ).is( ':visible' ) )
		    portlet.find( '.portlet-title' ).trigger( 'click' );
			
		General.scrollTo( portlet, 800 );
	    }
	);
    },
    
    blockEditing: function()
    {
	if ( General.empty( $( '#fk_id_fefop_contract' ).val() ) )
	    return false;
	
	$( '#btn-search-client' ).attr( 'disabled', true );
	$( '#btn-search-class' ).attr( 'disabled', true );
	$( '#fk_id_adddistrict' ).attr( 'readOnly', true );
	$( '#fk_id_fefop_modules' ).attr( 'readOnly', true );
	$( '#btn-export-contract' ).removeAttr( 'disabled' );
	
	Fefop.Contract.setContainerContract().setIdContract( $( '#fk_id_fefop_contract' ).val() );
	Form.initReadOnlySelect();
    },
    
    configSearchIsicClass: function()
    {
	$( '#fk_id_isicdivision' ).on(
	    'change',
	    function()
	    {
		if ( $( this ).is('[readonly]') ) 
		    return false;

		if ( General.empty( $( this ).val() ) ) {

		    $( '#fk_id_isicclasstimor' ).val( '' ).attr( 'disabled', true );
		    return false;
		}

		url = '/fefop/pce-fase/search-isic-class/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_isicclasstimor' );
	    }
	);
    },
    
    configChangeModule: function()
    {
	$( '#fk_id_fefop_modules' ).change(
	    function()
	    {
		if ( $( this ).is('[readonly]') ) 
		    return false;
		
		if ( General.empty( $( this ).val() ) )
		    return false;
		
		url = '/fefop/pce-fase/index/m/' + $( this ).val();
		$( this ).closest( 'form' ).removeClass( 'listener-data' );
		General.go( url );
	    }
	);
    },
    
    closePortlets: function()
    {
	setTimeout(
	    function()
	    {
		$( '.tab-content #data .expense-portlet .portlet-title' ).trigger( 'click' );
	    }
	, 1000 );
    },
    
    configChangeExpensesValues: function()
    {
	$( '.tab-content #data .expense-portlet .expense-quantity, .tab-content #data .expense-portlet .expense-amount' ).live( 'change',
	    function()
	    {
		var tr = $( this ).closest( 'tr' );
		var quantity = parseInt( tr.find( '.expense-quantity' ).eq( 0 ).val() );
		var amount = tr.find( '.expense-amount' ).eq( 0 ).maskMoney( 'unmasked' )[0];
		
		var fieldTotal = tr.find( '.expense-total' ).eq( 0 );
		
		var total = quantity * amount;
		fieldTotal.maskMoney( 'mask', total );
		
		Fefop.PceFaseContract.calcTotalExpenses();
	    }
	);
    },
    
    calcTotalExpenses: function()
    {
	var total = 0;
	$( '.expense-portlet' ).each(
	    function()
	    {
		var id = $( this ).find( '.expense-id' ).eq( 0 ).val();
		var totalExpense = 0;
		$( this ).find( '.expense-total' ).each(
		    function()
		    {
			totalExpense += $( this ).maskMoney( 'unmasked' )[0];
		    }
		);
	
		total += totalExpense;
		$( '#total_expense_' + id ).maskMoney( 'mask', totalExpense );
	    }
	);

	$( '#amount' ).maskMoney( 'mask', total );
    },
    
    fetchContract: function()
    {
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/pce-fase/fetch-contract/' ),
		data: {id: $( '#fk_id_fefop_contract' ).val()},
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
		   $( '#fk_id_fefop_contract' ).val( response.fk_id_fefop_contract );
		   Fefop.Contract.setContainerContract().reloadContract( response.fk_id_fefop_contract );
		   Fefop.PceFaseContract.blockEditing();
		   
		   $( '.nav-tabs a.ajax-tab' ).each(
			function()
			{
			    dataHref = $( this ).attr( 'data-href' );
			    $( this ).attr( 'data-href', dataHref + response.fk_id_fefop_contract );
			    $( this ).parent().removeClass( 'disabled' );
			}
		    );
		}
	    }
	);
    },
    
    addDetailedExpense: function( event, button )
    {
	event.stopPropagation();
	
	var form  = $( '.tab-content #data form' );
	var portlet = $( button ).closest( '.portlet' );
	var id = portlet.find( '.expense-id' ).eq( 0 ).val();
	var table = portlet.find( 'table' );
	
	$.ajax({
	    type: 'POST',
	    dataType: 'text',
	    data: {
		expense: id
	    },
	    url: General.getUrl( '/fefop/pce-fase/add-detailed-expense/' ),
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
		$( table ).find( 'tbody' ).append( response );
		General.scrollTo( table, 800 );
		
		$( table ).find( 'tbody tr:last .control-group input.required' ).each(
		    function()
		    {
			$( this ).rules( 'add', 'required' );
		    }
		);
	
		if ( !$( table ).closest( '.portlet-body' ).is( ':visible' ) )
		    $( table ).closest( '.portlet' ).find( '.portlet-title' ).trigger( 'click' );
	
		$( table ).find( 'tbody tr:last .control-group input:first' ).focus();
		
		Form.init();
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
	    
	    Fefop.PceFaseContract.calcTotalExpenses();
	};
	
	General.confirm( 'Ita hakarak hamoos item ida ne\'e ?', 'Hamoos item', remove );
    },
    
    configFollowup: function( pane )
    {
	Fefop.Contract.setfFollowupContainer( pane ).initFollowUp();
    },
    
    configDocument: function( pane )
    {
	Fefop.Contract.setfDocumentContainer( pane ).initDocument();
    },
    
    exportContract: function()
    {
	var id = $( '#id_pce_contract' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/pce-fase/export/id/' + id ) );
    },
    
    searchClient: function()
    {
	var settings = {
	    title: 'Buka Kliente',
	    url: '/fefop/pce-fase/search-client/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.PceFaseContract.initFormSearchClient( modal );
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
		url: General.getUrl( '/fefop/pce-fase/search-client-forward' ),
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
				Fefop.PceFaseContract.setClient( $( this ).data( 'id' ), modal );
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
		url: General.getUrl( '/fefop/pce-fase/fetch-client/' ),
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
		   var setFinalClient = function( checking )
		   {
		       if ( !checking.valid ) {
		 
			    Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
			    return false;
			}
			
			$( '#data form' ).populate( response, {resetForm: false} );
			General.scrollTo( '#breadcrumb' );
			modal.modal( 'hide' );
		    
		   };
		   
		   Fefop.Contract.checkBlacklist(
			{fk_id_perdata: response.fk_id_perdata},
			setFinalClient
		   );
		   
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    searchClass: function()
    {
	var settings = {
	    title: 'Buka Formasaun Profisional',
	    url: '/fefop/pce-fase/search-class/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.PceFaseContract.initFormClass( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormClass: function( modal )
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
		url: General.getUrl( '/fefop/pce-fase/search-class-forward' ),
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
				Fefop.PceFaseContract.setClass( $( this ).data( 'id' ), modal );
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
    },
    
    setClass: function( id, modal )
    {
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/pce-fase/fetch-class/' ),
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
		     $( '#data form' ).populate( response, {resetForm: false} );
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
};

$( document ).ready(
    function()
    {
	Fefop.PceFaseContract.init();
    }
);