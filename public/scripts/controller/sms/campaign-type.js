Sms = window.Sms || {};

Sms.CampaignType = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/sms/campaign-type' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	};
	
	Form.addValidate( form, submit );
	this.loadCampaignTypes();
    },
    
    loadCampaignTypes: function()
    {
	General.loadTable( '#campaign-type-list', '/sms/campaign-type/list' );
    }
};

$( document ).ready(
    function()
    {
	Sms.CampaignType.init();
    }
);