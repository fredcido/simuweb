Register = window.Register || {};

Register.MiniGroup = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/mini-group' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadMiniGroups();
	
	$( '#fk_id_profgroup' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_profsubgroup' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/mini-group/search-sub-group/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_profsubgroup' );
	    }
	)
    },
    
    loadMiniGroups: function()
    {
	General.loadTable( '#mini-group-list', '/register/mini-group/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.MiniGroup.init();
    }
);