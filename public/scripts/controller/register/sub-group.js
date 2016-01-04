Register = window.Register || {};

Register.SubGroup = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/sub-group' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadSubGroups();
    },
    
    loadSubGroups: function()
    {
	General.loadTable( '#sub-group-list', '/register/sub-group/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.SubGroup.init();
    }
);