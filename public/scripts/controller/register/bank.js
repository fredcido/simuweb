Register = window.Register || {};

Register.Bank = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/bank' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadBanks();
    },
    
    loadBanks: function()
    {
	General.loadTable( '#bank-list', '/register/bank/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.Bank.init();
    }
);