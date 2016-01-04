Fefop = window.Fefop || {};

Fefop.Expense = {
    
    init: function()
    {
	General.setTabsAjax( '.tabbable', Fefop.Expense.configForm );
	Fefop.Expense.configInformation();
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Fefop.Expense[method], pane );
    },
    
    configInformation: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/fefop/expense' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	Fefop.Expense.loadExpenses();
    },
    
    loadExpenses: function()
    {
	General.loadTable( '#expense-list', '/fefop/expense/list' );
    },
    
    configConfiguration: function( pane )
    {
	Fefop.Expense.configChangeItem( pane );
	
	General.drawTables( '#expense-item-list' );
	General.drawTables( '#expense-all-list' );
    },
    
    configChangeItem: function( form )
    {
	form.find( '#item_config' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#expense-item-list' ).dataTable().fnClearTable();
		    $( '#expense-all-list' ).dataTable().fnClearTable();
		    return false;
		}
		
		item = $( this ).val();
		
		/*
		$( '#expense-item-list' ).removeClass( 'drag-drop' )
		
		callbackItem = function()
		{
		    if ( $( '#expense-item-list' ).hasClass( 'drag-drop' ) )
			return false;
		    
		    $( '#expense-item-list' ).addClass( 'drag-drop' );
		    var dt = $( '#expense-item-list' ).dataTable();
		    
		    dt.rowReordering(
			{ 
			    sURL: General.getUrl( '/fefop/expense/order-expense/item/' + item ),
			    fnUpdateAjaxRequest: function( ajaxRequest, properties, table )
			    {
				var id = ajaxRequest.data.id;
				var amount = $( '#total_expense_' + id ).maskMoney( 'unmasked' )[0];
				ajaxRequest.data.amount = amount;
			    }
			}
		    );
	    
		    $( 'input.money-mask', $( '#expense-item-list' ) ).on(
			'change',
			function()
			{
			    Fefop.Expense.updateValueExpense( $( this ), item );
			}
		    );
	    
		    Form.init();
		};
		
		General.loadTable( '#expense-item-list', '/fefop/expense/expenses-item/id/' + item, callbackItem );
		*/
		
		Fefop.Expense.loadExpenseItems( item );
		General.loadTable( '#expense-all-list', '/fefop/expense/expenses-not-item/id/' + item );
	    }
	);
    },
    
    loadExpenseItems: function( item )
    {
	var table = $( '#expense-item-list' );
	
	$.ajax({
	    type: 'GET',
	    dataType: 'html',
	    url: General.getUrl( '/fefop/expense/expenses-item/id/' + item ),
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
		oTable = $( table ).dataTable();
		if ( oTable )
		    oTable.fnDestroy(); 
		
		$( table ).find( 'tbody' ).empty().html( response );
		
		General.drawTables( table );
		dt = $( table ).dataTable();
		
		dt.rowReordering(
		    { 
			sURL: General.getUrl( '/fefop/expense/order-expense/item/' + item ),
			fnUpdateAjaxRequest: function( ajaxRequest, properties, table )
			{
			    var id = ajaxRequest.data.id;
			    var amount = $( '#total_expense_' + id ).maskMoney( 'unmasked' )[0];
			    ajaxRequest.data.amount = amount;
			}
		    }
		);

		$( 'input.money-mask', table ).on(
		    'change',
		    function()
		    {
			Fefop.Expense.updateValueExpense( $( this ), item );
		    }
		);

		Form.init();
	    },
	    error: function ()
	    {
		$( table ).find( 'tbody' ).empty().html( response );
	    }
	});
    },
    
    updateValueExpense: function( expenseAmount, item )
    {
	var expense = $( expenseAmount ).data( 'expense' );
	var value = $( expenseAmount ).maskMoney( 'unmasked' )[0];
	
	$.ajax({
	    type: 'POST',
	    data: {
		id: expense,
		amount: value,
		item: item
	    },
	    dataType: 'json',
	    url: General.getUrl( '/fefop/expense/update-amount/' ),
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
		if ( !response )
		    $( expenseAmount ).maskMoney( 'mask', 0 );
	    },
	    error: function ()
	    {
		$( expenseAmount ).maskMoney( 'mask', 0 );
	    }
	});
    },
    
    getChecked: function ( selector )
    {
	dataTable = $( selector ).dataTable();
	ids = [];
	$( 'input:not(:disabled):checked', dataTable.fnGetNodes() ).each(
	    function()
	    {
		ids.push( $( this ).val() );
	    }
	);
	    
	return ids;
    },
    
    insertExpenseItem: function()
    {
	expenses = this.getChecked( '#expense-all-list' );
	form = $( '#expense-all-list' ).closest( 'form' );
	
	if ( !expenses.length ) {
	    
	    Message.msgError( 'Tenke hili Rúbrica ba hatama.', form );
	    return false;
	}
	
	var data = $( form ).serializeArray();
	for ( i in expenses )
	    data.push( { name: 'expenses[]', value: expenses[i] } );
	
	$.ajax(
	    {
		type: 'POST',
		data: $.param( data ),
		dataType: 'json',
		url: General.getUrl( '/fefop/expense/save-expenses-item' ),
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
			Message.msgError( msg, form );

		    } else {

			Message.msgSuccess( 'Operasaun diak', form );
			$( '#item_config', form ).trigger( 'change' );
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    }
	);
    },
    
    removeExpenseItem: function()
    {
	expenses = this.getChecked( '#expense-item-list' );
	form = $( '#expense-item-list' ).closest( 'form' );
	
	if ( !expenses.length ) {
	    
	    Message.msgError( 'Tenke hili Rúbrica ba hatama.', form );
	    return false;
	}
	
	var data = $( form ).serializeArray();
	for ( i in expenses )
	    data.push( { name: 'expenses[]', value: expenses[i] } );
	
	$.ajax(
	    {
		type: 'POST',
		data: $.param( data ),
		dataType: 'json',
		url: General.getUrl( '/fefop/expense/remove-expenses-item' ),
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
			Message.msgError( msg, form );

		    } else {

			Message.msgSuccess( 'Operasaun diak', form );
			$( '#item_config', form ).trigger( 'change' );
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    }
	);
    },
    
    removeExpense: function( id )
    {
	remove = function()
	{
	    var container = $( '#expense-list' ).closest( '.tab-pane' );
	    
	    $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/expense/remove-expense/id/' + id ),
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
			Message.msgError( 'Operasaun la diak', container );
		    } else {
			
			Message.msgSuccess( 'Operasaun diak', container );
			Fefop.Expense.loadExpenses();
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
	};
	
	General.confirm( 'Ita hakarak hamoos Rúbrica ida ne\'e ?', 'Hamoos Rúbrica', remove );
    }
};

$( document ).ready(
    function()
    {
	Fefop.Expense.init();
    }
);