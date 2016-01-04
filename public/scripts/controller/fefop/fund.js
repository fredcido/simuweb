Fefop = window.Fefop || {};

Fefop.Fund = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/fefop/fund' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadFunds();
	General.setTabsAjax( '.tabbable', this.configPlanning );
    },
    
    configPlanning: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '.tabbable .nav-tabs .ajax-tab' ).last().removeClass( 'loaded' ).trigger( 'click' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	Fefop.Fund.configFetchPlanning( form );
	Fefop.Fund.configCalcTotal( form );
    },
    
    configCalcTotal: function( form )
    {
	form.find( '.cost-module' ).live( 
	    'change', 
	    function()
	    {
		var total = 0;
		form.find( '.cost-module' ).each(
		    function()
		    {
			var amount = $( this ).maskMoney( 'unmasked' )[0];
			total += amount;
		    }
		);
	
		form.find( '#amount' ).eq( 0 ).maskMoney( 'mask', total );
	    }
	);
    },
    
    configFetchPlanning: function( form )
    {
	form.find( '#fk_id_fefopfund, #year_planning' ).change(
	    function()
	    {
		form.find( '.cost-module' ).val( 0 ).trigger( 'change' );
		
		if ( 
		     General.empty( form.find( '#fk_id_fefopfund' ).eq(0).val() )
		     || 
		     General.empty( form.find( '#year_planning' ).eq(0).val() )
		    )
		    return false;
		
		$.ajax(
		    {
			type: 'POST',
			dataType: 'json',
			url: General.getUrl( '/fefop/fund/fetch-planning/' ),
			data: {
			    fund: form.find( '#fk_id_fefopfund' ).eq(0).val(),
			    year: form.find( '#year_planning' ).eq(0).val()
			},
			beforeSend: function()
			{
			    General.loading( true );
			},
			complete: function()
			{
			    General.loading( false );
			},
			success: function ( response )
			{
			   for ( x in response ) {
			       
			       var obj = response[x];
			       form.find( '#additional_cost' ).maskMoney( 'mask', parseFloat( obj.additional_cost ) );
			       form.find( '#modules_cost_' + obj.num_module ).maskMoney( 'mask', parseFloat( obj.amount ) ).trigger( 'change' );
			   }
			}
		    }
		);
	    }
	);
    },
    
    loadFunds: function()
    {
	General.loadTable( '#fund-list', '/fefop/fund/list' );
    }
};

$( document ).ready(
    function()
    {
	Fefop.Fund.init();
    }
);