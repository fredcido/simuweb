Register = window.Register || {};

Register.Nation = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/nation' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadNations();
    },
    
    loadNations: function()
    {
	General.loadTable( '#nation-list', '/register/nation/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.Nation.init();
    }
);