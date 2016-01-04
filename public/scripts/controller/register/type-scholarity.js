Register = window.Register || {};

Register.TypeScholarity = {
   
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/type-scholarity' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadTypeScholarity();
    },
    
    loadTypeScholarity: function()
    {
	General.loadTable( '#type-scholarity-list', '/register/type-scholarity/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.TypeScholarity.init();
    }
);