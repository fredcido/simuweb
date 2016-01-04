Default = window.Default || {};

Default.Auth = {
    
    init: function()
    {
	this.configExternalForm();
    },
    
    configExternalForm: function()
    {
	var form  = $( 'form#external-form' );
	
	if ( !form.length )
	    return false;
	
	$( '#btn-client' ).click(
	    function()
	    {
		$( '.login-form' ).hide();
		$( '#external-form' ).show();
	    }
	);
	    
	$( '#back-btn' ).click(
	    function()
	    {
		$( '#external-form' ).hide();
		$( '.login-form' ).show();
	    }
	);
	    
	$( '#evidence_card' ).inputmask( 'AAA-AAA-AA-99-9999', {"clearIncomplete": true} );
	$( '#birth_date' ).inputmask( 'd/m/y', {"clearIncomplete": true} );
	    
	$( '#external-form' ).hide();
	
	this.configValidateExternal( form );
    },
    
    configValidateExternal: function( form )
    {
	$( form ).validate({
	    errorElement: 'label',
	    errorClass: 'help-inline',
	    focusInvalid: false,
	    rules: {
		evidence_card: {
		    required: true
		},
		birth_date: {
		    required: true
		}
	    },

	    messages: {
		evidence_card: {
		    required: "Tenki hakerek Kartaun Evidensia."
		},
		birth_date: {
		    required: "Tenki hakerek Data Moris."
		}
	    },

	    invalidHandler: function (event, validator) 
	    {
		Message.msgError( 'Tenki preensi kartaun evidensia no data moris.', $( form ) );
	    },

	    highlight: function (element) { // hightlight error inputs
		$( element ).closest( '.control-group' ).addClass( 'error' ); // set error class to the control group
	    },

	    success: function (label) {
		label.closest('.control-group').removeClass('error');
		label.remove();
	    },

	    errorPlacement: function (error, element) {
		error.addClass('help-small no-left-padding').insertAfter(element.closest('.input-icon'));
	    },

	    submitHandler: function ( form ) 
	    {
		Default.Auth.loginExternal( form );

		return false;
	    }
	});
    },
    
    loginExternal: function( form )
    {
	var pars = $( form ).serialize();

	$.ajax({
	    type: 'POST',
	    data: pars,
	    dataType: 'json',
	    url: form.action,
	    beforeSend: function()
	    { 
		Message.msgInfo( 'Hein ituan...', form );
	    },
	    success: function ( response )
	    {
		if ( response.valid )
		    General.go( response.redirect );
		else
		    Message.msgError( 'Dadus sala.', form );
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak.', form );
	    }
	});

	return false;
    },
    
    initProfile: function()
    {
	var form  = $( 'form#profile' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/auth/profile' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
	
	$( '#confirm_password' ).rules( 'add',
	    {
		equalTo: '#password',
		 messages: {
		    equalTo: "Favor hakerek passwork fila fali"
		}
	    }
	);

	return true;
    },
    
    login: function( form )
    {
	var pars = $( form ).serialize();

	$.ajax({
	    type: 'POST',
	    data: pars,
	    dataType: 'json',
	    url: form.action,
	    beforeSend: function()
	    { 
		Message.msgInfo( 'Hein ituan...', form );
	    },
	    success: function ( response )
	    {
		if ( response.valid )
		    General.go( response.redirect );
		else
		    Message.msgError( 'Usuário no senha la diak.', form );
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak.', form );
	    }
	});

	return false;
    }
};

$( document ).ready(
    function()
    {
	Default.Auth.init();
	Default.Auth.initProfile();
    }
);