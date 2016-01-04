Sms = window.Sms || {};

Sms.Campaign = {
    
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
		    $( '#campaign-list tbody' ).empty();
	     
		    oTable = $( '#campaign-list' ).dataTable();
		    oTable.fnDestroy(); 

		    $( '#campaign-list tbody' ).html( response );
		    
		    General.drawTables( '#campaign-list' );
		    General.scrollTo( '#campaign-list', 800 );
		},
		error: function ()
		{
		    Message.msgError( 'Operasaun la diak', form );
		}
	    });
	};
    
	Form.addValidate( form, submit );
    },
    
    initForm: function()
    {
	var form  = $( 'form#smsformcampaign' );
	
	if ( !form.length )
	    return false;
	
	General.setTabsAjax( '.tabbable', this.configForm );
	this.configInformation();
    },
    
    configForm: function( pane )
    {
	var id = $( pane ).attr( 'id' );
	method = 'config' + General.toUpperCamelCase( id );
	
	General.execFunction( Sms.Campaign[method], pane );
    },
    
    configInformation: function()
    {
	var form  = $( '.tab-content #data form' );
	submit = function()
	{
	    $( '#group-container' ).removeClass( 'text-error' );
	    
	    if ( !$( '.group-sending:checked' ).length ) {
		
		
		$( '#group-container' ).addClass( 'text-error' );
		Message.msgError( 'Tenki hili grupu ho kontatu atu haruka!', $( '#group-container' ) );
		return false;
	    }
	    
	    var obj = {
		callback: function( response )
		{
		    if ( response.status ) {
			
			if ( General.empty( $( '#id_campaign' ).val() ) ) {
			
			    $( form ).find( '#id_campaign' ).val( response.id );

			    window.history.replaceState( {}, "Campaign Edit", BASE_URL + "/sms/campaign/edit/id/" + response.id );

			    $( '.nav-tabs a.ajax-tab' ).each(
				function()
				{
				    dataHref = $( this ).attr( 'data-href' );
				    $( this ).attr( 'data-href', dataHref + response.id );
				    $( this ).parent().removeClass( 'disabled' );
				}
			    );
			} else
			    history.go( 0 );
		    }
		}
	    };

	    Form.submitAjax( form, obj );
	    return false;
	}
	
	Sms.Campaign.configMaxLength();
	Sms.Campaign.calcTotalSending();
	Form.addValidate( form, submit );
    },
    
    configSent: function( pane )
    {
	General.drawTables( pane.find( 'table' ), 
	    function()
	    {
		$( '.popovers' ).popover({html: true});
	    }
	);
    },
    
    configLog: function( pane )
    {
	General.drawTables( pane.find( 'table' ) );
    },
    
    configIncoming: function( pane )
    {
	General.drawTables( pane.find( 'table' ) );
    },
    
    configStatistics: function()
    {
	App.handleResponsive();
	Sms.Campaign.initStatistics();
	Sms.Campaign.initChartSending();
	Sms.Campaign.initChartSentDay();
	Sms.Campaign.initChartSentHour();
	Sms.Campaign.initChartSentGroup();
    },
    
    initChartSending: function()
    {
	$( '#graph-sending' ).highcharts({
	    credits: {
		enabled: false
	    },
	    chart: {
		plotBackgroundColor: null,
		plotBorderWidth: null,
		plotShadow: false
	    },
	    title: {
		text: 'Haruka tiha ona / Hein atu haruka'
	    },
	    tooltip: {
		pointFormat: '<b>{point.percentage:.1f}%</b>'
	    },
	    plotOptions: {
		pie: {
		    allowPointSelect: true,
		    cursor: 'pointer',
		    dataLabels: {
			enabled: true,
			color: '#000000',
			connectorColor: '#000000',
			format: '<b>{point.name}</b>: {point.y}'
		    }
		}
	    },
	    series: [{
		type: 'pie',
		data: []
	    }]
        });
	
	this.loadDataChartSending();
    },
    
    loadDataChartSending: function()
    {
	$.ajax(
	    {
		url: General.getUrl( '/sms/campaign/chart-sending' ),
		data: {
		    id: $( '#id_campaign' ).val()
		},
		type: 'POST',
		dataType: 'json',
		beforeSend: function()
		{
		    General.loading( true );
		},
		complete: function()
		{
		    General.loading( false );
		},
		success: function( response )
		{
		    var chart = $( '#graph-sending' ).highcharts();
		    chart.series[0].setData( response.data );
		}
	    }
	);
    },
    
    initChartSentDay: function()
    {
	$( '#graph-sent-day' ).highcharts({
	    credits: {
		enabled: false
	    },
            title: {
                text: 'Haruka husi loron iha fulan ne\'e'
            },
            xAxis: {
                categories: []
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Hira haruka'
                }
            },
	    legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [
		{
		    name: 'Haruka',
		    data: []
		},
		{
		    name: 'Sala',
		    data: [],
		    color: '#910000'
		}
	    ]
        });
	
	this.loadDataChartSentDay();
    },
    
    loadDataChartSentDay: function()
    {
	$.ajax(
	    {
		url: General.getUrl( '/sms/campaign/chart-sent-day' ),
		data: {
		    id: $( '#id_campaign' ).val()
		},
		type: 'POST',
		dataType: 'json',
		beforeSend: function()
		{
		    General.loading( true );
		},
		complete: function()
		{
		    General.loading( false );
		},
		success: function( response )
		{
		    var chart = $( '#graph-sent-day' ).highcharts();
		    chart.xAxis[0].setCategories( response.months );
		    chart.series[0].setData( response.sent );
		    chart.series[1].setData( response.errors );
		}
	    }
	);
    },
    
    initChartSentHour: function()
    {
	$( '#graph-sent-hour' ).highcharts({
	    credits: {
		enabled: false
	    },
            title: {
                text: 'Haruka husi ora iha loron ohin: ' + Date.today().toString('dd/MM/yyyy')
            },
            xAxis: {
                categories: []
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Hira haruka'
                }
            },
	    legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [
		{
		    name: 'Haruka',
		    data: []
		},
		{
		    name: 'Sala',
		    data: [],
		    color: '#910000'
		}
	    ]
        });
	
	this.loadDataChartSentHour();
    },
    
    loadDataChartSentHour: function()
    {
	$.ajax(
	    {
		url: General.getUrl( '/sms/campaign/chart-sent-hour' ),
		data: {
		    id: $( '#id_campaign' ).val()
		},
		type: 'POST',
		dataType: 'json',
		beforeSend: function()
		{
		    General.loading( true );
		},
		complete: function()
		{
		    General.loading( false );
		},
		success: function( response )
		{
		    var chart = $( '#graph-sent-hour' ).highcharts();
		    chart.xAxis[0].setCategories( response.hours );
		    chart.series[0].setData( response.sent );
		    chart.series[1].setData( response.errors );
		}
	    }
	);
    },
    
    initStatistics: function()
    {
	$.ajax(
	    {
		url: General.getUrl( '/sms/campaign/list-statistics' ),
		data: {
		    id: $( '#id_campaign' ).val(),
		},
		type: 'POST',
		dataType: 'json',
		beforeSend: function()
		{
		    $( '#indicators .number' ).html( '0' );
		    General.loading( true );
		},
		complete: function()
		{
		    General.loading( false );
		},
		success: function( response )
		{
		    for ( i in response ) {
			$( '#indicators .' + i + ' .number' ).html( response[i] );
		    }
		}
	    }
	);
    },
    
    initChartSentGroup: function()
    {
	$( '#graph-sent-group' ).highcharts({
	    credits: {
		enabled: false
	    },
	    chart: {
		type: 'pie'
	    },
	    title: {
		text: 'Haruka / Sala husi grupu'
	    },
	    tooltip: {
		pointFormat: '{series.name}: {point.y} / <b>{point.percentage:.1f}%</b>'
	    },
	    plotOptions: {
		pie: {
		    allowPointSelect: true,
		    size:'60%',
		    cursor: 'pointer',
		    dataLabels: {
			enabled: true,
			color: '#000000',
			connectorColor: '#000000',
			format: '<b>{point.name}</b>: {point.y}'
		    }
		}
	    },
	    series: [
		{
		    data: [],
		    name: 'Haruka',
		    center: ['20%']
		},
		{
		    data: [],
		    name: 'Sala',
		    center: ['80%']
		}
	    ]
        });
	
	this.loadDataChartGroupSent();
    },
    
    loadDataChartGroupSent: function()
    {
	$.ajax(
	    {
		url: General.getUrl( '/sms/campaign/chart-sent-group' ),
		data: {
		    id: $( '#id_campaign' ).val()
		},
		type: 'POST',
		dataType: 'json',
		beforeSend: function()
		{
		    General.loading( true );
		},
		complete: function()
		{
		    General.loading( false );
		},
		success: function( response )
		{
		    var chart = $( '#graph-sent-group' ).highcharts();
		    chart.series[0].setData( response.sent );
		    chart.series[1].setData( response.errors );
		}
	    }
	);
    },
    
    configMaxLength: function()
    {
	$( '#content' ).maxlength(
	    {
		text: 'Ita hakerek karakter <b>%length</b> husi <b>%maxlength</b>.'
	    }
	);
	    
	$( '#content' ).bind( 'update.maxlength', 
	    function( event, element, lastLength, length, maxLength, left )
	    {   
		length = length === undefined ? lastLength : length;
		var percent = ( length * 100 ) / maxLength;
		$( '#progress-content .bar' ).width( percent + '%' );  
	    }
	).data("maxlength").updateLength();
    },
    
    calcTotalSending: function( input )
    {
	var ids = [];
	$( '.group-sending:checked' ).each(
	    function()
	    {
		ids.push( $( this ).val() );
	    }
	);
	    
	$.ajax({
	    type: 'POST',
	    data: {
		groups: ids
	    },
	    dataType: 'json',
	    url: General.getUrl( '/sms/campaign/calc-sending/'),
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
		if ( !response.release ) {
		    
		    Message.msgError( 'Keta! Total hira atu haruka liu departamentu nia balansu.', $( '#group-container' ) );
		    $( input ).attr( 'checked', false ).trigger( 'change' );
		    $.uniform.update( input );
		    
		} else {
		    
		    $( '#progress-group .bar' ).width( response.percent + '%' );  
		    $( '#total-sending' ).html( response.total );
		}
	    },
	    error: function ()
	    {
		Message.msgError( 'Operasaun la diak', $( '#group-container' ) );
		$( '#total-sending' ).html( 0 );
		
		$( '.group-sending' ).attr( 'checked', false );
		$.uniform.update( '.group-sending' );
	    }
	});
    }
};

$( document ).ready(
    function()
    {
	Sms.Campaign.init();
    }
);