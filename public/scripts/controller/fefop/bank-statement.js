Fefop = window.Fefop || {};

Fefop.BankStatement = {
    
    init: function()
    {
	this.initFormFilterStatements();
	this.loadTotals();
	this.configEvents();
    },
    
    configEvents: function()
    {
	$( '#contract-list tbody tr .hide-expenses' ).live( 'click',
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

	$( '#contract-list tbody tr .contract-total' ).live( 'change',
	    function()
	    {
		Fefop.BankStatement.calcTotalStatement();
	    }
	);
	
	$( '#contract-list tbody a.select-category' ).live( 'click',
	    function()
	    {
		$( this ).addClass( 'hide' );
		$( this ).closest( 'td' ).find( '.control-group' ).removeClass( 'hide' );
	    }
	);

	$( '#contract-list tbody .expense-category' ).live( 'change',
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
		    
		    
		    $( '#contract-list tbody .expense-contract-' + idContract + ' .expense-category' ).each(
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
		    Message.msgError( 'Kategoria hili tiha ona ba kontratu ida ne\'e.',  $( '.modal-body' ) );
		    
		} else {

		    $( this ).closest( '.control-group' ).addClass( 'hide' );
		    $( this ).closest( 'tr' ).find( 'a.select-category' ).html( name ).removeClass( 'hide' );
		}
	    }
	);

	$( '#contract-list tbody .hide-select' ).live( 'click',
	    function()
	    {
		$( this ).closest( 'tr' ).find( 'select' ).trigger( 'change' );
	    }
	);

	$( '#contract-list tbody .expense-total' ).live( 'keyup',
	    function()
	    {
		var idContract = $( this ).data( 'contract' );
		Fefop.BankStatement.calcTotalContract( idContract );
	    }
	);
    },
    
    initFormFilterStatements: function()
    {
	var form  = $( 'form#search' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    Fefop.BankStatement.loadTransactions();
	};

	Form.addValidate( form, submit );
	
	this.loadTransactions();
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
		Fefop.BankStatement.configTransactionTable( table, response );
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
    
    reloadTransactions: function()
    {
	Fefop.BankStatement.loadTransactions();
	Fefop.BankStatement.loadTotals();
    },
    
    loadTotals: function()
    {
	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: General.getUrl('/fefop/bank-statement/calc-totals/' ),
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
    
    newStatement: function()
    {
	var settings = {
	    title: 'Transasaun Banku',
	    url: '/fefop/bank-statement/new-statement/',
	    callback: function( modal )
	    {
		Form.init();
		Fefop.BankStatement.initFormStatement( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormStatement: function( pane )
    {
	var form = pane.find( 'form' );
	submit = function()
	{
	    var type = parseInt( $( '#fk_id_fefop_type_transaction', form ).val() );
	    var contractOperations = [1,2];
	    
	    if ( $.inArray( type, contractOperations ) > -1 ) {
		
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
	    
	    }
	    
	    App.blockUI( form );
	    
	    var obj = {
		callback: function( response )
		{
		    App.unblockUI( form );
		    
		    if ( response.status ) {
			
			Fefop.BankStatement.loadTransactions();
			Fefop.BankStatement.loadTotals();
			
			pane.modal( 'hide' );
		    }
		}
	    };
	  
	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	Fefop.BankStatement.configChangeTransaction( form );	
    },
    
    configChangeTransaction: function( form )
    {
	var mapperTypeOperation = {
	    1: 'D',
	    2: 'C',
	    3: 'C',
	    4: 'C',
	    5: 'C'
	};
	
	$( '#fk_id_fefop_type_transaction', form ).on(
	    'change',
	    function()
	    {
		if ( $( this ).is( '[readonly]' ) )
		    return false;
		
		var type = parseInt( $( this ).val() );
		var contractOperations = [1,2];
		
		if ( $.inArray( type, contractOperations ) > -1 ) {
		    
		    $( '#contract-list', form ).closest( '.row-fluid.hide' ).removeClass( 'hide' );
		    $( '#amount', form ).attr( 'readonly', true );
		    $( '#fk_id_fefopfund', form ).val('').closest( '.container' ).addClass( 'hide' );
		    
		} else {
		    
		    $( '#contract-list' ).closest( '.row-fluid' ).addClass( 'hide' );
		    $( '#amount', form ).removeAttr( 'readonly' );
		    $( '#fk_id_fefopfund', form ).closest( '.container' ).removeClass( 'hide' );
		}
		
		if ( type > 2 ) {
		    Form.makeRequired( $( '#fk_id_fefopfund', form ), true );
		    $( '#contract-list tbody' ).empty();
		} else {
		    
		    $( '#fk_id_fefopfund', form ).val( '' );
		    Form.makeRequired( $( '#fk_id_fefopfund', form ), false );
		}
		
		if ( !General.empty( mapperTypeOperation[type] ) )
		    $( '#operation', form ).val( mapperTypeOperation[type] );
		    
	    }
	).trigger( 'change' );
    },
    
    searchContract: function()
    {
	var settings = {
	    title: 'Buka Kontratu',
	    url: '/fefop/bank-statement/search-contract/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.BankStatement.initFormSearchContract( modal );
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
		url: General.getUrl( '/fefop/bank-statement/list-contract' ),
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
				Fefop.BankStatement.setContract( $( this ).data( 'id' ), modal );
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
		max: 1000000,
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
		url: General.getUrl( '/fefop/bank-statement/fetch-contract/' ),
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
		   Fefop.BankStatement.addContractTransaction( response, modal );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    addContractTransaction: function( response, modal )
    {
	var table = $( '#contract-list' );
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
	    url: General.getUrl( '/fefop/bank-statement/add-contract/' ),
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
		Form.init();
		
		$( table ).find( 'tbody tr:last .control-group input.required' ).each(
		    function()
		    {
			$( this ).rules( 'add', 'required' );
			$( this ).maskMoney( 'mask', 0 );
		    }
		);
	
		modal.modal( 'hide' );
		General.scrollTo( table, 800 );
		//Fefop.BankStatement.checkContracts();
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
	var table = $( '#contract-list' );
	
	var lastRow = table.find( 'tr.expense-contract-' + idContract ).last();
	if ( !lastRow.length )
	    lastRow = trContract;
	
	$.ajax({
	    type: 'POST',
	    dataType: 'text',
	    data: {id_contract: idContract},
	    url: General.getUrl( '/fefop/bank-statement/add-expense/' ),
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
		Form.init();
		
		var row = table.find( 'tr.expense-contract-' + idContract ).last();
		$( row ).find( '.required' ).each(
		    function()
		    {
			$( this ).maskMoney( 'mask', 0 );
			$( this ).rules( 'add', 'required' );
		    }
		);
	
		Fefop.BankStatement.calcTotalStatement();
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
	    $( '#contract-list .expense-contract-' + idContract ).remove();
	    
	    Fefop.BankStatement.calcTotalStatement();
	    //Fefop.BankStatement.checkContracts();
	};
	
	General.confirm( 'Ita hakarak hamoos kontratu ida ne\'e ?', 'Hamoos kontratu', remove );
    },
    
    removeExpense: function( link, idContract )
    {
	remove = function()
	{
	    $( link ).closest( 'tr' ).remove();
	    Fefop.BankStatement.calcTotalContract( idContract );
	};
	
	General.confirm( 'Ita hakarak hamoos rúbrica ida ne\'e ?', 'Hamoos rúbrica', remove );
    },
    
    calcTotalContract: function( id )
    {
	var total = 0;
	$( '#contract-list .expense-contract-' + id + ' .expense-total').each(
	    function()
	    {
		total += $( this ).maskMoney( 'unmasked' )[0];
	    }	
	);

	$( '#contract-list #contract_total_' + id ).maskMoney( 'mask', total ).trigger( 'change' );
    },
    
    calcTotalStatement: function()
    {
	var total = 0;
	$( '#contract-list .contract-total' ).each(
	    function()
	    {
		total += $( this ).maskMoney( 'unmasked' )[0];
	    }
	);

	$( '#amount' ).maskMoney( 'mask', total );
    },
    
    checkContracts: function()
    {
	if ( $( '#contract-list .row-contract' ).length )
	    $( '#formstatement #fk_id_fefop_type_transaction' ).attr( 'readonly', true );
	else
	    $( '#formstatement #fk_id_fefop_type_transaction' ).removeAttr( 'readonly' );
    },
    
    editStatement: function( id )
    {
	var settings = {
	    title: 'Edita Transasaun Banku',
	    url: '/fefop/bank-statement/edit-statement/id/' + id,
	    callback: function( modal )
	    {
		Form.init();
		Fefop.BankStatement.initFormStatement( modal );
	    }
	};

	General.ajaxModal( settings );
    }
};

$( document ).ready(
    function()
    {
	Fefop.BankStatement.init();
    }
);  