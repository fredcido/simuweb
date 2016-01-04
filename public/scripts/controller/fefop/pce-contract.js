Fefop = window.Fefop || {};

Fefop.PceContract = {
    
    init: function()
    {
	this.initForm();
	this.initPrint();
	this.initFormSearch();
    },
    
    initForm: function()
    {
	if ( !$( '.tabbable' ).length )
	    return false;
	
	General.setTabsAjax( '.tabbable', this.configForm );
	Fefop.Contract.setIdContract( $( '#id_contract' ).val() );
    },
    
    initPrint: function()
    {
	if ( !$( '.form-actions.no-print' ).length )
	    return false;
	
	$( '#btn-print-contract' ).closest( '.span1' ).remove();
	$( '.portlet-body.hide').removeClass( 'hide' );
	$( '.portlet.green, .portlet.blue, .portlet.red').removeClass( 'green blue red' ).addClass( 'light-grey');
    },
    
    configFollowup: function( pane )
    {
	Fefop.Contract.setfFollowupContainer( pane ).initFollowUp();
    },
    
    configDocument: function( pane )
    {
	Fefop.Contract.setfDocumentContainer( pane ).initDocument();
    },
    
    configTechnicalFeedback: function( pane )
    {
	var form  = $( 'form', pane );
	
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( $( '#approved', form ).is( ':checked' ) )
			    $( '.nav-tabs .ajax-tab[href=#council-decision]' ).trigger( 'click' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );
    },
    
    configCouncilDecision: function( pane )
    {
	var form  = $( 'form', pane );
	
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			if ( $( '#approved', form ).is( ':checked' ) ) {
			    
			    setTimeout(
				function()
				{
				    history.go( 0 );
				},
				3 * 1000
			    );
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );
    },
    
    configRevision: function( pane )
    {
	var form  = $( 'form', pane );
	var idBusinessPlan = $( '#fk_id_businessplan', form ).val();
	
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( $( '#return_revision:checked', form ).length )
			    history.go( 0 );
			else {
			    
			    form.get(0).reset();
			    General.loadTable( $( '#revision-list', pane ), '/fefop/pce-contract/list-revision/id/' + idBusinessPlan );
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	General.loadTable( $( '#revision-list', pane ), '/fefop/pce-contract/list-revision/id/' + idBusinessPlan );
	Form.addValidate( form, submit );
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Fefop.PceContract[method], pane );
    },
    
    initFormSearch: function()
    {
	var form  = $( 'form#search' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    var pars = $( form ).serialize();
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: pars,
		dataType: 'text',
		url: form.attr( 'action' ),
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
		    $( '#pce-contract-list tbody' ).empty();
	     
		    oTable = $( '#pce-contract-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#pce-contract-list tbody' ).html( response );
		    
		    General.drawTables( '#pce-contract-list' );
		    General.scrollTo( '#pce-contract-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
	
	Form.addValidate( form, submit );
    },
    
    searchIsicClass: function( input )
    {
	if ( $( input ).is('[readonly]') ) 
	    return false;

	if ( General.empty( $( input ).val() ) ) {

	    $( '#fk_id_isicclasstimor' ).val( '' ).attr( 'disabled', true );
	    return false;
	}

	url = '/fefop/pce-contract/search-isic-class/id/' + $( input ).val();
	General.loadCombo( url, 'fk_id_isicclasstimor' );
    },
    
    printContract: function()
    {
	var id = $( '#business-plan' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/pce-contract/print/id/' + id ) );
    },
    
    exportContract: function()
    {
	var id = $( '#business-plan' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/pce-contract/export/id/' + id ) );
    }
};

$( document ).ready(
    function()
    {
	Fefop.PceContract.init();
    }
);