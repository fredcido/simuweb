Register = window.Register || {};

Register.SubDistrict = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/sub-district' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadSubDistricts();
	
	$( '#fk_id_addcountry' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_adddistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/sub-district/search-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_adddistrict' );
	    }
	)
    },
    
    loadSubDistricts: function()
    {
	General.loadTable( '#sub-district-list', '/register/sub-district/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.SubDistrict.init();
    }
);