Default = window.Default || {};

Default.Index = {

	dataIni: null,

	dataFim: null,

	init: function () {
		this.initCalendar();
		this.initDashboard();
		this.initChartClient();
		this.initChartVacancy();
		this.initChartOccupation();
		this.initChartGraduated();
	},

	initCalendar: function () {
		var liDateRange = $('<li />');
		liDateRange.addClass('pull-right no-text-shadow');

		var divDateRange = $('<div />');
		divDateRange.attr('id', 'dashboard-report-range').addClass('dashboard-date-range tooltips no-tooltip-on-touch-device responsive');

		var iconCalendar = $('<i />');
		iconCalendar.addClass('icon-calendar');
		var iconAngle = $('<i />');
		iconAngle.addClass('icon-angle-down');

		divDateRange.append(iconCalendar).append($('<span />')).append(iconAngle);
		liDateRange.append(divDateRange);

		$('ul.breadcrumb').append(liDateRange);

		$('#dashboard-report-range').daterangepicker({
				ranges: {
					'Tinan ida ne\'e': [Date.january().moveToFirstDayOfMonth(), 'today'],
					'Tinan ikus': [Date.january().add({
						years: -1
					}).moveToFirstDayOfMonth(), Date.december().add({
						years: -1
					}).moveToFirstDayOfMonth()],
					'Fulan ida ne\'e': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
					'Fulan ikus': [Date.today().moveToFirstDayOfMonth().add({
						months: -1
					}), Date.today().moveToFirstDayOfMonth().add({
						days: -1
					})]
				},
				opens: 'left',
				format: 'dd/MM/yyyy',
				locale: {
					customRangeLabel: 'Periodo'
				},
				separator: ' too ',
				startDate: Date.today().add({
					years: -1
				}),
				endDate: Date.today(),
				showWeekNumbers: false,
				buttonClasses: ['blue']
			},
			function (start, end) {
				Default.Index.dataIni = start.toString('dd/MM/yyyy');
				Default.Index.dataFim = end.toString('dd/MM/yyyy');
				$('#dashboard-report-range span').html(start.toString('dd/MM/yyyy') + ' - ' + end.toString('dd/MM/yyyy'));

				Default.Index.reloadDashboard();
			}
		);

		$('#dashboard-report-range').show();
		$('#dashboard-report-range span').html(Date.january().moveToFirstDayOfMonth().toString('dd/MM/yyyy') + ' - ' + Date.today().toString('dd/MM/yyyy'));

		this.dataIni = Date.january().moveToFirstDayOfMonth().toString('dd/MM/yyyy');
		this.dataFim = Date.today().toString('dd/MM/yyyy');
	},

	reloadDashboard: function () {
		this.initDashboard();
		this.loadDataChartClient();
		this.loadDataChartVacancy();
		this.loadDataChartOccupation();
		this.loadDataChartGraduated();
	},

	initDashboard: function () {
		$.ajax({
			url: General.getUrl('/index/dashboard'),
			data: {
				data_ini: this.dataIni,
				data_fim: this.dataFim
			},
			type: 'POST',
			dataType: 'json',
			beforeSend: function () {
				$('#indicators .number').html('0');
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				for (i in response) {
					$('#indicators .' + i + ' .number').html(response[i]);
				}
			}
		});
	},

	initChartClient: function () {
		$('#graph-client-ceop').highcharts({
			chart: {
				type: 'column'
			},
			credits: {
				enabled: false
			},
			title: {
				text: 'Kliente tuir CEOP'
			},
			xAxis: {
				categories: []
			},
			yAxis: {
				min: 0,
				title: {
					text: 'Total Rejistu'
				}
			},
			plotOptions: {
				column: {
					dataLabels: {
						enabled: true
					}
				}
			},
			series: [{
					name: 'Mane',
					data: []
				},
				{
					name: 'Feto',
					data: [],
					color: '#910000'
				}
			]
		});

		this.loadDataChartClient();
	},

	loadDataChartClient: function () {
		$.ajax({
			url: General.getUrl('/index/chart-client'),
			data: {
				data_ini: this.dataIni,
				data_fim: this.dataFim
			},
			type: 'POST',
			dataType: 'json',
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				var chart = $('#graph-client-ceop').highcharts();
				chart.xAxis[0].setCategories(response.categories);
				chart.series[0].setData(response.men);
				chart.series[1].setData(response.women);
			}
		});
	},

	initChartVacancy: function () {
		$('#graph-vacancy-ceop').highcharts({
			chart: {
				type: 'column'
			},
			credits: {
				enabled: false
			},
			legend: {
				enabled: false
			},
			plotOptions: {
				column: {
					colorByPoint: true,
					dataLabels: {
						enabled: true
					}
				}
			},
			title: {
				text: 'Vagas tuir CEOP'
			},
			xAxis: {
				categories: []
			},
			yAxis: {
				min: 0,
				title: {
					text: 'Total Vagas'
				}
			},
			series: [{
				name: 'Vagas',
				data: []
			}]
		});

		this.loadDataChartVacancy();
	},

	loadDataChartVacancy: function () {
		$.ajax({
			url: General.getUrl('/index/chart-vacancy'),
			data: {
				data_ini: this.dataIni,
				data_fim: this.dataFim
			},
			type: 'POST',
			dataType: 'json',
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				var chart = $('#graph-vacancy-ceop').highcharts();
				chart.xAxis[0].setCategories(response.categories);
				chart.series[0].setData(response.data);
			}
		});
	},

	initChartOccupation: function () {
		$('#graph-vacancy-occupation').highcharts({
			credits: {
				enabled: false
			},
			chart: {
				type: 'bar'
			},
			title: {
				text: 'Vagas Husi Okupasaun - Top 10'
			},
			xAxis: {
				categories: []
			},
			yAxis: {
				min: 0,
				title: {
					text: 'Okupasaun',
					align: 'high'
				},
				labels: {
					overflow: 'justify'
				}
			},
			plotOptions: {
				bar: {
					dataLabels: {
						enabled: true
					},
					colorByPoint: true
				}
			},
			legend: {
				enabled: false
			},
			series: [{
				name: 'Okupasaun',
				data: []
			}]
		});

		this.loadDataChartOccupation();
	},

	loadDataChartOccupation: function () {
		$.ajax({
			url: General.getUrl('/index/chart-occupation'),
			data: {
				data_ini: this.dataIni,
				data_fim: this.dataFim
			},
			type: 'POST',
			dataType: 'json',
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				var chart = $('#graph-vacancy-occupation').highcharts();
				chart.xAxis[0].setCategories(response.categories);
				chart.series[0].setData(response.data);
			}
		});
	},

	initChartGraduated: function () {
		$('#graph-graduated-course').highcharts({
			credits: {
				enabled: false
			},
			chart: {
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false
			},
			title: {
				text: 'Graduado tuir Kursu - Top 10'
			},
			tooltip: {
				pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
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

		this.loadDataChartGraduated();
	},

	loadDataChartGraduated: function () {
		$.ajax({
			url: General.getUrl('/index/chart-graduated'),
			data: {
				data_ini: this.dataIni,
				data_fim: this.dataFim
			},
			type: 'POST',
			dataType: 'json',
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				var chart = $('#graph-graduated-course').highcharts();
				chart.series[0].setData(response.data);
			}
		});
	}
};

$(document).ready(
	function () {
		Default.Index.init();
	}
);