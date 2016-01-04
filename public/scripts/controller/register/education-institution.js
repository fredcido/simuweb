Register = window.Register || {};

Register.EducationInstitute = {
    
    EDUCATION_FORMAL: 1,
    
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
		    $( '#education-institute-list tbody' ).empty();
	     
		    oTable = $( '#education-institute-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#education-institute-list tbody' ).html( response );
		    
		    General.drawTables( '#education-institute-list' );
		    General.scrollTo( '#education-institute-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	}
    
	Form.addValidate( form, submit );
    },
    
    initForm: function()
    {
	General.setTabsAjax( '.tabbable', this.configForm );
	
	this.configInformation();
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Register.EducationInstitute[method], pane );
    },
    
    configInformation: function()
    {
	var form  = $( '.tab-content #data form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( General.empty( $( '#id_fefpeduinstitution' ).val() ) ) {
			    
			    $( form ).find( '#id_fefpeduinstitution' ).val( response.id );

			    window.history.replaceState( {}, "Instituisaun Ensinu Edit", BASE_URL + "/register/education-institution/edit/id/" + response.id );

			    $( '.nav-tabs a.ajax-tab' ).each(
				function()
				{
				    dataHref = $( this ).attr( 'data-href' );
				    $( this ).attr( 'data-href', dataHref + response.id );
				    $( this ).parent().removeClass( 'disabled' );
				}
			    );

			    $( '.nav-tabs a.ajax-tab' ).eq( 0 ).trigger( 'click' );
			}
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
    
	Form.addValidate( form, submit );
    },
    
    configContact: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#contact #clear' ).trigger( 'click' );
			Register.EducationInstitute.loadContacts();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	
	$( '#contact #email' ).rules( 'add', 'email' );
	
	Register.EducationInstitute.loadContacts();
    },
    
    loadContacts: function()
    {
	General.loadTable( '#contact-list', '/register/education-institution/list-contacts/id/' + $( '#id_fefpeduinstitution' ).val() );
    },
    
    editContact: function( link )
    {
	id = $( link ).data( 'contact' );
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: BASE_URL + '/register/education-institution/fetch-contact/id/' + id,
		beforeSend: function()
		{
		    App.blockUI( '#contact form' );
		},
		complete: function()
		{
		    App.unblockUI( '#contact form' );
		},
		success: function ( response )
		{
		   $( '#contact form' ).populate( response, {resetForm: true} );
		   General.scrollTo( '#breadcrumb' );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', '#contact' );
		}
	    }
	);
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
		    url: BASE_URL + '/register/education-institution/delete-contact/',
		    data: {
			id_contact: id,
			id: $( '#id_fefpeduinstitution' ).val()
		    },
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
		    Register.EducationInstitute.loadContacts();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#contact' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos Contatu ida ne\'e ?', 'Hamoos contatu', remove );
    },
    
    configCourse: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#course #clear' ).trigger( 'click' );
			Register.EducationInstitute.loadCourses();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Register.EducationInstitute.configChangeTypeScholarity();
	Register.EducationInstitute.configChangeCategoryScholarity();
	//Register.EducationInstitute.configChangeClassificationScholarity();
	Register.EducationInstitute.loadCourses();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    configChangeTypeScholarity: function()
    {
	$( '#fk_id_pertypescholarity' ).change(
	    function()
	    {
		var type =  $( this ).val();
		if ( General.empty( type ) ) {
		    
		    $( '#fk_id_scholarity' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		// Search the categories
		url = '/register/education-institution/search-category/id/' + type;
		General.loadCombo( url, 'category' );
	    }
	);
    },
    
    configChangeCategoryScholarity: function()
    {
	$( '#category' ).change(
	    function()
	    {
		var category =  $( this ).val();
		var type =  $( '#fk_id_pertypescholarity' ).val();
		
		if ( General.empty( type ) || General.empty( category ) ) {
		    
		    $( '#fk_id_scholarity' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		/*
		if ( type == Register.EducationInstitute.EDUCATION_FORMAL || 'V' == category ) {
		  
		    $( '#scholarity_classification' ).val( 'V' == category ? 'A' : '' );
		    
		    $( '#classification' ).val( '' ).closest( '.span6' ).addClass( 'hide' );
		    Form.makeRequired( '#classification', false );
		    
		} else {
		    
		    $( '#classification' ).closest( '.span6' ).removeClass( 'hide' );
		    Form.makeRequired( '#classification', true );
		    $( '#scholarity_classification' ).val( '' );
		}
		*/
		
		url = '/register/education-institution/search-course/id/' + type + '/category/' + category;
		General.loadCombo( url, 'fk_id_scholarity' );
	    }
	);
    },
    
    configChangeClassificationScholarity: function()
    {
	$( '#classification' ).change(
	    function()
	    {
		$( '#scholarity_classification' ).val( $(this).val() );
	    }
	).trigger( 'change' );
    },
    
    loadCourses: function()
    {
	General.loadTable( '#course-list', '/register/education-institution/list-courses/id/' + $( '#id_fefpeduinstitution' ).val() );
    },
    
    editCourse: function( link )
    {
	id = $( link ).data( 'course' );
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: BASE_URL + '/register/education-institution/fetch-course/id/' + id,
		beforeSend: function()
		{
		    App.blockUI( '#course form' );
		},
		complete: function()
		{
		    App.unblockUI( '#course form' );
		},
		success: function ( response )
		{
		    $( '#fk_id_scholarity' ).attr( 'data-value', response.fk_id_scholarity );
		    $( '#category' ).attr( 'data-value', response.category );
		    
		    //response.classification = response.scholarity_classification;
		    
		    $( '#course form' ).populate( response, {resetForm: true} );
		    $( ' #fk_id_pertypescholarity' ).trigger( 'change' ); 
		    
		    General.scrollTo( '#breadcrumb' );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', '#course' );
		}
	    }
	);
    },
    
    removeCourse: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'course' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/register/education-institution/delete-course/',
		    data: {
			id_course: id,
			id: $( '#id_fefpeduinstitution' ).val()
		    },
		    beforeSend: function()
		    {
			App.blockUI( '#course-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#course-list' );
		    },
		    success: function ( response )
		    {
			Register.EducationInstitute.loadCourses();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#course' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos kursu ida ne\'e ?', 'Hamoos kursu', remove );
    },
    
    configStaff: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#staff #clear' ).trigger( 'click' );
			Register.EducationInstitute.loadStaff();
			Register.EducationInstitute.loadQualification();
			Register.EducationInstitute.loadStaffQualification();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Register.EducationInstitute.loadStaff();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
		$( '#btn-search-client' ).removeAttr( 'disabled' );
	    }
	);
    },
    
    loadStaff: function()
    {
	General.loadTable( '#staff-list', '/register/education-institution/list-staff/id/' + $( '#id_fefpeduinstitution' ).val() );
    },
    
    editStaff: function( link )
    {
	id = $( link ).data( 'staff' );
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: BASE_URL + '/register/education-institution/fetch-staff/id/' + id,
		beforeSend: function()
		{
		    App.blockUI( '#staff form' );
		},
		complete: function()
		{
		    App.unblockUI( '#staff form' );
		},
		success: function ( response )
		{
		    $( '#staff form' ).populate( response, {resetForm: true} );
		    $( '#position' ).trigger( 'change' ); 
		    $( '#btn-search-client' ).attr( 'disabled', true );
		    General.scrollTo( '#breadcrumb' );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', '#staff' );
		}
	    }
	);
    },
    
    removeStaff: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'staff' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/register/education-institution/delete-staff/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#staff-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#staff-list' );
		    },
		    success: function ( response )
		    {
			Register.EducationInstitute.loadStaff();
			$( '#staff form #clear' ).trigger( 'click' );
			Register.EducationInstitute.loadQualification();
			Register.EducationInstitute.loadStaffQualification();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#staff' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos funcionariu ida ne\'e ?', 'Hamoos funcionariu', remove );
    },
    
    configAddress: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#address #clear' ).trigger( 'click' );
			Register.EducationInstitute.loadAddress();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	
	$( '#start_date' ).daterangepicker(
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
	
	Register.EducationInstitute._configChangeCountry();
	Register.EducationInstitute._configChangeDistrict();
	Register.EducationInstitute._configChangeSubDistrict();
	Register.EducationInstitute.loadAddress();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    _configChangeCountry: function()
    {
	$( '#fk_id_addcountry' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_adddistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/education-institution/search-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_adddistrict' );
	    }
	);
    },
    
    _configChangeDistrict: function()
    {
	$( '#fk_id_adddistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsubdistrict' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/education-institution/search-sub-district/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsubdistrict' );
	    }
	);
    },
    
    _configChangeSubDistrict: function()
    {
	$( '#fk_id_addsubdistrict' ).change(
	    function()
	    {
		if ( General.empty( $( this ).val() ) ) {
		    
		    $( '#fk_id_addsucu' ).val( '' ).attr( 'disabled', true );
		    return false;
		}
		
		url = '/register/education-institution/search-suku/id/' + $( this ).val();
		General.loadCombo( url, 'fk_id_addsucu' );
	    }
	);
    },
    
    loadAddress: function()
    {
	General.loadTable( '#address-list', '/register/education-institution/list-address/id/' + $( '#id_fefpeduinstitution' ).val() );
    },
    
    editAddress: function( link )
    {
	id = $( link ).data( 'address' );
	$.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: BASE_URL + '/register/education-institution/fetch-address/id/' + id,
		beforeSend: function()
		{
		    App.blockUI( '#address form' );
		},
		complete: function()
		{
		    App.unblockUI( '#address form' );
		},
		success: function ( response )
		{
		    $( '#fk_id_adddistrict' ).attr( 'data-value', response.fk_id_adddistrict );
		    $( '#fk_id_addsubdistrict' ).attr( 'data-value', response.fk_id_addsubdistrict );
		    $( '#fk_id_addsucu' ).attr( 'data-value', response.fk_id_addsucu );
		    
		    $( '#address form' ).populate( response, {resetForm: true} );
		    $( '#fk_id_addcountry' ).trigger( 'change' ); 
		    
		    General.scrollTo( '#breadcrumb' );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', '#address' );
		}
	    }
	);
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
		    url: BASE_URL + '/register/education-institution/delete-address/',
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
			Register.EducationInstitute.loadAddress();
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
    
    configQualification: function( pane )
    {
	var form  = pane.find( 'form' );
	submit = function()
	{
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			$( '#qualification #clear' ).trigger( 'click' );
			Register.EducationInstitute.loadQualification();
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Form.addValidate( form, submit );
	Register.EducationInstitute.loadQualification();
	Register.EducationInstitute.loadStaffQualification();
	Register.EducationInstitute.configCategoryQualification();
	    
	form.bind( 'reset',
	    function()
	    {
		$( this ).find( '.chosen' ).val( '' ).trigger( 'change' );
	    }
	);
    },
    
    loadQualification: function()
    {
	General.loadTable( '#qualification-list', '/register/education-institution/list-qualification/id/' + $( '#id_fefpeduinstitution' ).val() );
    },
    
    removeQualification: function( link )
    {
	remove = function()
	{
	    id = $( link ).data( 'qualification' );
	    $.ajax(
		{
		    type: 'POST',
		    dataType: 'json',
		    url: BASE_URL + '/register/education-institution/delete-qualification/',
		    data: {id: id},
		    beforeSend: function()
		    {
			App.blockUI( '#staff-list' );
		    },
		    complete: function()
		    {
			App.unblockUI( '#qualification-list' );
		    },
		    success: function ( response )
		    {
			Register.EducationInstitute.loadQualification();
		    },
		    error: function ()
		    {
			Message.msgError( 'Operasaun la diak', '#qualification' );
		    }
		}
	    );
	};
	
	General.confirm( 'Ita hakarak hamoos kualifikasaun ida ne\'e ?', 'Hamoos kualifikasaun', remove );
    },
    
    loadStaffQualification: function()
    {
	url = '/register/education-institution/search-staff/id/' + $( '#id_fefpeduinstitution' ).val();
	General.loadCombo( url, 'fk_id_staff' );
    },
    
    configCategoryQualification: function()
    {
	$( '#category_qualification' ).change(
	    function()
	    {
		var category = $( this ).val();
		if ( General.empty( category ) )
		    return false;
		
		url = '/register/education-institution/search-course/id/2/category/' + $( this ).val();
		General.loadCombo( url, 'fk_id_perscholarity_staff' );
	    }
	);
    },
    
    listCourses: function( id )
    {
	var settings = {
	    title: 'Kursu Instituisaun Ensinu',
	    url: '/register/education-institution/detail-courses/id/' + id,
	    buttons: [
		{
		    css: 'green',
		    text: 'Troka',
		    click: function( modal )
		    {
			General.go( '/register/education-institution/edit/id/' + id );
		    }
		}
	    ],
	    callback: function( modal )
	    {
		General.drawTables( modal.find( 'table' ) );
	    }
	};

	General.ajaxModal( settings );
    },
    
    searchClient: function()
    {
	var settings = {
	    title: 'Buka Kliente',
	    url: '/register/education-institution/search-client/',
	    callback: function( modal )
	    {
		modal.css( 
		    {
			width: '90%',
			marginLeft: '-45%'
		    }
		);
		    
		Form.init();
		Register.EducationInstitute.initFormSearchClient( modal );
	    }
	};

	General.ajaxModal( settings );
    },
    
    initFormSearchClient: function( modal )
    {
	var form  = modal.find( 'form' );
	
	if ( !form.length )
	    return false;
	
	submit = function()
	{
	    var data = $( form ).serializeArray();
	    data.push( {name: 'list-ajax', value: 1} );
	    
	    Message.clearMessages( form );
   
	    $.ajax({
		type: 'POST',
		data: data,
		dataType: 'text',
		url: General.getUrl( '/register/education-institution/search-client-forward' ),
		beforeSend: function()
		{
		    App.blockUI( form );
		},
		complete: function()
		{
		    App.unblockUI( form );
		},
		success: function ( response )
		{
		    $( '#client-list tbody' ).empty();
	     
		    oTable = $( '#client-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#client-list tbody' ).html( response );
		    
		    callbackClick = function()
		    {
			$( '#client-list tbody a.action-ajax' ).click(
			    function()
			    {
				Register.EducationInstitute.setClient( $( this ).data( 'id' ), modal );
			    }
			);
		    };
		    
		    General.drawTables( '#client-list', callbackClick );
		    General.scrollTo( '#client-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
	Form.handleClientSearch( form );
    },
    
    setClient: function( id, modal )
    {
	 $.ajax(
	    {
		type: 'POST',
		dataType: 'json',
		url: General.getUrl( '/register/education-institution/fetch-client/' ),
		data: {id: id},
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
		    $( '#staff form' ).populate( response, {resetForm: true} );
		   General.scrollTo( '#breadcrumb' );
		   modal.modal( 'hide' );
		},
		error: function ()
		{
		   Message.msgError( 'Operasaun la diak', modal );
		}
	    }
	);
    }
};

$( document ).ready(
    function()
    {
	Register.EducationInstitute.init();
    }
);