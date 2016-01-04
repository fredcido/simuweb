Register = window.Register || {};

Register.IsicClass = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/isic-class' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadIsicClasss();
	
	$( '#fk_id_isicsection' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_isicdivision' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/isic-class/search-division/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_isicdivision' );
	    }
	);
	    
	$( '#fk_id_isicdivision' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_isicgroup' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/isic-class/search-group/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_isicgroup' );
	    }
	)
    },
    
    loadIsicClasss: function()
    {
	General.loadTable( '#class-list', '/register/isic-class/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.IsicClass.init();
    }
);