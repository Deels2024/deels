$(document).ready(function () {
	const swiper2 = new Swiper('.showcase__items', {
		slidesPerView: 1,
		spaceBetween: 8,
		initialSlide: 1,
		loop: true,
		allowTouchMove: true,
		centeredSlides: true,
		navigation: {
			nextEl: '.showcase__right.swiper-button-next',
			prevEl: '.showcase__left.swiper-button-prev',
		},
		breakpoints: {
			767: {
				slidesPerView: 3,
				loop: false,
				spaceBetween: 80,
				allowTouchMove: false,
			},
		},
	})

	$('.modal__block').click(function () {

	})

	$(".card__plus").click(function () {
		var swiper = new Swiper('.card__gallery', {
			slidesPerView: 6,
			spaceBetween: 5,
		})
		$(this).hide()
	});

	$(document).on('click', '.modal-third-back', function (e) {
		e.preventDefault();
		$.fancybox.close(true);
		$.fancybox.open($('#modal'), {
			beforeShow: function (instance, current) {
				$.get(
					'/action_campaigns',
					{category: window.actionCategory},
					function (ans) {
						instance.setContent(current, ans);
					}
				)
			}
		})
	});

	$(document).on('click', '.modal__refresh', function (e) {
		e.preventDefault();
		$.fancybox.close(true);
		$.fancybox.open($('#modal'), {
			modal: false,
			beforeShow: function (instance, current) {
				$.get(
					'/action_campaigns',
					{category: window.actionCategory},
					function (ans) {
						instance.setContent(current, ans);
					}
				)
			}
		})
	});

	$(document).on('click', '.open-fancybox', function (e) {
		if (window.userId === 0) {
			window.location = 'https://deels.ru/login?redirect=/';
		}
		if (
			$('.modal-third__input').val() !== ''
			&& parseInt($('.modal-third__input').val()) < $('.modal-third__input').attr('min')
			&& $(this).attr('href') === '#modal-foure'
		) {
			alert('Минимальный размер доната: ' + $('.modal-third__input').attr('min') + 'р.');
			return false;
		}

		e.preventDefault();
		let $this = $(this);
		$.fancybox.close(true);
		$.fancybox.open($($(this).attr('href')), {
			beforeShow: function (instance, current) {
				if ($(current.src).attr('id') === 'modal') {
					window.actionCategory = $this.data('category');
					$('.action_amount').text($this.data('amount'));
					$('.action_min_pay').text($this.data('min-pay'));
					$('.modal-third__input').attr('min', $this.data('min-pay'));
					$.get(
						'/action_campaigns',
						{category: $this.data('category')},
						function (ans) {
							instance.setContent(current, ans);
						}
					)
				}
				if ($(current.src).attr('id') === 'modal-second') {
					if ($this.data('id')) {
						window.actionCategoryItem = $this.data('id');
					}

					$.get(
						'/action_campaigns/' + window.actionCategoryItem,
						function (ans) {
							instance.setContent(current, ans);

							setTimeout(() => {
								var swiper = new Swiper('.card__gallery', {
									spaceBetween: 10,
									slidesPerView: 7,
									freeMode: true,
									watchSlidesProgress: true,
									breakpoints: {
										767: {
											slidesPerView: 5,
											spaceBetween: 5,
										}
									}
								})

								var swiperGallery = new Swiper('.card__cart', {
									thumbs: {
										swiper,
									},
								})
							}, 1)
						}
					)
				}
				if ($(current.src).attr('id') === 'modal-third') {
					window.selectedActionCampaign = $this.data('id');
					window.selectedActionCampaignTitle = $this.data('campaign');
				}
				if ($(current.src).attr('id') === 'modal-foure') {
					$('.action_campaign_name').text(window.selectedActionCampaignTitle)
					$('.action_campaign_price').text($('.modal-third__input').val())
				}
				// instance.update();
			}
		})
	});

	$(document).on('click', '.donate-amount-placeholder ul li', function (e) {
		$(this).closest('form').find($('[name="amount"]')).val($(this).data('value'));
	});
	$('.TinkoffPayForm').submit(function (e) {
		e.preventDefault();
		let receiptData = {
			"Email": $('#email').val(),
			"Taxation": "usn_income",
			"Items": [{
				"Name": "Донат в копилку " + window.selectedActionCampaignTitle,
				"Price": parseInt($('.modal-third__input').val()) * 100,
				"Quantity": 1.00,
				"Amount": parseInt($('.modal-third__input').val()) * 100,
				"PaymentMethod": "full_payment",
				"PaymentObject": "commodity",
				"Tax": "none"
			}]
		}
		$('.receiptTinkoff').val(JSON.stringify(receiptData));
		$('.TinkoffPayForm .tinkoffPayRowLast:eq(0)').val(parseInt($('.modal-third__input').val()))
		$('.TinkoffPayForm .tinkoffPayRowLast:eq(1)').val(Date.now() + '_' + window.userId + '_' + window.selectedActionCampaign)
		$('.TinkoffPayForm .tinkoffPayRowLast:eq(2)').val('Донат в копилку ' + window.selectedActionCampaignTitle)
		pay(this);

		$.fancybox.close(true);
		$.fancybox.open($('#modal-five'));
		return false;
	})

	// 	.fancybox({
	//
	//
	//
	//
	// })
})

$(document).click(function (e) {
	const target = $(e.target);
	if (target.is('.modal__message span') || target.is('.modal__message img')) {
		target.parents('.modal__message').addClass('modal__message-hide')
	}
});