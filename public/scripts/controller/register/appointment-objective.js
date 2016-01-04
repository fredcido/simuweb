Register = window.Register || {};

Register.AppointmentObjective = {
   
    init: function()
    {
	var form  = $( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			General.go( '/register/appointment-objective' );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );

	this.loadAppointmentObjective();
    },
    
    loadAppointmentObjective: function()
    {
	General.loadTable( '#appointment-objective-list', '/register/appointment-objective/list' );
    }
};

$( document ).ready(
    function()
    {
	Register.AppointmentObjective.init();
    }
);