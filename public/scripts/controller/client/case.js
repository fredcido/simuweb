Client = window.Client || {};

Client.Case = {

	barrierSelected: null,
	modalCurrent: null,
	caseCurrent: null,

	init: function () {
		this.initForm();
		this.initFormSearch();
	},

	initForm: function () {
		if (!$('#container-case').length)
			return false;

		Portlet.init('#container-case');

		this.configInformation();
	},

	initFormSearch: function () {
		var form = $('form#search');

		if (!form.length)
			return false;

		submit = function () {
			var pars = $(form).serialize();
			Message.clearMessages(form);

			$.ajax({
				type: 'POST',
				data: pars,
				dataType: 'text',
				url: form.attr('action'),
				beforeSend: function () {
					General.loading(true);
				},
				complete: function () {
					General.loading(false);
				},
				success: function (response) {
					$('#case-list tbody').empty();

					oTable = $('#case-list').dataTable();
					oTable.fnDestroy();

					$('#case-list tbody').html(response);

					General.drawTables('#case-list');
					General.scrollTo('#case-list', 800);
				},
				error: function () {
					Message.msgError('Operasaun la diak', form);
				}
			});
		}

		Form.addValidate(form, submit);
		Form.handleClientSearch(form);
	},

	configInformation: function () {
		var form = $('form#clientformactionplan');

		if (!form.length)
			return false;

		submit = function () {
			if (!$('#barrier-list tbody tr').length) {

				Message.msgError('Tenki tau Barreiras ho Intervensaun sira.', $('#information form'));
				return false;
			}

			var valid = true;

			$('#barrier-list .control-group').removeClass('error');
			$('#barrier-list select').each(
				function () {
					if (General.empty($(this).val())) {

						valid = false;
						$(this).closest('.control-group').addClass('error');
					}
				}
			);

			if (!valid) {

				Message.msgError('Tenki tau dadus hotu-hotu ba iha tabela kraik.', $('#information form'));
				return false;
			}

			var obj = {
				callback: function (response) {
					if (response.status) {

						if (General.empty($('#id_action_plan').val())) {

							$('#information form').find('#id_action_plan').val(response.id);
							window.history.replaceState({}, "Case Edit", General.getUrl("/client/case/edit/id/" + response.id));

							$('#container-case .dynamic-portlet').each(
								function () {
									dataUrl = $(this).attr('data-url');
									$(this).attr('data-url', dataUrl + response.id);
								}
							);

							// Release all the steps and go to step 1
							Portlet.releaseSteps(1, true);

							$('#print_case').removeAttr('disabled');
						}

						Client.Case.listActionBarriers();
						Client.Case.listBarriers();
						Client.Case.reloadFinish();
					}
				}
			};

			Form.submitAjax(form, obj);
			return false;
		};

		Form.addValidate(form, submit);
		Client.Case.listBarriers();
	},

	listBarriers: function () {
		if (General.empty($('#id_action_plan').val()))
			return false;

		$.ajax({
			type: 'GET',
			dataType: 'text',
			url: General.getUrl('/client/case/list-barriers/id/' + $('#id_action_plan').val()),
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				$('#barrier-list tbody').html(response);
				Form.init();
			},
			error: function () {
				Message.msgError('Operasaun la diak', form);
			}
		});
	},

	addBarrier: function () {
		if ($('#barrier-list td.dataTables_empty').length)
			$('#barrier-list tbody').empty();

		$.ajax({
			type: 'GET',
			dataType: 'text',
			url: General.getUrl('/client/case/add-barrier/'),
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				$('#barrier-list tbody').append(response);
				General.scrollTo('#barrier-list', 800);

				$('#barrier-list').addClass('has-barrier');

				Form.init();
			},
			error: function () {
				Message.msgError('Operasaun la diak', form);
			}
		});
	},

	checkClientData: function (id) {
		var settings = {
			title: 'Loke Kazu Foun',
			url: '/client/case/new-case/id/' + id,
			buttons: [{
					css: 'red',
					text: 'Kazu Foun',
					click: function (modal) {
						if (modal.find('table td a .icon-warning-sign').length)
							Message.msgError('Haree kriteria hotu! La bele loke kazu foun, uluk kompleta dadu sira iha ne\'e.', modal.find('.modal-body'));
						else
							General.go('/client/case/form/client/' + id);
					}
				},
				{
					css: 'blue',
					text: 'Atualiza Dados',
					click: function (modal) {
						General.go('/client/client/edit/id/' + id);
					}
				}
			],
			callback: function (modal) {
				if (modal.find('table td a .icon-warning-sign').length)
					Message.msgError('Haree kriteria hotu! La bele loke kazu foun, uluk kompleta dadu sira iha ne\'e.', modal.find('.modal-body'));
			}
		};

		General.ajaxModal(settings);
	},

	removeBarrier: function (link) {
		remove = function () {
			tr = $(link).closest('tr');

			id = tr.find('.id-barrier').val();

			if (General.empty(id))
				tr.remove();
			else {

				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: General.getUrl('/client/case/delete-barrier/'),
					data: {
						id_barrier: id,
						id: $('#id_action_plan').val()
					},
					beforeSend: function () {
						App.blockUI('#barrier-list');
					},
					complete: function () {
						App.unblockUI('#barrier-list');
					},
					success: function (response) {
						if (response.status) {

							tr.remove();
							Client.Case.listActionBarriers();
							Client.Case.reloadFinish();

						} else
							Message.msgError('Operasaun la diak', '#information form');
					},
					error: function () {
						Message.msgError('Operasaun la diak', '#information form');
					}
				});
			}
		};

		General.confirm('Ita hakarak hamoos Barreira ida ne\'e ?', 'Hamoos barreira', remove);
	},

	loadBarriers: function (combo) {
		barrierCombo = $(combo).closest('td').next().find('select').eq(0);

		if (General.empty($(combo).val())) {

			$(barrierCombo).val('').attr('disabled', true);
			return false;
		}

		url = '/client/case/search-barriers/id/' + $(combo).val();
		General.loadCombo(url, barrierCombo);
		return true;
	},

	loadIntervention: function (combo) {
		barrierCombo = $(combo).closest('td').next().find('select').eq(0);

		if (General.empty($(combo).val())) {

			$(barrierCombo).val('').attr('disabled', true);
			return false;
		}

		url = '/client/case/search-interventions/id/' + $(combo).val();
		General.loadCombo(url, barrierCombo);
		return true;
	},

	validateBarriers: function (select) {
		if (General.empty($(select).val()))
			return false;

		values = [];
		tr = $(select).closest('tr').eq(0);
		tr.find(':input.valid-barrier').each(
			function () {
				values.push($(this).val());
			}
		);

		values = values.join('|');

		valid = true;
		$(select).closest('table').find('tr').not(tr).each(
			function () {
				compare = [];
				$(this).find(':input.valid-barrier').each(
					function (index, element) {
						compare.push($(element).val());
					}
				);

				compare = compare.join('|');

				if (compare == values)
					valid = false;

				return valid;
			}
		);

		if (!valid) {

			Message.msgError('La bele hili Intervensaun fila fali!', $(select).closest('form'));

			setTimeout(
				function () {
					$(select).val('').trigger('change');
				},
				1000
			);
		}
	},

	configDevelopment: function () {
		var form = $('#development form');

		if (!form.length)
			return false;

		submit = function () {
			var dataForm = [];
			dataTable = $('#barrier-action-list').dataTable();
			$('select:not(:disabled)', dataTable.fnGetNodes()).each(
				function () {
					dataForm.push({
						name: 'status[' + $(this).attr('id') + ']',
						value: $(this).val()
					});
				}
			);

			$('input.date-finish:not(:disabled)', dataTable.fnGetNodes()).each(
				function () {
					dataForm.push({
						name: 'date_finish[' + $(this).data('id') + ']',
						value: $(this).val()
					});
				}
			);

			var obj = {
				callback: function (response) {
					if (response.status) {

						$('#development #clear').trigger('click');
						Client.Case.listActionBarriers();
						Client.Case.reloadFinish();
					}
				},
				data: dataForm
			};

			Form.submitAjax(form, obj);
			return false;
		};

		Form.addValidate(form, submit);
		Client.Case.listActionBarriers();
	},

	listActionBarriers: function () {
		if (!$('#barrier-action-list').length)
			return false;

		$.ajax({
			type: 'GET',
			dataType: 'text',
			url: General.getUrl('/client/case/list-action-barriers/id/' + $('#id_action_plan').val()),
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				objTable = $('#barrier-action-list').dataTable();
				objTable.fnDestroy();

				$('#barrier-action-list tbody').html(response);
				General.drawTables($('#barrier-action-list'));
				Client.Case.reloadFinish();
				Form.init();
			},
			error: function () {
				Message.msgError('Operasaun la diak', form);
			}
		});
	},

	setStatus: function (flag, item) {
		if ($(item).closest('table').dataTable) {

			dataTable = $(item).closest('table').dataTable();
			nodes = $('select:not(:disabled)', dataTable.fnGetNodes());

		} else
			nodes = $(item).closest('table').find('tbody select:not(:disabled)');

		$(nodes).each(
			function () {
				$(this).val(flag).trigger('change');
			}
		);
	},

	printCase: function () {
		General.newWindow(General.getUrl('/client/case/print/id/' + $('#id_action_plan').val()), 'Imprime Jestaun Kazu');
	},

	caseNote: function (id) {
		this.caseCurrent = id;

		var settings = {
			title: 'Nota Kazu sira',
			url: '/client/case/case-note/id/' + id,
			callback: function (modal) {
				Client.Case.configFormCase(modal);
				Client.Case.listCase(modal);
			}
		};

		General.ajaxModal(settings);
	},

	listCase: function (modal) {
		if (modal)
			modal.find('.nav-tabs a').eq(0).trigger('click');

		General.loadTable('#case-note-list', '/client/case/list-note-rows/id/' + Client.Case.caseCurrent);
	},

	configFormCase: function (modal) {
		var form = modal.find('form');

		if (!form.length)
			return false;

		submit = function () {
			App.blockUI(form);

			var obj = {
				callback: function (response) {
					if (response.status) {

						form.find('#clear').trigger('click');
						Client.Case.listCase(modal);
						Client.Case.reloadFinish();
					}

					App.unblockUI(form);
				}
			};

			Form.submitAjax(form, obj);
			return false;
		};

		Form.addValidate(form, submit);
		Form.initReset();
	},

	detailNote: function (id) {
		var settings = {
			title: 'Nota Kazu',
			url: '/client/case/case-note-detail/id/' + id
		};

		General.ajaxModal(settings);
	},

	editNote: function (id) {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: General.getUrl('/client/case/fetch-case-note/id/' + id),
			beforeSend: function () {
				App.blockUI('#list-note');
			},
			complete: function () {
				App.unblockUI('#list-note');
			},
			success: function (response) {
				$('#register-note form').populate(response, {
					resetForm: true
				});
				$('#register-note').closest('.tabbable').find('.nav-tabs a').eq(1).trigger('click');
			},
			error: function () {
				Message.msgError('Operasaun la diak', '#list-note');
			}
		});
	},

	deleteNote: function (id) {
		remove = function () {
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: General.getUrl('/client/case/delete-case-note/'),
				data: {
					id: id
				},
				beforeSend: function () {
					App.blockUI('#list-note');
				},
				complete: function () {
					App.unblockUI('#list-note');
				},
				success: function (response) {
					Client.Case.listCase();
					Client.Case.reloadFinish();
				},
				error: function () {
					Message.msgError('Operasaun la diak', '#list-note');
				}
			});
		};

		General.confirm('Ita hakarak hamoos nota kazu ida ne\'e ?', 'Hamoos nota kazu', remove);
	},

	appointment: function (id) {
		this.caseCurrent = id;

		var settings = {
			title: 'Audiensia sira',
			url: '/client/case/appointment/id/' + id,
			callback: function (modal) {
				Client.Case.configFormAppointment(modal);
				Client.Case.listAppointment(modal);
				Form.init();
			}
		};

		General.ajaxModal(settings);
	},

	listAppointment: function (modal) {
		if (modal)
			modal.find('.nav-tabs a').eq(0).trigger('click');

		General.loadTable('#appointment-list', '/client/case/list-appointment-rows/id/' + Client.Case.caseCurrent);
	},

	configFormAppointment: function (modal) {
		var form = modal.find('form');

		if (!form.length)
			return false;

		submit = function () {
			if (!$('#appointment-objective-list tbody tr').length) {

				Message.msgError('Tenki tau intensaun ba audiensia sira.', $('#register-appointment form'));
				return false;
			}

			var valid = true;

			$('#appointment-objective-list .control-group').removeClass('error');
			$('#appointment-objective-list select').each(
				function () {
					if (General.empty($(this).val())) {

						valid = false;
						$(this).closest('.control-group').addClass('error');
					}
				}
			);

			if (!valid) {

				Message.msgError('Tenki tau dadus hotu-hotu ba iha tabela kraik.', $('#register-appointment form'));
				return false;
			}

			App.blockUI(form);

			var obj = {
				callback: function (response) {
					if (response.status) {

						form.find('#clear').trigger('click');
						$('#appointment-objective-list tbody').empty();
						Client.Case.listAppointment(modal);
						Client.Case.reloadFinish();
					}

					App.unblockUI(form);
				}
			};

			Form.submitAjax(form, obj);
			return false;
		};

		Form.addValidate(form, submit);
		Form.initReset();
		$(form).on('clear',
			function () {
				$('#appointment_filled').parents('.toogle-container').addClass('deactivate');
				$('#appointment_filled').attr('disabled', 1).trigger('change');
				$('#appointment-objective-list tbody').empty();
			}
		);
	},

	addAppointmentObjective: function () {
		$.ajax({
			type: 'GET',
			dataType: 'text',
			url: General.getUrl('/client/case/add-appointment-objective/'),
			beforeSend: function () {
				App.blockUI($('#appointment-objective-list'));
			},
			complete: function () {
				App.unblockUI($('#appointment-objective-list'));
			},
			success: function (response) {
				$('#appointment-objective-list tbody').append(response);

				Form.init();
			},
			error: function () {
				Message.msgError('Operasaun la diak', form);
			}
		});
	},

	removeAppointmentObjective: function (link) {
		remove = function () {
			tr = $(link).closest('tr');
			id = tr.find('select.objective').val();

			appointment = $('#id_appointment').val();

			if (General.empty(appointment))
				tr.remove();
			else {

				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: General.getUrl('/client/case/delete-appointment-objective/'),
					data: {
						id_objective: id,
						id: appointment
					},
					beforeSend: function () {
						App.blockUI('#appointment-objective-list');
					},
					complete: function () {
						App.unblockUI('#appointment-objective-list');
					},
					success: function (response) {
						if (response.status) {

							tr.remove();

						} else
							Message.msgError('Operasaun la diak', '#register-appointment form');
					},
					error: function () {
						Message.msgError('Operasaun la diak', '#register-appointment form');
					}
				});
			}
		};

		General.confirm('Ita hakarak hamoos Intensaun ba Audiensia ida ne\'e ?', 'Hamoos intensaun ba audiensia', remove);
	},

	printAppointment: function (id) {
		General.newWindow(General.getUrl('/client/case/print-appointment/id/' + id), 'Imprime Audiensia Sira');
	},

	editAppointment: function (id) {
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: General.getUrl('/client/case/fetch-appointment/id/' + id),
			beforeSend: function () {
				App.blockUI('#list-note');
			},
			complete: function () {
				App.unblockUI('#appointment-list');
			},
			success: function (response) {
				$('#register-appointment form').populate(response, {
					resetForm: true
				});
				$('#register-appointment').closest('.tabbable').find('.nav-tabs a').eq(1).trigger('click');

				$('#appointment_filled').parents('.toogle-container').removeClass('deactivate');
				$('#appointment_filled').removeAttr('disabled').trigger('change');

				Client.Case.listAppointmentObjective(response.id_appointment);
			},
			error: function () {
				Message.msgError('Operasaun la diak', '#appointment-list');
			}
		});
	},

	listAppointmentObjective: function (id) {
		$.ajax({
			type: 'GET',
			dataType: 'text',
			url: General.getUrl('/client/case/list-appointment-objective/id/' + id),
			beforeSend: function () {
				App.blockUI('#appointment-objective-list');
			},
			complete: function () {
				App.unblockUI('#appointment-objective-list');
			},
			success: function (response) {
				$('#appointment-objective-list tbody').empty().append(response);
				Form.init();
			},
			error: function () {
				Message.msgError('Operasaun la diak', $('register-appointment'));
			}
		});
	},

	reloadFinish: function () {
		//General.loading( true );

		$('#finish-list tbody').load(
			General.getUrl('/client/case/list-finish/id/' + $('#id_action_plan').val()), {},
			function () {
				if ($('#button-finish').hasClass('disabled') || $('#finish-list td i.icon-warning-sign').length)
					$('#button-finish').attr('disabled', true);
				else
					$('#button-finish').removeAttr('disabled');

				if ($('#finish-list td i.icon-warning-sign').length)
					$('#print-certificate').addClass('hide');
				else
					$('#print-certificate').removeClass('hide');

				General.loading(false);
			}
		);
	},

	finishCase: function () {
		var container = $('#finish .box-content');
		if ($('#finish-list td i.icon-warning-sign').length) {

			Message.msgError('Erro: La bele remata kazu! Haree kriterio sira.', container);
			return false;
		}

		$.ajax({
			type: 'POST',
			data: {
				id: $('#id_action_plan').val()
			},
			dataType: 'json',
			url: General.getUrl('/client/case/finish-case/'),
			beforeSend: function () {
				General.loading(true);
			},
			complete: function () {
				General.loading(false);
			},
			success: function (response) {
				if (!response.status) {

					var msg = response.message.length ? response.message[0].message : 'Operasaun la diak';
					Message.msgError(msg, container);

				} else {
					$('#print-certificate').removeClass('hide').trigger('click');
					history.go(0);
				}
			},
			error: function () {
				Message.msgError('Operasaun la diak', container);
			}
		});

		return false;
	},

	timeline: function (barrier) {
		this.barrierSelected = barrier;

		var settings = {
			title: 'Liña Tempu',
			url: '/client/case/timeline/barrier/' + barrier + '/id/' + $('#id_action_plan').val(),
			callback: function (modal) {
				modal.css({
					width: '90%',
					marginLeft: '-45%'
				});

				Client.Case.listTimeline(barrier);
				Client.Case.configTimeline(modal);
				Client.Case.modalCurrent = modal;
				Form.init();
			}
		};

		General.ajaxModal(settings);
	},

	listTimeline: function (barrier, modal) {
		if (modal)
			modal.find('.nav-tabs a').eq(0).trigger('click');

		var action = $('#id_action_plan').val();

		General.loadTable('#timeline-list', '/client/case/list-timeline-rows/barrier/' + barrier + '/id/' + action);
	},

	configTimeline: function (modal) {
		var form = modal.find('form');

		if (!form.length)
			return false;

		submit = function () {
			App.blockUI(form);

			var obj = {
				callback: function (response) {
					if (response.status) {

						form.find('#clear').trigger('click');
						Client.Case.listTimeline(modal);
					}

					App.unblockUI(form);
				}
			};

			Form.submitAjax(form, obj);
			return false;
		};

		Form.addValidate(form, submit);
		Form.initReset();
	},

	searchJob: function (barrier) {
		this.barrierSelected = barrier;

		var settings = {
			title: 'Buka Empregu',
			url: '/client/case/job-barrier/barrier/' + barrier + '/id/' + $('#id_action_plan').val(),
			callback: function (modal) {
				modal.css({
					width: '90%',
					marginLeft: '-45%'
				});

				Client.Case.listJobBarrier();
				Client.Case.modalCurrent = modal;
				General.setTabsAjax(modal.find('.tabbable'), Client.Case.configSearchJob);
			}
		};

		General.ajaxModal(settings);
	},

	configSearchJob: function (pane) {
		form = pane.find('form');

		submit = function () {
			var data = $(form).serializeArray();
			data.push({
				name: 'list-ajax',
				value: 1
			});

			Message.clearMessages(form);

			$.ajax({
				type: 'POST',
				data: $.param(data),
				dataType: 'text',
				url: form.attr('action'),
				beforeSend: function () {
					App.blockUI(form);
				},
				complete: function () {
					App.unblockUI(form);
				},
				success: function (response) {
					$('#vacancy-list tbody').empty();

					oTable = $('#vacancy-list').dataTable();
					oTable.fnDestroy();

					$('#vacancy-list tbody').html(response);

					callbackClick = function () {
						$('#vacancy-list tbody a.action-ajax').click(
							function () {
								Client.Case.setClientVacancy($(this).data('id'));
							}
						);
					};

					General.drawTables('#vacancy-list', callbackClick);
					General.scrollTo('#vacancy-list', 800);
				},
				error: function () {
					Message.msgError('Operasaun la diak', form);
				}
			});
		};

		Form.addValidate(form, submit);

		$('#open_date').daterangepicker({
				format: 'dd/MM/yyyy',
				separator: ' to\'o '
			},
			function (start, end) {
				$('#open_date').val(start.toString('dd/MM/yyyy'));
				$('#close_date').val(end.toString('dd/MM/yyyy'));
			}
		);
	},

	setClientVacancy: function (vacancy) {
		var container = $('#search-job');

		$.ajax({
			type: 'POST',
			data: {
				id: $('#id_action_plan').val(),
				vacancy: vacancy,
				barrier: Client.Case.barrierSelected
			},
			dataType: 'json',
			url: General.getUrl('/client/case/client-vacancy/'),
			beforeSend: function () {
				App.blockUI(container);
			},
			complete: function () {
				App.unblockUI(container);
			},
			success: function (response) {
				if (!response.status) {

					var msg = response.message.length ? response.message[0].message : 'Operasaun la diak';
					Message.msgError(msg, container);

				} else {

					Client.Case.listJobBarrier();
					Client.Case.modalCurrent.find('.nav-tabs a').eq(0).trigger('click');
					Message.msgSuccess('Kliente iha lista kandidatu ba vaga empregu', $('#list-job-barrier'));
				}
			},
			error: function () {
				Message.msgError('Operasaun la diak', container);
			}
		});

		return false;
	},

	listJobBarrier: function () {
		General.loadTable('#job-barrier-list', '/client/case/list-job-barrier-rows/barrier/' + Client.Case.barrierSelected);
	},

	searchClass: function (barrier) {
		this.barrierSelected = barrier;

		var settings = {
			title: 'Buka Klase Formasaun',
			url: '/client/case/class-barrier/barrier/' + barrier + '/id/' + $('#id_action_plan').val(),
			callback: function (modal) {
				modal.css({
					width: '90%',
					marginLeft: '-45%'
				});

				Client.Case.listClassBarrier();
				Client.Case.modalCurrent = modal;
				General.setTabsAjax(modal.find('.tabbable'), Client.Case.configSearchClass);
			}
		};

		General.ajaxModal(settings);
	},

	configSearchClass: function (pane) {
		form = pane.find('form');

		submit = function () {
			var data = $(form).serializeArray();
			data.push({
				name: 'list-ajax',
				value: 1
			});

			Message.clearMessages(form);

			$.ajax({
				type: 'POST',
				data: $.param(data),
				dataType: 'text',
				url: form.attr('action'),
				beforeSend: function () {
					App.blockUI(form);
				},
				complete: function () {
					App.unblockUI(form);
				},
				success: function (response) {
					$('#class-list tbody').empty();

					oTable = $('#class-list').dataTable();
					oTable.fnDestroy();

					$('#class-list tbody').html(response);

					callbackClick = function () {
						$('#class-list tbody a.action-ajax').click(
							function () {
								Client.Case.setClientClass($(this).data('id'));
							}
						);
					};

					General.drawTables('#class-list', callbackClick);
					General.scrollTo('#class-list', 800);
				},
				error: function () {
					Message.msgError('Operasaun la diak', form);
				}
			});
		};

		Form.addValidate(form, submit);

		$('#open_date').daterangepicker({
				format: 'dd/MM/yyyy',
				separator: ' to\'o '
			},
			function (start, end) {
				$('#open_date').val(start.toString('dd/MM/yyyy'));
				$('#close_date').val(end.toString('dd/MM/yyyy'));
			}
		);
	},

	listClassBarrier: function () {
		General.loadTable('#class-barrier-list', '/client/case/list-class-barrier-rows/barrier/' + Client.Case.barrierSelected);
	},

	setClientClass: function (idClass) {
		var container = $('#search-class');

		$.ajax({
			type: 'POST',
			data: {
				id: $('#id_action_plan').val(),
				idClass: idClass,
				barrier: Client.Case.barrierSelected
			},
			dataType: 'json',
			url: General.getUrl('/client/case/client-class/'),
			beforeSend: function () {
				App.blockUI(container);
			},
			complete: function () {
				App.unblockUI(container);
			},
			success: function (response) {
				if (!response.status) {

					var msg = response.message.length ? response.message[0].message : 'Operasaun la diak';
					Message.msgError(msg, container);

				} else {

					Client.Case.listClassBarrier();
					Client.Case.modalCurrent.find('.nav-tabs a').eq(0).trigger('click');
					Message.msgSuccess('Kliente iha lista kandidatu ba klase formasaun', $('#list-class-barrier'));
				}
			},
			error: function () {
				Message.msgError('Operasaun la diak', container);
			}
		});

		return false;
	},

	cancelCase: function () {
		cancel = function () {
			var settings = {
				title: 'Kansela Kazy',
				url: '/client/case/cancel/id/' + $('#id_action_plan').val(),
				buttons: [{
					css: 'blue',
					text: 'Halot',
					click: function (modal) {
						modal.find('form').submit();
					}
				}],
				callback: function (modal) {
					var form = modal.find('form');
					submit = function () {
						var obj = {
							callback: function (response) {
								if (response.status) {

									modal.modal('hide');
									history.go(0);
								}
							}
						};

						Form.submitAjax(form, obj);
						return false;
					}

					Form.addValidate(form, submit);
				}
			};

			General.ajaxModal(settings);
		};

		General.confirm('Ita hakarak kansela kazu ida ne\'e ?', 'Kansela Kazu', cancel);
	},

	documentsCase: function () {
		var data = {
			client: $('#fk_id_perdata').val(),
			'case': $('#id_action_plan').val()
		};

		File.manager(data);
	},

	searchJobTraining: function (barrier) {
		this.barrierSelected = barrier;

		var settings = {
			title: 'Buka Job Training',
			url: '/client/case/job-training-barrier/barrier/' + barrier + '/id/' + $('#id_action_plan').val(),
			callback: function (modal) {
				modal.css({
					width: '90%',
					marginLeft: '-45%'
				});

				Client.Case.listJobTrainingBarrier();
				Client.Case.modalCurrent = modal;
				General.setTabsAjax(modal.find('.tabbable'), Client.Case.configSearchJobTraining);
			}
		};

		General.ajaxModal(settings);
	},

	configSearchJobTraining: function (pane) {
		form = pane.find('form');

		submit = function () {
			var data = $(form).serializeArray();
			data.push({
				name: 'list-ajax',
				value: 1
			});

			Message.clearMessages(form);

			$.ajax({
				type: 'POST',
				data: $.param(data),
				dataType: 'text',
				url: form.attr('action'),
				beforeSend: function () {
					App.blockUI(form);
				},
				complete: function () {
					App.unblockUI(form);
				},
				success: function (response) {
					$('#job-training-list tbody').empty();

					oTable = $('#job-training-list').dataTable();
					oTable.fnDestroy();

					$('#job-training-list tbody').html(response);

					callbackClick = function () {
						$('#job-training-list tbody a.action-ajax').click(
							function () {
								Client.Case.setClientJobTraining($(this).data('id'));
							}
						);
					};

					General.drawTables('#job-training-list', callbackClick);
					General.scrollTo('#job-training-list', 800);
				},
				error: function () {
					Message.msgError('Operasaun la diak', form);
				}
			});
		};

		Form.addValidate(form, submit);
	},

	listJobTrainingBarrier: function () {
		General.loadTable('#job-training-barrier-list', '/client/case/list-job-training-barrier-rows/barrier/' + Client.Case.barrierSelected);
	},

	setClientJobTraining: function (idJobTraining) {
		var container = $('#search-job-training');

		$.ajax({
			type: 'POST',
			data: {
				id: $('#id_action_plan').val(),
				idJobTraining: idJobTraining,
				barrier: Client.Case.barrierSelected
			},
			dataType: 'json',
			url: General.getUrl('/client/case/client-job-training/'),
			beforeSend: function () {
				App.blockUI(container);
			},
			complete: function () {
				App.unblockUI(container);
			},
			success: function (response) {
				if (!response.status) {

					var msg = response.message.length ? response.message[0].message : 'Operasaun la diak';
					Message.msgError(msg, container);

				} else {

					Client.Case.listClassBarrier();
					Client.Case.modalCurrent.find('.nav-tabs a').eq(0).trigger('click');
					Message.msgSuccess('Kliente iha lista kandidatu ba job training', $('#list-job-training-barrier'));
				}
			},
			error: function () {
				Message.msgError('Operasaun la diak', container);
			}
		});

		return false;
	}
};

$(document).ready(
	function () {
		Client.Case.init();
	}
);