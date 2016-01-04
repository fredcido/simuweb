Fefop = window.Fefop || {};

Fefop.BankConsolidate = {
    
    init: function()
    {
	General.setTabsAjax( '.tabbable', this.configForm );
	this.configData();
	this.loadTotals();
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Fefop.BankConsolidate[method], pane );
    },
    
    configData: function()
    {
	var form = $( '#data form' );
	submit = function()
	{
	    var table  = $( '#consolidate-list', form );
	    
	    if ( !$( 'input:checked', table ).length ) {
		
		Message.msgError( 'La iha lansamentu ba konsolida!', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status )
			history.go( 0 );
		}
	    };
	  
	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
    },
    
    loadTotals: function()
    {
	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: General.getUrl('/fefop/bank-consolidate/calc-totals/' ),
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
		    
		    var container = $( '.totals.total_' + x );
		    container.html( response[x] );
		}
	    },
	    error: function ()
	    {
		$( '.totals' ).html( 'Err!' );
	    }
	});
    },
    
    configListConsolidate: function( pane )
    {
	var form  = $( 'form', pane );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    Fefop.BankConsolidate.loadTransactions();
	};

	Form.addValidate( form, submit );
	Fefop.BankConsolidate.loadTransactions();
    },
    
    loadTransactions: function()
    {
	var form  = $( 'form#search' );
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
		var table = $( '#statement-list' );
		Fefop.BankConsolidate.configTransactionTable( table, response );
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

	var portlet = table.closest( '.portlet' );
	
	if ( portlet.find( '.tools a' ).hasClass( 'expand') )
	    portlet.find( '.tools a' ).trigger( 'click' );

	General.drawTables( table );
	General.scrollTo( table, 800 );
    },
    
    searchContract: function()
    {
	var settings = {
	    title: 'Buka Kontratu',
	    url: '/fefop/bank-consolidate/search-contract/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.BankConsolidate.initFormSearchContract( modal );
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
		url: General.getUrl( '/fefop/bank-consolidate/list-contract' ),
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
				Fefop.BankConsolidate.setContract( $( this ).data( 'id' ), modal );
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
		min: 100,
		max: 50000,
		step: 100,
		values: [1000, 20000],
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
		url: General.getUrl( '/fefop/bank-consolidate/fetch-contract/' ),
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
		    $( '#search #id_fefop_bank_contract' ).val( response.id_fefop_contract );
		    $( '#search #contract' ).val( response.num_contract + ' - ' + response.beneficiary );
		    
		    modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    detailSession: function( id )
    {
	var settings = {
	    title: 'Detallamentu konsolida',
	    url: '/fefop/bank-consolidate/redirect-detail-session/id/' + id
	};

	General.ajaxModal( settings );
    },
    
    detailConsolidate: function( id )
    {
	var settings = {
	    title: 'Detallamentu konsolida',
	    url: '/fefop/bank-consolidate/detail-consolidate-row/id/' + id
	};

	General.ajaxModal( settings );
    },
    
    removeConsolidate: function( id )
    {
	remove = function()
	{   
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/fefop/bank-consolidate/remove/' ),
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
			if ( response.status )
			    Fefop.BankConsolidate.loadTransactions();
		    },
		    error: function ()
		    {
		       Message.msgError( 'Operasaun la diak', $( '#search') );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos konsolidasaun ida ne\'e ?', 'Hamoos konsolidasaun', remove );
    }
    
};

$( document ).ready(
    function()
    {
	Fefop.BankConsolidate.init();
    }
);