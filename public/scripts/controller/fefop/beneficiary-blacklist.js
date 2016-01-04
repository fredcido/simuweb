Fefop = window.Fefop || {};

Fefop.BeneficiaryBlacklist = {
    
    isSearchingStaff: null,
    
    init: function()
    {
	General.setTabsAjax( '.tabbable', this.configForm );
	this.configInformation();
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Fefop.BeneficiaryBlacklist[method], pane );
    },
    
    configInformation: function()
    {
	var form  = $( '.tab-content #data form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status )
			$( '#data #clear' ).trigger( 'click' );
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	Fefop.BeneficiaryBlacklist.configChangeProgram( form );
	Fefop.BeneficiaryBlacklist.configMaxLength( form );
    },
    
    configChangeProgram: function( form )
    {
	form.find( '#id_fefop_programs' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    form.find( '#fk_id_fefop_modules' ).val( '' );//.attr( 'disabled', true );
		    return false;
		}
		
		url = '/fefop/beneficiary-blacklist/search-modules/id/' + $( this ).val();
		General.loadCombo( url, form.find( '#fk_id_fefop_modules') );
	    }
	);
    },
    
    configMaxLength: function( form )
    {
	form.find( 'textarea' ).maxlength(
	    {
		text: 'Ita hakerek karakter <b>%length</b> husi <b>%maxlength</b>.'
	    }
	);
	    
	form.find( 'textarea' ).bind( 'update.maxlength', 
	    function( event, element, lastLength, length, maxLength, left )
	    {   
		length = length === undefined ? lastLength : length;
		var percent = ( length * 100 ) / maxLength;
		form.find( '#progress-content .bar' ).width( percent + '%' );  
	    }
	).data("maxlength").updateLength();
    },
    
    searchClient: function()
    {
	var settings = {
	    title: 'Buka Kliente',
	    url: '/fefop/beneficiary-blacklist/search-client/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.BeneficiaryBlacklist.initFormSearchClient( modal );
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
		url: General.getUrl( '/fefop/beneficiary-blacklist/search-client-forward' ),
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
				Fefop.BeneficiaryBlacklist.setClient( $( this ).data( 'id' ), modal );
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
		url: General.getUrl( '/fefop/beneficiary-blacklist/fetch-client/' ),
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
		    $( '#data .identifier' ).val( '' );
		    $( '#data form' ).populate( response, {resetForm: false} );
		   General.scrollTo( '#breadcrumb' );
		   modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    searchInstitute: function()
    {
	this.isSearchingStaff = false;
	
	var settings = {
	    title: 'Buka Inst. Ensinu',
	    url: '/fefop/beneficiary-blacklist/search-institute/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.BeneficiaryBlacklist.initFormSearchInstitute( modal );
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
		url: General.getUrl( '/fefop/beneficiary-blacklist/search-institute-forward' ),
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
				Fefop.BeneficiaryBlacklist.setInstitute( $( this ).data( 'id' ), modal );
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
	if ( !General.empty( Fefop.BeneficiaryBlacklist.isSearchingStaff ) ) {
	
	    modal.modal( 'hide' );
	    Fefop.BeneficiaryBlacklist.listStaff( id );
	
	} else {
	    
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/fefop/beneficiary-blacklist/fetch-institute/' ),
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
			$( '#data .identifier' ).val( '' );
			$( '#data form' ).populate( response, {resetForm: false} );
		       General.scrollTo( '#breadcrumb' );
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
    
    searchEnterprise: function()
    {
	var settings = {
	    title: 'Buka Empreza',
	    url: '/fefop/beneficiary-blacklist/search-enterprise/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Fefop.BeneficiaryBlacklist.initFormSearchEnterprise( modal );
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
		url: General.getUrl( '/fefop/beneficiary-blacklist/search-enterprise-forward' ),
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
				Fefop.BeneficiaryBlacklist.setEnterprise( $( this ).data( 'id' ), modal );
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
		url: General.getUrl( '/fefop/beneficiary-blacklist/fetch-enterprise/' ),
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
		   $( '#data .identifier' ).val( '' );
		   $( '#data form' ).populate( response, {resetForm: false} );
		   General.scrollTo( '#breadcrumb' );
		   modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    searchStaff: function()
    {
	this.searchInstitute();
	this.isSearchingStaff = true;
    },
    
    listStaff: function( id )
    {
	var settings = {
	    title: 'Lista Staff',
	    url: '/fefop/beneficiary-blacklist/list-staff/id/' + id,
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
			    Fefop.BeneficiaryBlacklist.setStaff( $( this ).data( 'id' ), modal );
			}
		    );
		};

		General.drawTables( modal.find( 'table' ), callbackClick );
	    }
	};

	General.ajaxModal( settings );
    },
    
    setStaff: function( id, modal )
    {
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/beneficiary-blacklist/fetch-staff/' ),
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
		   $( '#data .identifier' ).val( '' );
		   $( '#data form' ).populate( response, {resetForm: false} );
		   General.scrollTo( '#breadcrumb' );
		   modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    },
    
    configList: function( pane )
    {
	var form  = pane.find( 'form' );
	
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
		    $( '#beneficiary-blacklist-list tbody' ).empty();
	     
		    oTable = $( '#beneficiary-blacklist-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#beneficiary-blacklist-list tbody' ).html( response );
		    
		    General.drawTables( '#beneficiary-blacklist-list' );
		    General.scrollTo( '#beneficiary-blacklist-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
	Fefop.BeneficiaryBlacklist.configChangeProgram( pane );
    },
    
    detail: function( id )
    {
	var settings = {
	    title: 'Haree Rejistu',
	    url: '/fefop/beneficiary-blacklist/detail/id/' + id,
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '50%',
			marginLeft: '-25%'
		    }
		);
		    
		Form.init();
	    }
	};

	General.ajaxModal( settings );
    },
    
    removeBlacklist: function( id )
    {
	var settings = {
	    title: 'Remove Rejistu',
	    url: '/fefop/beneficiary-blacklist/disable/id/' + id,
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '50%',
			marginLeft: '-25%'
		    }
		);
		    
		Form.init();
		
		var form  = modal.find( 'form' );
		submit = function()
		{
		    var obj = {
			callback: function( response )
			{
			    if ( response.status ) {
				
				$( '#btn-search-beneficiary' ).trigger( 'click' );
				modal.modal( 'hide' );
			    }
			}
		    };

		    Form.submitAjax( form, obj );
		    return false;
		};

		Form.addValidate( form, submit );
		Fefop.BeneficiaryBlacklist.configMaxLength( form );
	    }
	};

	General.ajaxModal( settings );
    }
};

$( document ).ready(
    function()
    {
	Fefop.BeneficiaryBlacklist.init();
    }
);