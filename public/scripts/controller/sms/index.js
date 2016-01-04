Sms = window.Sms || {};

Sms.Index = {
    
    dataIni: null,
    
    dataFim: null,
    
    init: function()
    {
	this.initStatistics();
	this.initLastSents();
	this.initChartSending();
	this.initChartSentDay();
	this.initChartSentHour();
	this.initChartSentGroup();
    },
    
    initLastSents: function()
    {
	General.loadTable( 
		'#sent-list', 
		'/sms/index/last-sent/',
		function()
	    {
		$( '.popovers' ).popover({html: true});
	    }
	);
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
		url: General.getUrl( '/sms/index/chart-sending' ),
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
		url: General.getUrl( '/sms/index/chart-sent-day' ),
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
		url: General.getUrl( '/sms/index/chart-sent-hour' ),
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
		url: General.getUrl( '/sms/index/list-statistics' ),
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
		url: General.getUrl( '/sms/index/chart-sent-group' ),
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
};

$( document ).ready(
    function()
    {
	Sms.Index.init();
    }
);