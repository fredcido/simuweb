Default = window.Default || {};

Default.Dashboard = {
    
    loadedContainers: 3,
    
    init: function()
    {
	this.highlight();
	this.loadGrids();
    },
    
    highlight: function()
    {
	$( '.portlet-title .tools .expand' ).pulsate(
	    {
		color: "#ff0000",
		reach: 50,
		repeat: 70,
		speed: 100,
		glow: true
	    }
	);
    },
    
    loadGrids: function()
    {
	var self = Default.Dashboard;
	callbackLoaded = function()
	{
	    self.loadedContainers--;
	    
	    if ( self.loadedContainers <= 0 ) {
		
		self.loadedContainers = 3;
		self.highlight();
		
		window.setTimeout( self.loadGrids, 20000 );
	    }
	};
	
	General.loadTable( '#job-list', '/auth/job-list/', callbackLoaded );
	General.loadTable( '#training-list', '/auth/training-list/', callbackLoaded );
	General.loadTable( '#ceop-list', '/auth/ceop-list/', callbackLoaded );
    }
};

$( document ).ready(
    function()
    {
	Default.Dashboard.init();
    }
);