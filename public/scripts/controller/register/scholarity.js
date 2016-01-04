Register = window.Register || {};

Register.Scholarity = {
    
    TYPE_FORMAL: 1,
     
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/scholarity' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	if ( $( '#competency-list' ).length > 0 )
	    General.drawTables( $( '#competency-list' ) );

	this.loadScholarity();
	this.configChangeTypeEducation();
	this.configChangeCategory();
    },
    
    configChangeTypeEducation: function()
    {
	$( '#fk_id_pertypescholarity' ).change(
	    function()
	    {
		var type = $( this ).val();
		
		if ( General.empty( type ) ) {
		    
		    $( '#category' ).attr( 'disabled', true );
		    return false;
		}
		
		// Search the categories
		url = '/register/scholarity/search-category/id/' + type;
		General.loadCombo( url, 'category' );
		
		// Search the levels
		url = '/register/scholarity/search-level/id/' + type;
		General.loadCombo( url, 'fk_id_perlevelscholarity' );
		
		if ( type == Register.Scholarity.TYPE_FORMAL ) {
		    
		    $( '#fk_id_perlevelscholarity' ).closest( '.row-fluid' ).removeClass( 'hide' );
		    Form.makeRequired( '#fk_id_perlevelscholarity', true );
		    
		    // When Formal Schoarity, set the area default to EDUCASAUN FORMAL
		    $( '#fk_id_scholarity_area' ).val( 15 ).trigger( 'change' );
		    
		} else {
		    
		    $( '#fk_id_perlevelscholarity' ).closest( '.row-fluid' ).addClass( 'hide' );
		    Form.makeRequired( '#fk_id_perlevelscholarity', false );
		    
		    $( '#fk_id_scholarity_area' ).val( '' ).trigger( 'change' );
		}
	    }
	);
    },
    
    configChangeCategory: function()
    {
	$( '#category' ).change(
	    function()
	    {
		var type = $( '#fk_id_pertypescholarity' ).val();
		var category = $( this ).val();
		
		if ( type == Register.Scholarity.TYPE_FORMAL )
		    return false;
		
		if ( category == 'N' ) {
		    
		    $( '#fk_id_perlevelscholarity' ).closest( '.row-fluid' ).removeClass( 'hide' );
		    
		    Form.makeRequired( '#fk_id_perlevelscholarity', true );    
		    Form.makeRequired( '#external_code', true );
		    
		    // Search the levels
		    url = '/register/scholarity/search-level/id/' + type;
		    General.loadCombo( url, 'fk_id_perlevelscholarity' );
		    
		} else {
		 
		    $( '#fk_id_perlevelscholarity' ).val( '' ).closest( '.row-fluid' ).addClass( 'hide' );
		    
		    Form.makeRequired( '#fk_id_perlevelscholarity', false );  
		    Form.makeRequired( '#external_code', false );
		}
	    }
	);
    },
    
    loadScholarity: function()
    {
	General.loadTable( '#scholarity-list', '/register/scholarity/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.Scholarity.init();
    }
);