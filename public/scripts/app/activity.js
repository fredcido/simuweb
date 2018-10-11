Activity = {
	init: function () {
		Activity.searchActivities();

		setInterval(
			Activity.searchActivities,
			60 * 1000
		);
	},

	searchActivities: function () {
		$.ajax({
			type: 'GET',
			dataType: 'html',
			url: General.getUrl('/index/list-activities/'),
			success: function (response) {
        Activity.load(response);
			}
		});
  },
  
  load: function(response) {
    var container = $('#user-activities');
    $('.dropdown-menu-list', container).html(response);

    var count = $('.dropdown-menu-list li', container).length;
    if (count > 0) {
      $('.badge, .count', container).html(count);
      container.removeClass('hide');
    } else {
      container.addClass('hide');
    }
  },

  trigger: function(type, id) {
    if ('A' == type) {
      // Client.Case.appointment(id);
      General.go('/client/case/edit/id/' + id);
    } else if ('T' == type) {
      General.go('/client/case/edit/id/' + id);
      // Client.Case.timeline(id);
    }
  },

  all: function() {
    $.ajax({
			type: 'GET',
			dataType: 'json',
			url: General.getUrl('/index/all-activities/'),
			success: function (response) {
        Activity.initCalendar(response);
			}
		});
  },

  initCalendar: function(data) {
    var modal = $('#modal-root').clone();
    modal.find('h3').html('Atividade hotu-hotu');

    modal.modal();

    var container = $('<div />');
    modal.find('.modal-body').append(container).css({height: '650px', maxHeight: '650px'});

    setTimeout(function(){
      container.fullCalendar({
        header: {
          left: 'prev,next today',
          center: 'title',
          right: 'month,basicWeek,basicDay'
        },
        navLinks: true,
        editable: true,
        eventLimit: true,
        events: data,
        eventClick: function(event) {
          modal.modal('hide');
          Activity.trigger(event.type, event.id);
        }
      });
    }, 5 * 100);
  }
};

$(document).ready(
	function () {
		Activity.init();
	}
);