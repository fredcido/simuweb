Register = window.Register || {};

Register.BarrierIntervention = {
   
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/barrier-intervention' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadBarrierIntervention();
	
	$( '#fk_id_barrier_type' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_barrier_name' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/barrier-intervention/search-barrier/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_barrier_name' );
	    }
	);
    },
    
    loadBarrierIntervention: function()
    {
	General.loadTable( '#barrier-intervention-list', '/register/barrier-intervention/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.BarrierIntervention.init();
    }
);