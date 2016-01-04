Admin = window.Admin || {};

Admin.Form = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status )
			General.go( '/admin/form' );
		    
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
	    
	this.loadForms();
    },
    
    loadForms: function()
    {
	General.loadTable( '#form-list', '/admin/form/list' );
    }
};

$( document ).ready(
    function()
    {
	Admin.Form.init();
    }
);