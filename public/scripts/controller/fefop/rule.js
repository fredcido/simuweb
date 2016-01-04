Fefop = window.Fefop || {};

Fefop.Rule = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	this.configChangeIdentifier();
	this.configChangeRule();
	this.configReorder();
    },
    
    configReorder: function()
    {
	$( "#container-rules" ).sortable({
	    connectWith: ".rule",
	    items: ".rule",
	    opacity: 0.8,
	    coneHelperSize: true,
	    placeholder: 'sortable-box-placeholder round-all',
	    forcePlaceholderSize: true,
	    tolerance: "pointer"
	});

	$(".column").disableSelection();
    },
    
    configChangeIdentifier: function()
    {
	$( '#identifier' ).on(
	    'change',
	    function()
	    {
		$( '#container-rules' ).empty();
		
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#btn-add-rule' ).attr( 'disabled', true );
		    $( '#btn-print-rule' ).attr( 'disabled', true );
		    return false;
		}
		
		$( '#btn-add-rule' ).removeAttr( 'disabled' );
		$( '#btn-print-rule' ).removeAttr( 'disabled' );
		Fefop.Rule.loadRules( $( this ).val() );
	    }
	);
    },
    
    configChangeRule: function()
    {
	$( '#container-rules .rule-name' ).live(
	    'change',
	    function()
	    {
		var itemRule = $( this ).closest( '.rule' ).find( '.item-rule' );
		itemRule.addClass( 'hide' ).find( ':input' ).val( '' );
		
		Form.makeRequired( itemRule.find( ':input' ), false );
		
		var rule = $( this ).val();
		if ( General.empty( rule ) ) {
		    
		    $( this ).closest( '.rule' ).find( '.rule-label' ).html( 'Regra' );
		    return false;
		}
		
		var ruleLabel = $( this ).find( 'option:selected' ).html();
		$( this ).closest( '.rule' ).find( '.rule-label' ).html( ruleLabel );
		
		Form.makeRequired( itemRule.filter( '#' + rule ).find( ':input' ), true );
		
		itemRule.filter( '#' + rule ).removeClass( 'hide' ).find( ':input:first' ).focus();
	    }
	);
    },
    
    loadRules: function( identifier )
    {
	General.loading( true );
	$( '#container-rules' ).load(
	    General.getUrl( '/fefop/rule/load-rules/id/' + identifier ),
	    function()
	    {
		General.scrollTo( '#container-rules .rule:first', 800 );

		$( "#container-rules" ).sortable( "refresh" );

		Form.makeRequired( $( '#container-rules .rule select.rule-name' ), true );
		Form.makeRequired( $( '#container-rules .rule :input.is-required' ), true );
		Form.init();
		 
		General.loading( false );
	    }
	);
    },
    
    addRule: function()
    {
	if ( General.empty( $( '#identifier' ).val() ) )
	    return false;
       
	$.ajax({
	     type: 'GET',
	     dataType: 'text',
	     url: General.getUrl( '/fefop/rule/add-rule/' ),
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
		 $( '#container-rules' ).append( response );
		 General.scrollTo( '#container-rules .rule:last', 800 );
		 
		 $( "#container-rules" ).sortable( "refresh" );
		 
		 Form.makeRequired( $( '#container-rules .rule:last select.rule-name' ), true );

		 Form.init();
	     },
	     error: function ()
	     {
		 Message.msgError( 'Operasaun la diak', $( 'form' ) );
	     }
	 });
    },
    
    removeRule: function( link, event )
    {
	event.stopPropagation();
	event.preventDefault();
	
	var remove = function()
	{
	    var container = $( link ).closest( '.rule' );
	    container.remove();
	};
	
	General.confirm( 'Ita hakarak hamoos item ida ne\'e ?', 'Hamoos item', remove );
    },
    
    printRule: function()
    {
	id = $( '#identifier' ).val();
	if ( General.empty( id ) )
	    return false;
	
	General.newWindow( General.getUrl( '/fefop/rule/print/id/' + id ), 'Imprime Regra' );
    }
};

$( document ).ready(
    function()
    {
	Fefop.Rule.init();
    }
);