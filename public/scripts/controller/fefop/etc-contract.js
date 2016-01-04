Fefop = window.Fefop || {};

Fefop.ETCContract = {
    
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
		    $( '#etc-contract-list tbody' ).empty();
	     
		    oTable = $( '#etc-contract-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#etc-contract-list tbody' ).html( response );
		    
		    General.drawTables( '#etc-contract-list' );
		    General.scrollTo( '#etc-contract-list', 800 );
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
	
	General.execFunction( Fefop.ETCContract[method], pane );
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
			
			if ( General.empty( $( '#id_etc_contract' ).val() ) ) {
			    
			    window.setTimeout(
				function()
				{
				    General.go( "/fefop/etc-contract/edit/id/" + response.id );
				},
				3000
			    );
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	this.configFocusPortlet();
	this.blockEditing();
	this.configChangeDistrict();
	this.configChangeSubDistrict();
	this.configChangeExpenseType();
	this.configChangeExpensesValues();
	this.configCalcDuration();
	this.configCalcDurationTotal();
	this.configCalcFormation();
	this.configSearchScholarityLevel();
    },
    
    configCalcDuration: function()
    {
	var calcDate = function()
	{
	    var container = $( this ).closest( 'tr' );
	    var dateStart = container.find( '.date-start' ).eq( 0 ).val();
	    var dateFim = container.find( '.date-finish' ).eq( 0 ).val();

	    if ( General.empty( dateStart ) || General.empty( dateFim ) ) {

		container.find( '.duration' ).eq( 0 ).val( '' );
		return false;
	    }

	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/fefop/etc-contract/calc-diff-month/' ),
		    data: {
			date_start: dateStart,
			date_finish: dateFim
		    },
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
		       container.find( '.duration' ).eq( 0 ).val( response.diff ).trigger( 'change' );
		    }
		}
	    );
	};
	
	$( '.date-start' ).live( 'change', calcDate );
	$( '.date-finish' ).live( 'change', calcDate );
    },
    
    configSearchScholarityLevel: function()
    {
	$( '.scholarity' ).live( 
	    'change', 
	    function()
	    {
		var container = $( this ).closest( 'tr' );
		var scholarity = $( this ).val();

		if ( General.empty( scholarity ) ) {

		    container.find( '.level' ).eq( 0 ).val( '' );
		    return false;
		}

		$.ajax(
		    {
			type: 'POST',
			dataType: 'json',
			url: General.getUrl( '/fefop/etc-contract/search-scholarity/' ),
			data: { id: scholarity },
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
			   container.find( '.level' ).eq( 0 ).val( response.level_scholarity );
			}
		    }
		);
	    }
	);
    },
    
    configCalcDurationTotal: function()
    {
	$( '.beneficiaries, .duration', '.employment ' ).live(
	    'change',
	    function()
	    {
		var container = $( this ).closest( 'tr' );
		var vlrBeneficiairies = parseInt( container.find( '.beneficiaries' ).eq( 0 ).val() );
		var vlrDurtion = parseInt( container.find( '.duration' ).eq( 0 ).val() );
		
		var totalDuration = 0;
		if ( !isNaN( vlrBeneficiairies) && !isNaN( vlrDurtion ) )
		  totalDuration = vlrBeneficiairies * vlrDurtion;

		container.find( '.expense-quantity' ).eq( 0 ).val( totalDuration ).trigger( 'change' );
	    }
	);
    },
    
    configCalcFormation: function()
    {
	$( '.beneficiaries, .duration', '.formation ' ).live(
	    'change',
	    function()
	    {
		var container = $( this ).closest( 'tr' );
		var vlrBeneficiairies = parseInt( container.find( '.beneficiaries' ).eq( 0 ).val() );

		container.find( '.expense-quantity' ).eq( 0 ).val( vlrBeneficiairies ).trigger( 'change' );
	    }
	);
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
	if ( General.empty( $( '#id_per_contract' ).val() ) )
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
		
		url = '/fefop/etc-contract/search-sub-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsubdistrict' );
	    }
	).trigger( 'change' );
    },
    
    configChangeSubDistrict: function()
    {
	$( '#fk_id_addsubdistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsucu' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/fefop/etc-contract/search-suku/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsucu' );
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
    
    configChangeExpenseType: function()
    {
	var expensesType = $( '.tab-content #data .expense-type' );
	
	expensesType.on( 'click',
	    function( e )
	    {
		e.stopPropagation();
		e.preventDefault();
		
		return false;
	    }
	);

	expensesType.on( 'change',
	    function()
	    {
		if ( $( this ).is( '[readonly]') )
		    return false;

		var idExpense = $( this ).data( 'expense' );
		var expenseContainer = $( '.expense-portlet#expense-portlet-' + idExpense );
		var btnAddDetailed = expenseContainer.find( '#btn-add-expense-detailed' );
		
		var fieldTotal = $( this ).closest( 'tr' ).find( '.cost-expense' ).eq( 0 );
		fieldTotal.maskMoney( 'mask', 0 ).trigger( 'change' );
		
		if ( General.empty( $( this ).val() ) ) {
		    
		    expenseContainer.find( '.portlet-body' ).empty();
		    btnAddDetailed.attr( 'disabled', true );
		    
		    if ( expenseContainer.find( '.portlet-body' ).is( ':visible' ) )
			expenseContainer.find( '.portlet-title' ).trigger( 'click' );
		    
		    return false;
		}
		
		var urlHeader = null;
		switch ( $( this ).val() ) {
		    case 'I':
			urlHeader = '/fefop/etc-contract/header-item/';
			break;
		    case 'E':
			urlHeader = '/fefop/etc-contract/header-employment/';
			break;
		    case 'F':
			urlHeader = '/fefop/etc-contract/header-formation/';
			break;
		}
		
		if ( !urlHeader )
		    return false;
		
		btnAddDetailed.removeAttr( 'disabled' );
		btnAddDetailed.data( 'type', $( this ).val() );
		
		if ( !expenseContainer.find( '.portlet-body' ).is( ':visible' ) )
		    expenseContainer.find( '.portlet-title' ).trigger( 'click' );
			
		General.scrollTo( expenseContainer, 800 );
		
		General.loading( true );
		expenseContainer.find( '.portlet-body' ).load(
		    General.getUrl( urlHeader ),
		    function()
		    {
			General.loading( false );
			Fefop.ETCContract.calcTotalExpenses();
		    }
		);
	    }
	);
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
		
		Fefop.ETCContract.calcTotalExpenses();
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
		url: General.getUrl( '/fefop/etc-contract/fetch-contract/' ),
		data: {id: $( '#id_per_contract' ).val()},
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
		   Fefop.ETCContract.blockEditing();
		   
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
    
    searchEnterprise: function()
    {
	var settings = {
	    title: 'Buka Empreza',
	    url: '/fefop/fe-contract/search-enterprise/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.ETCContract.initFormSearchEnterprise( modal );
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
		url: General.getUrl( '/fefop/etc-contract/search-enterprise-forward' ),
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
				Fefop.ETCContract.setEnterprise( $( this ).data( 'id' ), modal );
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
	};
    
	Form.addValidate( form, submit );
    },
    
    setEnterprise: function( id, modal )
    {
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/etc-contract/fetch-enterprise/' ),
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
		   var setFinalEnterprise = function( checking )
		   {
		       if ( !checking.valid ) {
		 
			    Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
			    return false;
			}
			
			$( '#data form' ).populate( response, {resetForm: false} );
			General.scrollTo( '#breadcrumb' );
			modal.modal( 'hide' );
			
			Fefop.ETCContract.detailEnterprise( response.fk_id_fefpenterprise );
		   };
		    
		   Fefop.Contract.checkBlacklist(
			{fk_id_fefpenterprise: response.fk_id_fefpenterprise},
			setFinalEnterprise
		   );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    detailEnterprise: function( id )
    {
	General.loading( true );
	
	$( '#detail-enterprise' ).load(
	    General.getUrl( '/fefop/etc-contract/detail-enterprise/id/' + id ),
	    function()
	    {
		General.scrollTo( $( this ) );
		General.loading( false );
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
	var type = $( button ).data( 'type' );
	
	var urlItem = null;
	
	switch ( type ) {
	    case 'I':
		urlItem = 'add-detailed-expense';
		break;
	    case 'E':
		urlItem = 'add-detailed-employment';
		break;
	    case 'F':
		urlItem = 'add-detailed-formation';
		break;
	}
	
	if ( !urlItem )
	    return false;
	
	$.ajax({
	    type: 'POST',
	    dataType: 'text',
	    data: {
		expense: id
	    },
	    url: General.getUrl( '/fefop/etc-contract/' + urlItem ),
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
		$( '#expense-' + id + '-type' ).attr( 'readonly', true );
		
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
	    var tr = $( link ).closest( 'tr' );
	    var id = tr.data( 'expense' );
	    var tbody = tr.closest( 'tbody' );
	    
	    tr.remove();
	    
	    Fefop.ETCContract.calcTotalExpenses();
	    
	    if ( !tbody.find( 'tr' ).length ) {
		
		$( '#expense-' + id + '-type' ).removeAttr( 'readonly' );
		Form.init();
	    }
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
	var id = $( '#id_etc_contract' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/etc-contract/export/id/' + id ) );
    }
};

$( document ).ready(
    function()
    {
	Fefop.ETCContract.init();
    }
);