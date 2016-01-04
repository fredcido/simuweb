Fefop = window.Fefop || {};

Fefop.DRHTrainingPlan = {
    
    init: function()
    {
	this.initForm();
	this.initFormSearch();
	$( '.money-mask' ).focus().blur();
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
		    $( '#drh-training-plan-list tbody' ).empty();
	     
		    oTable = $( '#drh-training-plan-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#drh-training-plan-list tbody' ).html( response );
		    
		    General.drawTables( '#drh-training-plan-list' );
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
    
    initForm: function()
    {
	var form  = $( 'form#fefopformdrhtrainingplan' );
	if ( form.length < 1 )
	    return false;
	
	submit = function()
	{
	    if ( $( '#beneficiary-list tbody tr' ).length < 1 ) {
		
		Message.msgError( 'Tenki tau benefisariu ida.', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( General.empty( $( '#id_drh_trainingplan' ).val() ) ) {
			
			    $( form ).find( '#id_drh_trainingplan' ).val( response.id );
			    window.history.replaceState( {}, "RI Contract Edit", BASE_URL + "/fefop/drh-training-plan/edit/id/" + response.id );
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	this.configChangeModality();
	this.configChangeCountry();
	this.configCalcDate();
	this.configCalcExpense();
    },
    
    createNum: function( id )
    {
	if ( $( '#drn-training-plan-number' ).length )
	    return false;

	span = $( '<span />' );
	span.attr( 'id', 'drn-training-plan-number' )
	    .addClass( 'well pull-right number-system' );

	$.ajax({
	    type: 'GET',
	    dataType: 'json',
	    url: General.getUrl( '/fefop/drh-training-plan/fetch-num-training-plan/id/' + id ),
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
		$( '#container-num-training-plan' ).append( span.append( $( '<strong />' ).html( response.num ) ) );
	    }
	});
    },
    
    configCalcExpense: function()
    {
	$( 'form .expense-amount').live( 'change', 
	    function()
	    {
		Fefop.DRHTrainingPlan.calcTotals();
	    }
	);
    },
    
    configCalcDate: function()
    {
	var form  = $( 'form' );
	form.find( '#date_start, #date_finish' ).change(
	    function()
	    {
		var dateIni = form.find( '#date_start' ).eq( 0 ).val();
		if ( General.empty( dateIni ) )
		    return false;
	
		var dateFim = form.find( '#date_finish' ).eq( 0 ).val();
		if ( General.empty( dateFim ) )
		    return false;
		
		$.ajax(
		    {
			type: 'POST',
			dataType: 'json',
			url: General.getUrl( '/fefop/drh-training-plan/calc-diff-date/' ),
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
    
    configChangeModality: function()
    {
	$( '#modality' ).change(
	    function()
	    {
		$( '#health-insurance .trainer p' ).addClass( 'hide' );
		
		var mValue = $( this ).val();
		if ( General.empty( mValue ) ) {
		    
		    Fefop.DRHTrainingPlan.setCountryTimor( false );
		    return false;
		}
		
		if ( mValue == 'T' ) {
		    $( '#health-insurance .trainer p.text-error' ).removeClass( 'hide' );
		} else {
		    
		    $( '#health-insurance .trainer p.text-success' ).removeClass( 'hide' );
		}
		
		if ( mValue == 'L' )
		    $( '#need_insurance' ).val( 0 );
		else
		    $( '#need_insurance' ).val( 1 );
		
		Fefop.DRHTrainingPlan.setCountryTimor( mValue != 'A' );
		
	    }
	).trigger( 'change' );

	$( '#country' ).change(
	    function()
	    {
		$( '#fk_id_addcountry' ).val( $( this ).val() );
	    }
	);
    },
    
    setCountryTimor: function( flag )
    {
	$( '#health-insurance .beneficiary p' ).addClass( 'hide' );
	
	if ( flag ) {
	    
	    $( '#country_timor' ).closest( '.span4' ).removeClass( 'hide' );
	    $( '#country' ).val('').trigger( 'change' ).closest( '.span4' ).addClass( 'hide' );
	    $( '#fk_id_addcountry' ).val( 1 );
	    $( '#health-insurance .beneficiary p.text-success' ).removeClass( 'hide' );
	    
	} else {
	    
	    $( '#country_timor' ).closest( '.span4' ).addClass( 'hide' );
	    $( '#country' ).trigger( 'change' ).closest( '.span4' ).removeClass( 'hide' );
	    $( '#health-insurance .beneficiary p.text-error' ).removeClass( 'hide' );
	}
	
	Form.makeRequired( $( '#country' ), !flag );
    },
     
    configChangeCountry: function()
    {
	$( '#fk_id_addcountry' ).change(
	    function()
	    {
		$( '#health-insurance .beneficiary p' ).addClass( 'hide' );
		
		var cValue = $( this ).val();
		if ( General.empty( cValue ) )
		    return false;
		
		if ( cValue != 1 )
		    $( '#health-insurance .beneficiary p.text-error' ).removeClass( 'hide' );
		else
		    $( '#health-insurance .beneficiary p.text-success' ).removeClass( 'hide' );
	    }
	).trigger( 'change' );
    },
    
    searchInstitute: function()
    {	
	this.isSearchingStaff = false;
	
	var settings = {
	    title: 'Buka Inst. Ensinu',
	    url: '/fefop/drh-training-plan/search-institute/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.DRHTrainingPlan.initFormSearchInstitute( modal );
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
		url: General.getUrl( '/fefop/drh-training-plan/search-institute-forward' ),
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
				Fefop.DRHTrainingPlan.setInstitute( $( this ).data( 'id' ), modal );
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
	if ( !General.empty( Fefop.DRHTrainingPlan.isSearchingStaff ) ) {
	
	    modal.modal( 'hide' );
	    Fefop.DRHTrainingPlan.listStaff( id );
	
	} else {
	    
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/fefop/drh-training-plan/fetch-institute/' ),
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
		       $( 'form' ).populate( response, {resetForm: false} );
		       General.scrollTo( '#breadcrumb' );
		       $( '#institution' ).trigger( 'change' );
		       modal.modal( 'hide' );
		    },
		    error: function ()
		    {
		       Message.msgError( 'Operasaun la diak', modal );
		    }
		}
	    );
	}
    },
    
    addBeneficiary: function( e )
    {
	e.stopPropagation();
	
	this.searchInstitute();
	this.isSearchingStaff = true;
    },
    
    listStaff: function( id )
    {
	var settings = {
	    title: 'Lista Staff',
	    url: '/fefop/drh-training-plan/list-staff/id/' + id,
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		callbackClick = function()
		{
		    modal.find( 'table tbody a.action-ajax' ).click(
			function()
			{
			    Fefop.DRHTrainingPlan.addStaff( $( this ).data( 'id' ), modal );
			}
		    );
		};

		General.drawTables( modal.find( 'table' ), callbackClick );
	    }
	};

	General.ajaxModal( settings );
    },
    
    addStaff: function( id, modal )
    {
	 var valid = true;
	 $( '#beneficiary-list .id-staff' ).each(
	    function()
	    {
		if ( id == $( this ).val() ) {
		    
		    valid = false;
		    return false;
		}
	    }
	 );
 
	 if ( !valid ) {
	     
	     Message.msgError( 'Benefisiariu iha lista tiha ona', modal.find( '.modal-body' ) );
	     return false;
	 }
	
	 var insertStaff = function( response )
	 {
	     if ( !response.valid ) {
		 
		 Message.msgError( Fefop.Contract.getMessageBlackList(), modal.find( '.modal-body' ) );
		 return false;
	     }
	     
	    $.ajax(
	       {
		   type: 'POST',
		   dataType: 'text',
		   url: General.getUrl( '/fefop/drh-training-plan/add-staff/' ),
		   data: {id: id},
		   beforeSend: function()
		   {
		       App.blockUI( modal );
		   },
		   complete: function()
		   {
		       App.unblockUI( modal );
		   },
		   success: function ( response )
		   {
		       $( '#beneficiary-list' ).find( 'tbody' ).append( response );

		       $( '#beneficiary-list' ).find( 'tbody tr:last .control-group input.required' ).each(
			   function()
			   {
			       $( this ).rules( 'add', 'required' );
			   }
		       );

		       if ( !$( '#beneficiary-list' ).closest( '.portlet-body' ).is( ':visible' ) )
			   $( '#beneficiary-list' ).closest( '.portlet' ).find( '.portlet-title' ).trigger( 'click' );

		       $( '#beneficiary-list' ).find( 'tbody tr:last .control-group input:first' ).focus();

		       Form.init();

		       General.scrollTo( '#beneficiary-list', 800 );
		       modal.modal( 'hide' );
		       Fefop.DRHTrainingPlan.calcTotals();
		   },
		   error: function ()
		   {
		      Message.msgError( 'Operasaun la diak', modal );
		   }
	       }
	   );
	};
	
	Fefop.Contract.checkBlacklist(
	    {fk_id_staff: id},
	    insertStaff
	);
    },
    
    removeItem: function( link )
    {
	remove = function()
	{
	    tr = $( link ).closest( 'tr' );
	    tr.remove();
	    
	    Fefop.DRHTrainingPlan.calcTotals();
	};
	
	General.confirm( 'Ita hakarak hamoos item ida ne\'e ?', 'Hamoos item', remove );
    },
    
    searchExpense: function( e )
    {	
	e.stopPropagation();
	
	var settings = {
	    title: 'Rúbrica',
	    url: '/fefop/drh-training-plan/business-expense/',
	    callback: function( modal )
	    {
		callbackClick = function()
		{
		    modal.find( 'table tbody a' ).click(
			function()
			{
			    $( this ).attr( 'disabled', true );
			    Fefop.DRHTrainingPlan.addExpense( $( this ).data( 'id' ), modal );
			}
		    );
		};
		
		General.drawTables( modal.find( 'table' ), callbackClick );
	    }
	};

	General.ajaxModal( settings );
    },
    
    addExpense: function( id, modal )
    {
	var valid = true;
	$( '#expense-list .id-expense' ).each(
	    function()
	    {
		if ( id == $( this ).val() ) {
		    
		    valid = false;
		    return false;
		}
	    }
	 );
 
	 if ( !valid ) {
	     
	     Message.msgError( 'Rúbrica iha lista tiha ona', modal.find( '.modal-body' ) );
	     return false;
	 }
	 
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'text',
		url: General.getUrl( '/fefop/drh-training-plan/add-expense/' ),
		data: {id: id},
		beforeSend: function()
		{
		    App.blockUI( modal );
		},
		complete: function()
		{
		    App.unblockUI( modal );
		},
		success: function ( response )
		{
		    $( '#expense-list' ).find( 'tbody' ).append( response );

		    $( '#expense-list' ).find( 'tbody tr:last .control-group input.required' ).each(
			function()
			{
			    $( this ).rules( 'add', 'required' );
			}
		    );

		    if ( !$( '#expense-list' ).closest( '.portlet-body' ).is( ':visible' ) )
			$( '#expense-list' ).closest( '.portlet' ).find( '.portlet-title' ).trigger( 'click' );

		    $( '#expense-list' ).find( 'tbody tr:last .control-group input:first' ).focus();

		    Form.init();

		    General.scrollTo( '#expense-list', 800 );
		    modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    calcTotals: function()
    {
	if ( $( '.expense-amount' ).length < 1 )
	    return false;
	
	if ( $( '#beneficiary-list tbody tr' ).length < 1 )
	    return false;
	
	var valid = false;
	$( '.expense-amount' ).each(
	    function()
	    {
		if ( !General.empty( $( this ).val() ) ) {
		    
		    valid = true;
		    return false;
		}
	    }
	);

	if ( !valid ) return false;
	
	var data = [];
	$( '.submit-field' ).each(
	    function()
	    {
		var value = $( this ).val();
		if ( $( this ).hasClass( 'money-mask' ) )
		    value = $( this ).maskMoney( 'unmasked' )[0];
		
		data.push( { name: $( this ).attr( 'name' ), value: value } );
	    }
	);

	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/drh-training-plan/calc-totals/' ),
		data: data,
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
		    for ( x in response.staff ) {
			
			var staff = response.staff[x];
			
			var idStaff = staff.id_staff;
			$( '#unit_cost-' + idStaff ).maskMoney( 'mask', staff.unit_cost ); 
			$( '#final_cost-' + idStaff ).val( staff.final_cost ); 
			$( '#training_fund-' + idStaff ).maskMoney( 'mask', staff.training_cost ); 
		    }
		    
		    $( '#amount_expenses' ).maskMoney( 'mask', response.amount_expenses ); 
		    $( '#amount' ).maskMoney( 'mask', response.amount ); 
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', $( 'form' ) );
		}
	    }
	);
    }
};

$( document ).ready(
    function()
    {
	Fefop.DRHTrainingPlan.init();
    }
);