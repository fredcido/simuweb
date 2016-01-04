Register = window.Register || {};

Register.AreaScholarity = {
   
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/area-scholarity' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadAreaScholarity();
    },
    
    loadAreaScholarity: function()
    {
	General.loadTable( '#area-scholarity-list', '/register/area-scholarity/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.AreaScholarity.init();
    }
);