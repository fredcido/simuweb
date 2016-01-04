Admin = window.Admin || {};

Admin.Group = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/admin/group' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
	    
	this.loadGroups();

	General.setTabsAjax( '.tabbable', this.configForm );
    },
    
    loadGroups: function()
    {
	General.loadTable( '#group-list', '/admin/group/list' );
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
        
	General.execFunction( Admin.Group[method], pane );
    },
            
    configGroupNote: function( pane )
    {
        Admin.Group.loadAllUsers();
        Admin.Group.loadUsersGroup();
        Admin.Group.loadAllTypes();
        Admin.Group.loadTypesGroup();
    },
            
    loadAllUsers: function()
    {
        General.loadTable( '#user-list', '/admin/group/list-users/id/' + $( '#id_usergroup').val() );
    },
            
    loadUsersGroup: function()
    {
        General.loadTable( '#user-group-list', '/admin/group/list-user-group/id/' + $( '#id_usergroup').val() );
    },
            
    loadAllTypes: function()
    {
        General.loadTable( '#type-list', '/admin/group/list-types/id/' + $( '#id_usergroup').val() );
    },
            
    loadTypesGroup: function()
    {
        General.loadTable( '#type-group-list', '/admin/group/list-types-group/id/' + $( '#id_usergroup').val() );
    },
    
    getChecked: function ( selector )
    {
	dataTable = $( selector ).dataTable();
	ids = [];
	$( 'input:not(:disabled):checked', dataTable.fnGetNodes() ).each(
	    function()
	    {
		ids.push( $( this ).val() );
	    }
	);
	    
	return ids;
    },
    
    insertUserGroup: function()
    {
	users = this.getChecked( '#user-list' );
	container = $( '#user-list' ).closest( '.portlet-body' );
	
	if ( !users.length ) {
	    
	    Message.msgError( 'Tenke hili uzuariu ba hatama.', container );
	    return false;
	}
	
	$.ajax(
	    {
		type: 'POST',
		data: {
		    users: users,
		    group: $( '#id_usergroup' ).val(),
		    action: 'user'
		},
		dataType: 'json',
		url: General.getUrl( '/admin/group/save-itens/' ),
		beforeSend: function()
		{
		    App.blockUI( '#user-list' );
		    General.scrollTo( '#user-list' );
		},
		complete: function()
		{
		    App.unblockUI( '#user-list' );
		},
		success: function ( response )
		{
		    if ( !response.status ) {

			var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
			Message.msgError( msg, container );

		    } else {

			Message.msgSuccess( 'Operasaun diak', container );
			Admin.Group.loadAllUsers();
			Admin.Group.loadUsersGroup();
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
    },
    
    removeUserGroup: function()
    {
	users = this.getChecked( '#user-group-list' );
	container = $( '#user-group-list' ).closest( '.portlet-body' );
	
	if ( !users.length ) {
	    
	    Message.msgError( 'Tenke hili uzuariu ba hasai.', container );
	    return false;
	}
	
	$.ajax(
	    {
		type: 'POST',
		data: {
		    users: users,
		    group: $( '#id_usergroup' ).val(),
		    action: 'delete-user'
		},
		dataType: 'json',
		url: General.getUrl( '/admin/group/save-itens/' ),
		beforeSend: function()
		{
		    App.blockUI( '#user-group-list' );
		    General.scrollTo( '#user-group-list' );
		},
		complete: function()
		{
		    App.unblockUI( '#user-group-list' );
		},
		success: function ( response )
		{
		    if ( !response.status ) {

			var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
			Message.msgError( msg, container );

		    } else {

			Message.msgSuccess( 'Operasaun diak', container );
			Admin.Group.loadAllUsers();
			Admin.Group.loadUsersGroup();
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
    },
    
    insertTypeNoteGroup: function()
    {
	types = this.getChecked( '#type-list' );
	container = $( '#type-list' ).closest( '.portlet-body' );
	
	if ( !types.length ) {
	    
	    Message.msgError( 'Tenke hili tipu nota ba hatama.', container );
	    return false;
	}
	
	$.ajax(
	    {
		type: 'POST',
		data: {
		    types: types,
		    group: $( '#id_usergroup' ).val(),
		    action: 'type-note'
		},
		dataType: 'json',
		url: General.getUrl( '/admin/group/save-itens/' ),
		beforeSend: function()
		{
		    App.blockUI( '#type-list' );
		    General.scrollTo( '#type-list' );
		},
		complete: function()
		{
		    App.unblockUI( '#type-list' );
		},
		success: function ( response )
		{
		    if ( !response.status ) {

			var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
			Message.msgError( msg, container );

		    } else {

			Message.msgSuccess( 'Operasaun diak', container );
			Admin.Group.loadAllTypes();
			Admin.Group.loadTypesGroup();
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
    },
    
    removeTypeNoteGroup: function()
    {
	types = this.getChecked( '#type-group-list' );
	container = $( '#type-group-list' ).closest( '.portlet-body' );
	
	if ( !types.length ) {
	    
	    Message.msgError( 'Tenke hili tipu nota ba hasai.', container );
	    return false;
	}
	
	$.ajax(
	    {
		type: 'POST',
		data: {
		    types: types,
		    group: $( '#id_usergroup' ).val(),
		    action: 'delete-type-note'
		},
		dataType: 'json',
		url: General.getUrl( '/admin/group/save-itens/' ),
		beforeSend: function()
		{
		    App.blockUI( '#type-group-list' );
		    General.scrollTo( '#type-group-list' );
		},
		complete: function()
		{
		    App.unblockUI( '#type-group-list' );
		},
		success: function ( response )
		{
		    if ( !response.status ) {

			var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
			Message.msgError( msg, container );

		    } else {

			Message.msgSuccess( 'Operasaun diak', container );
			Admin.Group.loadAllTypes();
			Admin.Group.loadTypesGroup();
		    }
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', container );
		}
	    }
	);
    }
};

$( document ).ready(
    function()
    {
	Admin.Group.init();
    }
);