Register = window.Register || {};

Register.Group = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/group' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadGroups();
    },
    
    loadGroups: function()
    {
	General.loadTable( '#group-list', '/register/group/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.Group.init();
    }
);