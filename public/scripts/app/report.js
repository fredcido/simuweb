Report = {
    
    init: function()
    {
	var form = $( 'form' );
	submit = function()
	{
	    Report.submit( form );
	};
	
	Form.addValidate( form, submit );
    },
    
    submit: function( form )
    {
	var newForm = Form.cloneForm( form );
    
	var action = newForm.attr( 'action' );
	action = action.replace( /validate\/?$/, 'output' );
	newForm.attr( 'action', action );
    
	newForm.removeAttr( 'onsubmit' )
	    .unbind( 'submit' )
	    .submit(
		function( e ) 
		{
		    action = $( this ).attr( 'action' );
		    iframeName = General.parseId( action );

		    if ( $( '#' + iframeName ).length )
			$( '#' + iframeName ).closest( '.box' ).remove();

		    iframe = $( '<iframe />' );
		    iframe.attr( 'id', iframeName ).attr( 'name', iframeName ).addClass( 'iframe-report' );

		    divBox = $( '<div />' );
		    divBox.addClass( 'box span12' );

		    divContent = $( '<div />' );
		    divContent.addClass( 'box-content' );

		    divContent.append( iframe );
		    divBox.append( divContent );

		    $( '#report-iframe' ).append( divBox );

		    iframe.load( 
			function()
			{  
			    General.loading( false );
			    
			    var iframeHeight = iframe.contents().find( 'body' ).outerHeight( true ) + 100;
			    //var iframeWidth = iframe.contents().find( 'body' ).outerWidth( true ) + 100;
			    
			    iframe.height( iframeHeight );
			    //iframe.width( iframeWidth );
			} 
		    );

		    iframe.empty();

		    General.scrollTo( iframe );
		    this.target = iframeName;

		    General.loading( true );
		}
	    );
	
	newForm.submit();
	$( '.form-report-clone' ).remove();
    },

    print: function()
    {
	var iframe = $( '.iframe-report' );
	if ( !iframe.length )
	    return false;
    
	iframe.get(0).contentWindow.print();
	return true;
    },

    closeIframe: function()
    {
	$( '.iframe-report' ).closest( '.box' ).remove();
    },

    exporting: function( url )
    {
	contentsIframe = $( '.iframe-report' ).contents();
    
	contentsIframe.find( '#control-bar a' ).addClass( 'disabled' );
    
	if ( $( '#exporting-frame' ).length )
	    $( '#exporting-frame' ).remove();
    
	var form = Form.cloneForm( $( 'form' ) );
    
	iframe = $( '<iframe />' );
	iframe.attr( 'id', 'exporting-frame' ).attr( 'name', 'exporting-frame' ).hide();

	$( 'body' ).append( iframe );
    
	setTimeout( 'Report.checkDownload()', 2000 );	

	form.target = 'exporting-frame';
	form.action = url;
	form.attr( 'action', url + '/id-report/' + contentsIframe.find( '#hash-report' ).val() )
	    .attr( 'id', 'form-export' )
	    .removeAttr( 'onsubmit' )
	    .unbind( 'submit' )
	    .submit();
    },

    checkDownload: function()
    {
	contentsIframe = $( '.iframe-report' ).contents();
	contentsIframe.find( '#control-bar a' ).removeClass( 'disabled' );

	$( '#form-export' ).remove();
	$( '#exporting-frame' ).remove();

	General.loading( false );
    }
};

$( document ).ready(
    function()
    {
	Report.init();
    }
);