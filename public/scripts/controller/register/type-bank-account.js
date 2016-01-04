Register = window.Register || {};

Register.TypeBankAccount = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/type-bank-account' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadTypeBankAccounts();
    },
    
    loadTypeBankAccounts: function()
    {
	General.loadTable( '#type-bank-account-list', '/register/type-bank-account/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.TypeBankAccount.init();
    }
);