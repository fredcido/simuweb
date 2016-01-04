Register = window.Register || {};

Register.Barrier = {
   
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/barrier' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadBarrier();
    },
    
    loadBarrier: function()
    {
	General.loadTable( '#barrier-list', '/register/barrier/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.Barrier.init();
    }
);