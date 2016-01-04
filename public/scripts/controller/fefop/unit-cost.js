Fefop = window.Fefop || {};

Fefop.UnitCost = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/fefop/unit-cost' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );

	this.loadUnitCosts();
	this.configChangeCategoryScholarity();
    },
    
    loadUnitCosts: function()
    {
	General.loadTable( '#unit-cost-list', '/fefop/unit-cost/list' );
    },
    
    configChangeCategoryScholarity: function()
    {
	$( '#category' ).change(
	    function()
	    {
		var category =  $( this ).val();
		if ( General.empty( category ) ) {
		    
		    $( '#fk_id_perscholarity' ).val( '' );
		    return false;
		}
	
		url = '/fefop/unit-cost/search-course/category/' + category;
		General.loadCombo( url, 'fk_id_perscholarity' );
	    }
	);
    }
};

$( document ).ready(
    function()
    {
	Fefop.UnitCost.init();
    }
);