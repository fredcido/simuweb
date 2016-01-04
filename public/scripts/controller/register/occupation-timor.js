Register = window.Register || {};

Register.OccupationTimor = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/occupation-timor' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.configSearchOccupation();
	this.loadOccupationTimors();
    },
    
    configSearchOccupation: function()
    {
	$( '#fk_id_profocupation' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#acronym' ).val( '' );
		    return false;
		}
		
		url = '/register/occupation-timor/search-occupation/id/' + $( this ).val();
		
		$.ajax({
		    type: 'GET',
		    url: BASE_URL + url,
		    dataType: 'json',
		    beforeSend: function () 
		    {
			App.blockUI( $( 'form' ) );
		    },
		    complete: function()
		    {
			App.unblockUI( $( 'form' ) );
		    },
		    success: function ( response ) 
		    {
			$( '#acronym' ).val( response.acronym );
		    },
		    error: function () 
		    {
			$( '#acronym' ).val( '' );
		    }
		});
	    }
	)
    },
    
    loadOccupationTimors: function()
    {
	General.loadTable( '#occupation-timor-list', '/register/occupation-timor/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.OccupationTimor.init();
    }
);