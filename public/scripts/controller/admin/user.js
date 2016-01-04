Admin = window.Admin || {};

Admin.User = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/admin/user' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
	
	$( '#confirm_password' ).rules( 'add',
	    {
		equalTo: '#password',
		messages: {
		    equalTo: "Favor hakerek passwork fila fali"
		}
	    }
	);
	    
	this.loadUsers();
    },
    
    loadUsers: function()
    {
	General.loadTable( '#user-list', '/admin/user/list' );
    }
};

$( document ).ready(
    function()
    {
	Admin.User.init();
    }
);