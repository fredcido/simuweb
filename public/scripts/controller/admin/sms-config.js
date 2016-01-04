Admin = window.Admin || {};

Admin.SmsConfig = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/admin/sms-config' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	Form.addValidate( form, submit );
    }
};

$( document ).ready(
    function()
    {
	Admin.SmsConfig.init();
    }
);