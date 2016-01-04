File = {
    
    data: null,
    
    manager: function( params )
    {
	this.data = params;
	
	var settings = {
	    title: 'Dokumentu Sira',
	    data: params,
	    url: '/client/document/',
	    callback: function( modal )
	    {
		modal.find( '.modal-body' ).height( '500px' );
		App.initUniform( '.fileupload-toggle-checkbox' );
		File.initUploader();
		File.loadFiles();
	    }
	};

	General.ajaxModal( settings );
    },
    
    initUploader: function()
    {
	$( '#fileupload' ).fileupload({
	    filesContainer: $( 'table .files' ),
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
		File.loadFiles();
	    }
	);
    },
    
    loadFiles: function()
    {
	$.ajax({
	    type: 'POST',
	    dataType: 'text',
	    data: this.data,
	    url: General.getUrl( '/client/document/list-files/' ),
	    beforeSend: function()
	    {
		App.blockUI( $( '#list-files' ) );
	    },
	    complete: function()
	    {
		App.unblockUI( $( '#list-files' ) );
	    },
	    success: function ( response )
	    {
		$( '#list-files' ).html( response );
		Form.init();
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', $( '#list-files' ) );
	    }
	});
    },
    
    download: function( file )
    {
	General.newWindow( file );
    },
    
    deleteFile: function( file, item )
    {
	remove = function()
	{
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/client/document/delete/' ),
		    data: {
			file: file
		    },
		    beforeSend: function()
		    {
			App.blockUI( '#list-files' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#list-files' );
		    },
		    success: function ( response )
		    {
			$( item ).closest( 'tr' ).remove();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#list-files' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos dokumentu ida ne\'e ?', 'Hamoos dokumentu', remove );
    }
};