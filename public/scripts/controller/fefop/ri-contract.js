Fefop = window.Fefop || {};

Fefop.RIContract = {
    
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
		    $( '#ri-contract-list tbody' ).empty();
	     
		    oTable = $( '#ri-contract-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#ri-contract-list tbody' ).html( response );
		    
		    General.drawTables( '#ri-contract-list' );
		    General.scrollTo( '#ri-contract-list', 800 );
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

	this.configChangeDistrict();

	Form.addValidate( form, submit );
    },
    
    initForm: function()
    {
	General.setTabsAjax( '.tabbable', this.configForm );
	
	this.configInformation();
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Fefop.RIContract[method], pane );
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
			
			if ( General.empty( $( '#id_ri_contract' ).val() ) ) {
			
			    $( form ).find( '#id_ri_contract' ).val( response.id );
			    window.history.replaceState( {}, "RI Contract Edit", BASE_URL + "/fefop/ri-contract/edit/id/" + response.id );
			    
			    Fefop.RIContract.fetchContract();
			    Fefop.RIContract.blockEditing();
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
	this.configChangeDistrict();
	this.configChangeExpensesValues();
    },
    
    configFocusPortlet: function()
    {
	$( '#expense-list tbody tr' ).css( 'cursor', 'pointer' ).click(
	    function()
	    {
		var id = $( this ).find( '.cost-expense' ).eq(0).attr( 'id' ).replace( /[^0-9]/g, '' );
		console.log( id );
		
		var portlet = $( '.expense-portlet#expense-portlet-' + id );
		console.log( portlet );
		
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
	if ( General.empty( $( '#id_ri_contract' ).val() ) )
	    return false;
	
	$( '#btn-search-institute' ).attr( 'disabled', true );
	$( '#fk_id_adddistrict' ).attr( 'readOnly', true );
	$( '#btn-export-contract' ).removeAttr( 'disabled' );
	Fefop.Contract.setContainerContract().setIdContract( $( '#fk_id_fefop_contract' ).val() );
	Form.initReadOnlySelect();
    },
    
    configChangeDistrict: function()
    {
	$( '#fk_id_adddistrict' ).change(
	    function()
	    {
		if ( $( this ).is('[readonly]') ) 
		    return false;
		
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsubdistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/fefop/ri-contract/search-sub-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsubdistrict' );
	    }
	).trigger( 'change' );
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
		
		Fefop.RIContract.calcTotalExpenses();
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
		url: General.getUrl( '/fefop/ri-contract/fetch-contract/' ),
		data: {id: $( '#id_ri_contract' ).val()},
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
		   Fefop.RIContract.blockEditing();
		   
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
    
    searchInstitute: function()
    {	
	var settings = {
	    title: 'Buka Inst. Ensinu',
	    url: '/fefop/ri-contract/search-institute/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.RIContract.initFormSearchInstitute( modal );
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
		url: General.getUrl( '/fefop/ri-contract/search-institute-forward' ),
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
				Fefop.RIContract.setInstitute( $( this ).data( 'id' ), modal );
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
	var setInstituteFinal = function( checking )
	{
	     if ( !checking.valid ) {
		 
		Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
		return false;
	    }

	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/fefop/ri-contract/fetch-institute/' ),
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
		       
		       $( '#institution' ).trigger( 'change' );
		       
		       if ( !General.empty( response.district ) ) {
			   
			   $( '#fk_id_adddistrict' ).val( response.district );
			   
			   if ( !General.empty( response.sub_district ) )
			       $( '#fk_id_addsubdistrict' ).attr( 'data-value', response.sub_district );
			   
			   $( '#fk_id_adddistrict' ).trigger( 'change' );
		       }
		       
		       modal.modal( 'hide' );
		    },
		    error: function ()
		    {
		       Message.msgError( 'Operasaun la diak', modal );
		    }
		}
	    );

	};
	
	Fefop.Contract.checkBlacklist(
	    {fk_id_fefpeduinstitution: id},
	    setInstituteFinal
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
	    url: General.getUrl( '/fefop/ri-contract/add-detailed-expense/' ),
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
	    
	    Fefop.RIContract.calcTotalExpenses();
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
	var id = $( '#id_ri_contract' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/ri-contract/export/id/' + id ) );
    }
};

$( document ).ready(
    function()
    {
	Fefop.RIContract.init();
    }
);