$(function () {
	$('[data-toggle="tooltip"]').tooltip();
});


//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function () {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	var url = window.location;
	var element = $('ul.nav a').filter(function () {
		return this.href == url;
	}).addClass('active').parent().parent().addClass('in').parent();
	if (element.is('li')) {
		element.addClass('active');
		element.find('.nav.in').addClass('show');
	}

	//console.log(element);


	$(document).on('click', '#mobile-menu-toggle a', function () {
		$('.navbar-default.sidebar').toggleClass('dblock');
	});

});
