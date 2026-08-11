/* Preloader */

$(window).on("load", function () {
	$("#preloader").fadeOut();
	setTimeout(function () {
		for (let i = 0; i < close.length; ++i) {
			setTimeout(() => {
				close[i].click();

			}, i * 750)
		}
	}, 1800)
});

/* Header fixed */

if ($(this).scrollTop() > 100) {
	$(".header").addClass("fixed");
	$("#mobileshow").addClass("fixed");
}
$(window).scroll(function () {
	if ($(this).scrollTop() > 100) {
		$(".header").addClass("fixed");
		$("#mobileshow").addClass("fixed");
	} else if ($(this).scrollTop() < 100) {
		$(".header").removeClass("fixed");
		$("#mobileshow").removeClass("fixed");
	}
});

/* Header */

$("#menuOpen").click(function (event) {
	event.preventDefault();
	$(".header__menu").addClass("header__menu-open");
});
$("#menuClose").click(function (event) {
	event.preventDefault();
	$(".header__menu").removeClass("header__menu-open");
});

let headerSearchOpen = false;

function searchOpen() {
	headerSearchOpen = true;

	$(".header__input").removeClass("hide");
	setTimeout(function () {
		$(".header__input").addClass("header__input-show");
		$(".header__list").addClass("header__list-hide");
		$(".header__search").addClass("header__search-show");
		$('.header__icons').addClass("header__icons-hide");

		if (window.innerWidth >= 520) {
			$("#searchClose").fadeIn(100);
		}

	}, 50);
	setTimeout(function () {
		$(".header__list").addClass("hide");
	}, 400);
}

function searchClose() {
	headerSearchOpen = false;

	$(".header__list").removeClass("hide");
	setTimeout(function () {
		$(".header__list").removeClass("header__list-hide");
		$(".header__input").addClass("hide");
	}, 50);
	$(".header__search").removeClass("header__search-show");
	$('.header__icons').removeClass("header__icons-hide");

	if (window.innerWidth >= 520) {
		$("#searchClose").fadeOut(100);
	}

}

$("#searchOpen").click(function (event) {
	event.preventDefault();
	if (!headerSearchOpen) {
		searchOpen()
	} else if (window.innerWidth <= 520 && headerSearchOpen) {
		searchClose()
	}
});
$("#searchClose").click(function (event) {
	event.preventDefault();
	searchClose()
});

$(".main__follow-text span").click(function () {
	$(".main__follow-text").addClass("main__follow-text-hide");
});

/* dreams_btn */

$(".dream__items-btn").click(function (e) {
	let num = $(this).attr("data-btn");
	$(".dream__item").removeClass("dream__item-show");
	$(".dream__item[data-btn='" + num + "'").addClass("dream__item-show");
	$(".dream__items-btn").removeClass("active");
	$(this).addClass("active");
});

/* product btn */

$(".product__btn-heart").click(function () {
	$(".product__btn-heart").toggleClass("product__btn-heart-active");
});

$(".btn__copy").click(function () {
	let area = document.createElement("textarea");
	document.body.appendChild(area);
	area.value = window.location.href;
	area.select();
	document.execCommand("copy");
	document.body.removeChild(area);
});

// sidebar open

$(".sidebar__open").click(function () {
	$(".sidebar").addClass("sidebar-open");
	$("header").addClass("index");
	$(".background").addClass("fix");
});
$(".sidebar__close").click(function () {
	$(".sidebar").removeClass("sidebar-open");
	$("header").removeClass("index");
	$(".background").removeClass("fix");
});

// password

$('.sign__recovery').click(function () {
	$('.sign__message').show()
	setTimeout(function () {
		$(".sign__message").addClass("animate__fadeOutDown");
	}, 3000)
	setTimeout(function () {
		$('.sign__message').hide()
		$(".sign__message").removeClass("animate__fadeOutDown");
	}, 5000)
})

// banks open
$(".banks__open>a").click(function (event) {
	event.preventDefault();
})
$(".banks__open").click(function (event) {
	$(this).toggleClass("banks__open-active");
});

$(".banks__open-gender").click(function (event) {
	event.preventDefault();
	$(this).toggleClass("banks__open-active");
});

$(".banks__open-city").click(function (event) {
	event.preventDefault();
	$(this).toggleClass("banks__open-active");
});

$('.new__hide li').click(function () {
	$(this).parents('.new__input').children('span').html($(this).html())
	$(this).parents('.new__input').children('span').attr('data-selected', true)
})

$('.support__hide li').click(function () {
	$(this).parents('.support__open').children('span').html($(this).html())
})
// load

$('.new__load').click(function () {
	$(this).parents('.new__img').children('input').trigger('click')
});

$('.profile__change').click(function () {
	console.log($(this).parents('.profile__img').children('input'))
	$(this).parents('.profile__img').children('input').trigger('click')
});

$('.deposit__btn_pay').click(function (event) {
	let login = $('input[name="name"]').val()
	if (!login) {
		event.preventDefault();
		$('input[name="name"]').addClass('input-red')
		$('input[name="name"]').prev().addClass('label-red')
		$('.deposit__name-hide').show()
	} else {
		$('input[name="name"]').removeClass('input-red')
		$('input[name="name"]').prev().removeClass('label-red')
		$('input[name="name"]').parent().find('.deposit__name-hide').hide()
	}

	let email = $('#email').val()
	if (!email) {
		event.preventDefault();
		$('#email').addClass('input-red')
		$('#email').prev().addClass('label-red')
		$('#email').parent().find('.deposit__name-hide').show()
	} else {
		$('#email').removeClass('input-red')
		$('#email').prev().removeClass('label-red')
		$('#email').parent().find('.deposit__name-hide').hide()
	}
});

$('input[name="name"]').focusout(function () {
	if ($(this).val()) {
		$('input[name="name"]').removeClass('input-red')
		$('input[name="name"]').prev().removeClass('label-red')
		$('input[name="name"]').parent().find('.deposit__name-hide').hide()
	}
})
$('#email').focusout(function () {
	if ($(this).val()) {
		$('#email').removeClass('input-red')
		$('#email').prev().removeClass('label-red')
		$('#email').parent().find('.deposit__name-hide').hide()
	}
})

// Contacts Map
if ($("#map").length > 0) {
	google.maps.event.addDomListener(window, "load", init);

	function init() {
		var mapOptions = {
			zoom: 11,
			center: new google.maps.LatLng(59.8347232, 30.1400171),
			styles: [
				{
					elementType: "geometry",
					stylers: [
						{
							color: "#f5f5f5",
						},
					],
				},
				{
					elementType: "labels.icon",
					stylers: [
						{
							visibility: "off",
						},
					],
				},
				{
					elementType: "labels.text.fill",
					stylers: [
						{
							color: "#616161",
						},
					],
				},
				{
					elementType: "labels.text.stroke",
					stylers: [
						{
							color: "#f5f5f5",
						},
					],
				},
				{
					featureType: "administrative.land_parcel",
					elementType: "labels.text.fill",
					stylers: [
						{
							color: "#bdbdbd",
						},
					],
				},
				{
					featureType: "poi",
					elementType: "geometry",
					stylers: [
						{
							color: "#eeeeee",
						},
					],
				},
				{
					featureType: "poi",
					elementType: "labels.text.fill",
					stylers: [
						{
							color: "#757575",
						},
					],
				},
				{
					featureType: "poi.park",
					elementType: "geometry",
					stylers: [
						{
							color: "#e5e5e5",
						},
					],
				},
				{
					featureType: "poi.park",
					elementType: "labels.text.fill",
					stylers: [
						{
							color: "#9e9e9e",
						},
					],
				},
				{
					featureType: "road",
					elementType: "geometry",
					stylers: [
						{
							color: "#ffffff",
						},
					],
				},
				{
					featureType: "road.arterial",
					elementType: "labels.text.fill",
					stylers: [
						{
							color: "#757575",
						},
					],
				},
				{
					featureType: "road.highway",
					elementType: "geometry",
					stylers: [
						{
							color: "#dadada",
						},
					],
				},
				{
					featureType: "road.highway",
					elementType: "labels.text.fill",
					stylers: [
						{
							color: "#616161",
						},
					],
				},
				{
					featureType: "road.local",
					elementType: "labels.text.fill",
					stylers: [
						{
							color: "#9e9e9e",
						},
					],
				},
				{
					featureType: "transit.line",
					elementType: "geometry",
					stylers: [
						{
							color: "#e5e5e5",
						},
					],
				},
				{
					featureType: "transit.station",
					elementType: "geometry",
					stylers: [
						{
							color: "#eeeeee",
						},
					],
				},
				{
					featureType: "water",
					elementType: "geometry",
					stylers: [
						{
							color: "#c9c9c9",
						},
					],
				},
				{
					featureType: "water",
					elementType: "labels.text.fill",
					stylers: [
						{
							color: "#9e9e9e",
						},
					],
				},
			],
		};
		var mapElement = document.getElementById("map");
		var map = new google.maps.Map(mapElement, mapOptions);
		var marker = new google.maps.Marker({
			position: new google.maps.LatLng(59.8347232, 30.1400171),
			map: map,
		});
	}
}


// modal
$(function () {
	$(".product__btn-pay").click(function () {
		$("body").addClass("overflow");
		$(".popup__wrape_1").fadeIn();
	});

	$(".product__btn-pay_auto").click(function () {
		if (window.userId===0) {
			window.location = 'https://deels.ru/login'
		}

		$("body").addClass("overflow");
		$(".popup__wrape_1_auto").fadeIn();
	});

	$(".feedback__item img").click(function () {
		$("body").addClass("overflow");
		$(".popup__wrape").fadeIn();
		let src = $(this).attr('data-modal');
		console.log(src)
		$(".popup__wrape .popup__content").attr('src', src);
	});

	$(".popup__close").click(function () {
		$("body").removeClass("overflow");
		$(".popup__wrape_1").fadeOut();
		$(".popup__wrape_1_auto").fadeOut();
		$(".popup__wrape").fadeOut();
		$(".popup__wrape_abuse").fadeOut();
	});
	$(".popup__wrape").click(function () {
		$("body").removeClass("overflow");
		$(".popup__wrape").fadeOut();
	});
	$(".popup__wrape_1").click(function () {
		$("body").removeClass("overflow");
		$(".popup__wrape_1").fadeOut();
	});
	$(".popup__wrape_1_auto").click(function () {
		$("body").removeClass("overflow");
		$(".popup__wrape_1_auto").fadeOut();
	});
	$(".popup__wrape_abuse_close").click(function () {
		$("body").removeClass("overflow");
		$(".popup__wrape_abuse").fadeOut();
	});

	$(".popup__modal:not(.abuse__modal)").click(function (event) {
		event.stopPropagation();
	});
});
// carousel

if ($(".bank__carousel").length > 0) {
	$(".bank__carousel").owlCarousel({
		loop: true,
		margin: 10,
		responsiveClass: true,
		navText: "",
		responsive: {
			0: {
				items: 2,
			},
			900: {
				items: 2,
				nav: false,
				dots: true,
			},
			910: {
				items: 3,
				dots: false,
				nav: true,
			},
			1390: {
				items: 3,
				dots: false,
				nav: true,
			},
			1400: {
				items: 4,
				margin: 20,
				dots: false,
				nav: true,
			},
		},
	});
}

if ($(".useful__carousel").length > 0) {
	$(".useful__carousel").owlCarousel({
		loop: true,
		margin: 10,
		responsiveClass: true,
		navText: "",
		responsive: {
			0: {
				items: 1,
				nav: true,
				dots: true,
			},
			400: {
				items: 2,
				nav: true,
				dots: true,
			},
			900: {
				items: 2,
				nav: true,
				dots: true,
			},
			910: {
				items: 4,
				dots: true,
				nav: true,
			},
			1390: {
				items: 4,
				dots: true,
				nav: true,
			},
			1400: {
				items: 4,
				margin: 20,
				dots: true,
				nav: true,
			},
		},
	});
}



if ($(".profile__carousel").length > 0) {
	$(".profile__carousel").owlCarousel({
		loop: false,
		margin: 0,
		responsiveClass: true,
		navText: "",
		responsive: {
			0: {
				items: 1,
			},
			900: {
				items: 1,
				nav: true,
				dots: true,
			},
			960: {
				items: 2,
				margin: 20,
				dots: true,
				nav: true,
			},
			1390: {
				items: 2,
				margin: 20,
				dots: true,
				nav: true,
			},
			1400: {
				items: 2,
				margin: 20,
				dots: true,
				nav: true,
			},
		},
	});
}
if ($(".catalog__carousel").length > 0) {
	setTimeout(function () {
		$(".catalog__carousel").owlCarousel({
			loop: true,
			responsiveClass: true,
			dots: false,
			nav: true,
			navText: "",
			margin: 20,
			autoWidth: true,
			items: 4,
		});
	}, 2000);
}


// mask
// jQuery Mask Plugin v1.14.16
// github.com/igorescobar/jQuery-Mask-Plugin
var $jscomp = $jscomp || {};
$jscomp.scope = {};
$jscomp.findInternal = function (a, n, f) {
	a instanceof String && (a = String(a));
	for (var p = a.length, k = 0; k < p; k++) {
		var b = a[k];
		if (n.call(f, b, k, a)) return {i: k, v: b}
	}
	return {i: -1, v: void 0}
};
$jscomp.ASSUME_ES5 = !1;
$jscomp.ASSUME_NO_NATIVE_MAP = !1;
$jscomp.ASSUME_NO_NATIVE_SET = !1;
$jscomp.SIMPLE_FROUND_POLYFILL = !1;
$jscomp.defineProperty = $jscomp.ASSUME_ES5 || "function" == typeof Object.defineProperties ? Object.defineProperty : function (a, n, f) {
	a != Array.prototype && a != Object.prototype && (a[n] = f.value)
};
$jscomp.getGlobal = function (a) {
	return "undefined" != typeof window && window === a ? a : "undefined" != typeof global && null != global ? global : a
};
$jscomp.global = $jscomp.getGlobal(this);
$jscomp.polyfill = function (a, n, f, p) {
	if (n) {
		f = $jscomp.global;
		a = a.split(".");
		for (p = 0; p < a.length - 1; p++) {
			var k = a[p];
			k in f || (f[k] = {});
			f = f[k]
		}
		a = a[a.length - 1];
		p = f[a];
		n = n(p);
		n != p && null != n && $jscomp.defineProperty(f, a, {configurable: !0, writable: !0, value: n})
	}
};
$jscomp.polyfill("Array.prototype.find", function (a) {
	return a ? a : function (a, f) {
		return $jscomp.findInternal(this, a, f).v
	}
}, "es6", "es3");
(function (a, n, f) {
	"function" === typeof define && define.amd ? define(["jquery"], a) : "object" === typeof exports && "undefined" === typeof Meteor ? module.exports = a(require("jquery")) : a(n || f)
})(function (a) {
	var n = function (b, d, e) {
		var c = {
			invalid: [], getCaret: function () {
				try {
					var a = 0, r = b.get(0), h = document.selection, d = r.selectionStart;
					if (h && -1 === navigator.appVersion.indexOf("MSIE 10")) {
						var e = h.createRange();
						e.moveStart("character", -c.val().length);
						a = e.text.length
					} else if (d || "0" === d) a = d;
					return a
				} catch (C) {
				}
			}, setCaret: function (a) {
				try {
					if (b.is(":focus")) {
						var c =
							b.get(0);
						if (c.setSelectionRange) c.setSelectionRange(a, a); else {
							var g = c.createTextRange();
							g.collapse(!0);
							g.moveEnd("character", a);
							g.moveStart("character", a);
							g.select()
						}
					}
				} catch (B) {
				}
			}, events: function () {
				b.on("keydown.mask", function (a) {
					b.data("mask-keycode", a.keyCode || a.which);
					b.data("mask-previus-value", b.val());
					b.data("mask-previus-caret-pos", c.getCaret());
					c.maskDigitPosMapOld = c.maskDigitPosMap
				}).on(a.jMaskGlobals.useInput ? "input.mask" : "keyup.mask", c.behaviour).on("paste.mask drop.mask", function () {
					setTimeout(function () {
							b.keydown().keyup()
						},
						100)
				}).on("change.mask", function () {
					b.data("changed", !0)
				}).on("blur.mask", function () {
					f === c.val() || b.data("changed") || b.trigger("change");
					b.data("changed", !1)
				}).on("blur.mask", function () {
					f = c.val()
				}).on("focus.mask", function (b) {
					!0 === e.selectOnFocus && a(b.target).select()
				}).on("focusout.mask", function () {
					e.clearIfNotMatch && !k.test(c.val()) && c.val("")
				})
			}, getRegexMask: function () {
				for (var a = [], b, c, e, t, f = 0; f < d.length; f++) (b = l.translation[d.charAt(f)]) ? (c = b.pattern.toString().replace(/.{1}$|^.{1}/g, ""), e = b.optional,
					(b = b.recursive) ? (a.push(d.charAt(f)), t = {
						digit: d.charAt(f),
						pattern: c
					}) : a.push(e || b ? c + "?" : c)) : a.push(d.charAt(f).replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&"));
				a = a.join("");
				t && (a = a.replace(new RegExp("(" + t.digit + "(.*" + t.digit + ")?)"), "($1)?").replace(new RegExp(t.digit, "g"), t.pattern));
				return new RegExp(a)
			}, destroyEvents: function () {
				b.off("input keydown keyup paste drop blur focusout ".split(" ").join(".mask "))
			}, val: function (a) {
				var c = b.is("input") ? "val" : "text";
				if (0 < arguments.length) {
					if (b[c]() !== a) b[c](a);
					c = b
				} else c = b[c]();
				return c
			}, calculateCaretPosition: function (a) {
				var d = c.getMasked(), h = c.getCaret();
				if (a !== d) {
					var e = b.data("mask-previus-caret-pos") || 0;
					d = d.length;
					var g = a.length, f = a = 0, l = 0, k = 0, m;
					for (m = h; m < d && c.maskDigitPosMap[m]; m++) f++;
					for (m = h - 1; 0 <= m && c.maskDigitPosMap[m]; m--) a++;
					for (m = h - 1; 0 <= m; m--) c.maskDigitPosMap[m] && l++;
					for (m = e - 1; 0 <= m; m--) c.maskDigitPosMapOld[m] && k++;
					h > g ? h = 10 * d : e >= h && e !== g ? c.maskDigitPosMapOld[h] || (e = h, h = h - (k - l) - a, c.maskDigitPosMap[h] && (h = e)) : h > e && (h = h + (l - k) + f)
				}
				return h
			}, behaviour: function (d) {
				d =
					d || window.event;
				c.invalid = [];
				var e = b.data("mask-keycode");
				if (-1 === a.inArray(e, l.byPassKeys)) {
					e = c.getMasked();
					var h = c.getCaret(), g = b.data("mask-previus-value") || "";
					setTimeout(function () {
						c.setCaret(c.calculateCaretPosition(g))
					}, a.jMaskGlobals.keyStrokeCompensation);
					c.val(e);
					c.setCaret(h);
					return c.callbacks(d)
				}
			}, getMasked: function (a, b) {
				var h = [], f = void 0 === b ? c.val() : b + "", g = 0, k = d.length, n = 0, p = f.length, m = 1,
					r = "push",
					u = -1, w = 0;
				b = [];
				if (e.reverse) {
					r = "unshift";
					m = -1;
					var x = 0;
					g = k - 1;
					n = p - 1;
					var A = function () {
						return -1 <
							g && -1 < n
					}
				} else x = k - 1, A = function () {
					return g < k && n < p
				};
				for (var z; A();) {
					var y = d.charAt(g), v = f.charAt(n), q = l.translation[y];
					if (q) v.match(q.pattern) ? (h[r](v), q.recursive && (-1 === u ? u = g : g === x && g !== u && (g = u - m), x === u && (g -= m)), g += m) : v === z ? (w--, z = void 0) : q.optional ? (g += m, n -= m) : q.fallback ? (h[r](q.fallback), g += m, n -= m) : c.invalid.push({
						p: n,
						v: v,
						e: q.pattern
					}), n += m; else {
						if (!a) h[r](y);
						v === y ? (b.push(n), n += m) : (z = y, b.push(n + w), w++);
						g += m
					}
				}
				a = d.charAt(x);
				k !== p + 1 || l.translation[a] || h.push(a);
				h = h.join("");
				c.mapMaskdigitPositions(h,
					b, p);
				return h
			}, mapMaskdigitPositions: function (a, b, d) {
				a = e.reverse ? a.length - d : 0;
				c.maskDigitPosMap = {};
				for (d = 0; d < b.length; d++) c.maskDigitPosMap[b[d] + a] = 1
			}, callbacks: function (a) {
				var g = c.val(), h = g !== f, k = [g, a, b, e], l = function (a, b, c) {
					"function" === typeof e[a] && b && e[a].apply(this, c)
				};
				l("onChange", !0 === h, k);
				l("onKeyPress", !0 === h, k);
				l("onComplete", g.length === d.length, k);
				l("onInvalid", 0 < c.invalid.length, [g, a, b, c.invalid, e])
			}
		};
		b = a(b);
		var l = this, f = c.val(), k;
		d = "function" === typeof d ? d(c.val(), void 0, b, e) : d;
		l.mask =
			d;
		l.options = e;
		l.remove = function () {
			var a = c.getCaret();
			l.options.placeholder && b.removeAttr("placeholder");
			b.data("mask-maxlength") && b.removeAttr("maxlength");
			c.destroyEvents();
			c.val(l.getCleanVal());
			c.setCaret(a);
			return b
		};
		l.getCleanVal = function () {
			return c.getMasked(!0)
		};
		l.getMaskedVal = function (a) {
			return c.getMasked(!1, a)
		};
		l.init = function (g) {
			g = g || !1;
			e = e || {};
			l.clearIfNotMatch = a.jMaskGlobals.clearIfNotMatch;
			l.byPassKeys = a.jMaskGlobals.byPassKeys;
			l.translation = a.extend({}, a.jMaskGlobals.translation, e.translation);
			l = a.extend(!0, {}, l, e);
			k = c.getRegexMask();
			if (g) c.events(), c.val(c.getMasked()); else {
				e.placeholder && b.attr("placeholder", e.placeholder);
				b.data("mask") && b.attr("autocomplete", "off");
				g = 0;
				for (var f = !0; g < d.length; g++) {
					var h = l.translation[d.charAt(g)];
					if (h && h.recursive) {
						f = !1;
						break
					}
				}
				f && b.attr("maxlength", d.length).data("mask-maxlength", !0);
				c.destroyEvents();
				c.events();
				g = c.getCaret();
				c.val(c.getMasked());
				c.setCaret(g)
			}
		};
		l.init(!b.is("input"))
	};
	a.maskWatchers = {};
	var f = function () {
		var b = a(this), d = {}, e = b.attr("data-mask");
		b.attr("data-mask-reverse") && (d.reverse = !0);
		b.attr("data-mask-clearifnotmatch") && (d.clearIfNotMatch = !0);
		"true" === b.attr("data-mask-selectonfocus") && (d.selectOnFocus = !0);
		if (p(b, e, d)) return b.data("mask", new n(this, e, d))
	}, p = function (b, d, e) {
		e = e || {};
		var c = a(b).data("mask"), f = JSON.stringify;
		b = a(b).val() || a(b).text();
		try {
			return "function" === typeof d && (d = d(b)), "object" !== typeof c || f(c.options) !== f(e) || c.mask !== d
		} catch (w) {
		}
	}, k = function (a) {
		var b = document.createElement("div");
		a = "on" + a;
		var e = a in b;
		e || (b.setAttribute(a,
			"return;"), e = "function" === typeof b[a]);
		return e
	};
	a.fn.mask = function (b, d) {
		d = d || {};
		var e = this.selector, c = a.jMaskGlobals, f = c.watchInterval;
		c = d.watchInputs || c.watchInputs;
		var k = function () {
			if (p(this, b, d)) return a(this).data("mask", new n(this, b, d))
		};
		a(this).each(k);
		e && "" !== e && c && (clearInterval(a.maskWatchers[e]), a.maskWatchers[e] = setInterval(function () {
			a(document).find(e).each(k)
		}, f));
		return this
	};
	a.fn.masked = function (a) {
		return this.data("mask").getMaskedVal(a)
	};
	a.fn.unmask = function () {
		clearInterval(a.maskWatchers[this.selector]);
		delete a.maskWatchers[this.selector];
		return this.each(function () {
			var b = a(this).data("mask");
			b && b.remove().removeData("mask")
		})
	};
	a.fn.cleanVal = function () {
		return this.data("mask").getCleanVal()
	};
	a.applyDataMask = function (b) {
		b = b || a.jMaskGlobals.maskElements;
		(b instanceof a ? b : a(b)).filter(a.jMaskGlobals.dataMaskAttr).each(f)
	};
	k = {
		maskElements: "input,td,span,div",
		dataMaskAttr: "*[data-mask]",
		dataMask: !0,
		watchInterval: 300,
		watchInputs: !0,
		keyStrokeCompensation: 10,
		useInput: !/Chrome\/[2-4][0-9]|SamsungBrowser/.test(window.navigator.userAgent) &&
			k("input"),
		watchDataMask: !1,
		byPassKeys: [9, 16, 17, 18, 36, 37, 38, 39, 40, 91],
		translation: {
			0: {pattern: /\d/},
			9: {pattern: /\d/, optional: !0},
			"#": {pattern: /\d/, recursive: !0},
			A: {pattern: /[a-zA-Z0-9]/},
			S: {pattern: /[a-zA-Z]/}
		}
	};
	a.jMaskGlobals = a.jMaskGlobals || {};
	k = a.jMaskGlobals = a.extend(!0, {}, k, a.jMaskGlobals);
	k.dataMask && a.applyDataMask();
	setInterval(function () {
		a.jMaskGlobals.watchDataMask && a.applyDataMask()
	}, k.watchInterval)
}, window.jQuery, window.Zepto);


// wNumb

!function (e) {
	"function" == typeof define && define.amd ? define([], e) : "object" == typeof exports ? module.exports = e() : window.wNumb = e()
}(function () {
	"use strict";
	var o = ["decimals", "thousand", "mark", "prefix", "suffix", "encoder", "decoder", "negativeBefore", "negative", "edit", "undo"];

	function w(e) {
		return e.split("").reverse().join("")
	}

	function h(e, t) {
		return e.substring(0, t.length) === t
	}

	function f(e, t, n) {
		if ((e[t] || e[n]) && e[t] === e[n]) throw new Error(t)
	}

	function x(e) {
		return "number" == typeof e && isFinite(e)
	}

	function n(e, t, n, r, i, o, f, u, s, c, a, p) {
		var d, l, h, g = p, v = "", m = "";
		return o && (p = o(p)), !!x(p) && (!1 !== e && 0 === parseFloat(p.toFixed(e)) && (p = 0), p < 0 && (d = !0, p = Math.abs(p)), !1 !== e && (p = function (e, t) {
			return e = e.toString().split("e"), (+((e = (e = Math.round(+(e[0] + "e" + (e[1] ? +e[1] + t : t)))).toString().split("e"))[0] + "e" + (e[1] ? e[1] - t : -t))).toFixed(t)
		}(p, e)), -1 !== (p = p.toString()).indexOf(".") ? (h = (l = p.split("."))[0], n && (v = n + l[1])) : h = p, t && (h = w((h = w(h).match(/.{1,3}/g)).join(w(t)))), d && u && (m += u), r && (m += r), d && s && (m += s), m += h, m += v, i && (m += i), c && (m = c(m, g)), m)
	}

	function r(e, t, n, r, i, o, f, u, s, c, a, p) {
		var d, l = "";
		return a && (p = a(p)), !(!p || "string" != typeof p) && (u && h(p, u) && (p = p.replace(u, ""), d = !0), r && h(p, r) && (p = p.replace(r, "")), s && h(p, s) && (p = p.replace(s, ""), d = !0), i && function (e, t) {
			return e.slice(-1 * t.length) === t
		}(p, i) && (p = p.slice(0, -1 * i.length)), t && (p = p.split(t).join("")), n && (p = p.replace(n, ".")), d && (l += "-"), "" !== (l = (l += p).replace(/[^0-9\.\-.]/g, "")) && (l = Number(l), f && (l = f(l)), !!x(l) && l))
	}

	function i(e, t, n) {
		var r, i = [];
		for (r = 0; r < o.length; r += 1) i.push(e[o[r]]);
		return i.push(n), t.apply("", i)
	}

	return function e(t) {
		if (!(this instanceof e)) return new e(t);
		"object" == typeof t && (t = function (e) {
			var t, n, r, i = {};
			for (void 0 === e.suffix && (e.suffix = e.postfix), t = 0; t < o.length; t += 1) if (void 0 === (r = e[n = o[t]])) "negative" !== n || i.negativeBefore ? "mark" === n && "." !== i.thousand ? i[n] = "." : i[n] = !1 : i[n] = "-"; else if ("decimals" === n) {
				if (!(0 <= r && r < 8)) throw new Error(n);
				i[n] = r
			} else if ("encoder" === n || "decoder" === n || "edit" === n || "undo" === n) {
				if ("function" != typeof r) throw new Error(n);
				i[n] = r
			} else {
				if ("string" != typeof r) throw new Error(n);
				i[n] = r
			}
			return f(i, "mark", "thousand"), f(i, "prefix", "negative"), f(i, "prefix", "negativeBefore"), i
		}(t), this.to = function (e) {
			return i(t, n, e)
		}, this.from = function (e) {
			return i(t, r, e)
		})
	}
});

// filter catalog


if ($('.catalog').length > 0 && document.querySelector(".__select")) {
	const selectSingle = document.querySelector(".__select");
	const selectSingle_title = selectSingle.querySelector(".__select__title");
	const selectSingle_labels = selectSingle.querySelectorAll(".__select__label");

	// Toggle menu
	selectSingle_title.addEventListener("click", () => {
		$(".control-label").removeClass("active");
		$(".slider__wrape").removeClass("active");
		if ("active" === selectSingle.getAttribute("data-state")) {
			selectSingle.setAttribute("data-state", "");
		} else {
			selectSingle.setAttribute("data-state", "active");
		}
	});

	// Close when click to option
	for (let i = 0; i < selectSingle_labels.length; i++) {
		selectSingle_labels[i].addEventListener("click", (evt) => {
			selectSingle_title.textContent = evt.target.textContent;
			selectSingle.setAttribute("data-state", "");
		});
	}

	$(".control-label").click(function () {
		$(".control-label").toggleClass("active");
		$(".slider__wrape").toggleClass("active");
		selectSingle.setAttribute("data-state", "");
	});

	let slider = $("#slider-range ");
	let sliderMobile = $("#slider-range-mobile");

	sliderDesc = noUiSlider.create(slider[0], {
		start: [0, 1000],
		tooltips: true,
		connect: true,
		range: {
			min: [0],
			max: [1000],
		},
		format: wNumb({
			decimals: 0,
			to: function (value) {
				return Math.round(value) + "";
			},
			from: function (value) {
				return Math.round(value) + "";
			},
		}),
	});
	noUiSlider.create(sliderMobile[0], {
		start: [0, 1000],
		tooltips: true,
		connect: true,
		range: {
			min: [0],
			max: [1000],
		},
		format: wNumb({
			decimals: 0,
			to: function (value) {
				return Math.round(value) + "";
			},
			from: function (value) {
				return Math.round(value) + "";
			},
		}),
	});

	$(".filter__cancel").click(function () {
		$(".control-label").removeClass("active");
		$(".slider__wrape").removeClass("active");
		selectSingle.setAttribute("data-state", "");
		selectSingle_title.textContent =
			selectSingle_title.getAttribute("data-default");
		slider[0].noUiSlider.reset();
	});

	$(".catalog__filter-open").click(function () {
		$("header").addClass("index");
		$(".catalog__filter-menu").addClass("catalog__filter-menu-show");
	});
	$("#filterClose").click(function (event) {
		event.preventDefault();
		$("header").removeClass("index");
		$(".catalog__filter-menu").removeClass("catalog__filter-menu-show");
	});

	$(".catalog__filter-banks li").click(function () {
		$(".catalog__filter-banks li").removeClass("catalog__filter-active");
		$(this).addClass("catalog__filter-active");
	});
	$(".catalog__filter-category li").click(function () {
		$(".catalog__filter-category li").removeClass("catalog__filter-active");
		$(this).addClass("catalog__filter-active");
	});
}

// slick-slider

if ($(".slider-for").length > 0) {
	$('.slider-for').slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: false,
		fade: true,
		asNavFor: '.slider-nav'
	});
}
if ($(".slider-nav").length > 0) {
	$('.slider-nav').slick({
		slidesToShow: 5,
		slidesToScroll: 1,
		asNavFor: '.slider-for',
		dots: false,
		arrows: false,
		centerMode: true,
		focusOnSelect: true
	});
}
if ($(".product__carousel").length > 0) {
	$(".product__carousel").owlCarousel({
		loop: true,
		responsiveClass: true,
		dots: true,
		navText: "",
		items: 1,
	});
}
if ($('#input')) {
	$('#input').mask('999.999.999.999.00₽', {reverse: true})
}

let pageHref = window.location.href;


function copytext(text) {
	let $tmp = $("<textarea>");
	$("body").append($tmp);
	$tmp.val(text).select();
	document.execCommand("copy");
	$tmp.remove();
}

$('[data-share-instagramm]').click(function () {
	copytext(`${pageHref}`)
})

$('[data-share-vk]').attr('href', `https://vkontakte.ru/share.php?url=${pageHref}`);
$('[data-share-facebook]').attr('href', `https://www.facebook.com/sharer.php?u=${pageHref}`);
$('[data-share-twitter]').attr('href', `https://twitter.com/intent/tweet?text=title.description.${pageHref}`);

// var close = document.getElementsByClassName("closebtn");
// var i;
//
// for (i = 0; i < close.length; i++) {
// 	close[i].onclick = function () {
// 		var div = this.parentElement;
// 		div.style.opacity = "0";
// 		setTimeout(function () {
// 			div.style.display = "none";
// 		}, 600);
// 	}
// }
(function (window, document, $) {
	'use strict';

	var config = window.DeelsFooterConfig || {};
	var routes = config.routes || {};
	var user = config.user || null;
	var searchTimer = null;
	var updateChat = false;
	var threadId;
	var chatMessagesPage = 1;
	var chatMessagesTotalPages = 1;
	var chatMessagesLoading = false;
	var notificationChatMode = false;
	var lastAssistant = '';
	var isAssistant = false;

	function getUserId() {
		return user && user.id ? user.id : null;
	}

	function toggleChat() {
		if ($('.chat, .header-chat__btn').hasClass('active')) {
			closeChat();
			return;
		}

		notificationChatMode = false;
		$('.chat, .header-chat__btn').addClass('active');
		getChatList();
	}

	function resetChatView() {
		$('body .chat__wrap').removeClass('active');
		$('body .chat__wrap').first().addClass('active');
		$('body .chat__wrap.messages').removeClass('active');
		updateChat = false;
	}

	function closeChat() {
		$('.chat, .header-chat__btn').removeClass('active');
		notificationChatMode = false;
		resetChatView();
	}

	function updateUserChat() {
		// Periodic chat refresh was disabled in the Blade version too.
	}

	function scrollChatToBottom() {
		var chatBody = $('.chat-body.messages_body');
		chatBody.scrollTop(chatBody.prop('scrollHeight'));
	}

	function resetChatMessagesPagination(data) {
		chatMessagesPage = data.current_page || 1;
		chatMessagesTotalPages = data.total_pages || 1;
		chatMessagesLoading = false;
	}

	function prependOlderChatMessages(html) {
		var chatBody = $('.chat-body.messages_body');
		var previousHeight = chatBody.prop('scrollHeight');
		var previousTop = chatBody.scrollTop();
		var fragment = $($.parseHTML($.trim(html)));

		if (!fragment.length) {
			return;
		}

		var firstExistingDate = chatBody.children('.chat-wrap').first().data('info');
		var lastLoadedWrap = fragment.filter('.chat-wrap').last();

		if (firstExistingDate && lastLoadedWrap.data('info') === firstExistingDate) {
			chatBody.children('.chat-wrap').first().prepend(lastLoadedWrap.children());
			lastLoadedWrap.remove();
		}

		chatBody.prepend(fragment);
		chatBody.scrollTop(previousTop + chatBody.prop('scrollHeight') - previousHeight);
	}

	function toggleOlderMessagesLoader(show) {
		var chatBody = $('.chat-body.messages_body');
		var loader = chatBody.children('.chat-messages-loader');

		if (show) {
			if (!loader.length) {
				chatBody.prepend('<div class="chat-messages-loader">Загрузка...</div>');
			}
			return;
		}

		loader.remove();
	}

	function loadOlderChatMessages() {
		if (!config.isAuthenticated || chatMessagesLoading || chatMessagesPage >= chatMessagesTotalPages || !threadId) {
			return;
		}

		chatMessagesLoading = true;
		toggleOlderMessagesLoader(true);

		$.ajax({
			url: routes.messagesShow,
			type: 'GET',
			data: {
				thread: threadId,
				user_id: getUserId(),
				messages_only: true,
				page: chatMessagesPage + 1
			},
			success: function (data) {
				if (!data.success) {
					return;
				}

				prependOlderChatMessages(data.view);
				chatMessagesPage = data.current_page || chatMessagesPage + 1;
				chatMessagesTotalPages = data.total_pages || chatMessagesTotalPages;
			},
			complete: function () {
				toggleOlderMessagesLoader(false);
				chatMessagesLoading = false;
			}
		});
	}

	function bindChatMessagesScroll() {
		$('.chat-body.messages_body')
			.off('scroll.chatMessages')
			.on('scroll.chatMessages', function () {
				if ($(this).scrollTop() <= 40) {
					loadOlderChatMessages();
				}
			});
	}

	function openChatThread(data) {
		if (!config.isAuthenticated) {
			return;
		}

		$('.chat, .header-chat__btn').addClass('active');
		updateChat = false;
		threadId = data.thread;
		chatMessagesLoading = false;

		$.ajax({
			url: routes.messagesShow,
			type: 'GET',
			data: data,
			success: function (response) {
				if (!$('.chat').hasClass('active')) {
					return;
				}

				updateChat = true;
				threadId = response.thread || threadId;
				updateUserChat();
				$('body .chat__wrap').removeClass('active');
				$('body .chat__wrap.messages').addClass('active');
				$('.messages').html(response.view);
				resetChatMessagesPagination(response);
				bindChatMessagesScroll();
				scrollChatToBottom();
			}
		});
	}

	function getChatList(query) {
		$('body .chat-list').html('Загружаем список чатов...');
		$.ajax({
			url: routes.messagesGetList,
			type: 'GET',
			data: {
				user_id: getUserId(),
				query: query
			},
			success: function (data) {
				$('body .chat-list').html(data.view);
			}
		});
	}

	function getChatErrorText(data) {
		return data.error || data.errors || data.message || 'Не удалось отправить сообщение';
	}

	function isChatSuspiciousRestriction(data) {
		return Boolean(data && (
			Object.prototype.hasOwnProperty.call(data, 'need_actions')
			|| data.shouldShowEmailPrompt
			|| data.shouldShowPhonePrompt
		));
	}

	function loadScript(src, callback) {
		var script = document.createElement('script');
		script.src = src;
		script.onload = callback;
		document.head.appendChild(script);
	}

	function setCookie(name, value, days) {
		var expires = '';
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = '; expires=' + date.toUTCString();
		}
		document.cookie = name + '=' + (value || '') + expires + '; path=/';
	}

	function initFirebase() {
		var firebaseSettings = config.firebase || {};

		if (!firebaseSettings.enabled || !firebaseSettings.scriptUrl || !routes.storeToken || !config.isAuthenticated) {
			return;
		}

		loadScript(firebaseSettings.scriptUrl, function () {
			if (!window.firebase) {
				return;
			}

			window.firebase.initializeApp(firebaseSettings.config);
			var messaging = window.firebase.messaging();

			$('body').on('click', '.header-chat__btn', function () {
				messaging.requestPermission()
					.then(function () {
						return messaging.getToken();
					})
					.then(function (response) {
						$.ajaxSetup({
							headers: {
								'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							}
						});
						$.ajax({
							url: routes.storeToken,
							type: 'POST',
							data: {
								token: response,
								user_id: getUserId()
							},
							dataType: 'JSON',
							error: function (error) {
								console.log(error);
							}
						});
					})
					.catch(function (error) {
						console.log(error);
					});
			});

			messaging.onMessage(function (payload) {
				console.log(payload);
			});
		});
	}

	function initTwitchEmbed() {
		var twitchSettings = config.twitch || {};

		if (!document.getElementById('twitch-embed') || !twitchSettings.channel || !twitchSettings.scriptUrl) {
			return;
		}

		loadScript(twitchSettings.scriptUrl, function () {
			if (!window.Twitch || !window.Twitch.Player) {
				return;
			}

			new window.Twitch.Player('twitch-embed', {
				width: 400,
				title: false,
				branding: false,
				height: 300,
				channel: twitchSettings.channel
			});
		});
	}

	function initWebSocket() {
		if (!config.websocket || !config.websocket.url) {
			return;
		}

		try {
			var ws = new WebSocket(config.websocket.url);
			var wsUserId = config.websocket.userId || null;

			ws.onopen = function () {
				console.log('welcome!');
				ws.send(JSON.stringify({
					type: 'socket',
					command: 'register',
					user_id: wsUserId,
					userId: wsUserId
				}));
			};
			ws.onerror = function (e) {
				console.log(e);
			};
			ws.onmessage = function () {};
		} catch (error) {
			console.log(error);
		}
	}

	$(document).ready(function () {
		var phoneMask = '+7 (999) 999-99-99';
		var phonePrefix = '+7 ';
		var navigationKeys = [8, 9, 13, 27, 35, 36, 37, 38, 39, 40, 46];

		$('.phone-mask').mask(phoneMask);
		$('body')
			.on('focus', '.phone-mask', function () {
				if (!this.value) {
					this.value = phonePrefix;
				}
			})
			.on('blur', '.phone-mask', function () {
				if (this.value.replace(/\D/g, '') === '7') {
					this.value = '';
				}
			})
			.on('keydown', '.phone-mask', function (e) {
				if (e.ctrlKey || e.metaKey || e.altKey || navigationKeys.indexOf(e.which || e.keyCode) !== -1) {
					return;
				}

				if (!/^\d$/.test(e.key || String.fromCharCode(e.which || e.keyCode))) {
					e.preventDefault();
				}
			})
			.on('paste', '.phone-mask', function (e) {
				var pastedText = (e.originalEvent.clipboardData || window.clipboardData).getData('text');

				if (/\D/.test(pastedText)) {
					e.preventDefault();
				}
			});

		if (routes.streamStatus) {
			$.ajax({
				url: routes.streamStatus,
				type: 'POST',
				success: function (data) {
					$('.stream_button').removeClass('online wave_red').addClass('wave');
					if (data.success && data.status === 'online') {
						$('.stream_button').removeClass('wave').addClass('online wave_red');
					}
				}
			});
		}

		$('.main_promo_slider').hide().owlCarousel({
			margin: 20,
			loop: true,
			dots: true,
			nav: false,
			autoWidth: true,
			infinite: true,
			autoplay: true,
			autoplaySpeed: 1000,
			items: 1,
			navText: [
				'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="33" viewBox="0 0 16 33" fill="none"><path d="M15 32L1.70148 18.5601C1.4869 18.3459 1.30952 18.0436 1.1866 17.6826C1.06368 17.3216 0.999397 16.9142 1.00001 16.5C0.999397 16.0858 1.06368 15.6784 1.1866 15.3174C1.30952 14.9564 1.4869 14.6541 1.70148 14.4399L15 1" stroke="#00F0FF"/></svg>',
				'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="33" viewBox="0 0 16 33" fill="none"><path d="M1 32L14.2985 18.5601C14.5131 18.3459 14.6905 18.0436 14.8134 17.6826C14.9363 17.3216 15.0006 16.9142 15 16.5C15.0006 16.0858 14.9363 15.6784 14.8134 15.3174C14.6905 14.9564 14.5131 14.6541 14.2985 14.4399L1 1" stroke="#00F0FF"/></svg>'
			],
			onInitialized: function () {
				$('.main_promo_slider').show();
			}
		});

		if (!config.isAuthenticated || !config.isAdmin) {
			if ($(window).width() > 960) {
				$('.stream_buttons_block').addClass('open');
			}
			$(window).resize(function () {
				if ($(window).width() > 960) {
					$('.stream_buttons_block').addClass('open');
				}
			});
		}

		var streamButton = document.getElementById('stream_button');
		var streamButtonsBlock = document.getElementById('stream_buttons_block');
		var streamClose = document.getElementById('stream_close');
		var mainDiv = document.getElementById('main-button');
		var fbaTg = document.getElementById('fba_tg');

		if (mainDiv) {
			mainDiv.addEventListener('click', function () {
				this.children.item(0).classList.toggle('fa-times');
				this.classList.toggle('open');
			});
		}

		if (streamButton && streamButtonsBlock) {
			streamButton.addEventListener('click', function () {
				streamButtonsBlock.classList.toggle('open');
			});
			if (streamClose) {
				streamClose.addEventListener('click', function () {
					streamButtonsBlock.classList.toggle('open');
				});
			}
		}

		if (fbaTg) {
			fbaTg.addEventListener('click', function () {
				this.children.item(0).classList.toggle('fa-times');
				this.classList.toggle('open');
			});
		}

		var kpromo = $('.kpromo-slider');
		kpromo.hide();
		kpromo.owlCarousel({
			loop: true,
			responsiveClass: true,
			dots: false,
			items: 3,
			nav: true,
			responsive: {
				0: {center: true, items: 1.4},
				350: {center: true, items: 1.6},
				400: {center: true, items: 1.8},
				500: {items: 2.5},
				630: {items: 2.9},
				780: {items: 2},
				980: {items: 2},
				1200: {items: 3}
			},
			onInitialized: function () {
				$('.kpromo-slider').show();
			}
		});

		if (typeof lozad === 'function') {
			lozad().observe();
		}

		$('.magnific_image').each(function () {
			var item = $(this).attr('data-image');
			if (item === undefined || item === '') {
				item = $(this).attr('src');
			}
			if (item !== undefined && item !== '' && $.fn.magnificPopup) {
				$(this).magnificPopup({
					items: {src: item},
					type: 'image'
				});
			}
		});
	});

	$('.header-chat__btn:not(.header-chat__btn--notifications), [data-close]').click(function () {
		toggleChat();
	});

	$(document).on('click', function (e) {
		if (!$('.chat').hasClass('active')) {
			return;
		}

		if ($(e.target).closest('.chat, .header-chat__btn').length) {
			return;
		}

		closeChat();
	});

	$('body').on('keydown input', 'textarea[data-at-expandable]', function () {
		this.style.removeProperty('height');
		this.style.height = (this.scrollHeight + 2) + 'px';
	});

	$('body').on('click', '.header-chat__btn--notifications', function (e) {
		e.preventDefault();
		var dataThread = $(this).attr('data-thread');

		if (!dataThread) {
			return;
		}

		notificationChatMode = true;
		$(this).removeAttr('data-badge');
		$('.header-chat__btn ').removeAttr('data-badge');
		openChatThread({
			thread: dataThread,
			user_id: getUserId()
		});

	});

	$('body').on('click', '.chat_btn', function (e) {
		e.preventDefault();
		e.stopPropagation();
		notificationChatMode = false;
		openChatThread({
			sender_id: getUserId(),
			user_id: $(this).attr('data-user')
		});
	});

	$('body').on('input', '[name="chat_search"]', function () {
		clearTimeout(searchTimer);
	});

	$('body').on('click', '.chat-list .chat-item', function () {
		notificationChatMode = false;
		openChatThread({
			thread: $(this).attr('data-thread'),
			user_id: getUserId()
		});
	});

	$('body').on('click', '.close_chat', function () {
		if (notificationChatMode) {
			closeChat();
			return;
		}

		var thParent = $('body .chat__wrap.active');
		thParent.prev().addClass('active');
		thParent.removeClass('active');
		getChatList();
		updateChat = false;
	});

	$('body').on('submit', '.send_message', function (e) {
		e.preventDefault();
		var form = $(this);
		var messageField = form.find('[name="message"]');
		var messageText = messageField.val();
		var formData = {
			thread_id: form.find('[name="thread_id"]').val(),
			user_id: form.find('[name="user_id"]').val(),
			recipients: form.find('[name="recipients[]"]').val(),
			message: messageText
		};
		var now = new Date(Date.now());
		var message = $('<div class="msg right-msg"><div class="msg-img"></div><div class="msg-bubble"><div class="msg-info"><div class="msg-info-name"></div><div class="msg-info-time"></div></div><div class="msg-text"></div></div></div>');

		message.find('.msg-img').css('background-image', user && user.avatar ? 'url(' + user.avatar + ')' : '');
		message.find('.msg-info-name').text(user && user.username ? user.username : '');
		message.find('.msg-info-time').text(now.getHours() + ':' + now.getMinutes());
		message.find('.msg-text').text(messageText);

		if (form.find('[name="is_assistant"]').length) {
			lastAssistant = messageText;
			isAssistant = true;
			$('.assistant_loader').show();
		}

		updateUserChat();
		messageField.val('');
		$('body .chat.active .chat-wrap').last().append(message);
		setTimeout(function () {
			$('.chat-body.messages_body').animate({
				scrollTop: $('.chat-body.messages_body').prop('scrollHeight')
			}, 200);
		}, 100);

		$.ajax({
			type: 'POST',
			url: routes.messagesSendMessage,
			data: formData,
			dataType: 'json',
			encode: true
		}).done(function (data) {
			if (data.success) {
				if (!form.find('[name="thread_id"]').length) {
					form.append('<input type="hidden" name="thread_id" value="' + data.thread_id + '">');
				}
				setTimeout(function () {
					$('.chat-body.messages_body').animate({
						scrollTop: $('.chat-body.messages_body').prop('scrollHeight')
					}, 200);
				}, 100);
				return;
			}

			message.remove();
			messageField.val(messageText);
			if (isChatSuspiciousRestriction(data)) {
				$('.alert-container .alert.danger').remove();
				return;
			}
			$('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>' + getChatErrorText(data) + '</div>');
		}).fail(function (xhr) {
			var data = xhr.responseJSON || {};
			message.remove();
			messageField.val(messageText);
			if (isChatSuspiciousRestriction(data)) {
				$('.alert-container .alert.danger').remove();
				return;
			}
			$('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>' + getChatErrorText(data) + '</div>');
		});
	});

	$('body').on('click', '.abuse', function () {
		if (!config.isAuthenticated) {
			return;
		}
		$('.abuse_user_id').val($(this).attr('data-user'));
		$('.abuse_modal').fadeIn();
	});

	$('body').on('click', '.abuse_close', function () {
		$('.abuse_modal').fadeOut();
	});

	$('body').on('submit', '.abuse_form', function (e) {
		e.preventDefault();
		if (!config.isAuthenticated) {
			return;
		}

		$.ajax({
			type: 'POST',
			url: routes.userAbuse,
			data: {
				user_id: $('.abuse_user_id').val(),
				abuser_id: $('.abuse_abuser_id').val(),
				abuse: $('.abuse_reason').val()
			},
			success: function (data) {
				$('.abuse_modal').fadeOut();
				$('.abuse_reason').val('');
				if (data.success) {
					$('.alert-container').html('<div class="alert success"> <span class="closebtn">&times;</span> ' + data.message + '</div>');
				} else {
					$('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> ' + data.error + '</div>');
				}
			}
		});
	});

	$('body').on('click', '.need_auth', function (e) {
		e.preventDefault();
		$('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span>Вы не авторизованы! Нажмите <a href="' + routes.login + '"><u>здесь</u></a>, чтобы авторизоваться</div>');
	});

	initFirebase();
	initTwitchEmbed();
	initWebSocket();

	window.toggleChat = toggleChat;
	window.closeChat = closeChat;
	window.get_chat_list = getChatList;
	window.update_user_chat = updateUserChat;
	window.setCookie = setCookie;
})(window, document, jQuery);
