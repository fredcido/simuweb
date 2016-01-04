Register = window.Register || {};

Register.IsicSection = {
    
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/isic-section' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadIsicSections();
    },
    
    loadIsicSections: function()
    {
	General.loadTable( '#section-list', '/register/isic-section/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.IsicSection.init();
    }
);