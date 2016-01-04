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
			General.go( '/register/isic-subsector' );
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
	General.loadTable( '#subsector-list', '/register/isic-subsector/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.IsicDivision.init();
    }
);