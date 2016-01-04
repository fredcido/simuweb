Admin = window.Admin || {};

Admin.UserBusiness = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/admin/user-business' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
	
	this.loadUsersBusiness();
	this.configChangeCEOP();
    },
    
    loadUsersBusiness: function()
    {
	General.loadTable( '#user-business-list', '/admin/user-business/list' );
    },
    
    configChangeCEOP: function()
    {
	$( '#fk_id_dec' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_sysuser' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/admin/user-business/load-users/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_sysuser', Admin.UserBusiness.searchUserConfig );
	    }
	);
    },
    
    searchUserConfig: function()
    {
	$.ajax({
	    type: 'GET',
	    dataType: 'json',
	    url: General.getUrl( '/admin/user-business/search-user-ceop/id/' + $( '#fk_id_dec' ).val() ),
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
		if ( !empty( response ) )
		    $( '#fk_id_sysuser' ).val( response.id ).trigger( 'change' );
	    }
	});
    }
};

$( document ).ready(
    function()
    {
	Admin.UserBusiness.init();
    }
);