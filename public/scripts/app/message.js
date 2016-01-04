Message = {
    
    fadeOut: true,
    
    setFadeOut: function( flag )
    {
	this.fadeOut = flag;
	return this;
    },
    
    clearMessages: function( container )
    {
	var divContainer = $( container );

	if ( divContainer.find( '.alert' ).length )
	    divContainer.find( '.alert:not(.not-remove)' ).remove();
    },
    
    showBulkMessages: function( messages, container )
    {
	var divContainer = $( container );
	if ( divContainer.find( '.alert' ).length )
	    divContainer.find( '.alert:not(.not-remove)' ).remove();
	
	var messagesDivs = [];
	for ( i in messages ) {
	    
	    var msgDiv = $( '<div />' );
	    msgDiv.addClass( 'alert alert-' + messages[i].level );

	    var buttonClose = $( '<button />' );
	    buttonClose.attr( 'type', 'button' )
			.addClass( 'close' )
			.attr( 'data-dismiss', 'alert' );

	    msgDiv.html( messages[i].message );
	    msgDiv.prepend( buttonClose );

	    divContainer.prepend( msgDiv );

	    General.scrollTo( msgDiv );

	    msgDiv.fadeIn();
	    messagesDivs.push( msgDiv );
	}
	
	if ( this.fadeOut ) {

		setTimeout(
		    function()
		    {
			for ( i in messagesDivs ) {
			    
			    var msgDiv = messagesDivs[i];
			    msgDiv.fadeOut( 'slow',
				function()
				{
				    msgDiv.remove();
				}
			    );
			}
		    },
		    10 * 1000
		);
	    }
	
	this.fadeOut = true;
    },

    showMsg: function( text, type, container )
    {
	var divContainer = $( container );
	if ( divContainer.find( '.alert' ).length )
	    divContainer.find( '.alert:not(.not-remove)' ).remove();

	var msgDiv = $( '<div />' );
	msgDiv.addClass( 'alert alert-' + type );

	var buttonClose = $( '<button />' );
	buttonClose.attr( 'type', 'button' )
		    .addClass( 'close' )
		    .attr( 'data-dismiss', 'alert' );

	msgDiv.html( text );
	msgDiv.prepend( buttonClose );

	divContainer.prepend( msgDiv );

	General.scrollTo( msgDiv );

	msgDiv.fadeIn();

	if ( this.fadeOut ) {
	    
	    setTimeout(
		function()
		{
		    msgDiv.fadeOut( 'slow',
			function()
			{
			    msgDiv.remove();
			}
		    );
		},
		10 * 1000
	    );
	}
	
	this.fadeOut = true;
    },

    msgError: function( text, container )
    {
	this.showMsg( text, 'error', container );
    },

    msgInfo: function( text, container )
    {
	this.showMsg( text, 'info', container );
    },

    msgSuccess: function( text, container )
    {
	this.showMsg( text, 'success', container );
    }
}