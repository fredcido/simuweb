Register = window.Register || {};

Register.InternationalOccupation = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/international-occupation' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadInternationalOccupations();
	
	$( '#fk_id_profgroup' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_profsubgroup' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/international-occupation/search-sub-group/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_profsubgroup' );
	    }
	);
	    
	$( '#fk_id_profsubgroup' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_profminigroup' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/international-occupation/search-mini-group/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_profminigroup' );
	    }
	)
    },
    
    loadInternationalOccupations: function()
    {
	General.loadTable( '#occupation-list', '/register/international-occupation/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.InternationalOccupation.init();
    }
);