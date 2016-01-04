Pce = window.Pce || {};

Pce = {
    
    init: function()
    {
	this.initWizard();
	this.initFormInformation();
	this.initFormBusinessPlan();
	this.initFormFinishPlan();
    },
    
    initFormInformation: function()
    {
	var form  = $( '#form_information' );
	if ( !form.length )
	    return false;
	
	submit = function()
	{   
	    totalPartisipants = parseInt( $( '#client-pce-list tbody tr' ).length );
	    if ( totalPartisipants > 5 ) {
		
		Message.msgError( 'Keta liu kliente 5 (lima)', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			General.loading( true );
			$( '#business-plan' ).load( General.getUrl( '/external/pce/business-plan/id/' + response.id ), 
			    function()
			    {
				$( '#business-wizard .navbar-inner .step' ).eq( 1 ).parent().removeClass( 'disabled' );
				$( '#business-wizard' ).bootstrapWizard( 'show',1 );
				Pce.initFormBusinessPlan();
				General.loading( false );
				Form.init();
			    }
			);
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
    },
    
    initFormBusinessPlan: function()
    {
	var form  = $( '#form_businessplan' );
	if ( !form.length )
	    return false;
	
	submit = function()
	{   
	    if ( !$( '.expense-field.cost_expense', form ).length ) {
		
		Message.msgError( 'Keta halo planu negosiu ne\'e tamba la iha Rúbrica.', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.loading( true );
			$( '#finish-plan' ).load( General.getUrl( '/external/pce/finish-plan/id/' + response.id ), 
			    function()
			    {
				$( '#business-wizard .navbar-inner .step' ).eq( 2 ).parent().removeClass( 'disabled' );
				//$( '#business-wizard' ).bootstrapWizard( 'show', 2 );
				
				Pce.initFormFinishPlan();
				General.loading( false );
				Form.init();
			    }
			);
		
			Pce.loadFinancialAnalysis( response.id );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	Pce.initExpensesBusiness( form );
	Pce.configChangeSubDistrict( form );
	Pce.loadFinancialAnalysis( $( '#id_businessplan', form ).val() );
    },
    
    loadFinancialAnalysis: function( id )
    {
	General.loading( true );
	$( '#financial-analysis' ).load( General.getUrl( '/external/pce/fetch-financial-analysis/id/' + id ),
	    function()
	    {
		General.loading( false );
		Form.init();
		General.scrollTo( $( '#financial-analysis' ) );
	    }
	);
    },
    
    initFormFinishPlan: function()
    {
	var form  = $( '#form_finishplan' );
	if ( !form.length )
	    return false;
	
	submit = function()
	{   
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			history.go( 0 );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );
    },
    
    configChangeSubDistrict: function( form )
    {
	$( '#fk_id_addsubdistrict' ).on( 'change',
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsucu', form ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/external/pce/search-suku/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsucu' );
	    }
	);
    },
    
    initExpensesBusiness: function( form )
    {
	this.configFocusPortlet( form );
	this.configChangeExpensesValues( form );
	this.configChangeCalcTotals( form );
	this.configChangeReserveFund( form );
	this.configCalcFirstYear( form );
	this.configCalcFollowingYears( form );
	
	$( '#form_businessplan .total-fields' ).focus().blur();
    },
    
    configChangeReserveFund: function( form )
    {
	$( '#total_fields-total_expense', form ).on( 'change',
	    function()
	    {
		var totalExpense = $( this ).maskMoney( 'unmasked' )[0];
		if ( General.empty( totalExpense ) ) {
		    
		    $( '#total_fields-reserve_fund', form ).maskMoney( 'mask', 0 );
		    return false;
		}
		
		var reserveFund = totalExpense * 0.1 + 600;
		totalExpense += reserveFund;
		
		console.log(totalExpense);
		
		$( '#total_fields-reserve_fund', form ).maskMoney( 'mask', reserveFund );
		$( this ).maskMoney( 'mask', totalExpense );
	    }
	).trigger( 'change' );
    },
    
    configCalcFirstYear: function( form )
    {
	$( '#total_fields-revenue, #total_fields-investiment, #total_fields-total_expense', form ).on( 'change',
	    function()
	    {
		var totalRevenue = $( '#total_fields-revenue', form ).maskMoney( 'unmasked' )[0];
		if ( General.empty( totalRevenue ) ) {

		    $( '#total_fields-first_year', form ).maskMoney( 'mask', 0 );
		    return false;
		}

		var totalInvestiment = $( '#total_fields-investiment', form ).maskMoney( 'unmasked' )[0];
		var totalExpense = $( '#total_fields-total_expense', form ).maskMoney( 'unmasked' )[0];

		var totalFirstYear = totalRevenue - totalInvestiment - totalExpense;

		$( '#total_fields-first_year', form ).maskMoney( 'mask', totalFirstYear );
	    }
	);
    },
    
    configCalcFollowingYears: function( form )
    {
	$( '#total_fields-revenue, #total_fields-annual_expense', form ).on( 'change',
	    function()
	    {
		var totalRevenue = $( '#total_fields-revenue', form ).maskMoney( 'unmasked' )[0];
		if ( General.empty( totalRevenue ) ) {

		    $( '#total_fields-following_year', form ).maskMoney( 'mask', 0 );
		    return false;
		}

		var totalAnnualExpense = $( '#total_fields-annual_expense', form ).maskMoney( 'unmasked' )[0];

		var totalFollowingYear = totalRevenue - totalAnnualExpense;

		$( '#total_fields-following_year', form ).maskMoney( 'mask', totalFollowingYear );
	    }
	);
    },
    
    configChangeCalcTotals: function( form )
    {
	$( '.expense-field', form ).on( 'change',
	    function()
	    {
		var table = $( this ).closest( 'table' );
		var total = 0;
		
		$( '.expense-field', table ).each(
		    function()
		    {
			var totalExpense = $( this ).maskMoney( 'unmasked' )[0];
			total += totalExpense;
		    }
		);
	
		$( 'tfoot .total-fields', table ).maskMoney( 'mask', total ).trigger( 'change' );
	    }
	).trigger( 'change' );
    },
    
    configFocusPortlet: function( form )
    {
	$( '#expense-list tbody tr.row-expense', form ).css( 'cursor', 'pointer' ).on( 'click',
	    function()
	    {
		var id = $( this ).find( '.cost_expense' ).eq(0).attr( 'id' ).replace( /[^0-9]/g, '' );
		
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
    
    configChangeExpensesValues: function( form )
    {
	$( '.expense-portlet .expense-quantity, .expense-portlet .expense-amount', form ).live( 'change',
	    function()
	    {
		var tr = $( this ).closest( 'tr' );
		var quantity = parseInt( tr.find( '.expense-quantity' ).eq( 0 ).val() );
		var amount = tr.find( '.expense-amount' ).eq( 0 ).maskMoney( 'unmasked' )[0];
		
		var fieldTotal = tr.find( '.expense-total' ).eq( 0 );
		
		var total = quantity * amount;
		fieldTotal.maskMoney( 'mask', total );
		
		Pce.calcTotalExpenses();
	    }
	);
    },
    
    calcTotalExpenses: function()
    {
	var total = 0;
	$( '#form_businessplan .expense-portlet' ).each(
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
		$( '#cost_expense_' + id ).maskMoney( 'mask', totalExpense ).trigger( 'change' );
	    }
	);
    },
    
    addDetailedExpense: function( event, button )
    {
	event.stopPropagation();
	
	var form  = $( '#form_businessplan' );
	var portlet = $( button ).closest( '.portlet' );
	var id = portlet.find( '.expense-id' ).eq( 0 ).val();
	var table = portlet.find( 'table' );
	
	$.ajax({
	    type: 'POST',
	    dataType: 'text',
	    data: {
		expense: id
	    },
	    url: General.getUrl( '/external/pce/add-detailed-expense/' ),
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
		General.scrollTo( table, 800 );
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
	    
	    Pce.calcTotalExpenses();
	};
	
	General.confirm( 'Ita hakarak hamoos item ida ne\'e ?', 'Hamoos item', remove );
    },
    
    initWizard: function()
    {
	$( '#business-wizard' ).bootstrapWizard({
	    nextSelector: 'a.btn.next',
	    previousSelector: 'a.btn.previous',
	    onTabShow: function ( tab, navigation, index )
	    {
		if ( $( tab ).hasClass( 'disabled' ) )
		    return false;
		
		$( 'li', $( '#form_wizard' ) ).removeClass( 'done' );
		
		var li_list = navigation.find( 'li' );
		for ( var i = 0; i < index; i++)
		    $( li_list[i] ).addClass( 'done' );
		
		var total = navigation.find( 'li' ).length;
		var current = index + 1;
		var $percent = (current / total) * 100;
		$( '#business-wizard' ).find( '.bar' ).css(
		    {
			width: $percent + '%'
		    }
		);
	    }
	});
    },
    
    searchIsicClass: function( input )
    {
	if ( $( input ).is('[readonly]') ) 
	    return false;

	if ( General.empty( $( input ).val() ) ) {

	    $( '#fk_id_isicclasstimor' ).val( '' ).attr( 'disabled', true );
	    return false;
	}

	url = '/external/pce/search-isic-class/id/' + $( input ).val();
	General.loadCombo( url, 'fk_id_isicclasstimor' );
    },
    
    searchClient: function()
    {
	totalPartisipants = parseInt( $( '#client-pce-list tbody tr' ).length );
	if ( totalPartisipants >= 5 ) {
	    
	    Message.msgError( 'La bele partisipantes liu 5', $( '#partisipants-group' ) );
	    return false;
	}
	
	var settings = {
	    title: 'Buka Kliente',
	    url: '/external/pce/search-client/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Pce.initFormSearchClient( modal );
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
		url: General.getUrl( '/external/pce/search-client-forward' ),
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
				Pce.addClient( $( this ).data( 'id' ), form, modal );
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
    
    addClient: function( id, form, modal )
    {
	if ( id == $( '#fk_id_perdata' ).val() ) {
	    
	    Message.msgError( 'Keta hili kliente hanesan responsavel grupu', form );
	    return false;
	}
	
	var valid = true;
	$( '#client-pce-list .client-group' ).each(
	    function()
	    {
		if ( id == $( this ).val() )
		    valid = false;
		
		return valid;
	    }
	);
	    
	if ( !valid ) {
	    
	    Message.msgError( 'Kliente iha tiha ona', form );
	    return false;
	}
	
	totalPartisipants = parseInt( $( '#client-pce-list tbody tr' ).length );
	if ( totalPartisipants >= 5 ) {
	    
	    Message.msgError( 'La bele partisipantes liu 5', form );
	    return false;
	}
	
	$.ajax({
	    type: 'GET',
	    dataType: 'text',
	    url: General.getUrl( '/external/pce/add-client/id/' + id ),
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
		$( '#client-pce-list tbody' ).append( response );
		modal.modal( 'hide' );
		General.scrollTo( '#client-pce-list', 800 );
		Pce.calcTotalPartisipants();
		
		Form.init();
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', form );
	    }
	});
	
	return true;
    },
    
    calcTotalPartisipants: function()
    {
	totalPartisipants = parseInt( $( '#client-pce-list tbody tr' ).length );
	$( '#total_partisipants' ).val( totalPartisipants );
	$( '#partisipants' ).val( totalPartisipants > 1 ? 'G' : 'S' );
    },
    
    removeClient: function( link )
    {
	remove = function()
	{
	    tr = $( link ).closest( 'tr' );
	    tr.remove();
	    Pce.calcTotalPartisipants();
	};
	
	General.confirm( 'Ita hakarak hasai benefisiariu ida ne\'e ?', 'Hasai Benefisiariu', remove );
    }
};

$( document ).ready(
    function()
    {
	Pce.init();
    }
);