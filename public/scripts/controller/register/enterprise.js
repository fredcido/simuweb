Register = window.Register || {};

Register.Enterprise = {
    
    TIMOR_LESTE: 1,
    
    init: function()
    {
	this.initFormSearch();
	this.initForm();
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
		    $( '#enterprise-list tbody' ).empty();
	     
		    oTable = $( '#enterprise-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#enterprise-list tbody' ).html( response );
		    
		    General.drawTables( '#enterprise-list' );
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
    
    initForm: function()
    {
	General.setTabsAjax( '.tabbable', this.configForm );
	
	this.configInformation();
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Register.Enterprise[method], pane );
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
			
			if ( General.empty( $( '#id_fefpenterprise' ).val() ) ) {
			
			    $( form ).find( '#id_fefpenterprise' ).val( response.id );

			    window.history.replaceState( {}, "Empreza Edit", BASE_URL + "/register/enterprise/edit/id/" + response.id );

			    $( '.nav-tabs a.ajax-tab' ).each(
				function()
				{
				    dataHref = $( this ).attr( 'data-href' );
				    $( this ).attr( 'data-href', dataHref + response.id );
				    $( this ).parent().removeClass( 'disabled' );
				}
			    );

			    $( '.nav-tabs a.ajax-tab' ).eq( 0 ).trigger( 'click' );
			
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
    },
    
    configContact: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#contact #clear' ).trigger( 'click' );
			Register.Enterprise.loadContacts();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	
	$( '#contact #email' ).rules( 'add', 'email' );
	
	Register.Enterprise.loadContacts();
    },
    
    loadContacts: function()
    {
	General.loadTable( '#contact-list', '/register/enterprise/list-contacts/id/' + $( '#id_fefpenterprise' ).val() );
    },
    
    editContact: function( link )
    {
	id = $( link ).data( 'contact' );
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: BASE_URL + '/register/enterprise/fetch-contact/id/' + id,
		beforeSend: function()
		{
		    App.blockUI( '#contact form' );
		},
		complete: function()
		{
		    App.unblockUI( '#contact form' );
		},
		success: function ( response )
		{
		   $( '#contact form' ).populate( response, {resetForm: true} );
		   General.scrollTo( '#breadcrumb' );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', '#contact' );
		}
	    }
	);
    },
    
    removeContact: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'contact' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/register/enterprise/delete-contact/',
		    data: {
			id_contact: id,
			id: $( '#fk_id_fefpenterprise' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( '#contact-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#contact-list' );
		    },
		    success: function ( response )
		    {
		    Register.Enterprise.loadContacts();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#contact' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Contatu ida ne\'e ?', 'Hamoos contatu', remove );
    },
    
    configAddress: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#address #clear' ).trigger( 'click' );
			Register.Enterprise.loadAddress();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	
	$( '#start_date' ).daterangepicker(
	    {
                format: 'dd/MM/yyyy',
                separator: ' to\'o '
            },
	    function( start, end )
	    {
		$( '#start_date' ).val( start.toString( 'dd/MM/yyyy' ) );
		$( '#finish_date' ).val( end.toString( 'dd/MM/yyyy' ) );
	    }
        );
	
	Register.Enterprise._configChangeCountry();
	Register.Enterprise._configChangeDistrict();
	Register.Enterprise._configChangeSubDistrict();
	Register.Enterprise.loadAddress();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    _configChangeCountry: function()
    {
	$( '#fk_id_addcountry' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_adddistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		els = $( '#address .box-content .row-fluid :input' ).not( '#fk_id_addcountry, #complement, #start_date, #finish_date' );
		
		if ( $( this ).val() != Register.Enterprise.TIMOR_LESTE ) {
		    
		    els.each(
			function()
			{
			    $( this ).val( '' ).trigger( 'change' ).attr( 'disabled', true );
				    
			    var label = $( this ).parents( '.control-group' ).find( 'label' );	    
			    if ( label.hasClass( 'required' ) )
				label.attr( 'data-required', true ).removeClass( 'required' );
			}
		    );
			
		    $( '#complement' ).rules( 'add', 'required' );
		    $( '#complement' ).parents( '.control-group' ).find( 'label' ).addClass( 'required' );
		    
		} else {
		    
		    url = '/register/enterprise/search-district/id/' + $( this ).val();
		    General.loadCombo( url, 'fk_id_adddistrict' );
		    
		    els.each(
			function()
			{
			    $( this ).val( '' ).trigger( 'change' ).removeAttr( 'disabled' );
				    
			    var label = $( this ).parents( '.control-group' ).find( 'label' );	    
			    if ( label.attr( 'data-required' ) )
				label.addClass( 'required' );
			}
		    );
		    
		    $( '#complement' ).rules( 'remove', 'required' )
		    $( '#complement' ).parents( '.control-group' ).find( 'label' ).removeClass( 'required' );
		}
	    }
	);
    },
    
    _configChangeDistrict: function()
    {
	$( '#fk_id_adddistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsubdistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/enterprise/search-sub-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsubdistrict' );
	    }
	);
    },
    
    _configChangeSubDistrict: function()
    {
	$( '#fk_id_addsubdistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsucu' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/enterprise/search-suku/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsucu' );
	    }
	);
    },
    
    loadAddress: function()
    {
	General.loadTable( '#address-list', '/register/enterprise/list-address/id/' + $( '#fk_id_fefpenterprise' ).val() );
    },
    
    editAddress: function( link )
    {
	id = $( link ).data( 'address' );
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: BASE_URL + '/register/enterprise/fetch-address/id/' + id,
		beforeSend: function()
		{
		    App.blockUI( '#address form' );
		},
		complete: function()
		{
		    App.unblockUI( '#address form' );
		},
		success: function ( response )
		{
		    $( '#fk_id_adddistrict' ).attr( 'data-value', response.fk_id_adddistrict );
		    $( '#fk_id_addsubdistrict' ).attr( 'data-value', response.fk_id_addsubdistrict );
		    $( '#fk_id_addsucu' ).attr( 'data-value', response.fk_id_addsucu );
		    
		    $( '#address form' ).populate( response, {resetForm: true} );
		    $( '#fk_id_addcountry' ).trigger( 'change' ); 
		    
		    General.scrollTo( '#breadcrumb' );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', '#address' );
		}
	    }
	);
    },
    
    removeAddress: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'address' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/register/enterprise/delete-address/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#address-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#address-list' );
		    },
		    success: function ( response )
		    {
			Register.Enterprise.loadAddress();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#address' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos hela fatin ida ne\'e ?', 'Hamoos hela fatin', remove );
    },
    
    configStaff: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#staff #clear' ).trigger( 'click' );
			Register.Enterprise.loadStaff();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Register.Enterprise.loadStaff();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    loadStaff: function()
    {
	General.loadTable( '#staff-list', '/register/enterprise/list-staff/id/' + $( '#id_fefpenterprise' ).val() );
    },
    
    editStaff: function( link )
    {
	id = $( link ).data( 'staff' );
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: BASE_URL + '/register/enterprise/fetch-staff/id/' + id,
		beforeSend: function()
		{
		    App.blockUI( '#staff form' );
		},
		complete: function()
		{
		    App.unblockUI( '#staff form' );
		},
		success: function ( response )
		{
		    $( '#staff form' ).populate( response, {resetForm: true} );
		    $( '#position' ).trigger( 'change' ); 
		    General.scrollTo( '#breadcrumb' );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', '#staff' );
		}
	    }
	);
    },
    
    removeStaff: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'staff' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/register/enterprise/delete-staff/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#staff-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#staff-list' );
		    },
		    success: function ( response )
		    {
			Register.Enterprise.loadStaff();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#staff' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos funcionariu ida ne\'e ?', 'Hamoos funcionariu', remove );
    }
};

$( document ).ready(
    function()
    {
	Register.Enterprise.init();
    }
);