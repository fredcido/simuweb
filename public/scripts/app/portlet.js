Portlet = {
    container: null,
    
    setContainer: function( selector )
    {
	this.container = $( selector );
	return this;
    },
    
    init: function( selector )
    {
	if ( !General.empty( selector ) )
	    this.setContainer( selector );
	
	this.initPortlets();
	this.initRefresh();
	this.initAjaxLoaded();
	this.buildControl();
	this.hideControl();
	setTimeout(
	    'Portlet.setDefaultActive()',
	    500
	);
	
	return this;
    },
    
    setDefaultActive: function()
    {
	var hash = $( location ).attr( 'hash' );
	if ( (/#p-([0-9]+)/i).exec( hash ) ) {
	    
	    step = hash.replace( /[^0-9]+/, '' );
	    this.fireStep( step );
	}
    },
    
    hideControl: function()
    {
	setTimeout(
	    function()
	    {
		$( '.steps-control .control-tool' ).trigger( 'click' );
	    },
	    2000
	);
    },
    
    initPortlets: function()
    {
	this.container.find( '.dynamic-portlet.disabled > .portlet-body' ).addClass( 'hide' );
	
	this.container.find( '.dynamic-portlet' ).bind( 'portlet-open',
	    function()
	    {
		index = $( this ).closest( '.row-fluid' ).index();
		$( '.steps-control .btn-group a.btn-control' ).removeClass( 'active' ).eq( index ).addClass( 'active' );
		//$( location ).attr( 'hash', '#p-' + (++index) );
		App.scrollTo( $( this ) );
	    }
	);
	
	return this;
    },
    
    initRefresh: function()
    {
	var portlet = this;
	this.container.on( 'click', '.dynamic-portlet > .portlet-title .tools .reload', 
	    function( e )
	    {
		e.preventDefault();
		e.stopPropagation();
		
		step = $( this ).closest( '.portlet' );
		step.removeClass( 'loaded' );
		portlet.loadAjax( step );
		
		return false;
	    }
	);
    },
    
    loadAjax: function( step )
    {
	if ( step.hasClass( 'disabled' ) )
	    return true;

	this.focusStep( step, true );
	
	if ( step.hasClass( 'loaded' ) || !$( step ).data( 'url' ) )
	    return false;

	var body = step.find( '.portlet-body' );
	App.blockUI( body );
	
	// Load ajax content
	$.get( 
	    step.data( 'url' ),
	    function( content )
	    {
		body.html( content );
		
		App.unblockUI( body );
		App.fixContentHeight();
		App.initUniform();
		Form.init();

		step.addClass( 'loaded' );

		if ( step.data( 'callback' ) ) {

		    callback = eval( step.data( 'callback' ) );
		    callback( step );
		}
	    }
	);
    },
    
    initAjaxLoaded: function()
    {
	var self = this;
	this.container.find( '.portlet' ).each(
	    function()
	    {
		if ( !$( this ).hasClass( 'ajax-loaded' ) )
		    return true;
		
		var portlet = $( this );
		portlet.bind( 'portlet-open', 
		    function()
		    {
			self.loadAjax( $( this ) );
		    }
		);
	    }
	);
    },
    
    focusStep: function( step, scroll )
    {
	this.container.find( '.dynamic-portlet > .portlet-body' ).not( step ).slideUp();
	this.container.find( '.dynamic-portlet >.portlet-title .tools .collapse' ).removeClass( 'collapse' ).addClass( 'expand' );
	
	step.find( '.portlet-title .tools .expand' ).removeClass( 'expand' ).addClass( 'collapse' );
	step.find( '.portlet-body' ).slideDown( 200 );
	
	index = step.closest( '.row-fluid' ).index();
	$( '.steps-control .btn-group a.btn-control' ).removeClass( 'active' ).eq( index ).addClass( 'active' );
	
	if ( scroll ) App.scrollTo( step );
    },
    
    buildControl: function()
    {
	mainControl = this.buildMainControl();
	control = this.buildToolControl();
	steps = this.buildSteps();
	
	row = $( '<div />' );
	row.addClass( 'row-fluid' );
	
	mainControl.append( row.clone().append( control ) ).append( row.clone().append( steps ) );
	$( 'body' ).append( mainControl );
    },
    
    buildMainControl: function()
    {
	div = $( '<div />' );
	div.addClass( 'steps-control row-fluid hidden-phone' );
	
	return div;
    },
    
    buildToolControl: function()
    {
	div = $( '<div />' );
	div.addClass( 'span12 text-center' );
	
	control = $( '<a />' );
	control.attr( 'href', 'javascript:;' )
		.addClass( 'btn red control-tool' );
		
	icon = $( '<i />' );
	icon.addClass( 'icon-chevron-down' );
	
	var self = this;
	control.click(
	    function( e )
	    {
		e.stopPropagation();
		divMain = $( this ).closest( '.steps-control' );
		divInner = divMain.find( '.span12' ).eq( 1 );

		var height = divInner.outerHeight( true );
		var eventToggle = $( this ).find( 'i' ).hasClass( 'icon-chevron-down' );

		finalBottom =  0;
		if ( eventToggle )
		    finalBottom =  '-' + height;

		finalBottom += 'px';

		if ( eventToggle )
		    $( this ).find( 'i' ).removeClass( 'icon-chevron-down' ).addClass( 'icon-chevron-up' );
		else
		    $( this ).find( 'i' ).removeClass( 'icon-chevron-up' ).addClass( 'icon-chevron-down' );

		divMain.animate(
		    {
			bottom: finalBottom
		    }, 'slow'
		);
	    }
	);
	
	div.append( control.append( icon ) );
	
	return div;
    },
    
    buildSteps: function()
    {
	container = $( '<div />' );
	container.addClass( 'span12 text-center' );
	
	span = $( '<div />' );
	span.addClass( 'span12' );
	
	container.append( span );
	
	btnGroup = $( '<div />' );
	btnGroup.addClass( 'btn-group' );
	
	title = $( '<a />' );
	title.attr( 'href', 'javascript:;' )
	     .addClass( 'btn' )
	     .append( $( '<strong />' ).html( 'Hakats' ) );
	     
	btnGroup.append( title );
	
	var self = this;
	this.container.find( '.dynamic-portlet' ).each(
	    function( index )
	    {
		a = $( '<a />' );
		a.attr( 'href', 'javascript:;' ).addClass( 'btn black btn-control tooltips' );
		a.html( ++index );
		
		if ( $( this ).hasClass( 'disabled' ) )
		    a.addClass( 'disabled' );
		
		a.attr( 'data-original-title', $( this ).find( '> .portlet-title .caption' ).text() );
		
		a.click(
		    function( e )
		    {
			e.stopPropagation();
			
			if ( $( this ).hasClass( 'disabled' ) || $( this ).hasClass( 'active' ) )
			    return false;
			
			self.container.find( '.portlet-title .tools .collapse' ).removeClass( 'collapse' ).addClass( 'expand' );
			self.container.find( '.dynamic-portlet > .portlet-body' ).hide();
			
			portlet = self.container.find( '.dynamic-portlet' ).eq( $( this ).index() - 1 );
			portlet.find( '.portlet-title .tools .expand' ).trigger( 'click' );
		    }
		);
		
		btnGroup.append( a );
	    }
	);
	    
	btnGroup.find( 'a.btn-control:first' ).addClass( 'active' );
	
	$( '.tooltips' ).tooltip();
	    
	span.append( btnGroup );
	return container;
    },
    
    releaseStepByIndex: function( index, fire )
    {
	portlet = this.container.find( '.dynamic-portlet' ).eq( index );
	this.releaseStep( portlet, fire );
    },
    
    releaseStep: function( portlet, fire )
    {
	if ( portlet.hasClass( 'disabled' ) ) {
	
	    portlet.removeClass( 'disabled' );

	    tools = portlet.find( '.portlet-title .tools' );

	    reload = $( '<a />' );
	    reload.addClass( 'reload' ).attr( 'href', 'javascript:;' );

	    collapse = $( '<a />' );
	    collapse.addClass( 'expand' ).attr( 'href', 'javascript:;' );

	    tools.append( reload ).append( collapse );
	}
	
	aControl = $( '.steps-control .btn-group a.btn-control' ).eq( portlet.closest( '.row-fluid' ).index() ).removeClass( 'disabled' );
	
	if ( fire )
	    aControl.trigger( 'click' );
    },
    
    fireStep: function ( step )
    {
	aControl = $( '.steps-control .btn-group a.btn-control' ).eq( step - 1 );
	aControl.trigger( 'click' );
    },
    
    releaseSteps: function( goIndex )
    {
	var self = this;
	this.container.find( '.dynamic-portlet' ).each(
	    function( index )
	    {
		self.releaseStep( $( this ), index == goIndex );
	    }
	);
    }
};