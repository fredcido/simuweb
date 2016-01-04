Register = window.Register || {};

Register.Ceop = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/ceop' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
	
	$( '#email' ).rules( 'add', 'email' );

	this.loadCeops();
    },
    
    loadCeops: function()
    {
	General.loadTable( '#ceop-list', '/register/ceop/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.Ceop.init();
    }
);