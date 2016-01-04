Register = window.Register || {};

Register.BarrierType = {
   
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/barrier-type' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadBarrierType();
    },
    
    loadBarrierType: function()
    {
	General.loadTable( '#barrier-type-list', '/register/barrier-type/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.BarrierType.init();
    }
);