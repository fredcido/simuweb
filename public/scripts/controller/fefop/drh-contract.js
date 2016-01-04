Fefop = window.Fefop || {};

Fefop.DRHContract = {
    
    init: function()
    {
	this.initForm();
	this.initFormBulk();
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
		    $( '#drh-contract-list tbody' ).empty();
	     
		    oTable = $( '#drh-contract-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#drh-contract-list tbody' ).html( response );
		    
		    General.drawTables( '#drh-contract-list' );
		    General.scrollTo( '#drh-contract-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};

	Form.addValidate( form, submit );
    },
    
    initForm: function()
    {
	if ( !$( '.tabbable' ).length )
	    return false;
	
	General.setTabsAjax( '.tabbable', this.configForm );
	
	this.configInformation();
    },
    
    initFormBulk: function()
    {
	var form = $( '#fefopformdrhbulkcontract' );
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    if ( !$( '#container-contracts .contract' ).length ) {
		
		Message.msgError( 'Keta Halot! Presiza benefisiariu ba halo kontratu', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#contracts_ids', form ).val( response.id.join(',') );
			$( '#btn-print-contracts', form ).removeAttr( 'disabled' );
			
			setTimeout(
			    function()
			    {
				Fefop.DRHContract.printContracts();
			    },
			    2 * 1000
			);
			
			/*General.go( General.getUrl( '/fefop/drh-contract/export-contracts/ids/' + response.id.join(',') ) );
			setTimeout(
			    function()
			    {
				General.go( '/fefop/drh-contract/list' );
			    },
			    15 * 1000
			);*/
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	this.configCalcDateBulk( form );
    },
    
    printContracts: function()
    {
	var id = $( '#contracts_ids' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.go( General.getUrl( '/fefop/drh-contract/export-contracts/ids/' + id ) );
    },
    
    searchPlanningBluk: function()
    {	
	var settings = {
	    title: 'Buka Plano Formasaun',
	    url: '/fefop/drh-contract/planning-bulk/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.DRHContract.initFormPlanningBulk( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormPlanningBulk: function( modal )
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
		url: General.getUrl( '/fefop/drh-contract/search-planning-bulk' ),
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
		    $( '#drh-training-plan-list tbody' ).empty();
	     
		    oTable = $( '#drh-training-plan-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#drh-training-plan-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#drh-training-plan-list tbody a.action-ajax' ).click(
			    function()
			    {
				Fefop.DRHContract.setPlanningBluk( $( this ).data( 'id' ), modal );
			    }
			);
		    };
		    
		    General.drawTables( '#drh-training-plan-list', callbackClick );
		    General.scrollTo( '#drh-training-plan-list', 800 );
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
		min: 15,
		max: 50000,
		values: [1000, 20000],
		slide: function( event, ui ) 
		{
		    $( '#slider-age-amount' ).text( '$' + ( ui.values[0] ) + ' - ' + '$' + ( ui.values[1]  ));
		    $( '#minimum_amount' ).val( ui.values[0] );
		    $( '#maximum_amount' ).val( ui.values[1] );
		}
	    }
	);
    
	Form.addValidate( form, submit );
    },
    
    setPlanningBluk: function( id, modal )
    { 
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/drh-contract/fetch-planning/' ),
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
		    $( 'form' ).populate( response, {resetForm: true} );  
		    $( '#container-contracts' ).empty();
		    modal.modal('hide');
		    
		    Fefop.DRHContract.loadBulkBeneficiaries( response.fk_id_drh_trainingplan );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal.find( '.modal-body' ) );
		}
	    }
	);
    },
    
    loadBulkBeneficiaries: function( id )
    {
	$( '#container-contracts' ).load(
	    General.getUrl('/fefop/drh-contract/list-contracts/id/' + id ),
	    function()
	    {
		General.scrollTo( $( '#container-contracts' ) );
		Form.init();
	    }
	);
    },
    
    configCalcDateBulk: function( form )
    {
	form.find( '#date_start, #date_finish' ).live(
	    'change',
	    function()
	    {
		var container = $( this ).closest( '.contract' );
		
		var dateIni = container.find( '#date_start' ).eq( 0 ).val();
		if ( General.empty( dateIni ) )
		    return false;
		
		var dateFim = container.find( '#date_finish' ).eq( 0 ).val();
		if ( General.empty( dateFim ) )
		    return false;
		
		$.ajax(
		    {
			type: 'POST',
			dataType: 'json',
			url: General.getUrl( '/fefop/drh-contract/calc-diff-date/' ),
			data: {
			    date_start: dateIni,
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
			   container.find( '#duration_days' ).eq( 0 ).val( response.diff );
			}
		    }
		);
	    }
	);
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Fefop.DRHContract[method], pane );
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
			
			if ( General.empty( $( '#id_drh_contract' ).val() ) ) {
			
			    $( form ).find( '#id_drh_contract' ).val( response.id );
			    window.history.replaceState( {}, "DRH Contract Edit", BASE_URL + "/fefop/drh-contract/edit/id/" + response.id );
			    
			    Fefop.DRHContract.fetchContract();
			    Fefop.DRHContract.blockEditing();
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	this.blockEditing();
	this.configCalcDate();
    },
    
    configCalcDate: function()
    {
	var form  = $( '.tab-content #data form' );
	form.find( '#date_start, #date_finish' ).change(
	    function()
	    {
		var dateIni = form.find( '#date_start' ).eq( 0 ).val();
		if ( General.empty( dateIni ) )
		    return false;
		
		dateIniObj = Date.parseExact( dateIni, "d/M/yyyy");
		dateIniObj.add({years:1});
		
		form.find( '#date_formation' ).eq( 0 ).val( dateIniObj.toString('dd/MM/yyyy') );
		
		var dateFim = form.find( '#date_finish' ).eq( 0 ).val();
		if ( General.empty( dateFim ) )
		    return false;
		
		$.ajax(
		    {
			type: 'POST',
			dataType: 'json',
			url: General.getUrl( '/fefop/drh-contract/calc-diff-date/' ),
			data: {
			    date_start: dateIni,
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
			   form.find( '#duration_days' ).eq( 0 ).val( response.diff );
			}
		    }
		);
	    }
	);
    },
    
    blockEditing: function()
    {
	if ( General.empty( $( '#id_drh_contract' ).val() ) )
	    return false;
	
	$( '#btn-search-planning' ).attr( 'disabled', true ).addClass( 'disabled' );
	$( '#fk_id_adddistrict' ).attr( 'readonly', true );
	$( '#btn-export-contract' ).removeAttr( 'disabled' );
	Form.initReadOnlySelect();
	Fefop.Contract.setContainerContract().setIdContract( $( '#fk_id_fefop_contract' ).val() );
    },
    
    
    fetchContract: function()
    {
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/drh-contract/fetch-contract/' ),
		data: {id: $( '#id_drh_contract' ).val()},
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
		   Fefop.DRHContract.blockEditing();
		   
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
    
    searchPlanning: function()
    {	
	var settings = {
	    title: 'Buka Plano Formasaun',
	    url: '/fefop/drh-contract/search-planning/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.DRHContract.initFormPlanning( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormPlanning: function( modal )
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
		url: General.getUrl( '/fefop/drh-contract/search-planning-forward' ),
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
		    $( '#drh-beneficiary-list tbody' ).empty();
	     
		    oTable = $( '#drh-beneficiary-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#drh-beneficiary-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#drh-beneficiary-list tbody a.action-ajax' ).click(
			    function()
			    {
				Fefop.DRHContract.setPlanning( $( this ).data( 'id' ), modal );
			    }
			);
		    };
		    
		    General.drawTables( '#drh-beneficiary-list', callbackClick );
		    General.scrollTo( '#drh-beneficiary-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
    },
    
    setPlanning: function( id, modal )
    { 
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/drh-contract/fetch-staff/' ),
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
		   var setFinalPlanning = function( checking )
		   {
		       if ( !checking.valid ) {
		 
			    Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
			    return false;
			}

			$( '#data form' ).populate( response, {resetForm: false} );  
			General.scrollTo( '#breadcrumb' );

			$( '#expense-list tr .cost-expense' ).eq( 0 ).maskMoney( 'mask', response.training_fund );
			$( '#expense-list tr .cost-expense' ).eq( 1 ).maskMoney( 'mask', response.final_cost );

			modal.modal( 'hide' );
		   };
		   
		   Fefop.Contract.checkBlacklist(
			{fk_id_staff: response.id_staff, fk_id_perdata: response.fk_id_perdata},
			setFinalPlanning
		   );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal.find( '.modal-body' ) );
		}
	    }
	);
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
	var id = $( '#id_drh_contract' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/drh-contract/export/id/' + id ) );
    }
};

$( document ).ready(
    function()
    {
	Fefop.DRHContract.init();
    }
);