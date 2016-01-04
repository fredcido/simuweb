Fefop = window.Fefop || {};

Fefop.Contract = {
    
    followupContainer: null,
    
    documentContainer: null,
    
    idContract: null,
    
    containerContract: null,
    
    reloadContract: function( id, selector )
    {
	if ( !General.empty( id ) )
	    this.setIdContract( id );
	
	if ( !General.empty( selector ) )
	    this.setContainerContract( selector );
	
	$( this.containerContract ).load( General.getUrl( '/fefop/index/contract/id/' + this.idContract ) );
    },
    
    setIdContract: function( id )
    {
	this.idContract = id;
	return this;
    },
    
    setContainerContract: function( container )
    {
	this.containerContract = container || '#container-contract';
	return this;
    },
    
    setfFollowupContainer: function( container )
    {
	this.followupContainer = $( container );
	return this;
    },
    
    setfDocumentContainer: function( container )
    {
	this.documentContainer = $( container );
	return this;
    },
    
    initFollowUp: function()
    {
	General.setTabsAjax( this.followupContainer.find( '.tabbable' ), this.configForm );
	this.configFollowUp();
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Fefop.Contract[method], pane );
    },
    
    configFollowUp: function()
    {
	var form  = this.followupContainer.find( 'form' );
	var self = this;
	submit = function()
	{
	    if ( General.empty( $( '#fk_id_fefop_status', form ).val() ) 
		    && 
		 General.empty( $( '#description', form ).val() ) ) {
		
		Message.msgError( 'Tenki prensi Status ka Deskrisaun', form );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			Fefop.Contract.reloadContract();
			Fefop.Contract.configListFollowup( self.followupContainer.find( '.tab-content #list-followup' ) );
			$( form ).find( '#clear' ).trigger( 'click' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	this.configMaxLength();
	Form.addValidate( form, submit );
    },
    
    configListFollowup: function( pane )
    {
	General.loadTable( pane.find( 'table' ), '/fefop/followup/list-followup/id/' + Fefop.Contract.idContract );
    },
    
    configMaxLength: function()
    {
	var field = this.followupContainer.find( '#description' );
	
	var self = this;
	$( field ).maxlength(
	    {
		text: 'Ita hakerek karakter <b>%length</b> husi <b>%maxlength</b>.'
	    }
	);
	    
	$( field ).bind( 'update.maxlength', 
	    function( event, element, lastLength, length, maxLength, left )
	    {   
		length = length === undefined ? lastLength : length;
		var percent = ( length * 100 ) / maxLength;
		self.followupContainer.find( '#progress-content .bar' ).width( percent + '%' );  
	    }
	).data("maxlength").updateLength();
    },
    
    initDocument: function()
    {
	General.setTabsAjax( this.documentContainer.find( '.tabbable' ), this.configForm );
	this.configDocument();
    },
    
    configDocument: function()
    {
	var form  = this.documentContainer.find( 'form' );
	var self = this;
	
	form.fileupload({
	    filesContainer: form.find( 'table .files' ),
	    uploadTemplateId: null,
	    downloadTemplateId: null,
	    uploadTemplate: function (o) {
		var rows = $();
		$.each(o.files, function (index, file) {
		    
		    var row = '<tr class="template-upload fade">' +
				'<td><span class="name"></span></td>' +
				'<td><span class="size"></span></td>';
			    
			if ( file.error ) {
			    row += '<td class="error" colspan="2"><span class="label label-important">Error</span> </td>'
			} else if ( o.files.valid && !index) {
			    
			    row += '<td><div class="progress progress-success progress-striped active" role="progressbar">' +
				    '<div class="bar" style="width:0%;"></div></div></td><td class="start">';
				
			    if ( !o.options.autoUpload )
				row += '<button class="btn blue"><i class="icon-upload icon-white"></i> <span>Hahu</span></button>';

			    row += '</td>';
			} else {
			    row += '<td colspan="2"></td>';
			}
			
			row += '<td class="cancel">';
			if ( !index )
			    row += '<button class="btn red"><i class="icon-ban-circle icon-white"></i> <span>Kansela</span></button>';
			
			row += '</td></tr>';
			
		    row = $( row );
		    
		    row.find( '.name' ).text( file.name );
		    row.find( '.size' ).text( o.formatFileSize( file.size ) );
		    
		    if ( file.error )
			row.find( '.error' ).append( file.error );
		    
		    rows = rows.add( row );
		});
		
		return rows;
	    },
	    downloadTemplate: function (o) {
		var rows = $();
		$.each(o.files, function ( index, file) {
		    
		    var row = '<tr class="template-download template-upload fade">';
		    
		    if ( file.error ) {
			
			row += '<td><span class="name"></span></td>' +
				'<td><span class="size"></span></td>' +
				'<td colspan="3" class="error"><span class="label label-important">Error</span> </td>';
			    
		    } else {
			
			row += '<td><a class="name" href="" target="_blank" title="" download=""></a>' + 
				'<td><span class="size"></span></td>' +
				'<td colspan="2"></td>' +
				'<td class="delete">' +
				'<button class="btn red"><i class="icon-trash icon-white"></i><span> Hamoos</span></button>' +
				'  <input type="checkbox" class="fileupload-checkbox hide" name="delete" value="1"></td>';
		    }
		    
		    row = $( row );
		    row.find( '.name' ).text( file.name );
		    row.find( '.size' ).text( o.formatFileSize( file.size ) );
		    
		    if ( file.error ) {
			
			row.find( '.error' ).append( file.error );
			
		    } else {
			
			row.find( '.name' ).attr( 'href', file.url )
					   .attr( 'title', file.name )
					   .attr( 'download', file.name );
			
			row.find( '.delete button' ).attr( 'data-type', file.delete_type )
					   .attr( 'data-url', file.delete_url );
		    }
		   
		    rows = rows.add(row);
		});
		
		return rows;
	    }
	}).bind( 'fileuploadstop', 
	    function()
	    {
		Fefop.Contract.configListFiles( self.documentContainer.find( '.tab-content #list-files' ) );
	    }
	);
    },
    
    configListFiles: function( pane )
    {
	General.loadTable( pane.find( 'table' ), '/fefop/document/list-files-rows/contract/' + Fefop.Contract.idContract );
    },
    
    download: function( file )
    {
	General.newWindow( file );
    },
    
    deleteFile: function( file )
    {
	var self = this;
	remove = function()
	{
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/fefop/document/delete/' ),
		    data: { file: file },
		    beforeSend: function()
		    {
			App.blockUI( self.documentContainer.find( '#list-files' ) );
		    },
		    complete: function()
		    {
			App.unblockUI( self.documentContainer.find( '#list-files' ) );
		    },
		    success: function ( response )
		    {
			Fefop.Contract.configListFiles( self.documentContainer.find( '.tab-content #list-files' ) );
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#list-files' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos dokumentu ida ne\'e ?', 'Hamoos dokumentu', remove );
    },
    
    checkBlacklist: function( identifiers, callback )
    {
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/fefop/index/check-blacklist/' ),
		data: { identifiers: identifiers },
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
		    General.execFunction( callback, response );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak' );
		}
	    }
	);
    },
    
    getMessageBlackList: function()
    {
	return 'Keta hili uzuariu ida nee. Verifika statuto kliente iha Sekretaridu FEFOP.';
    },
    
    printGrid: function( selector, title )
    {
	var table = $( selector );
	
	var rows = table.dataTable()._('tr', {"filter": "applied"});
	var header = table.dataTable().dataTableSettings[0].aoColumns;
	
	var modal = $( '#modal-root' ).clone();
	
	iframe = $( '<iframe />' );
	iframe.attr( 'src', General.getUrl( '/fefop/index/print-grid' ) );
	iframe.addClass( 'iframe-print' );
	
	title = title || $( 'h3.page-title' ).html();
	
	 iframe.load( 
	    function()
	    {  
		General.loading( false );

		var bodyIframe = iframe.contents().find( 'body' );
		
		bodyIframe.find( 'h3' ).html( title );
		
		var trHeader = $( '<tr />' );
		bodyIframe.find( 'table thead' ).append( trHeader );
		
		$.each(
		    header,
		    function( index, value )
		    {
			if ( index !== ( header.length - 1 ) )
			    trHeader.append( $( '<th>' ).html( value.sTitle ) );
		    }
		);
		
		$.each(
		    rows,
		    function( index, row )
		    {
			var tr = $( '<tr />' );
			bodyIframe.find( 'table tbody' ).append( tr );
			
			$.each( 
			    row,
			    function( index, col )
			    {
				if ( index !== ( row.length - 1 ) )
				    tr.append( $( '<td>' ).html( col ) );
			    }
			);
		    }
		);
	
		var iframeHeight = bodyIframe.outerHeight( true ) + 100;
		var iframeWidth = bodyIframe.outerWidth( true ) + 100;

		//iframe.height( iframeHeight );
		//iframe.width( iframeWidth );
			    
		modal.find('.modal-body').css( 'maxHeight', '800px' );
		modal.modal();
		iframe.get(0).contentWindow.print();
	    } 
	);
	
	modal.attr( 'id', (new Date()).getTime() );
	modal.find( '.modal-header h3' ).html( title );
	
	// Append to the body
	$( 'body' ).append( modal );
	
	General.loading( true );
	modal.find( '.modal-body' ).append( iframe );
	    
	// Remove modal when it's closed
	modal.on( 'hidden', 
	    function () 
	    {
		modal.remove();
	    }
	);
	
	modal.css( 
	    {
		width: '90%',
		marginLeft: '-45%',
	    }
	);
    }
};