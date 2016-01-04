Fefop = window.Fefop || {};

Fefop.Financial = {
    
    setContractCallBack: null,
    containerEnterprise: null,
    
    init: function()
    {
	General.setTabsAjax( '.tabbable', this.configForm );
	this.initFormFilterTransactions();
	this.loadTotals();
	this.configEvents();
    },
    
    configEvents: function()
    {
	$( '#transaction #contract-list tbody tr .hide-expenses' ).live( 'click',
	    function()
	    {
		var idContract = $( this ).closest( 'tr' ).find( '.id-contract' ).eq( 0 ).val();
		var table = $( this ).closest( 'tbody' );

		if ( $( this ).hasClass( 'is-hidden' ) ) {

		    $( this ).find( 'i' ).removeClass().addClass( 'icon-minus' );
		    table.find( '.expense-contract-' + idContract ).show();
		    $( this ).removeClass( 'is-hidden' );

		} else {

		    $( this ).find( 'i' ).removeClass().addClass( 'icon-plus' );
		    table.find( '.expense-contract-' + idContract ).hide();
		    $( this ).addClass( 'is-hidden' );
		}
	    }
	);

	$( '#transaction #contract-list tbody tr .contract-total' ).live( 'change',
	    function()
	    {
		Fefop.Financial.calcTotalTransaction();
	    }
	);
	
	$( '#transaction #contract-list tbody a.select-category' ).live( 'click',
	    function()
	    {
		$( this ).addClass( 'hide' );
		$( this ).closest( 'td' ).find( '.control-group' ).removeClass( 'hide' );
	    }
	);

	$( '#transaction #contract-list tbody .expense-category' ).live( 'change',
	    function()
	    {
		var category = $( this ).val();
		var idBudget = $( this ).attr( 'id' );
		var idContract = $( this ).data( 'contract' );
		var name = $( this ).find( 'option:selected' ).html();
		var valid = true;

		if ( General.empty( category ) )
		    name = 'Hili ida';
		else {
		    
		    
		    $( '#transaction #contract-list tbody .expense-contract-' + idContract + ' .expense-category' ).each(
			function()
			{
			    if ( $( this ).attr( 'id' ) ===  idBudget )
				return true;
			    
			    if ( $( this ).val() === category ) {
				valid = false;
				return valid;
			    }
			}
		    );
		}
		
		if ( !valid ) {
			
		    $( this ).val( '' ).trigger( 'change' );
		    Message.msgError( 'Kategoria hili tiha ona ba kontratu ida ne\'e.',  $( this ).closest( '.portlet-body' ) );
		    
		} else {

		    $( this ).closest( '.control-group' ).addClass( 'hide' );
		    $( this ).closest( 'tr' ).find( 'a.select-category' ).html( name ).removeClass( 'hide' );
		}
	    }
	);

	$( '#transaction #contract-list tbody .hide-select' ).live( 'click',
	    function()
	    {
		$( this ).closest( 'tr' ).find( 'select' ).trigger( 'change' );
	    }
	);

	$( '#transaction #contract-list tbody .expense-total' ).live( 'keyup',
	    function()
	    {
		var idContract = $( this ).data( 'contract' );
		Fefop.Financial.calcTotalContract( idContract );
	    }
	);
    },
    
    initFormFilterTransactions: function()
    {
	var form  = $( '#data form#search' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    Fefop.Financial.loadTransactions();
	};

	Form.addValidate( form, submit );
	
	form.find( '#slider-amount' ).slider(
	    {
		range: true,
		min: 0,
		max: 1000000,
		step: 100,
		values: [0, 20000],
		slide: function( event, ui ) 
		{
		    form.find( '#slider-age-amount' ).text( '$' + ( ui.values[0] ) + ' - ' + '$' + ( ui.values[1]  ));
		    form.find( '#minimum_amount' ).val( ui.values[0] );
		    form.find( '#maximum_amount' ).val( ui.values[1] );
		}
	    }
	);
	
	this.loadTransactions();
    },
    
    loadTransactions: function()
    {
	var form  = $( '#data form#search' );
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
		var table = $( '#data #transaction-list' );
		Fefop.Financial.configTransactionTable( table, response );
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', form );
	    }
	});
    },
    
    configTransactionTable: function( table, response )
    {
	table.find( 'tbody' ).empty();

	oTable = table.dataTable();
	oTable.fnDestroy(); 

	table.find( 'tbody' ).html( response );

	var drawpopOver = function()
	{
	    $( '.popovers' ).popover({html: true});
	};

	var settings = {
	    "aaSorting": [],
	    "fnFooterCallback": function ( row, data, start, end, display ) {

		var total = 0;
		for ( i in data ) {

		    var totalTransaction = General.toFloat( $( data[i][6] ).eq( 0 ).val() );
		    total += totalTransaction;
		}

		$( row ).find( 'th span' ).eq( 0 ).html( General.numberFormat( total, 2, '.', ',' ) );
	    }
	};
	
	var portlet = table.closest( '.portlet' );
	
	if ( portlet.find( '.tools a' ).hasClass( 'expand') )
	    portlet.find( '.tools a' ).trigger( 'click' );

	General.drawTables( table, drawpopOver, settings );
	General.scrollTo( table, 800 );
    },
    
    reloadTransactions: function()
    {
	Fefop.Financial.loadTransactions();
	Fefop.Financial.loadTotals();
    },
    
    loadTotals: function()
    {
	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: General.getUrl('/fefop/financial/calc-totals/' ),
	    beforeSend: function()
	    {
		General.loading( true );
		$( '.totals' ).html( '0.00' );
	    },
	    complete: function()
	    {
		General.loading( false );
	    },
	    success: function ( response )
	    {
		for ( x in response ) {
		    
		    var container = $( '.totals.' + x );
		    container.html( response[x] );
		    
		    //container.removeClass( 'text-important text-sucess' );
		    //var className = parseFloat( response[x].replace( /\$/g, '' ) ) < 0 ? 'text-error' : 'text-success';
		    //container.addClass( className );
		}
	    },
	    error: function ()
	    {
		$( '.totals' ).html( 'Err!' );
	    }
	});
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Fefop.Financial[method], pane );
    },
    
    newTransaction: function()
    {
	$( '.nav-tabs a' ).eq( 0 ).trigger( 'click' );
	var linkTab = $( '.nav-tabs .ajax-tab' ).eq( 0 );

	if ( linkTab.attr( 'data-former-url' ) )
	    linkTab.attr( 'data-href', linkTab.data( 'former-url' ) );
		
	linkTab.removeClass( 'loaded' ).trigger( 'click' );
    },
    
    configTransaction: function( pane )
    {
	var form = pane.find( 'form' );
	submit = function()
	{
	    if ( !form.find( '#contract-list tbody tr' ).length ) {
		
		Message.msgError( 'Tenki hili kontratu ida!', form );
		return false;
	    }
	    
	    var valid = true;
	    form.find( '#contract-list tbody tr .id-contract' ).each(
		function()
		{
		    var idContract = $( this ).val();
		    if ( !form.find( '#contract-list tbody tr.expense-contract-' + idContract ).length ) {
			
			valid = false;
			return false;
		    }
		}
	    );
	    
	    if ( !valid ) {
		
		Message.msgError( 'Kontratu hotu tenki iha rúbrica ida!', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			Fefop.Financial.newTransaction();
			$( '.nav-tabs a' ).eq( 0 ).trigger( 'click' );
			Fefop.Financial.loadTransactions();
			Fefop.Financial.loadTotals();
		    }
		}
	    };
	  
	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
    },
    
    searchEnterprise: function( form )
    {
	Fefop.Financial.containerEnterprise = $( form ).closest( '.tab-pane' );
	
	var settings = {
	    title: 'Buka Empreza',
	    url: '/fefop/financial/search-enterprise/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.Financial.initFormSearchEnterprise( modal );
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
		url: General.getUrl( '/fefop/financial/search-enterprise-forward' ),
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
				Fefop.Financial.setEnterprise( $( this ).data( 'id' ), modal );
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
		url: General.getUrl( '/fefop/financial/fetch-enterprise/' ),
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
		   var form = Fefop.Financial.containerEnterprise;
		   form.find( '#fk_id_fefpenterprise' ).val( '' );
		   form.find( 'form' ).populate( response, {resetForm: false} );
		   
		   General.scrollTo( form.find( '#enterprise' ) );
		   modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    transactionContract: function()
    {
	Fefop.Financial.setContractCallBack = Fefop.Financial.setTransactionContract;
	Fefop.Financial.searchContract();
    },
    
    setTransactionContract: function( response, modal )
    {
	$( '.nav-tabs a' ).eq( 0 ).trigger( 'click' );
	var linkTab = $( '.nav-tabs .ajax-tab' ).eq( 1 );

	var url = General.getUrl( '/fefop/financial/contract-control/id/' + response.id_fefop_contract );
	linkTab.attr( 'data-href', url ).parent().removeClass( 'disabled' );
	linkTab.removeClass( 'loaded' ).trigger( 'click' );
	modal.modal( 'hide' );
    },
    
    configContract: function( panel )
    {
	Fefop.Financial.configFormFund( panel );
	Fefop.Financial.configFormExpense( panel );
	Fefop.Financial.configFormAdditionals( panel );
	Fefop.Financial.configFormTransactionContract( panel );
    },
    
    configFormTransactionContract: function( panel )
    {
	var idContract = panel.find( '#id-contract' ).eq( 0 ).val();
	
	var url = General.getUrl( '/fefop/financial/transaction-contract/id/' + idContract );
	panel.find( '#transaction-contract' ).load( 
	    url,
	    function()
	    {
		Form.init();
		Fefop.Financial.loadTransactionsContract( idContract );
	    }
	);
    },
    
    loadTransactionsContract: function( idContract )
    {
	var table = $( '#transaction-contract' ).find( 'table' );
	$.ajax(
	    {
		type: 'POST',
		dataType: 'text',
		url: General.getUrl( '/fefop/financial/fetch-transaction-contract/' ),
		data: {id: idContract},
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
		    Fefop.Financial.configTransactionTable( table, response );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    configFormExpense: function( panel )
    {
	var idContract = panel.find( '#id-contract' ).eq( 0 ).val();
	
	var url = General.getUrl( '/fefop/financial/expense-contract/id/' + idContract );
	panel.find( '#expense-contract' ).load( 
	    url,
	    function()
	    {
		Form.init();
	    }
	);
    },
    
    configFormAdditionals: function( panel )
    {
	var idContract = panel.find( '#id-contract' ).eq( 0 ).val();
	
	var url = General.getUrl( '/fefop/financial/additional-costs/id/' + idContract );
	var paneContract = panel.find( '#additional-contract' );
	
	paneContract.load( 
	    url,
	    function()
	    {		
		paneContract.find( '.additional-cost' ).on(
		    'change',
		    function()
		    {
			var container = $( this ).closest( 'tr' );
			$( this ).addClass( 'changed' );
			
			if ( container.find( '.additional-cost' ).length === 1 || container.find( '.additional-cost.changed' ).length >= 1 ) {
			    
			    var data = { 
				contract: $( '#contract #id-contract' ).val(),
				expense: $( this ).data( 'expense' ),
				funds: {}
			    };
			    
			    container.find( '.additional-cost' ).each(
				function()
				{
				    data.funds[$(this).data('fund')] = $( this ).maskMoney( 'unmasked' )[0];
				}
			    );

			    $.ajax({
				type: 'POST',
				dataType: 'json',
				data: data,
				url: General.getUrl( '/fefop/financial/save-additional-contract/' ),
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
				    if ( response.status ) {
					
					Message.msgSuccess( 'Operasaun diak', paneContract );
					container.find( '.changed' ).removeClass( 'changed' );
					
				    } else {
					if ( response.description.length )
					    Message.showBulkMessages( response.description, paneContract );
					else
					    Message.msgError( 'Operasaun la diak', paneContract );
				    }
				},
				error: function ()
				{
				    Message.msgError( 'Erro ao executar operação', paneContract );
				}
			    });
			}
		    }
		);
	
		Form.init();
	    }
	);
    },
    
    configFormFund: function( panel )
    {
	var idContract = panel.find( '#id-contract' ).eq( 0 ).val();
	
	var url = General.getUrl( '/fefop/financial/fund-contract/id/' + idContract );
	panel.find( '#contract-fund' ).load( 
	    url,
	    function()
	    {
		var form = $( this ).find( 'form' ).eq( 0 );
		submit = function()
		{
		    var obj = {
			callback: function( response )
			{
			    if ( response.status ) {
				
				Fefop.Financial.configFormFund( panel );
				Fefop.Financial.configFormAdditionals( panel );
				
				form.find( '.required.changed' ).removeClass( 'changed' );
			    }
			}
		    };

		    Form.submitAjax( form, obj );
		    return false;
		};
		
		form.find( '.required' ).on(
		    'change',
		    function()
		    {
			var valid = true;
			form.find( '.required' ).each(
			    function()
			    {
				if ( $( this ).val() === "" )
				    valid = false;
			    }
			);
		
			if ( valid ) {
			    
			    $( this ).addClass( 'changed' );
			    if ( form.find( '.required' ).length === 1 || form.find( '.required.changed' ).length >= 1 )
				form.submit();
			}
		    }
		);

		Form.addValidate( form, submit );
		Form.init();
	    }
	);
    },
    
    searchContractTransaction: function()
    {
	Fefop.Financial.setContractCallBack = Fefop.Financial.addContractTransaction;
	Fefop.Financial.searchContract();
    },
    
    addContractTransaction: function( response, modal )
    {
	var table = $( '#transaction #contract-list' );
	var valid = true;
	table.find( '.id-contract' ).each(
	    function()
	    {
		if ( response.id_fefop_contract === $( this ).val() )
		    valid = false;
	    }
	);

	if ( !valid ) {
	    
	    Message.msgError( 'Kontratu iha tiha ona', modal.find( '.modal-body' ) );
	    return false;
	}
	
	$.ajax({
	    type: 'POST',
	    dataType: 'text',
	    data: {row: response},
	    url: General.getUrl( '/fefop/financial/add-contract/' ),
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
		table.find( 'tbody' ).append( response );
		
		$( table ).find( 'tbody tr:last .control-group input.required' ).each(
		    function()
		    {
			$( this ).rules( 'add', 'required' );
		    }
		);
	
		modal.modal( 'hide' );
		General.scrollTo( table, 800 );
		Form.init();
	    },
	    error: function ()
	    {
		Message.msgError( 'Erro ao executar operação', modal.find( '.modal-body' ) );
	    }
	});
    },
    
    addExpense: function( link )
    {
	var trContract = $( link ).closest( 'tr' );
	var idContract = trContract.find( '.id-contract' ).eq( 0 ).val();
	var table = $( '#transaction #contract-list' );
	
	var lastRow = table.find( 'tr.expense-contract-' + idContract ).last();
	if ( !lastRow.length )
	    lastRow = trContract;
	
	$.ajax({
	    type: 'POST',
	    dataType: 'text',
	    data: {id_contract: idContract},
	    url: General.getUrl( '/fefop/financial/add-expense/' ),
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
		$( response ).insertAfter( lastRow );
		
		var row = table.find( 'tr.expense-contract-' + idContract ).last();
		$( row ).find( '.required' ).each(
		    function()
		    {
			$( this ).rules( 'add', 'required' );
		    }
		);
		
		Form.init();
	    },
	    error: function ()
	    {
		Message.msgError( 'Erro ao executar operação', $( '#transaction form' ) );
	    }
	});
    },
    
    removeContract: function( link )
    {
	remove = function()
	{
	    tr = $( link ).closest( 'tr' );
	    var idContract = tr.find( '.id-contract' ).eq( 0 ).val();
	    
	    tr.remove();
	    $( '#transaction #contract-list .expense-contract-' + idContract ).remove();
	    
	    Fefop.Financial.calcTotalTransaction();
	};
	
	General.confirm( 'Ita hakarak hamoos kontratu ida ne\'e ?', 'Hamoos kontratu', remove );
    },
    
    removeExpense: function( link, idContract )
    {
	remove = function()
	{
	    $( link ).closest( 'tr' ).remove();
	    Fefop.Financial.calcTotalContract( idContract );
	};
	
	General.confirm( 'Ita hakarak hamoos rúbrica ida ne\'e ?', 'Hamoos rúbrica', remove );
    },
    
    searchContract: function()
    {
	var settings = {
	    title: 'Buka Kontratu',
	    url: '/fefop/financial/search-contract/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.Financial.initFormSearchContract( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormSearchContract: function( modal )
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
		url: General.getUrl( '/fefop/financial/list-contract' ),
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
		    modal.find( '#contract-list tbody' ).empty();
	     
		    oTable = modal.find( '#contract-list' ).dataTable();
		    oTable.fnDestroy(); 

		    modal.find( '#contract-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			modal.find( '#contract-list tbody a.action-ajax' ).click(
			    function()
			    {
				$( this ).attr( 'disabled', true );
				Fefop.Financial.setContract( $( this ).data( 'id' ), modal );
			    }
			);
		    };
		    
		    General.drawTables( modal.find( '#contract-list' ), callbackClick );
		    General.scrollTo( modal.find( '#contract-list' ), 200 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
	
	modal.find( '#slider-amount' ).slider(
	    {
		range: true,
		min: 0,
		max: 1000000,
		step: 100,
		values: [0, 20000],
		slide: function( event, ui ) 
		{
		    modal.find( '#slider-age-amount' ).text( '$' + ( ui.values[0] ) + ' - ' + '$' + ( ui.values[1]  ));
		    modal.find( '#minimum_amount' ).val( ui.values[0] );
		    modal.find( '#maximum_amount' ).val( ui.values[1] );
		}
	    }
	);
    
	Form.addValidate( form, submit );
    },
    
    setContract: function( id, modal )
    {
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/financial/fetch-contract/' ),
		data: {id: id},
		beforeSend: function()
		{
		    General.loading( true );
		    App.blockUI( modal.find( '.modal-body' ) );
		},
		complete: function()
		{
		    General.loading( false );
		    App.unblockUI( modal.find( '.modal-body' ) );
		},
		success: function ( response )
		{
		   Fefop.Financial.setContractCallBack( response, modal );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    calcTotalContract: function( id )
    {
	var total = 0;
	$( '#transaction #contract-list .expense-contract-' + id + ' .expense-total').each(
	    function()
	    {
		total += $( this ).maskMoney( 'unmasked' )[0];
	    }	
	);

	$( '#transaction #contract-list #contract_total_' + id ).maskMoney( 'mask', total ).trigger( 'change' );
    },
    
    calcTotalTransaction: function()
    {
	var total = 0;
	$( '#transaction #contract-list .contract-total' ).each(
	    function()
	    {
		total += $( this ).maskMoney( 'unmasked' )[0];
	    }
	);

	$( '#transaction #amount' ).maskMoney( 'mask', total );
    },
    
    editReceipt: function( id )
    {
	var linkTab = $( '.nav-tabs .ajax-tab' ).eq( 0 );
	
	if ( !linkTab.attr( 'data-former-url' ) )
	    linkTab.attr( 'data-former-url', linkTab.attr( 'data-href' ) );
	
	var url = General.getUrl( '/fefop/financial/edit-receipt/id/' + id );
	linkTab.attr( 'data-href', url );
		
	linkTab.removeClass( 'loaded' ).trigger( 'click' );
    },
    
    addTransactionContract: function( e )
    {
	e.stopPropagation();
	
	var idContract = $( '#contract #id-contract' ).val();
	if ( General.empty( idContract ) )
	    return false;
	
	if ( !Fefop.Financial.checkFundAmounts() ) {

	    var portlet = $( '#contract #contract-fund' ).closest( '.portlet' );
	
	    if ( portlet.find( '.tools a' ).hasClass( 'expand') )
		portlet.find( '.tools a' ).trigger( 'click' );
	    
	    Message.msgError( 'Uluk insere rejistu tenki insere folin hira fundu ketak selu', portlet.find( '.portlet-body' ) );
	    return false;
	}
	    
	
	var settings = {
	    title: 'Transasaun Kontratu',
	    url: '/fefop/financial/new-transaction-contract/id/' + idContract,
	    callback: function( modal )
	    {
		Form.init();
		Fefop.Financial.initFormTransactionContract( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    checkFundAmounts: function()
    {
	var valid = false;
	$( '#contract #contract-fund .fund-amount' ).each(
	    function()
	    {
		var value = $( this ).maskMoney( 'unmasked' )[0];
		if ( parseFloat( value ) !== 0 ) {
		    
		    valid = true;
		    return false;
		}
	    }
	);

	return valid;
    },
    
    editTransaction: function( id )
    {
	var settings = {
	    title: 'Transasaun Kontratu',
	    url: '/fefop/financial/edit-transaction-contract/id/' + id,
	    callback: function( modal )
	    {
		Form.init();
		Fefop.Financial.initFormTransactionContract( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormTransactionContract: function( modal )
    {
	Fefop.Financial.configFormNewTransactionContract( modal );
	Fefop.Financial.configChangeTypeTransaction( modal );
	Fefop.Financial.configChangeComponent( modal );
	Fefop.Financial.configBudgetCategory( modal );
    },
    
    configFormNewTransactionContract: function( modal )
    {
	var form = modal.find( 'form' );
	submit = function()
	{	 
	    App.blockUI( form );
	    
	    var obj = {
		callback: function( response )
		{
		    App.unblockUI( form );
		    
		    if ( response.status ) {
			
			var linkTab = $( '.nav-tabs .ajax-tab' ).eq( 0 );
			if ( linkTab.attr( 'data-former-url' ) )
			    linkTab.attr( 'data-href', linkTab.data( 'former-url' ) );
			
			linkTab.removeClass( 'loaded' );
			
			Fefop.Financial.loadTransactions();
			Fefop.Financial.loadTotals();
			Fefop.Financial.configContract( $( '.tab-pane#contract' ) );
			
			setTimeout(
			    function()
			    {
				modal.modal( 'hide' );
			    },
			    4000
			);
		    }
		}
	    };
	  
	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
    },
    
    configChangeTypeTransaction: function( modal )
    {
	modal.find( '#fk_id_fefop_type_transaction' ).change(
	    function()
	    {
		var transaction = $( this ).val();
		if ( General.empty( transaction  ) )
		    return false;
		
		modal.find( '#fk_id_budget_category_type' ).trigger( 'change' );
	    }
	).trigger( 'change' );
    },
    
    configChangeComponent: function( modal )
    {
	modal.find( '#fk_id_budget_category_type' ).change(
	    function()
	    {
		var component = $( this ).val();
		if ( General.empty( component  ) )
		    return false;
		
		var idContract = modal.find( '#fk_id_fefop_contract' ).val();
		var url = General.getUrl( '/fefop/financial/load-budget-category/id/' + component + '/contract/' + idContract );
		General.loadCombo( url, modal.find( '#fk_id_budget_category' ) );
	    }
	);
    },
    
    configBudgetCategory: function( modal )
    {
	modal.find( '#fk_id_budget_category' ).change(
	    function()
	    {
		var category = $( this ).val();
		if ( General.empty( category  ) ) {
		    
		    modal.find( '#total_contract' ).maskMoney( 'mask', 0 );
		    return false;
		}
		
		var contract = modal.find( '#fk_id_fefop_contract' ).val();
		
		var callback = function( amount )
		{
		    modal.find( '#total_contract' ).maskMoney( 'mask', amount );
		};
		
		Fefop.Financial.searchAmountContractBudget( contract, category, callback );
	    }
	);
    },
    
    searchAmountContractBudget: function( contract, category, callback )
    {
	$.ajax({
	    type: 'POST',
	    data: {
		contract: contract,
		category: category
	    },
	    dataType: 'json',
	    url: General.getUrl( '/fefop/financial/contract-amount-category/' ),
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
		callback.apply( this, [response.amount] );
	    }
	});
    },
    
    searchListContract: function()
    {
	Fefop.Financial.setContractCallBack = Fefop.Financial.setContractSearch;
	Fefop.Financial.searchContract();
    },
    
    setContractSearch: function( response, modal )
    {
	var form = $( '#data form' );
	
	$( '#fk_id_fefop_contract', form ).val( response.id_fefop_contract );
	$( '#name_contract', form ).val( response.num_contract + ' - ' + response.beneficiary );
	
	modal.modal( 'hide' );
    },
};

$( document ).ready(
    function()
    {
	Fefop.Financial.init();
    }
);