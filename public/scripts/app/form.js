Form = {
    init: function()
    {
	$.fn.clearForm = function( disabled )
	{
	    container = null;
	    clearDisabled = disabled == undefined ? true : disabled;

	    if ( $( this ).hasClass( 'btn' ) )
		container = $( this ).closest( 'form' );
	    else
		container = $( this );
	    
	    Message.clearMessages( container );

	    container.find( ':input' ).each( 
		function( index, e ) 
		{
		    if ( !clearDisabled && $( this ).attr( 'disabled' ) )
			return true;

		    if ( $( e ).hasClass( 'no-clear' ) )
			return true;

		    switch( e.type ) {
			case 'hidden':
			case 'password':
			case 'select-multiple':
			case 'text':
			case 'textarea':
			case 'select-one':

			    $( e ).val('');

			    if ( $( e ).hasClass( 'chzn-done' ) )
				$( e ).trigger( 'liszt:updated' );
			case 'checkbox':
			case 'radio':
			    e.checked = false;
			    $( e ).trigger( 'change' );
		    }
		}
	    );

	    $.uniform.update( container.find( ':input' ) );
	    
	    if ( container.validate() )
		container.validate().resetForm();
	    
	    container.find( '.control-group.error' ).removeClass( 'error' );
	    container.find( '.chosen' ).val( '' ).trigger( 'change' );
	    
	    container.trigger( 'clear' );

	    return this;
	}
	
	this.initCustomSelect();
	this.initReadOnlySelect();
	this.initMasks();
	this.initReset();
	this.initToggleButton();
    },
    
    initToggleButton: function()
    {
	$( '.toggle-check:not(.rendered)' ).each(
	    function()
	    {
		container = $( '<div />' );
		container.addClass( 'toogle-container' );
		
		$( this ).parent().append( container.append( $( this ) ) );
		
		container.toggleButtons({
		    label: {
			enabled: "SIN",
			disabled: "LAE"
		    },
		    style: {
			enabled: "info",
			disabled: "danger"
		    }
		});
		
		$( this ).addClass( 'rendered' );
	    }
	);
    },
    
    initCustomSelect: function()
    {
	$( 'select.chosen' ).select2(
	    {
		allowClear: true,
		placeholder: 'Hili ida'
	    }
	);
    },
    
    initMasks: function()
    {
	// Date mask
	$( '.date-mask' ).inputmask( 'd/m/y', {"clearIncomplete": true} );

	$( ".date-mask" ).datepicker({format: "dd/mm/yyyy"}).on( 'changeDate', 
            function(ev) 
            { 
		$( this ).focus();
                $( this ).datepicker( 'hide' ); 
                $( this ).trigger( 'change' ); 
            } 
        );
	
	$( '.time-picker' ).timepicker( {showMeridian: false, defaultTime: 'value'} );

	// Montth YEar
	$( '.month-year-mask' ).inputmask( 'm/y', {"clearIncomplete": true} );

	// Year mask
	$( '.year-mask' ).inputmask( 'y', {"clearIncomplete": true} );

	// Time mask
	$( '.time-mask' ).inputmask( 'h:s', {"clearIncomplete": true} );

	// Tel mask
	$( '.mobile-phone' ).inputmask( '(670)9999-9999', {"clearIncomplete": true} );
	$( '.house-phone' ).inputmask( '(670)9999-9999', {"clearIncomplete": true} );
	$( '.phone-mask' ).inputmask( '(999)9999-9999', {"clearIncomplete": true} );

	// only numbers mask
	$( '.text-numeric' ).inputmask( {'mask': '9', 'repeat': 5, 'greedy': false});

	// only number with 4 digits
	$( '.text-numeric4' ).inputmask( {'mask': '9', 'repeat': 4, 'greedy': false});

	// Money mask
	$( '.money-mask' ).maskMoney( {showSymbol: true, allowZero: true} );
	
	$( '.evidence_card' ).inputmask( 'AAA-AAA-AA-99-9999' );
    },
    
    initReset: function()
    {
	$( '#clear.btn' ).live( 'click',
	    function()
	    {
		$( this ).closest( 'form' ).clearForm();
	    }
	);
    },
    
    initReadOnlySelect: function()
    {
	$( 'select' ).off( 'change.selectreadonly' );
	
	$( 'select[readonly]' ).each(
	    function()
	    {
		$( this ).data( 'prev', $( this ).val() );
		$( this ).on
		(
		    'change.selectreadonly',
		    function( data )
		    {
			data.stopPropagation();
			data.preventDefault();
			
			var obj = $( this );

			obj.val( obj.data( 'prev' ) );
			obj.data( 'prev', obj.val() );
			
			return false;
		    }
		);
	    }
	);
    },
    
    submitAjax: function ( form, obj )
    {
	if ( General.empty( obj.data ) )
	    pars = $(form).serialize();
	else
	    pars = obj.data.concat( $( form ).serializeArray() );

	$.ajax({
	    type: 'POST',
	    data: pars,
	    dataType: 'json',
	    url: $(form).attr('action'),
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
		if ( obj && obj.callback )
		    General.execFunction( obj.callback, response );
		
		Form.showErrorsForm( form, response.errors );

		if ( response.fields && response.fields.length ) {

		    for ( id in response.fields ) {

			var ele = $( '#' + response.fields[id] );
			ele.closest( '.control-group' ).addClass( 'error' );
		    }
		}

		if ( !response.status ) {

		    if ( !response.description.length )
			Message.msgError( 'Operasaun la diak', form );
			
		} else if ( General.empty( obj.nomsg ) ) {
		    Message.msgSuccess( 'Operasaun diak', form );
		}
		
		if ( response.description.length )
		    Message.showBulkMessages( response.description, form );
		
		if ( response.status )
		    $( form ).data( 'initial-data', $( form ).serialize() );
	    },
	    error: function ()
	    {
		App.unblockUI( form );
		Message.msgError( 'Operasaun la diak', form );
	    }
	});

	return false;
    },

    showErrorsForm: function( form, errors )
    {
	$( form ).find( '.control-group.error' ).removeClass( 'error' );
	$( form ).find( '.control-group .help-inline' ).remove();

	for ( id in errors ) {

	    var ele = form.find( '#' + id );
	    ele.closest( '.control-group' ).addClass( 'error' );
	}
    },
    
    cloneForm: function( form )
    {
	var newForm = $( form ).clone();
	newForm.addClass( 'form-report-clone' ).hide();
	$( 'body' ).append( newForm );

	$( form ).find( 'select' ).each( 
	    function( index, node )
	    {
		newForm.find( 'select' ).eq( index ).val( $(node).val() );
	    }
	);

	newForm.attr( 'method', 'post' );

	return newForm;
    },
    
    getRequiredForm: function( form )
    {
	rules = {};
	$( form ).find( 'label.required' ).each(
	    function( index, element )
	    {
		rules[$(element).attr('for')] = 'required';
	    }
	);
	    
	$( form ).find( '.chosen' ).each(
	    function()
	    {
		var label = $( this ).closest( '.control-group' ).find( 'label.required' );
		if ( label.length )
		    rules[$(this).attr('id')] = 'required';
	    }
	);

	$( form ).find( '.control-group .controls .required' ).each(
	    function( index, element )
	    {
		rules[$(element).attr('id')] = 'required';
	    }
	);
	    
	return rules;
    },
    
    configureObserver: function( form )
    {
	setTimeout(
	    function()
	    {
		var id = $( form ).attr( 'id' );
	
		if ( (/(search|report)/g).exec( id ) )
		    return false;

		$( form ).data( 'initial-data', $( form ).serialize() )
			 .addClass( 'listener-data' )
			 .on(
			    'submit',
			    function()
			    {
				$( form ).data( 'initial-data', $( form ).serialize() )
			    }
			 );
	    },
	    500
	);
    },
    
    listenFormChange: function()
    {
	window.onbeforeunload = function( e ) 
	{
	   var valid = true;
	   $( 'form.listener-data' ).each(
		function()
		{
		    var current = $( this ).serialize();
		    var initialData = $( this ).data( 'initial-data' );
		    
		    if ( current !== initialData )
			valid = false;
		}
	   );
	    
	    if ( !valid ) {
		
		e.stopPropagation();
		e.preventDefault();
		
		return 'Ita iha dadus katak ita troka maibe seidauk Halot!';
	    }
	};
    },
    
    addValidate: function( form, submit )
    {
	Form.configureObserver( form );
	
	$.validator.methods["date"] = function (value, element) {return true;} 
	
	rules = Form.getRequiredForm( form );
	    
	$.validator.messages.required = "Obrigatoriu, tenki preensi.";
	$.validator.messages.email = "Favor hatama email loos.";
	
	$( form ).validate(
	    {
		errorElement: 'span',
		errorClass: 'help-block',
		focusInvalid: false,
		ignore: "",
		rules: rules,

		errorPlacement: function ( error, element ) 
		{
		    error.insertAfter( element );
		},

		invalidHandler: function( event, validator ) 
		{
		    Message.clearMessages( form );
		    Message.msgError( "Haree campos obrigatoriu ba formulariu ne'e.", form );
		},

		highlight: function( element ) 
		{
		    $( element ).closest( '.control-group' ).removeClass( 'success' ).addClass( 'error' );
		},

		unhighlight: function( element ) 
		{
		    $( element ).closest( '.control-group' ).removeClass( 'error' );
		},

		submitHandler: submit
	    }
	);
    },
    
    makeRequired: function( selector, required )
    { 
	if ( required ) {
	    
	    $( selector ).each(
		function()
		{
		    $( this ).rules( 'add', 'required' );
		    $( this ).closest( '.control-group' ).find( 'label' ).addClass( 'required' );
		}
	    );
	    
	} else {
	    
	    $( selector ).each(
		function()
		{
		    $( this ).rules( 'remove', 'required' );
		    $( this ).closest( '.control-group' ).find( 'label' ).removeClass( 'required' );
		}
	    );	    
	}
	
	$( selector ).each(
	    function()
	    {
		$( this ).closest( '.control-group.error' ).removeClass( 'error' ).find( '.help-block' ).remove();
	    }
	);
    },
    
    handleClientSearch: function( form )
    {
	// Get the required fields
	rules = Form.getRequiredForm( form );
	
	// The fields to search client
	inputsKeys = form.find( '#num_district' ).closest( '.controls' ).find( ':input' );
	
	inputsKeys.on( 'change keyup blur',
	    function()
	    {
		var required = !!inputsKeys.filter( function(){ return General.empty( $( this ).val() ); } ).length;
		
		for ( i in rules )
		    Form.makeRequired( $( form ).find( '#' + i ), required );
	    }
	);

	evidenceCard = $( '#evidence', form );
	
	evidenceCard.on( 'change keyup blur paste',
	    function()
	    {
		var required = General.empty( $( this ).val() );
		for ( i in rules )
		    Form.makeRequired( $( form ).find( '#' + i ), required );
	    }
	);
    }
}

$( document ).ready(
    function()
    {
	Form.init();
	Form.listenFormChange();
    }
);