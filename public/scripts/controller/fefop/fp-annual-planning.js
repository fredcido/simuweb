Fefop = window.Fefop || {};

Fefop.FPAnnualPlanning = {

	scholarities: [],

	init: function () {
		this.configChangeEvents();
		this.initCalendar();
	},

	configChangeEvents: function () {
		$('#institution, #year_planning').on('change',
			function () {
				$('#id_annual_plannnig').val(null);
				var institution = $('#institution').val();
				var year = $('#year_planning').val();
				Fefop.FPAnnualPlanning.scholarities = [];

				if (!General.empty(institution) && !General.empty(year)) {

					$('#btn-add-planning').removeAttr('disabled');
					Fefop.FPAnnualPlanning.initCalendar();
					Fefop.FPAnnualPlanning.fetchEvents();

				} else
					$('.portlet .actions button').attr('disabled', true);
			}
		);
	},

	initCalendar: function () {
		h = {
			left: 'title',
			center: '',
			right: 'prev,next'
		};

		var currentYear = General.empty($('#year_planning').val()) ? new Date().getFullYear() : $('#year_planning').val();

		$('#calendar-planning').fullCalendar('destroy');
		$('#calendar-planning').fullCalendar({
			header: h,
			slotMinutes: 15,
			editable: false,
			year: currentYear,
			day: 1,
			month: 0,
			droppable: false,
			eventClick: function (calEvent, jsEvent, view) {
				Fefop.FPAnnualPlanning.addNewEvent(calEvent.id);
			},
			eventRender: function (event, element) {
				var bt = $('<button />');
				bt.attr('type', 'button').addClass('btn red');
				bt.click(
					function (e) {
						e.stopPropagation();
						remove = function () {
							$.ajax({
								type: 'POST',
								dataType: 'json',
								url: General.getUrl('/fefop/fp-annual-planning/delete-event/'),
								data: {
									event: event.id
								},
								beforeSend: function () {
									General.loading(true);
								},
								complete: function () {
									General.loading(false);
								},
								success: function (response) {
									if (response.status) {

										Fefop.FPAnnualPlanning.initCalendar();
										Fefop.FPAnnualPlanning.fetchEvents();
									} else
										Message.msgError('Keta remove planeamentu ne\'e', $('form'));
								},
								error: function () {
									Message.msgError('Operasaun la diak', $('form'));
								}
							});
						};

						General.confirm('Ita hakarak hamoos Formasaun ida ne\'e ?', 'Hamoos Formasaun', remove);
					}
				);

				var icon = $('<i />');
				icon.addClass('icon-remove-circle');
				bt.append(icon);

				element.find('.fc-event-title').html('').append(bt).append(event.title);
			},
			viewDisplay: function (view) {
				var now = new Date();
				var end = new Date();
				var begin = new Date();

				end.setFullYear(currentYear);
				end.setMonth(11);
				begin.setFullYear(currentYear);
				begin.setMonth(0);

				var cal_date_string = view.start.getMonth() + '/' + view.start.getFullYear();
				var cur_date_string = now.getMonth() + '/' + now.getFullYear();
				var end_date_string = end.getMonth() + '/' + end.getFullYear();
				var begin_date_string = begin.getMonth() + '/' + begin.getFullYear();

				if (cal_date_string == begin_date_string) {
					jQuery('.fc-button-prev').addClass("fc-state-disabled");
				} else {
					jQuery('.fc-button-prev').removeClass("fc-state-disabled");
				}

				if (end_date_string == cal_date_string) {
					jQuery('.fc-button-next').addClass("fc-state-disabled");
				} else {
					jQuery('.fc-button-next').removeClass("fc-state-disabled");
				}
			}
		});
	},

	fetchEvents: function () {
		$.ajax({
			type: 'POST',
			data: $('form').serialize(),
			dataType: 'json',
			url: General.getUrl('/fefop/fp-annual-planning/fetch-events'),
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				for (x in response.events) {

					eventObject = $.extend({}, response.events[x]);
					Fefop.FPAnnualPlanning.addEvent(eventObject);
				}

				$('#id_annual_planning').val(response.planning);
				$('#total_students').val(response.students);
				$('#total_cost').val(response.cost);

				if (!General.empty(response.planning))
					$('.portlet .actions button').removeAttr('disabled');
			},
			error: function () {
				Message.msgError('Operasaun la diak', $('form'));
			}
		});
	},

	getColorEvent: function (event) {
		var id = event.scholarity;
		if (General.empty(Fefop.FPAnnualPlanning.scholarities[id])) {

			var color = '#' + Math.floor(Math.random() * 4194303).toString(16); //App.getRandLayoutColorCode();
			Fefop.FPAnnualPlanning.scholarities[id] = color;
		} else
			color = Fefop.FPAnnualPlanning.scholarities[id];

		return color;
	},

	addEvent: function (event) {
		event.backgroundColor = Fefop.FPAnnualPlanning.getColorEvent(event);
		event.className = 'event-annual-planning';

		$('#calendar-planning').fullCalendar('renderEvent', event, true);
	},

	createButtonRemove: function (event) {
		var bt = $('<button />');
		bt.attr('type', 'button').addClass('btn red');
		bt.click(
			function (e) {
				e.stopPropagation();
				alert(event.id);
			}
		);

		var icon = $('<i />');
		icon.addClass('icon-remove-circle');
		bt.append(icon);

		return $('<div />').append(bt).append(event.title).html();
	},

	addNewEvent: function (id) {
		var settings = {
			title: 'Rejistu Formasaun',
			url: '/fefop/fp-annual-planning/new-formation/id/' + (id === undefined ? '' : id),
			data: $('form').serialize(),
			callback: function (modal) {
				modal.css({
					width: '50%',
					marginLeft: '-25%'
				}).find('.modal-body').css('maxHeight', '450px');

				Form.init();
				Fefop.FPAnnualPlanning.initFormNewFormation(modal);
			}
		};

		General.ajaxModal(settings);
	},


	initFormNewFormation: function (modal) {
		Fefop.FPAnnualPlanning.configChangeCategoryScholarity(modal);
		Fefop.FPAnnualPlanning.configChangeScholarity(modal);
		Fefop.FPAnnualPlanning.configCalcTotalStudents(modal);

		var form = modal.find('form');
		submit = function () {
			var obj = {
				callback: function (response) {
					if (response.status) {

						Fefop.FPAnnualPlanning.initCalendar();
						Fefop.FPAnnualPlanning.fetchEvents();

						if (General.empty($('#fk_id_fefop_contract').val())) {

							$('#fk_id_fefop_contract').val(response.contract);
							$('.portlet .actions button').removeAttr('disabled');
						}

						setTimeout(
							function () {
								modal.modal('hide');
							},
							2000
						);
					}
				}
			};

			Form.submitAjax(form, obj);
			return false;
		};

		Form.addValidate(form, submit);
	},

	fetchEvent: function (id) {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: General.getUrl('/fefop/fp-annual-planning/fetch-event/id/' + id),
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				Fefop.FPAnnualPlanning.addEvent(response);
			},
			error: function () {
				Message.msgError('Operasaun la diak', $('form'));
			}
		});
	},

	configChangeCategoryScholarity: function (modal) {
		modal.find('#category').change(
			function () {
				var category = $(this).val();
				if (General.empty(category)) {

					modal.find('#fk_id_perscholarity').val('');
					return false;
				}

				var institute = modal.find('#fk_id_fefpeduinstitution').val();

				url = '/fefop/fp-annual-planning/search-course/category/' + category + '/institute/' + institute;
				General.loadCombo(url, modal.find('#fk_id_perscholarity'));
			}
		);
	},

	configChangeScholarity: function (modal) {
		modal.find('#fk_id_perscholarity').change(
			function () {
				var scholarity = $(this).val();
				if (General.empty(scholarity)) {

					modal.find('#fk_id_unit_cost').val('');
					return false;
				}

				url = '/fefop/fp-annual-planning/fetch-unit-cost/scholarity/' + scholarity;
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: General.getUrl(url),
					beforeSend: function () {
						General.loading(true);
					},
					complete: function () {
						General.loading(false);
					},
					success: function (response) {
						modal.find('#fk_id_unit_cost').val(response.id);
						modal.find('#unit_cost').val(response.cost);

						var totalStudents = modal.find('#total_students').val();
						var totalCost = (totalStudents * response.cost).toFixed(2);
						modal.find('#total_cost').val(totalCost);
					},
					error: function () {
						Message.msgError('Operasaun la diak', $('form'));
					}
				});
			}
		);
	},

	configCalcTotalStudents: function (modal) {
		modal.find('#total_man, #total_woman').change(
			function () {
				var men = General.getFieldFloatValue(modal.find('#total_man'));
				var women = General.getFieldFloatValue(modal.find('#total_woman'));
				var cost = General.getFieldFloatValue(modal.find('#unit_cost'));
				var total = men + women;

				modal.find('#total_students').val(total);

				var totalCost = (total * cost).toFixed(2);
				modal.find('#total_cost').val(totalCost).trigger('change');
			}
		);
	},

	resetForm: function () {
		$('form').populate({}, {
			resetForm: true
		});
		Fefop.FPAnnualPlanning.initCalendar();
	},

	searchInstitute: function () {
		var settings = {
			title: 'Buka Inst. Ensinu',
			url: '/fefop/fp-annual-planning/search-institute/',
			callback: function (modal) {
				modal.css({
					width: '90%',
					marginLeft: '-45%'
				});

				Form.init();
				Fefop.FPAnnualPlanning.initFormSearchInstitute(modal);
			}
		};

		General.ajaxModal(settings);
	},

	initFormSearchInstitute: function (modal) {
		var form = modal.find('form');

		if (!form.length)
			return false;

		submit = function () {
			var data = $(form).serializeArray();
			data.push({
				name: 'list-ajax',
				value: 1
			});

			Message.clearMessages(form);

			$.ajax({
				type: 'POST',
				data: data,
				dataType: 'text',
				url: General.getUrl('/fefop/fp-annual-planning/search-institute-forward'),
				beforeSend: function () {
					App.blockUI(form);
				},
				complete: function () {
					App.unblockUI(form);
				},
				success: function (response) {
					$('#education-institute-list tbody').empty();

					oTable = $('#education-institute-list').dataTable();
					oTable.fnDestroy();

					$('#education-institute-list tbody').html(response);

					callbackClick = function () {
						$('#education-institute-list tbody a.action-ajax').click(
							function () {
								Fefop.FPAnnualPlanning.setInstitute($(this).data('id'), modal);
							}
						);
					};

					General.drawTables('#education-institute-list', callbackClick);
					General.scrollTo('#education-institute-list', 800);
				},
				error: function () {
					Message.msgError('Operasaun la diak', form);
				}
			});
		};

		Form.addValidate(form, submit);
	},

	setInstitute: function (id, modal) {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: General.getUrl('/fefop/fp-annual-planning/fetch-institute/'),
			data: {
				id: id
			},
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				$('form').populate(response, {
					resetForm: false
				});
				General.scrollTo('#breadcrumb');
				$('#institution').trigger('change');
				modal.modal('hide');
			},
			error: function () {
				Message.msgError('Operasaun la diak', modal);
			}
		});
	},

	printContract: function () {
		id = $('#id_annual_planning').val();
		if (General.empty(id))
			return false;

		General.newWindow(General.getUrl('/fefop/fp-annual-planning/print/id/' + id), 'Imprime Planeamentu ba Tinan');
	},

	fetchUnitCost: function () {
		var scholarity = $('#new-formation #fk_id_perscholarity').val();

		$('#new-formation #unit_cost').val('');
		$('#new-formation #fk_id_unit_cost').val('');

		if (!General.empty(scholarity)) {

			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: General.getUrl('/fefop/fp-annual-planning/fetch-unit-cost/'),
				data: {
					scholarity: scholarity
				},
				beforeSend: function () {
					General.loading(true);
				},
				complete: function () {
					General.loading(false);
				},
				success: function (response) {
					if (General.empty(response) && General.empty(response.id))
						return false;

					$('#new-formation #unit_cost').maskMoney('mask', response.cost).trigger('change');
					$('#new-formation #fk_id_unit_cost').val(response.id);
				},
				error: function () {
					Message.msgError('Operasaun la diak', $('#fk_id_unit_cost').closest('form'));

					$('#new-formation #unit_cost').val('');
					$('#new-formation #fk_id_unit_cost').val('');
				}
			});
		}
	},

	calcTotalFormation: function () {
		var totalStudents = $('#new-formation #total_students').val();
		var unitCost = $('#new-formation #unit_cost').maskMoney('unmasked')[0];

		var total = unitCost * totalStudents;
		$('#new-formation #total_cost').maskMoney('mask', total);

	}
};

$(document).ready(
	function () {
		Fefop.FPAnnualPlanning.init();
	}
);