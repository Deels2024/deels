$(".product__btn-pay").click(function () {
  $("body").addClass("overflow");
  $(".popup__wrape").fadeIn();
});

$(".feedback__item img").click(function () {
  $("body").addClass("overflow");
  $(".popup__wrape").fadeIn();
  let src = $(this).attr('data-modal');
  console.log(src)
  $(".popup__wrape img").attr('src', src);
});

$(".popup__close").click(function () {
  $("body").removeClass("overflow");
  $(".popup__wrape").fadeOut();
});
$(".popup__wrape").click(function () {
  $("body").removeClass("overflow");
  $(".popup__wrape").fadeOut();
});
$(".popup__modal").click(function (event) {
  event.stopPropagation();
});
