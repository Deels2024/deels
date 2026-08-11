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

slider = $("#slider-range ");
sliderMobile = $("#slider-range-mobile");

noUiSlider.create(slider[0], {
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

slider.noUiSlider.on('end.one', function () {

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
  $(".catalog__filter-menu").addClass("catalog__filter-menu-show");
});
$("#filterClose").click(function (event) {
  event.preventDefault();
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
