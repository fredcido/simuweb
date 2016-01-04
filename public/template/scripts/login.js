var Login = function () {
    
    return {
        //main function to initiate the module
        init: function () {
        	
           $( '#username' ).focus();
           
           $( '.login-form' ).validate({
	            errorElement: 'label', //default input error message container
	            errorClass: 'help-inline', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
	            rules: {
	                username: {
	                    required: true
	                },
	                password: {
	                    required: true
	                }
	            },

	            messages: {
	                username: {
	                    required: "Tenki hakerek login."
	                },
	                password: {
	                    required: "Tenki hakerek password."
	                }
	            },

	            invalidHandler: function (event, validator) 
		    {
			Message.msgError( 'Tenki preensi login no password.', $( '.login-form' ) );
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
	                Default.Auth.login( form );
			
			return false;
	            }
	        });
        }
    };
}();