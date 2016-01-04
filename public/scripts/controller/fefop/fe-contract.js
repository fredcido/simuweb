Fefop = window.Fefop || {};

Fefop.FEContract = {
    
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
		    $( '#fe-contract-list tbody' ).empty();
	     
		    oTable = $( '#fe-contract-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#fe-contract-list tbody' ).html( response );
		    
		    General.drawTables( '#fe-contract-list' );
		    General.scrollTo( '#fe-contract-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
	
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
	
	General.execFunction( Fefop.FEContract[method], pane );
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
			
			if ( General.empty( $( '#id_fe_contract' ).val() ) ) {
			
			    $( form ).find( '#id_fe_contract' ).val( response.id );
			    window.history.replaceState( {}, "FE Contract Edit", BASE_URL + "/fefop/fe-contract/edit/id/" + response.id );
			    
			    Fefop.FEContract.fetchContract();
			    Fefop.FEContract.blockEditing();
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
	this.configChangeDistrict();
    },
    
    configCalcDate: function()
    {
	var form  = $( '.tab-content #data form' );
	form.find( '#date_start, #date_finish' ).change(
	    function()
	    {
		var dateFim = form.find( '#date_finish' ).eq( 0 ).val();
		if ( General.empty( dateFim ) ) {
		    
		    form.find( '#duration_month' ).eq( 0 ).val( '' );
		    return false;
		}
		
		dateFimObj = Date.parseExact( dateFim, "d/M/yyyy");
		dateFimObj.add({years:1});
		
		form.find( '#date_formation' ).eq( 0 ).val( dateFimObj.toString('dd/MM/yyyy') );
		
		var dateIni = form.find( '#date_start' ).eq( 0 ).val();
		if ( General.empty( dateIni ) )
		    return false;
		
		$.ajax(
		    {
			type: 'POST',
			dataType: 'json',
			url: General.getUrl( '/fefop/fe-contract/calc-diff-month/' ),
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
			   form.find( '#duration_month' ).eq( 0 ).val( response.diff );
			}
		    }
		);
	    }
	);
    },
    
    blockEditing: function()
    {
	if ( General.empty( $( '#id_fe_contract' ).val() ) )
	    return false;
	
	$( '#btn-search-entity' ).attr( 'disabled', true ).addClass( 'disabled' );
	$( '#btn-search-beneficiary' ).attr( 'disabled', true ).addClass( 'disabled' );
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
		
		url = '/fefop/fe-contract/search-sub-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsubdistrict' );
	    }
	).trigger( 'change' );
    },
    
    fetchContract: function()
    {
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fe-contract/fetch-contract/' ),
		data: {id: $( '#id_fe_contract' ).val()},
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
		   Fefop.FEContract.blockEditing();
		   
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
    
    searchRegistration: function()
    {	
	var settings = {
	    title: 'Buka Ficha Inskrisaun',
	    url: '/fefop/fe-contract/search-registration/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.FEContract.initFormSearchRegistration( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormSearchRegistration: function( modal )
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
		url: General.getUrl( '/fefop/fe-contract/search-registration-forward' ),
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
		    $( '#fe-registration-list tbody' ).empty();
	     
		    oTable = $( '#fe-registration-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#fe-registration-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#fe-registration-list tbody a.action-ajax' ).click(
			    function()
			    {
				Fefop.FEContract.setRegistration( $( this ).data( 'id' ), modal );
			    }
			);
		    };
		    
		    General.drawTables( '#fe-registration-list', callbackClick );
		    General.scrollTo( '#fe-registration-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
    },
    
    setRegistration: function( id, modal )
    { 
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fe-contract/fetch-registration/' ),
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
		   if ( !General.empty( response.id_fe_contract ) ) {
		       
			Message.msgError( 'Ficha inskrisaun ne\'e iha kontratu tiha ona.', modal.find( '.modal-body' ) );
			return false;
		   }
		   
		   var setClientRegistration = function( checking )
		   {
		       if ( !checking.valid ) {
		 
			    Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
			    return false;
			}
			
			Fefop.FEContract.modalEntityRegistration( response );
			modal.modal( 'hide' );
		   };
		    
		   Fefop.Contract.checkBlacklist(
			{fk_id_perdata: response.fk_id_perdata},
			setClientRegistration
		   );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    modalEntityRegistration: function( data )
    {
	var settings = {
	    title: 'Hili Entidade ba Ficha Inskrisaun',
	    url: '/fefop/fe-contract/select-entity/',
	    data: data,
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		
		var callbackClick = function()
		{
		    $( 'tbody a.action-ajax', modal.find( 'table' ) ).on(
			'click',
			function()
			{
			    var type = $( this ).data( 'type' );
			    var id = $( this ).data( 'id' );
			    var entity = $( this ).data( 'entity' );
			    
			    var setFinalRegistration = function( checking )
			    {
				if ( !checking.valid ) {

				     Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
				     return false;
				 }

				 data[type] = id;
				 data.entity = entity;
				 
				 $( '#fk_id_fefpeduinstitution, #fk_id_fefpenterprise, #fk_id_trainee' ).val( '' );
				 
				 console.log( data );
				 
				 $( '#data form' ).populate( data, {resetForm: false} );
				 $( '#data form .chosen' ).trigger( 'change' );
				 
				 General.scrollTo( '#breadcrumb' );
				 modal.modal( 'hide' );
			    };

			    Fefop.Contract.checkBlacklist(
				 {type: id},
				 setFinalRegistration
			    );
			}
		    );
		};
		General.drawTables( modal.find( 'table' ), callbackClick );
	    }
	};

	General.ajaxModal( settings );
    },
    
    searchInstitute: function()
    {	
	var settings = {
	    title: 'Buka Inst. Ensinu',
	    url: '/fefop/fe-contract/search-institute/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.FEContract.initFormSearchInstitute( modal );
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
		url: General.getUrl( '/fefop/fe-contract/search-institute-forward' ),
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
				Fefop.FEContract.setInstitute( $( this ).data( 'id' ), modal );
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
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fe-contract/fetch-institute/' ),
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
		   var setFinalInstitute = function( checking )
		   {
		       if ( !checking.valid ) {
		 
			    Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
			    return false;
			}
			
			$( '#fk_id_fefpeduinstitution, #fk_id_fefpenterprise, #fk_id_trainee' ).val( '' );
			$( '#data form' ).populate( response, {resetForm: false} );

			General.scrollTo( '#breadcrumb' );

			$( '#entity' ).trigger( 'change' );

			modal.modal( 'hide' );
		   };
		    
		   Fefop.Contract.checkBlacklist(
			{fk_id_fefpeduinstitution: response.fk_id_fefpeduinstitution},
			setFinalInstitute
		   );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
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
	
	$.ajax({
	    type: 'POST',
	    dataType: 'text',
	    data: {
		expense: id
	    },
	    url: General.getUrl( '/fefop/fe-contract/add-detailed-expense/' ),
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
		Fefop.FEContract.initFormSearchEnterprise( modal );
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
		url: General.getUrl( '/fefop/fe-contract/search-enterprise-forward' ),
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
				Fefop.FEContract.setEnterprise( $( this ).data( 'id' ), modal );
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
	}
    
	Form.addValidate( form, submit );
    },
    
    setEnterprise: function( id, modal )
    {
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fe-contract/fetch-enterprise/' ),
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
			
			$( '#fk_id_fefpeduinstitution, #fk_id_fefpenterprise, #fk_id_trainee' ).val( '' );
			$( '#data form' ).populate( response, {resetForm: false} );
			General.scrollTo( '#breadcrumb' );
			modal.modal( 'hide' );
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
    
    searchClient: function()
    {
	var settings = {
	    title: 'Buka Kliente',
	    url: '/fefop/fe-contract/search-client/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.FEContract.initFormSearchClient( modal );
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
		url: General.getUrl( '/fefop/fe-contract/search-client-forward' ),
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
				Fefop.FEContract.setClient( $( this ).data( 'id' ), modal );
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
	};
    
	Form.addValidate( form, submit );
	Form.handleClientSearch( form );
    },
    
    setClient: function( id, modal )
    {
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fe-contract/fetch-client/' ),
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
		   var setFinalClient = function( checking )
		   {
		       if ( !checking.valid ) {
		 
			    Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
			    return false;
			}
			
			$( '#data form' ).populate( response, {resetForm: false} );
			General.scrollTo( '#breadcrumb' );
			modal.modal( 'hide' );
		    
		   };
		   
		   Fefop.Contract.checkBlacklist(
			{fk_id_perdata: response.fk_id_perdata},
			setFinalClient
		   );
		   
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
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
    
    searchTrainee: function()
    {	
	var settings = {
	    title: 'Buka Job Training',
	    url: '/fefop/fe-contract/search-trainee/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.FEContract.initFormSearchTrainee( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormSearchTrainee: function( modal )
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
		url: General.getUrl( '/fefop/fe-contract/search-trainee-forward' ),
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
		    $( '#trainee-list tbody' ).empty();
	     
		    oTable = $( '#trainee-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#trainee-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#trainee-list tbody a.action-ajax' ).click(
			    function()
			    {
				Fefop.FEContract.setTrainee( $( this ).data( 'id' ), modal );
			    }
			);
		    };
		    
		    General.drawTables( '#trainee-list', callbackClick );
		    General.scrollTo( '#trainee-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
    },
    
    setTrainee: function( id, modal )
    { 
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fe-contract/fetch-trainee/' ),
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
		   if ( response.valid ) {
		       
		    var setFinalTrainee = function( checking )
		    {
			if ( !checking.valid ) {

			     Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
			     return false;
			}

			$( '#fk_id_fefpeduinstitution, #fk_id_fefpenterprise, #fk_id_trainee' ).val( '' );
			$( '#data form' ).populate( response, {resetForm: false} );

			$( '#fk_id_scholarity_area' ).trigger( 'change' );
			$( '#date_start' ).trigger( 'change' );
			$( '#entity' ).trigger( 'change' );

			General.scrollTo( '#breadcrumb' );

			modal.modal( 'hide' );
		    };

		    Fefop.Contract.checkBlacklist(
			 {fk_id_perdata: response.fk_id_perdata},
			 setFinalTrainee
		    );
	   
		   } else 
			     Message.msgError( 'Rejistu iha ne\'e iha kontratu tiha ona.', modal.find( 'form' ) );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    exportContract: function()
    {
	var id = $( '#id_fe_contract' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/fe-contract/export/id/' + id ) );
    }
};

$( document ).ready(
    function()
    {
	Fefop.FEContract.init();
    }
);