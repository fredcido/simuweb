Admin = window.Admin || {};

Admin.Access = {
    
    init: function()
    {
	$( "#user_access input" ).click(
	    function()
	    {
		Admin.Access.savePermission( $( this ) );
	    }
	);
	    
	$( '#fk_id_sysuser' ).change( 
	    function()
	    {
		Admin.Access.seekPermissions();
		Admin.Access.seekSourceUsers();
	    }
	);
	    
	$( '#user_source' ).change( 
	    function()
	    {
		Admin.Access.copyPermissions();
	    }
	);
	
	$( '#access_collapse' ).click(
	    function () 
	    {
		$( '.tree-toggle', $( '#user_access' ) ).addClass( 'closed' );
		$( '.branch', $( '#user_access' ) ).removeClass( 'in' );
	    }
	);

	$( '#access_expand' ).click(
	    function () 
	    {
		$( '.tree-toggle', $( '#user_access' ) ).removeClass( 'closed' );
		$( '.branch', $( '#user_access' ) ).addClass( 'in' );
	    }
	);
    },
    
    copyPermissions: function()
    {
	var user = $( '#fk_id_sysuser' ).val();
	var userSource = $( '#user_source' ).val();
	
	if ( General.empty( user ) || General.empty( userSource ) )
	    return false;
	
	copy = function()
	{
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/admin/access/copy-permissions/' ),
		    data: {id: user, source: userSource},
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
			Admin.Access.seekPermissions();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', 'form' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak kopia permissaun husi uzuiariu ne\'e ?', 'Kopia Permissaun', copy );
    },
    
    seekSourceUsers: function()
    {
	if ( General.empty( $( '#fk_id_sysuser' ).val() ) ) {
		    
	    $( '#user_source' ).val( '' ).attr( 'disabled', true );
	    return false;
	}

	url = '/admin/access/seek-source-user/id/' + $( '#fk_id_sysuser' ).val();
	General.loadCombo( url, 'user_source' );
	return true;
    },
    
    savePermission: function( check )
    {
	var user = $( '#fk_id_sysuser' ).val();
	if ( General.empty( user ) )
	    return false;

	var operation = eval( '(' + check.val() + ')' );

	operation.user = user;
	operation.insert = check.attr( 'checked' ) ? 1 : 0;

	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    data: operation,
	    url: BASE_URL + '/admin/access/save/',
	    beforeSend: function()
	    {
		//General.loading( true );
	    },
	    complete: function()
	    {
		//General.loading( false );
	    },
	    error: function()
	    {
		check.attr( 'checked', !check.attr( 'checked' ) );
		$.uniform.update( check );
	    }
	});

	return true;
    },
    
    seekPermissions: function()
    {
	var user = $( '#fk_id_sysuser' ).val();
	$( "#user_access input" ).attr( 'checked', false );
	$.uniform.update( "#user_access input" );

	if ( General.empty( user ) )
	    return false;

	$.ajax({
	    type: 'POST',
	    dataType: 'json',
	    url: BASE_URL + '/admin/access/seek-permissions/id/' + user,
	    success: function ( response )
	    {
		for ( i in response ) {

		    var idOper =  response[i].idOper;
		    var idForm = response[i].idForm;

		    var idField = 'oper_' + idForm + '_' + idOper;

		    element = $( "#user_access input#" + idField );
		    element.attr( 'checked', true )
		    $.uniform.update( element );
		}
	    }
	});

	return true;
    }
};

$( document ).ready(
    function()
    {
	Admin.Access.init();
    }
);