Register = window.Register || {};

Register.District = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/district' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadDistricts();
    },
    
    loadDistricts: function()
    {
	General.loadTable( '#district-list', '/register/district/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.District.init();
    }
);