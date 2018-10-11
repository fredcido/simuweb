Note = {

	init: function () {
		Note.searchNotes();

		setInterval(
			Note.searchNotes,
			60 * 1000
		);
	},

	searchNotes: function (noSound) {
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: General.getUrl('/note/list-notes-to-user/'),
			success: function (response) {
				if (response.total > 0)
					Note.buildNotes(response, noSound);
				else
					Note.clearAll();
			}
		});
	},

	clearAll: function () {
		$('#header_notification_bar').remove();
	},

	buildNotes: function (data, noSound) {
		liMain = $('<li />');
		liMain.addClass('dropdown')
			.attr('id', 'header_notification_bar');

		aToggle = $('<a />');
		aToggle.attr('data-target', '#')
			.attr('href', '/')
			.addClass('dropdown-toggle')
			.attr('data-toggle', 'dropdown');

		i = $('<i />');
		i.addClass('icon-warning-sign');

		span = $('<span />');
		span.addClass('badge').html(data.total);

		aToggle.append(i).append(span);
		liMain.append(aToggle);

		ulContainer = $('<ul />');
		ulContainer.addClass('dropdown-menu extended notification');

		liTitle = $('<li />');
		pTitle = $('<p />');
		pTitle.html('Ita iha ' + data.total + ' avisu sira foun');

		liTitle.append(pTitle);
		ulContainer.append(liTitle);

		liContainer = $('<li />');
		ulNotes = $('<ul />');
		ulNotes.addClass('dropdown-menu-list scroller').height('250px');

		liContainer.append(ulNotes);
		ulContainer.append(liContainer);

		for (i in data.notes) {

			note = data.notes[i];

			li = $('<li />');
			a = $('<a />');
			a.attr('href', 'javascript:;');
			Note.attachDetail(a, note.id_note);

			span = $('<span />');
			span.addClass('label label-' + (note.level == 1 ? 'warning' : 'important'));

			i = $('<i />');
			i.addClass('icon-' + (note.level == 1 ? 'bullhorn' : 'bolt'));

			span.append(i);

			spanTime = $('<span />');
			spanTime.addClass('time');
			spanTime.html(note.date);

			a.append(span).append(' ' + note.title + ' - ').append(spanTime);
			li.append(a);
			ulNotes.append(li);
		}

		if (data.total > 10) {

			liExternal = $('<li />');
			liExternal.addClass('external');

			aExternal = $('<a / >');
			aExternal.attr('href', 'javascript:;').click(Note.listAll);

			iExternal = $('<i />');
			iExternal.addClass('m-icon-swapright');

			aExternal.append('Hare hotu-hotu').append(iExternal);
			liExternal.append(aExternal);

			ulContainer.append(liExternal);
		}

		liMain.append(ulContainer);
		Note.clearAll();
		$('.header .nav').prepend(liMain);

		aToggle.dropdown();
		liMain.pulsate({
			color: "#fdbe41",
			reach: 50,
			repeat: 20,
			speed: 100,
			glow: true
		});

		ulNotes.slimScroll({
			size: '7px',
			color: '#a1b2bd',
			height: '250px',
			alwaysVisible: false,
			railVisible: false,
			disableFadeOut: true
		});

		if (!noSound)
			$('#message-audio').get(0).play();
	},

	attachDetail: function (a, id) {
		a.click(
			function () {
				Note.detailNote(id);
			}
		);
	},

	detailNote: function (id) {
		var settings = {
			title: 'Avizu',
			url: '/note/detail/id/' + id,
			buttons: [{
				css: 'blue',
				text: 'Remata',
				click: function (modal) {
					Note.finishNote(id, modal);
				}
			}]
		};

		General.ajaxModal(settings);
	},

	finishNote: function (id, modal) {
		container = modal.find('.modal-body');
		$.ajax({
			type: 'POST',
			data: {
				note: id
			},
			dataType: 'json',
			url: General.getUrl('/note/finish-note/'),
			beforeSend: function () {
				App.blockUI(container);
			},
			complete: function () {
				App.unblockUI(container);
			},
			success: function (response) {
				if (!response.status) {

					var msg = response.description.length ? response.description[0].message : 'Operasaun la diak';
					Message.msgError(msg, container);

				} else {

					Message.msgSuccess('Avizu le tiha ona', container);
					Note.clearAll();
					Note.searchNotes(true);
				}
			}
		});
	},

	newNote: function () {
		var settings = {
			title: 'Avizu Foun',
			url: '/note/form/',
			buttons: [{
				css: 'blue',
				text: 'Halot',
				click: function (modal) {
					modal.find('form').submit();
				}
			}],
			callback: function (modal) {
				form = modal.find('form');

				submit = function () {
					if (General.empty($('#users').val()) && General.empty($('#groups').val())) {

						Message.msgError('Tenki hili uzuariu ka grupu atu rejistu avizu', form);
						$('#users, #groups').closest('.control-group').addClass('error');
						return false;
					}

					$('#users, #groups').closest('.control-group').removeClass('error');

					var message = CKEDITOR.instances.message.getData();
					if (General.empty(message)) {

						Message.msgError('Tenki prensi mensajen atu rejistu avizu', form);
						return false;
					}

					$('#message').val(message);
					App.blockUI(form);

					var obj = {
						callback: function (response) {
							App.unblockUI(form);

							if (response.status) {

								Message.msgSuccess('Operasaun diak', form);
								modal.modal('hide');
								Note.searchNotes();
							}
						}
					};

					Form.submitAjax(form, obj);
					return false;
				};

				Form.addValidate(form, submit);
				Form.init();

				CKEDITOR.replace('message', {
					toolbar: [
						['Bold', 'Italic', '-', 'NumberedList', 'BulletedList']
					]
				});
			}
		};

		General.ajaxModal(settings);
	},

	listAll: function () {
		var settings = {
			title: 'Avizu Hotu-hotu',
			url: '/note/list-all/',
			callback: function (modal) {
				Note.listByUser();
				Note.listToUser();
			}
		};

		General.ajaxModal(settings);
	},

	listToUser: function () {
		General.loadTable('#note-to-user', '/note/list-to-user-rows/');
	},

	listByUser: function () {
		General.loadTable('#note-by-user', '/note/list-by-user-rows/');
	}
};

$(document).ready(
	function () {
		Note.init();
	}
);