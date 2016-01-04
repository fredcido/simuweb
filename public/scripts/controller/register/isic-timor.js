Register = window.Register || {};

Register.IsicTimor = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/isic-timor' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
	
	$( '#fk_id_isicdivision' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_isicgroup' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/isic-timor/search-group/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_isicgroup' );
	    }
	);
	    
	    $( '#fk_id_isicgroup' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_isicclass' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/isic-timor/search-class/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_isicclass' );
	    }
	);

	this.configSearchClass();
	this.loadIsicTimor();
    },
    
    configSearchClass: function()
    {
	$( '#fk_id_isicclass' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#acronym' ).val( '' );
		    return false;
		}
		
		url = '/register/isic-timor/fetch-class/id/' + $( this ).val();
		
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
    
    loadIsicTimor: function()
    {
	General.loadTable( '#isic-timor-list', '/register/isic-timor/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.IsicTimor.init();
    }
);