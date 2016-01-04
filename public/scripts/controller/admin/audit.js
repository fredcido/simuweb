Admin = window.Admin || {};

Admin.Audit = {
    
    init: function()
    {
	this._configForm();
	this._configRangeDate();
	this._configSearchForm();
    },
    
    _configForm: function()
    {
	var form  = $( 'form' );
	
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
		    $( '#audit-list tbody' ).empty();
	     
		    oTable = $( '#audit-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#audit-list tbody' ).html( response );
		    
		    General.drawTables( '#audit-list' );
		    General.scrollTo( '#audit-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	}
    
	Form.addValidate( form, submit );
    },
    
    _configRangeDate: function()
    {
	$( '#start_date,#finish_date' ).daterangepicker(
	    {
                format: 'dd/MM/yyyy',
                separator: ' to\'o '
            },
	    function( start, end )
	    {
		$( '#start_date' ).val( start.toString( 'dd/MM/yyyy' ) );
		$( '#finish_date' ).val( end.toString( 'dd/MM/yyyy' ) );
	    }
        );
    },
    
    _configSearchForm: function()
    {
	$( '#fk_id_sysmodule' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_sysform' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/admin/audit/search-form/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_sysform' );
	    }
	);
    }
};

$( document ).ready(
    function()
    {
	Admin.Audit.init();
    }
);