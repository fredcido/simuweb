Admin = window.Admin || {};

Admin.SmsCredit = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/admin/sms-credit' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );
	this.loadSmsCredits();
	this.loadBalance();
	this.configCalc();
    },
    
    configCalc: function()
    {
	$( '#amount, #value' ).on( 'change blur',
	    function()
	    {
		current = $( this ).attr( 'id' );
		target = '#' + ( current == 'amount' ? 'value' : 'amount' );
		
		$.ajax({
		    type: 'POST',
		    dataType: 'json',
		    url: General.getUrl( '/admin/sms-credit/calc/'),
		    data: {
			value: General.toFloat( $( this ).val() ),
			type: current
		    },
		    beforeSend: function()
		    {
			//General.loading( true );
		    },
		    complete: function()
		    {
			//General.loading( false );
		    },
		    success: function ( response )
		    {
			$( target ).val( response.value );
		    },
		    error: function ()
		    {
			$( target ).val( '' );
		    }
		});
	    }
	);
    },
    
    loadSmsCredits: function()
    {
	General.loadTable( '#sms-credit-list', '/admin/sms-credit/list' );
    },
    
    loadBalance: function()
    {
	General.loadTable( '#sms-balance-list', '/admin/sms-credit/balance' );
    }
};

$( document ).ready(
    function()
    {
	Admin.SmsCredit.init();
    }
);