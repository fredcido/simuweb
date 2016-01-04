Register = window.Register || {};

Register.Suku = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/suku' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadSukus();
	
	$( '#fk_id_addcountry' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_adddistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/suku/search-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_adddistrict' );
	    }
	);
	    
	$( '#fk_id_adddistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsubdistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/suku/search-sub-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsubdistrict' );
	    }
	)
    },
    
    loadSukus: function()
    {
	General.loadTable( '#suku-list', '/register/suku/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.Suku.init();
    }
);