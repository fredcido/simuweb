Default = window.Default || {};

Default.Statistics = {
    
    init: function()
    {
	$( '.portlet-body table' ).each(
	    function()
	    {
		General.drawTables( $( this) );
	    }
	);

	this.closePortlets();
    },
    
    closePortlets: function()
    {
	if ( $( '.portlet-title').length < 2 )
	    return false;
	
	setTimeout(
	    function()
	    {
		$( '.portlet-title' ).trigger( 'click' );
	    }
	, 500 );
    },
    
    download: function( file )
    {
	General.newWindow( file );
    }
};

$( document ).ready(
    function()
    {
	Default.Statistics.init();
    }
);