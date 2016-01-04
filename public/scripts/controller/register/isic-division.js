Register = window.Register || {};

Register.IsicDivision = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/isic-division' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadIsicDivisions();
    },
    
    loadIsicDivisions: function()
    {
	General.loadTable( '#division-list', '/register/isic-division/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.IsicDivision.init();
    }
);