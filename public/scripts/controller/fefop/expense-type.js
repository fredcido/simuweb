Fefop = window.Fefop || {};

Fefop.ExpenseType = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/fefop/expense-type' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadExpenseTypes();
    },
    
    loadExpenseTypes: function()
    {
	General.loadTable( '#expense-type-list', '/fefop/expense-type/list' );
    },
    
    removeTypeExpense: function( id )
    {
	remove = function()
	{
	    var container = $( '#expense-type-list' ).closest( '.tab-pane' );
	    
	    $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/expense-type/remove-expense-type/id/' + id ),
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
			Fefop.ExpenseType.loadExpenseTypes();
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
	};
	
	General.confirm( 'Ita hakarak hamoos Komponente ida ne\'e ?', 'Hamoos Komponente', remove );
    }
};

$( document ).ready(
    function()
    {
	Fefop.ExpenseType.init();
    }
);