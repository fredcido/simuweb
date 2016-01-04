Register = window.Register || {};

Register.BudgetCategory = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/budget-category' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadBudgetCategory();
    },
    
    loadBudgetCategory: function()
    {
	General.loadTable( '#budget-category-list', '/register/budget-category/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.BudgetCategory.init();
    }
);