Fefop = window.Fefop || {};

Fefop.FPContract = {
    
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
		    $( '#fp-contract-list tbody' ).empty();
	     
		    oTable = $( '#fp-contract-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#fp-contract-list tbody' ).html( response );
		    
		    General.drawTables( '#fp-contract-list' );
		    General.scrollTo( '#fp-contract-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
	
	$( '#slider-amount' ).slider(
	    {
		range: true,
		min: 0,
		max: 50000,
		values: [0, 20000],
		slide: function( event, ui ) 
		{
		    $( '#slider-age-amount' ).text( '$' + ( ui.values[0] ) + ' - ' + '$' + ( ui.values[1]  ));
		    $( '#minimum_amount' ).val( ui.values[0] );
		    $( '#maximum_amount' ).val( ui.values[1] );
		}
	    }
	);

	this.configChangeCategoryScholarity( form );
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
	
	General.execFunction( Fefop.FPContract[method], pane );
    },
    
    configInformation: function()
    {
	var form  = $( '.tab-content #data form' );
	form.bind( 'clear',
	    function()
	    {
		form.find( '#btn-search-class' ).attr( 'disabled', true );
	    }
	);

	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( General.empty( $( '#id_fp_contract' ).val() ) ) {
			
			    $( form ).find( '#id_fp_contract' ).val( response.id );
			    window.history.replaceState( {}, "FP Contract Edit", BASE_URL + "/fefop/fp-contract/edit/id/" + response.id );
			    
			    Fefop.FPContract.fetchContract();
			}
		    }
		}
	    };
	    
	    dataTable = $( '#client-list' ).dataTable();
	    var data = [];
	    $( '.submit-field', dataTable.fnGetNodes() ).each(
		function()
		{
		    data.push( { name: $( this ).attr( 'name' ), value: $( this ).val() } );
		}
	    );
    
	    if ( data.length < 1 ) {
		
		Message.msgError( 'La bele halo kontraktu ho turma mak la iha benefisiariu', form );
		return false;
	    }
    
	    obj.data = data;

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	if ( !General.empty( $( '#id_fp_contract' ).val() ) ) {
	 
	    Fefop.FPContract.blockEditing();
	    Fefop.FPContract.listBeneficiaries();
	    Fefop.Contract.setContainerContract().setIdContract( $( '#fk_id_fefop_contract' ).val() );
	}
    },
    
    fetchContract: function()
    {
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fp-contract/fetch-contract/' ),
		data: {id: $( '#id_fp_contract' ).val()},
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
		   Fefop.FPContract.blockEditing();
		   
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
    
    blockEditing: function()
    {
	var container  = $( '.tab-content #data form' );
	
	container.find( '#btn-search-planning' ).attr( 'disabled', true );
	container.find( '#btn-search-class' ).attr( 'disabled', true );
	container.find( '#fk_id_adddistrict' ).attr( 'readOnly', true );
	container.find( '#btn-export-contract' ).removeAttr( 'disabled' );
	container.find( '.form-actions' ).remove();
	Form.initReadOnlySelect();
    },
    
    searchAnnualPlanning: function()
    {	
	var settings = {
	    title: 'Buka Planeamentu ba Tinan',
	    url: '/fefop/fp-contract/search-annual-planning/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.FPContract.initFormSearchAnnualPlanning( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormSearchAnnualPlanning: function( modal )
    {
	var form  = modal.find( 'form' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    var data = $( form ).serializeArray();
	    
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: data,
		dataType: 'text',
		url: form.attr( 'action' ),
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
		    $( '#annual-planning-list tbody' ).empty();
	     
		    oTable = $( '#annual-planning-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#annual-planning-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#annual-planning-list tbody a.action-ajax' ).click(
			    function()
			    {
				Fefop.FPContract.setAnnualPlanning( $( this ).data( 'id' ), modal );
			    }
			);
		    };
		    
		    General.drawTables( '#annual-planning-list', callbackClick );
		    General.scrollTo( '#annual-planning-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
	this.configChangeCategoryScholarity( form );
    },
    
    configChangeCategoryScholarity: function( modal )
    {
	modal.find( '#category' ).change(
	    function()
	    {
		var category =  $( this ).val();
		if ( General.empty( category ) ) {
		    
		    modal.find( '#fk_id_perscholarity' ).val( '' );
		    return false;
		}
	
		url = '/fefop/fp-contract/search-course/category/' + category;
		General.loadCombo( url, modal.find( '#fk_id_perscholarity' ) );
	    }
	);
    },
    
    setAnnualPlanning: function( id, modal )
    { 
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fp-contract/fetch-annual-planning/' ),
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
			
			$( 'form' ).populate( response, {resetForm: false} );
			$( '#btn-search-class' ).removeAttr( 'disabled' );

			modal.modal( 'hide' );
			General.scrollTo( '#breadcrumb' );

			Fefop.FPContract.fetchUnitCost();
			Fefop.FPContract.detailPlanningCourse( response.fk_id_planning_course );
		   };
		    
		   Fefop.Contract.checkBlacklist(
			{fk_id_fefpeduinstitution: response.id_fefpeduinstitution},
			setFinalPlanning
		   );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    fetchUnitCost: function()
    {
	var container  = $( '.tab-content #data form' );
	var scholarity = container.find( '#fk_id_perscholarity' ).val();
	
	container.find( '#unit_cost' ).val( '' );
	container.find( '#amount' ).val( '' );
	container.find( '#fk_id_unit_cost' ).val( '' );
	
	if ( !General.empty( scholarity ) ) {
	    
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/fefop/fp-contract/fetch-unit-cost/' ),
		    data: {
			scholarity: scholarity
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
			if ( General.empty( response ) ) {
			
			    Message.setFadeOut( false ).msgError( 'Kustu Unitariu seidauk rejistu ba kursu ne\'e. Keta rejistu Kontraktu.', container );
			    return false;
			}

			container.find( '#unit_cost' ).val( response.cost );
			container.find( '#fk_id_unit_cost' ).val( response.id );
			
			Fefop.FPContract.calcUnitCost();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', container );
		       
			container.find( '#unit_cost' ).val( '' );
			container.find( '#amount' ).val( '' );
			container.find( '#fk_id_unit_cost' ).val( '' );
		    }
		}
	    );
	}
    },
    
    searchClass: function()
    {
	var container  = $( '.tab-content #data form' );
	var scholarity = container.find( '#fk_id_perscholarity' ).val();
	
	if ( General.empty( scholarity ) ) {
	    
	    Message.msgError( 'Tenki hili Planeamentu ba Tinan uluk buka Turma.', container );
	    return false;
	}
	
	var settings = {
	    title: 'Buka Klase Formasaun',
	    url: '/fefop/fp-contract/class-planning/',
	    data: {
		
	    },
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
	
		Form.init();
		Fefop.FPContract.configSearchClass( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    configSearchClass: function( pane )
    {
	form = pane.find( 'form' );
	
	var container  = $( '.tab-content #data form' );
	var institute = container.find( '#fk_id_fefpeduinstitution' ).val();
	var scholarity = container.find( '#fk_id_perscholarity' ).val();
	
	form.find( '#fk_id_fefpeduinstitution' ).val( institute ).trigger( 'change' );
	form.find( '#fk_id_perscholarity' ).val( scholarity ).trigger( 'change' );
	
	submit = function()
	{
	    var data = $( form ).serializeArray();
	    data.push( {name: 'list-ajax', value: 1} );
	    
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: $.param( data ),
		dataType: 'text',
		url: form.attr( 'action' ),
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
		    $( '#class-list tbody' ).empty();
	     
		    oTable = $( '#class-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#class-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#class-list tbody a.action-ajax' ).click(
			    function()
			    {
				Fefop.FPContract.setClass( $( this ).data( 'id' ), pane );
			    }
			);
		    };
		    
		    General.drawTables( '#class-list', callbackClick );
		    General.scrollTo( '#class-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
	
	Form.addValidate( form, submit );
    },
    
    setClass: function( id_class, modal )
    {
	var container  = $( '.tab-content #data form' );
	var institute = container.find( '#fk_id_fefpeduinstitution' ).val();
	var scholarity = container.find( '#fk_id_perscholarity' ).val();
	
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fp-contract/fetch-class/' ),
		data: {
		    scholarity: scholarity,
		    institute: institute,
		    id_class: id_class
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
		    if ( General.empty( response ) || General.empty( response.status ) ) {

			Message.setFadeOut( false ).msgError( 'Turma ida ne\'e la hanesan Instituisaun de Ensinu no mos Formasaun iha Planeamentu ba Tinan.', modal.find( 'form' ) );
			return false;
		    }

		    container.find( '#class_name' ).val( response.class );
		    container.find( '#fk_id_fefpstudentclass' ).val( response.id );
		    modal.modal( 'hide' );
		    
		    if ( response.date_start != container.find( '#start_date' ).val() || 
			 response.date_finish != container.find( '#finish_date' ).val() ) {
			
			var message = 'Klase nia loron inisiu no loron remata la hanesan planeamentu: Inisiu: ' + response.date_start + ', Remata: ' + response.date_finish;
			Message.setFadeOut( false ).msgError( message, container );
		    }
		    
		    Fefop.FPContract.listClients();
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );

		    container.find( '#class_name' ).val( '' );
		    container.find( '#fk_id_fefpstudentclass' ).val( '' );
		}
	    }
	);
    },
    
    listClients: function()
    {
	var container  = $( '.tab-content #data form' );
	var id_class = container.find( '#fk_id_fefpstudentclass' ).val();
	
	if ( General.empty( id_class ) ) {
	    
	     $( '#client-list tbody' ).empty();
	     
	    oTable = $( '#client-list' ).dataTable();
	    oTable.fnDestroy(); 
	    return false;
	}
	
	callback = function()
	{
	    App.initUniform();
	    Form.init();
	    Fefop.FPContract.calcUnitCost();
	};
	
	General.loadTable( '#client-list', '/fefop/fp-contract/list-client/id/' + id_class, callback );
    },
    
    listBeneficiaries: function()
    {
	var container  = $( '.tab-content #data form' );
	var id_class = container.find( '#fk_id_fefpstudentclass' ).val();
	
	if ( General.empty( id_class ) ) {
	    
	     $( '#client-list tbody' ).empty();
	     
	    oTable = $( '#client-list' ).dataTable();
	    oTable.fnDestroy(); 
	    return false;
	}
	
	callback = function()
	{
	    App.initUniform();
	    Form.init();
	};
	
	General.loadTable( '#client-list', '/fefop/fp-contract/list-beneficiaries/id/' + $( '#id_fp_contract' ).val(), callback );
    },
    
    calcUnitCost: function()
    {
	var container  = $( '.tab-content #data form' );
	var cost = container.find( '#unit_cost' ).val();
	var idClass = container.find( '#fk_id_fefpstudentclass' ).val();
	
	if ( General.empty( cost ) || General.empty( idClass ) )
	    return false;
	
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/fp-contract/calc-unit-cost/' ),
		data: {
		    cost: cost,
		    id_class: idClass
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
		   container.find( '#amount' ).val( response.total );
		   
		   var dataTable = container.find( '#client-list' ).dataTable();
		   $( '.cost-client', dataTable.fnGetNodes() ).each(
			function()
			{
			    if ( !General.empty( response.costs[$(this).attr( 'id' )] ) )
				$( this ).val( response.costs[$(this).attr( 'id' )] );
			}
		    );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );

		    container.find( '#amount' ).val( '' );
		   
		   var dataTable = container.find( '#client-list' ).dataTable();
		   $( '.cost-client', dataTable.fnGetNodes() ).each(
			function()
			{
			    $( this ).val( 0 );
			}
		    );
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
	var id = $( '#id_fp_contract' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/fp-contract/export/id/' + id ) );
    },
    
    detailPlanningCourse: function( id )
    {
	$( '#planning-course-detail' ).load( General.getUrl( '/fefop/fp-contract/detail-planning/id/' + id ) );
    }
};

$( document ).ready(
    function()
    {
	Fefop.FPContract.init();
    }
);