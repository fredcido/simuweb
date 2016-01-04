Admin = window.Admin || {};

Admin.Module = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/admin/module' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadModules();
    },
    
    loadModules: function()
    {
	General.loadTable( '#module-list', '/admin/module/list' );
    }
};

$( document ).ready(
    function()
    {
	Admin.Module.init();
    }
);