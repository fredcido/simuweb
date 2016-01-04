Client = window.Client || {};

Client.Client = {
    
    TIMOR_LESTE: 1,
    
    init: function()
    {
	this.initFormSearch();
	this.initForm();
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
		    $( '#client-list tbody' ).empty();
	     
		    oTable = $( '#client-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#client-list tbody' ).html( response );
		    
		    General.drawTables( '#client-list' );
		    General.scrollTo( '#client-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	}
    
	Form.addValidate( form, submit );
	Form.handleClientSearch( form );
    },
    
    initForm: function()
    {
	if ( !$( '#container-client' ).length )
	    return false;
	
	Portlet.init( '#container-client' );
	
	this.configInformation();
    },
    
    configInformation: function()
    {
	var form  = $( '#information form' );
	submit = function()
	{
	    var obj = {
		callback: Client.Client.afterSaveInformation
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
    
	Form.addValidate( form, submit );
	
	$( '#information #email' ).rules( 'add', 'email' );
	
	Client.Client.configCalcAge();
	Client.Client.configChangeDistrict();
    },
    
    afterSaveInformation: function( response )
    {
	if ( response.status ) {
			
	    if ( General.empty( $( '#id_perdata' ).val() ) ) {

		$( '#information form' ).find( '#id_perdata' ).val( response.id );

		window.history.replaceState( {}, "Client Edit", General.getUrl( "/client/client/edit/id/" + response.id ) );

		$( '#container-client .dynamic-portlet' ).each(
		    function()
		    {
			dataUrl = $( this ).attr( 'data-url' );
			$( this ).attr( 'data-url', dataUrl + response.id );
		    }
		);
		    
		$( '#date_registration' ).attr( 'disabled', true );
		$( '#fk_id_adddistrict' ).attr( 'disabled', true ).trigger( 'liszt:updated' );
		$( '#num_subdistrict' ).attr( 'disabled', true ).trigger( 'liszt:updated' );
		
		$( '#document-container' ).empty();

		// Release all the steps and go to step 1
		Portlet.releaseSteps( 1, true );
		
		// Create the client num
		Client.Client.createNum( response.id );
		// Create the back link
		Client.Client.createBackLink( response.id );
                // Create case link
		Client.Client.createCaseLink( response.id );
	    }
	} else {
	    
	    if ( !General.empty( response.fields ) ) {
		    
		var settings = {
		    title: 'Kliente iha rejistu tiha ona'
		};
		
		switch( true ) {
		    case !General.empty( response.fields.same_name ):
			
			settings.url = General.getUrl( '/client/client/same-name/flag/N/id/' + response.fields.same_name );
			settings.buttons = [
			    {
				css: 'blue',
				text: 'Atualiza Kliente',
				click: function( modal )
				{
				    General.go( '/client/client/edit/id/' + response.fields.same_name );
				}
			    },
			    {
				css: 'green',
				text: 'Konfirma Kliente',
				click: function( modal )
				{
				    Client.Client.byPassClientName( 'N', modal );
				}
			    }
			];
			
			break;
		    case !General.empty( response.fields.same_birth ):
			
			settings.url = General.getUrl( '/client/client/same-name/flag/B/id/' + response.fields.same_birth );
			settings.buttons = [
			    {
				css: 'blue',
				text: 'Atualiza Kliente',
				click: function( modal )
				{
				    General.go( '/client/client/edit/id/' + response.fields.same_birth );
				}
			    }
			];
			
			break;
		}
		
		if ( !General.empty( settings.url ) )
		    General.ajaxModal( settings );
	    }
	}
    },
    
    byPassClientName: function( flag, modal )
    {
	if ( 'N' == flag ) {

	    $( '#by_pass_name' ).val( 1 );
	    $( '#information form' ).submit();

	} else
	    $( '#by_pass_name' ).val( 0 );

	modal.modal( 'hide' );
    },
    
    createNum: function( id )
    {
	if ( $( '#client-number' ).length )
	    return false;

	span = $( '<span />' );
	span.attr( 'id', 'client-number' )
	    .addClass( 'well pull-right number-system' );

	$.ajax({
	    type: 'GET',
	    dataType: 'json',
	    url: General.getUrl( '/client/client/fetch-num-client/id/' + id ),
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
		$( '#container-num-client' ).append( span.append( $( '<strong />' ).html( response.num ) ) );
	    }
	});
    },
    
    createBackLink: function( id )
    {
	a = $( '<a />' );
	a.addClass( 'btn red' ).attr( 'href', General.getUrl( '/client/client/view/id/' + id ) );
	
	icon = $( '<i/>' );
	icon.addClass( 'm-icon-swapleft m-icon-white' );
	
	a.append( icon ).append( ' Fila ba Kliente' );
	$( '#button-back' ).append( a ).closest( '.row-fluid' ).show();
    },
            
    createCaseLink: function( id )
    {
	a = $( '<a />' );
	a.addClass( 'btn blue' )
            .attr( 'href', 'javascript:;' )
            .click(
                function()
                {
                    Client.Case.checkClientData( id );
                }
            );
	
	icon = $( '<i/>' );
	icon.addClass( 'icon-folder-open' );
	
	a.append( icon ).append( ' Kazu' );
	$( '#button-back' ).append( a ).closest( '.row-fluid' ).show();
    },
    
    configChangeDistrict: function()
    {
	$( '#fk_id_adddistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#num_subdistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/client/client/search-sub-district/id/' + $( this ).val();
		General.loadCombo( url, 'num_subdistrict' );
	    }
	);
    },
    
    configCalcAge: function()
    {
        $( '#birth_date' ).blur(
            function()
            {
                $( this ).trigger( 'change' );
            }
        );
            
	$( '#birth_date' ).change(
	    function()
	    {
		birth = $( this ).val();
		if ( General.empty( birth ) ) {
		    
		    $( '#age' ).val( '' );
		    $( '#document-container' ).empty();
		    return false;
		}
		
		$.ajax(
		    {
			type: 'POST',
			data: {
			    birth: birth
			},
			dataType: 'json',
			url: General.getUrl( '/client/client/calc-age/' ),
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
			    $( '#age' ).val( response.age ).focus();
			    
			    if ( General.empty( $( '#id_perdata' ).val() ) && response.age >= 16 ) {
				
                                if ( !$( '#document-container #fk_id_pertypedocument' ).length ) {
                                    
                                    General.loading( true );
                                    $( '#document-container' ).load( 
                                                                        General.getUrl( '/client/client/information-document' ), {}, 
                                                                        function()
                                                                        {
                                                                            General.loading( false );

                                                                            $( '#document-container :input' ).each(
                                                                                function()
                                                                                {
                                                                                    $( this ).rules( 'add', 'required' );
                                                                                }
                                                                            );

                                                                            $( '#document-container :input:first' ).focus();

                                                                            App.fixContentHeight();
                                                                            Form.init();

                                                                            App.scrollTo( $( '#document-container' ) );
                                                                        }
                                                                    );
                                }
				
			    } else
				$( '#document-container' ).empty();
			},
			error: function ()
			{
			    Message.msgError( 'Operasaun la diak', $( '#information form' ) );
			}
		    }
		);
	    }
	);
    },
    
    configDocument: function()
    {
	var form  = $( '#document form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#document #clear' ).trigger( 'click' );
			Client.Client.loadDocuments();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Client.Client.loadDocuments();
    },
    
    loadDocuments: function()
    {
	General.loadTable( '#document-list', '/client/client/list-document/id/' + $( '#id_perdata' ).val() );
    },
    
    removeDocument: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'document' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/client/delete-document/' ),
		    data: {
			id_document: id,
			id: $( '#id_perdata' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( '#document-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#document-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadDocuments();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#document' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Dokumentu ida ne\'e ?', 'Hamoos dokumentu', remove );
    },
    
    configAddress: function( pane )
    {
	var form  = $( '#address form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#address #clear' ).trigger( 'click' );
			Client.Client.loadAddress();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	
	Client.Client._configChangeCountry();
	Client.Client._configChangeDistrict();
	Client.Client._configChangeSubDistrict();
	Client.Client.loadAddress();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    _configChangeCountry: function()
    {
	$( '#address #fk_id_addcountry' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#address #fk_id_adddistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		els = $( '#address .box-content .row-fluid :input' ).not( '#fk_id_addcountry, #complement' );
		
		if ( $( this ).val() != Client.Client.TIMOR_LESTE ) {
		    
		    els.each(
			function()
			{
			    $( this ).val( '' ).trigger( 'change' ).attr( 'disabled', true );
				    
			    var label = $( this ).parents( '.control-group' ).find( 'label' );	    
			    if ( label.hasClass( 'required' ) )
				label.attr( 'data-required', true ).removeClass( 'required' );
			}
		    );
			
		    $( '#address #complement' ).rules( 'add', 'required' );
		    $( '#address #complement' ).parents( '.control-group' ).find( 'label' ).addClass( 'required' );
		    
		} else {
		    
		    url = '/client/client/search-district/id/' + $( this ).val();
		    General.loadCombo( url, 'address #fk_id_adddistrict' );
		    
		    els.each(
			function()
			{
			    $( this ).val( '' ).trigger( 'change' ).removeAttr( 'disabled' );
				    
			    var label = $( this ).parents( '.control-group' ).find( 'label' );	    
			    if ( label.attr( 'data-required' ) )
				label.addClass( 'required' );
			}
		    );
		    
		    $( '#address #complement' ).rules( 'remove', 'required' )
		    $( '#address #complement' ).parents( '.control-group' ).find( 'label' ).removeClass( 'required' );
		}
	    }
	);
    },
    
    _configChangeDistrict: function()
    {
	$( '#address #fk_id_adddistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#address #fk_id_addsubdistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/client/client/search-sub-district-address/id/' + $( this ).val();
		General.loadCombo( url, 'address #fk_id_addsubdistrict' );
	    }
	);
    },
    
    _configChangeSubDistrict: function()
    {
	$( '#address #fk_id_addsubdistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#address #fk_id_addsucu' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/client/client/search-suku/id/' + $( this ).val();
		General.loadCombo( url, 'address #fk_id_addsucu' );
	    }
	);
    },
    
    loadAddress: function()
    {
	General.loadTable( '#address-list', '/client/client/list-address/id/' + $( '#fk_id_perdata' ).val() );
    },

    removeAddress: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'address' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/client/delete-address/' ),
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#address-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#address-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadAddress();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#address' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos hela fatin ida ne\'e ?', 'Hamoos hela fatin', remove );
    },
    
    configVisit: function()
    {
	var form  = $( '#visit form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#visit #clear' ).trigger( 'click' );
			Client.Client.loadVisit();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Client.Client.loadVisit();
    },
    
    loadVisit: function()
    {
	General.loadTable( '#visit-list', '/client/client/list-visit/id/' + $( '#id_perdata' ).val() );
    },
    
    removeVisit: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'visit' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/client/delete-visit/' ),
		    data: {
			id_visit: id,
			id: $( '#id_perdata' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( '#visit-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#visit-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadVisit();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#visit form' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Vizita ida ne\'e ?', 'Hamoos vizita', remove );
    },
    
    configScholarity: function()
    {
	General.setTabsAjax( '#scholarity .tabbable', Client.Client.configFormScholarity );
    },
    
    configFormScholarity: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id ) + 'Scholarity';
	
	General.execFunction( Client.Client[method], pane );
    },
    
    configFormalScholarity: function( pane )
    {
	var form = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			pane.find( '#clear' ).trigger( 'click' );
			Client.Client.loadFormalScholarity( pane );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};

	Form.addValidate( form, submit );
	
        /*
	pane.find( '#start_date,#finish_date' ).daterangepicker(
	    {
                format: 'dd/MM/yyyy',
                separator: ' to\'o '
            },
	    function( start, end )
	    {
		pane.find( '#start_date' ).val( start.toString( 'dd/MM/yyyy' ) );
		pane.find( '#finish_date' ).val( end.toString( 'dd/MM/yyyy' ) );
	    }
        );
        */
	    
	pane.find( '#category' ).change(
	    function()
	    {
		var category =  $( this ).val();
		if ( General.empty( category ) ) {
		    
		    pane.find( '#fk_id_scholarity' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/client/client/search-course/type/1/category/' + category;
		General.loadCombo( url, 'formal #fk_id_perscholarity' );
	    }
	);
	    
	Client.Client.loadFormalScholarity( pane );
    },
    
    loadFormalScholarity: function( pane )
    {
	General.loadTable( pane.find( '#formal-scholarity-list' ), '/client/client/list-formal-scholarity/id/' + $( '#id_perdata' ).val() );
    },
    
    removeFormalScholarity: function( link )
    {
	pane = $( link ).closest( '.tab-pane' );
	remove = function()
	{
	    id = $( link ).data( 'scholarity' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/client/delete-scholarity/' ),
		    data: {
			id_scholarity: id,
			id: $( '#id_perdata' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( pane.find( '#scholarity-list' ) );
		    },
		    complete: function()
		    {
			App.unblockUI( pane.find( '#scholarity-list' ) );
		    },
		    success: function ( response )
		    {
			Client.Client.loadFormalScholarity( pane );
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', pane.find( 'form' ) );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Eskolaridade ida ne\'e ?', 'Hamoos eskolaridade', remove );
    },
    
    configNonFormalScholarity: function( pane )
    {
	var form = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			pane.find( '#clear' ).trigger( 'click' );
			Client.Client.loadNonFormalScholarity( pane );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );
	
        /*
	pane.find( '#start_date,#finish_date' ).daterangepicker(
	    {
                format: 'dd/MM/yyyy',
                separator: ' to\'o '
            },
	    function( start, end )
	    {
		pane.find( '#start_date' ).val( start.toString( 'dd/MM/yyyy' ) );
		pane.find( '#finish_date' ).val( end.toString( 'dd/MM/yyyy' ) );
	    }
        );
        */
	    
	pane.find( '#category' ).change(
	    function()
	    {
		var category =  $( this ).val();
		if ( General.empty( category ) ) {
		    
		    pane.find( '#fk_id_scholarity' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/client/client/search-course/type/2/category/' + category;
		General.loadCombo( url, 'non-formal #fk_id_perscholarity' );
	    }
	);
	    
	Client.Client.loadNonFormalScholarity( pane );
    },
    
    loadNonFormalScholarity: function( pane )
    {
	General.loadTable( pane.find( '#non-formal-scholarity-list' ), '/client/client/list-non-formal-scholarity/id/' + $( '#id_perdata' ).val() );
    },
    
    removeNonFormalScholarity: function( link )
    {
	pane = $( link ).closest( '.tab-pane' );
	remove = function()
	{
	    id = $( link ).data( 'scholarity' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/client/delete-scholarity/' ),
		    data: {
			id_scholarity: id,
			id: $( '#id_perdata' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( pane.find( '#scholarity-list' ) );
		    },
		    complete: function()
		    {
			App.unblockUI( pane.find( '#scholarity-list' ) );
		    },
		    success: function ( response )
		    {
			Client.Client.loadNonFormalScholarity( pane );
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', pane.find( 'form' ) );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Eskolaridade ida ne\'e ?', 'Hamoos eskolaridade', remove );
    },
    
    configCompetency: function()
    {
	General.setTabsAjax( '#competency .tabbable', Client.Client.configFormCompetency );
    },
    
    configFormCompetency: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Client.Client[method], pane );
    },
    
    configLanguage: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#language #clear' ).trigger( 'click' );
			Client.Client.loadLanguage();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Client.Client.loadLanguage();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    loadLanguage: function()
    {
	General.loadTable( '#language-list', '/client/client/list-language/id/' + $( '#id_perdata' ).val() );
    },
    
    removeLanguage: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'language' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/client/client/delete-language/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#language-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#language-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadLanguage();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#language' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Lian Fuan ida ne\'e ?', 'Hamoos Lian Fuan', remove );
    },
    
    configKnowledge: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#knowledge #clear' ).trigger( 'click' );
			Client.Client.loadKnowledge();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Client.Client.loadKnowledge();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    loadKnowledge: function()
    {
	General.loadTable( '#knowledge-list', '/client/client/list-knowledge/id/' + $( '#id_perdata' ).val() );
    },
    
    removeKnowledge: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'knowledge' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/client/client/delete-knowledge/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#knowledge-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#knowledge-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadKnowledge();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#knowledge' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Konesimentu ida ne\'e ?', 'Hamoos Konesimentu', remove );
    },
    
    configExperience: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			pane.find( '#clear' ).trigger( 'click' );
			Client.Client.loadExperience();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	
        /*
	pane.find( '#start_date, #finish_date' ).daterangepicker(
	    {
                format: 'dd/MM/yyyy',
                separator: ' to\'o ',
		showDropdowns: true
            },
	    function( start, end )
	    {
		pane.find( '#start_date' ).val( start.toString( 'dd/MM/yyyy' ) );
		pane.find( '#finish_date' ).val( end.toString( 'dd/MM/yyyy' ) );
		Client.Client.calcYearsExperience( pane );
	    }
        );
        */
       
       pane.find( '#start_date, #finish_date' ).bind( 'change', 
            function()
            {
                if ( !General.empty( pane.find( '#start_date' ).val() ) && !General.empty( pane.find( '#finish_date' ).val() ) )
                    Client.Client.calcYearsExperience( pane );
            }
        );
            
        pane.find( '#start_date, #finish_date' ).bind( 'blur',
            function()
            {
                $( this ).trigger( 'change' );
            }
        );
	
	Client.Client.loadExperience();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    calcYearsExperience: function( pane )
    {
	$.ajax(
	    {
		type: 'POST',
		data: {
		    birth: pane.find( '#start_date' ).val(),
		    finish: pane.find( '#finish_date' ).val()
		},
		dataType: 'json',
		url: General.getUrl( '/client/client/calc-age/' ),
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
		    $( '#experience_year' ).val( response.age ).focus();
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', $( '#information form' ) );
		}
	    }
	);
    },
    
    loadExperience: function()
    {
	General.loadTable( '#experience-list', '/client/client/list-experience/id/' + $( '#id_perdata' ).val() );
    },
    
    removeExperience: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'experience' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/client/client/delete-experience/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#experience-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#experience-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadExperience();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#experience' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Esperiensia Profisional ida ne\'e ?', 'Hamoos Esperiensia Profisional', remove );
    },
    
    configHandicapped: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#handicapped #clear' ).trigger( 'click' );
			Client.Client.loadHandicapped();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Client.Client.loadHandicapped();
    },
    
    loadHandicapped: function()
    {
	General.loadTable( '#handicapped-list', '/client/client/list-handicapped/id/' + $( '#id_perdata' ).val() );
    },
    
    removeHandicapped: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'handicapped' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/client/client/delete-handicapped/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#handicapped-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#handicapped-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadHandicapped();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#handicapped' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Defisiensia ida ne\'e ?', 'Hamoos Defisiensia', remove );
    },
    
    configBank: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#bank #clear' ).trigger( 'click' );
			Client.Client.loadBank();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Client.Client.loadBank();
    },
    
    loadBank: function()
    {
	General.loadTable( '#bank-list', '/client/client/list-bank/id/' + $( '#id_perdata' ).val() );
    },
    
    removeBank: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'bank' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/client/delete-bank/' ),
		    data: {
			id_bank: id,
			id: $( '#id_perdata' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( '#bank-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#bank-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadBank();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#bank' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos konta banku ida ne\'e ?', 'Hamoos konta banku', remove );
    },
    
    configDependent: function( pane )
    {
	var form = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			pane.find( '#clear' ).trigger( 'click' );
			Client.Client.loadDepedendent( pane );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );  
	Client.Client.loadDepedendent( pane );
    },
    
    loadDepedendent: function()
    {
	General.loadTable( '#dependent-list', '/client/client/list-dependent/id/' + $( '#id_perdata' ).val() );
    },
    
    removeDependent: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'dependent' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/client/delete-dependent/' ),
		    data: {
			id_dependent: id,
			id: $( '#id_perdata' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( '#dependent-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#dependent-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadDepedendent();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#dependent' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Dependente ida ne\'e ?', 'Hamoos Dependente', remove );
    },
    
    configContact: function()
    {
	General.setTabsAjax( '#contact .tabbable', Client.Client.configFormContact );
    },
    
    configFormContact: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Client.Client[method], pane );
    },
    
    configContactClient: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#contact-client #clear' ).trigger( 'click' );
			Client.Client.loadContact();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Client.Client.loadContact();
	
	$( '#contact-client #email' ).rules( 'add', 'email' );
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    loadContact: function()
    {
	General.loadTable( '#contact-list', '/client/client/list-contact/id/' + $( '#id_perdata' ).val() );
    },
    
    removeContact: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'contact' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/client/delete-contact/' ),
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#contact-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#contact-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadContact();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#contact-client' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Kontatu ida ne\'e ?', 'Hamoos Kontatu', remove );
    },
    
    configAbout: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#about #clear' ).trigger( 'click' );
			Client.Client.loadAbout();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Client.Client.loadAbout();
	
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    loadAbout: function()
    {
	General.loadTable( '#about-list', '/client/client/list-about/id/' + $( '#id_perdata' ).val() );
    },
    
    removeAbout: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'about' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/client/delete-about/' ),
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#about-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#about-list' );
		    },
		    success: function ( response )
		    {
			Client.Client.loadAbout();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#about' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Hatene CEOP ida ne\'e ?', 'Hamoos Hatene CEOP', remove );
    },
    
    printCurriculum: function( id )
    {
	General.newWindow( General.getUrl( '/client/client/print/id/' + id ), 'Imprime Kliente nia Curriculum' );
    }
};

$( document ).ready(
    function()
    {
	Client.Client.init();
    }
);