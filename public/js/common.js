$(document).ready(function () {

	// Phone mask
	var phoneMask = "+7 (999) 999-99-99";
	var phonePrefix = "+7 ";
	var navigationKeys = [8, 9, 13, 27, 35, 36, 37, 38, 39, 40, 46];

	$(".phone-mask").mask(phoneMask);

	$("body")
		.on("focus", ".phone-mask", function () {
			if (!this.value) {
				this.value = phonePrefix;
			}
		})
		.on("blur", ".phone-mask", function () {
			if (this.value.replace(/\D/g, "") === "7") {
				this.value = "";
			}
		})
		.on("keydown", ".phone-mask", function (e) {
			if (e.ctrlKey || e.metaKey || e.altKey || navigationKeys.indexOf(e.which || e.keyCode) !== -1) {
				return;
			}

			if (!/^\d$/.test(e.key || String.fromCharCode(e.which || e.keyCode))) {
				e.preventDefault();
			}
		})
		.on("paste", ".phone-mask", function (e) {
			var pastedText = (e.originalEvent.clipboardData || window.clipboardData).getData("text");

			if (/\D/.test(pastedText)) {
				e.preventDefault();
			}
		});
	
	
	

	// Main menu open
	if ($(".header__burger").length > 0) {
		$(".header__burger").click(function (event) {
			event.preventDefault();
			$(this).toggleClass("active");
			$("body").toggleClass("lock");
			$(".menu").toggleClass("active");
		});
		
		
		
		
		$(".menu__closed").click(function () {
			$(this).toggleClass("active");
			$("body").toggleClass("lock");
			$(".menu").toggleClass("active");
		});
	}

	// Header search open
	$(".header__search-btn").on("click", function () {
		$(".header__search").removeClass("active");
		$(this).parent().toggleClass("active");
	});
	
	
	
	$(function ($) {
		$(document).mouseup(function (e) {
			var div = $(".header__search");
			if (!div.is(e.target) && div.has(e.target).length === 0) {
				div.removeClass("active");
				$(".header__search-btn").removeClass("active");
			}
		});
	});

	
	
	
	// Fillter open
	if ($(".filter-mobile").length > 0) {
		$(".filter-mobile").click(function (event) {
			event.preventDefault();
			$(this).toggleClass("active");
			$("body").toggleClass("lock");
			$(".mobile-window").toggleClass("active");
		});
		$(".mobile-window__closed").click(function () {
			$(this).toggleClass("active");
			$("body").toggleClass("lock");
			$(".mobile-window").toggleClass("active");
		});
	}

	// Main Benefit sliders
	if ($('.promo__right').length > 0) {
		$('.promo__right').slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: false,
			dots: false,
			infinite: true,
			speed: 500,
			fade: true,
			asNavFor: '.promo__left-slick',
		});
		$('.promo__left-slick').slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: false,
			dots: false,
			infinite: true,
			speed: 500,
			fade: true,
			asNavFor: '.promo__right',
		});
		// On before slide change
		$('.promo__right').on('beforeChange', function (event, slick, currentSlide, nextSlide) {
			console.log(currentSlide);
			$(".promo__left-number").removeClass("active");
			$('.promo__left-number[data-id="' + nextSlide + '"]').addClass("active");
		});
	}
	$(".promo__left-number").on("click", function () {
		var id = $(this).data('id');
		$('.promo__right').slick('slickGoTo', id);
		$(".promo__left-number").removeClass("active");
		$(this).addClass("active");
	});

	// Main Production sliders
	if ($('.main-info__slick').length > 0) {
		$('.main-info__slick').slick({
			slidesToShow: 4,
			slidesToScroll: 1,
			arrows: false,
			dots: false,
			infinite: true,
			speed: 1000,
			swipeToSlide: true,
			responsive: [
				{
					breakpoint: 1330,
					settings: {
						slidesToShow: 3,
						slidesToScroll: 1,
						dots: true,
					}
				},
				{
					breakpoint: 1010,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 1,
						dots: true,
					}
				},
				{
					breakpoint: 600,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1,
						dots: true,
					}
				}
			]
		});
		$('.arrow.arrow_prev').click(function (event) {
			$(this).parents('.main-info').find('.main-info__slick').slick('slickPrev');
		});
		$('.arrow.arrow_next').click(function (event) {
			$(this).parents('.main-info').find('.main-info__slick').slick('slickNext');
		});
	}

	// fancybox
	$(".callback--js").on("click", function (event) {
		event.preventDefault();
		$.fancybox.open({
			closeExisting: true,
			src: "#callback-modal",
			baseClass: "modal__bg",
			touch: false
		});
	});

	/*Product slider*/
	if ($('.card__big, .card__small').length > 0) {
		$('.card__big').slick({
			dots: false,
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: false,
			fade: true,
			swipe: false,
			asNavFor: '.card__small',
			responsive: [
				{
					breakpoint: 1010,
					settings: {
						slidesToShow: 1,
						swipe: true,
					}
				}
			]
		});
		$('.card__small').slick({
			slidesToShow: 4,
			slidesToScroll: 1,
			asNavFor: '.card__big',
			arrows: false,
			dots: false,
			infinite: true,
			focusOnSelect: true,
			vertical: true,
			verticalSwiping: true,
		});
		$('.arrow.arrow_prev').click(function (event) {
			$(this).parents('.card').find('.card__big').slick('slickPrev');
		});
		$('.arrow.arrow_next').click(function (event) {
			$(this).parents('.card').find('.card__big').slick('slickNext');
		});
	}

	$("[data-info]").on("click", function (e) {
		e.preventDefault();
		$(".campaign-info__tab").removeClass('active');
		$(".campaign-info__block").removeClass('active');
		$(this).addClass('active');
		var info = $(this).data('info');
		$('[data-info-block="' + info + '"]').toggleClass('active');
	});

	// Main Production sliders
	if ($('.recommendation__row').length > 0) {
		if ($(window).outerWidth() <= '1230') {
			$('.recommendation__row').slick({
				slidesToShow: 3,
				slidesToScroll: 1,
				arrows: false,
				dots: false,
				infinite: true,
				speed: 1000,
				swipeToSlide: true,
				responsive: [
					{
						breakpoint: 760,
						settings: {
							slidesToShow: 2,
							slidesToScroll: 1,
						}
					},
					{
						breakpoint: 600,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1,
						}
					}
				]
			});
			$('.arrow.arrow_prev').click(function (event) {
				$(this).parents('.recommendation').find('.recommendation__row').slick('slickPrev');
			});
			$('.arrow.arrow_next').click(function (event) {
				$(this).parents('.recommendation').find('.recommendation__row').slick('slickNext');
			});
		}
	}

	if ($('#days').length > 0) {
		nonLinearStepSlider = document.getElementById('days');
		noUiSlider.create(nonLinearStepSlider, {
			start: [0, 1000],
			tooltips: true,
			connect: true,
			range: {
				'min': [0],
				'max': [1000]
			},
			format: wNumb({
				decimals: 0,
				to: function (value) {
					return Math.round(value) + '';
				},
				from: function (value) {
					return Math.round(value) + '';
				}
			})
		});
		nonLinearStepSlider.noUiSlider.on('change', function () {
			submitFilter();
		});
	}

	// Custom Select
	$('.select').click(function (event) {
		if (!$(this).hasClass('disabled')) {
			$('.select').not(this).removeClass('active').find('.select-options').slideUp(300);
			$(this).toggleClass('active');
			$(this).find('.select-options').slideToggle(300);
		}
	});
	$('.select-options__value').click(function () {
		$(this).parents('.select').find('.select-title__value').html($(this).html());
		if ($.trim($(this).data('value')) != '') {
			$(this).parents('.select').find('input').val($(this).data('value'));
		} else {
			$(this).parents('.select').find('input').val($(this).html());
		}
	});
	$(document).click(function (e) {
		if (!$(e.target).is(".select *")) {
			$('.select').removeClass('active');
			$('.select-options').slideUp(300);
		}
		;
	});

	// Accardion
	if ($(".sidebar__top").length > 0) {
		if ($(window).width() >= '1010') {
			$('.sidebar__top').click(function () {
				$(this).parent().toggleClass('active');
				if ($(this).parent().hasClass('active')) {
					$(this).next().slideDown();
				} else {
					$('.sidebar__block').slideUp();
					$(".sidebar__box").removeClass("active");
				}
			});
			$(function ($) {
				$(document).mouseup(function (e) {
					var div = $(".sidebar__box.active");
					if (!div.is(e.target) && div.has(e.target).length === 0) {
						div.removeClass("active");
					}
				});
			});
			$(function ($) {
				$(document).mouseup(function (e) {
					var div = $(".sidebar__block");
					if (!div.is(e.target) && div.has(e.target).length === 0) {
						div.slideUp();
					}
				});
			});
		}
	}

	// Contacts Map
	if ($('#map').length > 0) {
		google.maps.event.addDomListener(window, 'load', init);

		function init() {
			var mapOptions = {
				zoom: 11,
				center: new google.maps.LatLng(59.8347232,30.1400171),
				styles: [
					{
						"elementType": "geometry",
						"stylers": [
							{
								"color": "#f5f5f5"
							}
						]
					},
					{
						"elementType": "labels.icon",
						"stylers": [
							{
								"visibility": "off"
							}
						]
					},
					{
						"elementType": "labels.text.fill",
						"stylers": [
							{
								"color": "#616161"
							}
						]
					},
					{
						"elementType": "labels.text.stroke",
						"stylers": [
							{
								"color": "#f5f5f5"
							}
						]
					},
					{
						"featureType": "administrative.land_parcel",
						"elementType": "labels.text.fill",
						"stylers": [
							{
								"color": "#bdbdbd"
							}
						]
					},
					{
						"featureType": "poi",
						"elementType": "geometry",
						"stylers": [
							{
								"color": "#eeeeee"
							}
						]
					},
					{
						"featureType": "poi",
						"elementType": "labels.text.fill",
						"stylers": [
							{
								"color": "#757575"
							}
						]
					},
					{
						"featureType": "poi.park",
						"elementType": "geometry",
						"stylers": [
							{
								"color": "#e5e5e5"
							}
						]
					},
					{
						"featureType": "poi.park",
						"elementType": "labels.text.fill",
						"stylers": [
							{
								"color": "#9e9e9e"
							}
						]
					},
					{
						"featureType": "road",
						"elementType": "geometry",
						"stylers": [
							{
								"color": "#ffffff"
							}
						]
					},
					{
						"featureType": "road.arterial",
						"elementType": "labels.text.fill",
						"stylers": [
							{
								"color": "#757575"
							}
						]
					},
					{
						"featureType": "road.highway",
						"elementType": "geometry",
						"stylers": [
							{
								"color": "#dadada"
							}
						]
					},
					{
						"featureType": "road.highway",
						"elementType": "labels.text.fill",
						"stylers": [
							{
								"color": "#616161"
							}
						]
					},
					{
						"featureType": "road.local",
						"elementType": "labels.text.fill",
						"stylers": [
							{
								"color": "#9e9e9e"
							}
						]
					},
					{
						"featureType": "transit.line",
						"elementType": "geometry",
						"stylers": [
							{
								"color": "#e5e5e5"
							}
						]
					},
					{
						"featureType": "transit.station",
						"elementType": "geometry",
						"stylers": [
							{
								"color": "#eeeeee"
							}
						]
					},
					{
						"featureType": "water",
						"elementType": "geometry",
						"stylers": [
							{
								"color": "#c9c9c9"
							}
						]
					},
					{
						"featureType": "water",
						"elementType": "labels.text.fill",
						"stylers": [
							{
								"color": "#9e9e9e"
							}
						]
					}
				]

			};
			var mapElement = document.getElementById('map');
			var map = new google.maps.Map(mapElement, mapOptions);
			markerImage = 'images/contacts/marker.svg';
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(59.8347232,30.1400171),
				map: map,
				icon: markerImage
			});
		}
	}

	/*Product slider*/
	if ($('.campaign__big, .campaign__small').length > 0) {
		$('.campaign__big').slick({
			dots: false,
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: false,
			fade: true,
			swipe: false,
			asNavFor: '.campaign__small',
			responsive: [
				{
					breakpoint: 760,
					settings: {
						slidesToShow: 1,
						swipe: true,
						dots: true,
					}
				}
			]
		});
		$('.campaign__small').slick({
			slidesToShow: 3,
			slidesToScroll: 1,
			asNavFor: '.campaign__big',
			arrows: false,
			dots: false,
			infinite: true,
			focusOnSelect: true,
			vertical: true,
			verticalSwiping: true,
		});
		$('.arrow.arrow_prev').click(function (event) {
			$(this).parents('.card').find('.campaign__big').slick('slickPrev');
		});
		$('.arrow.arrow_next').click(function (event) {
			$(this).parents('.card').find('.campaign__big').slick('slickNext');
		});
	}

	// Main Production sliders
	if ($('.cabinet-company__slick').length > 0) {
		$('.cabinet-company__slick').slick({
			slidesToShow: 3,
			slidesToScroll: 1,
			arrows: false,
			dots: false,
			infinite: true,
			speed: 1000,
			swipeToSlide: true,
			responsive: [
				{
					breakpoint: 1330,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 1,
					}
				},
				{
					breakpoint: 1010,
					settings: {
						slidesToShow: 2,
						slidesToScroll: 1,
					}
				},
				{
					breakpoint: 600,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1,
						dots: true,
					}
				}
			]
		});
		$('.arrow.arrow_prev').click(function (event) {
			$(this).parents('.cabinet').find('.cabinet-company__slick').slick('slickPrev');
		});
		$('.arrow.arrow_next').click(function (event) {
			$(this).parents('.cabinet').find('.cabinet-company__slick').slick('slickNext');
		});
	}

	setTimeout(function () {
		if (!getCookie('agreedCookie')) {
			$(".bottom").addClass("active");
		}
	}, 1000);
	$(".bottom__closed").on("click", function () {
		$(".bottom").removeClass("active");
		setCookie('agreedCookie', 1, 999);
	});

	function getCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		}
		return null;
	}

	$('.addLike').click(function (e) {
		e.preventDefault();
		if (window.userId === 0) {
			if (confirm('Чтобы лайкнуть копилку, необходимо авторизоваться')) {
				window.location = '/login';
			}
		}
		let $this = this;
		$.post('/campaign/' + $(this).data('campaign') + '/addLike', {campaignId: $(this).data('campaign')}, function () {
			let likeNum = $('span:last-of-type', $this);
			likeNum.text(parseInt(likeNum.text()) + 1);
		})
	});

	if ($('#campaign__code-btn').length) {
		document.getElementById("campaign__code-btn").addEventListener("click", function (e) {
			copyToClipboard(document.getElementById("campaign__code-field"));
			e.preventDefault();
		});
	}

	function copyToClipboard(elem) {
		// create hidden text element, if it doesn't already exist
		var targetId = "_hiddenCopyText_";
		var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
		var origSelectionStart, origSelectionEnd;
		if (isInput) {
			// can just use the original source element for the selection and copy
			target = elem;
			origSelectionStart = elem.selectionStart;
			origSelectionEnd = elem.selectionEnd;
		} else {
			// must use a temporary form element for the selection and copy
			target = document.getElementById(targetId);
			if (!target) {
				var target = document.createElement("textarea");
				target.style.position = "absolute";
				target.style.left = "-9999px";
				target.style.top = "0";
				target.id = targetId;
				document.body.appendChild(target);
			}
			target.textContent = elem.textContent;
		}
		// select the content
		var currentFocus = document.activeElement;
		target.focus();
		target.setSelectionRange(0, target.value.length);

		// copy the selection
		var succeed;
		try {
			succeed = document.execCommand("copy");
		} catch (e) {
			succeed = false;
		}
		// restore original focus
		if (currentFocus && typeof currentFocus.focus === "function") {
			currentFocus.focus();
		}

		if (isInput) {
			// restore prior selection
			elem.setSelectionRange(origSelectionStart, origSelectionEnd);
		} else {
			// clear temporary content
			target.textContent = "";
		}
		return succeed;
	}


	/*Header scroll*/
	var mainHeader = $('.cd-auto-hide-header'),
		secondaryNavigation = $('.cd-secondary-nav'),
		belowNavHeroContent = $('.sub-nav-hero'),
		headerHeight = mainHeader.height();

	//set scrolling variables
	var scrolling = false,
		previousTop = 0,
		currentTop = 0,
		scrollDelta = 10,
		scrollOffset = 5;

	$(window).on('scroll', function () {
		if (!scrolling) {
			scrolling = true;
			(!window.requestAnimationFrame)
				? setTimeout(autoHideHeader, 250)
				: requestAnimationFrame(autoHideHeader);
		}
	});

	$(window).on('resize', function () {
		headerHeight = mainHeader.height();
	});

	function autoHideHeader() {
		var currentTop = $(window).scrollTop();

		(belowNavHeroContent.length > 0)
			? checkStickyNavigation(currentTop) // secondary navigation below intro
			: checkSimpleNavigation(currentTop);

		previousTop = currentTop;
		scrolling = false;
	}

	function checkSimpleNavigation(currentTop) {
		//there's no secondary nav or secondary nav is below primary nav
		if (previousTop - currentTop > scrollDelta) {
			//if scrolling up...
			mainHeader.addClass('active header_active header_fixed');
			mainHeader.removeClass('header_bottom');
		} else if (currentTop - previousTop > scrollDelta && currentTop > scrollOffset) {
			//if scrolling down...
			mainHeader.removeClass('active header_active header_fixed header_fix');
			mainHeader.addClass('header_bottom');
		}
		if (currentTop == 0) {
			mainHeader.removeClass('active header_active header_fixed');
			mainHeader.addClass('header_fix');
		}
	}

});
