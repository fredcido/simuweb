Admin = window.Admin || {};

Admin.Department = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/admin/department' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );
	this.loadDepartments();
    },
    
    loadDepartments: function()
    {
	General.loadTable( '#department-list', '/admin/department/list' );
    }
};

$( document ).ready(
    function()
    {
	Admin.Department.init();
    }
);