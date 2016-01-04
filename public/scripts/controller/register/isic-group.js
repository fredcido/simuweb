Register = window.Register || {};

Register.IsicGroup = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/isic-group' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadIsicGroups();
	
	$( '#fk_id_isicsection' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_isicdivision' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/isic-group/search-division/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_isicdivision' );
	    }
	)
    },
    
    loadIsicGroups: function()
    {
	General.loadTable( '#isic-group-list', '/register/isic-group/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.IsicGroup.init();
    }
);