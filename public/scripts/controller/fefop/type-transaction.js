Fefop = window.Fefop || {};

Fefop.TypeTransaction = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/fefop/type-transaction' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );

	this.loadTypeTransactions();
    },
    
    loadTypeTransactions: function()
    {
	General.loadTable( '#type-transaction-list', '/fefop/type-transaction/list' );
    },
    
    removeType: function( id )
    {
	remove = function()
	{
	    var container = $( '#type-transaction-list' ).closest( '.tab-pane' );
	    
	    $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/type-transaction/remove-transaction-type/id/' + id ),
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
			Fefop.TypeTransaction.loadTypeTransactions();
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
	};
	
	General.confirm( 'Ita hakarak hamoos Tipu Transasaum ida ne\'e ?', 'Hamoos Tipu Transasaum', remove );
    }
};

$( document ).ready(
    function()
    {
	Fefop.TypeTransaction.init();
    }
);