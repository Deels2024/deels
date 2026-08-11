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
$(".useful__carousel").owlCarousel({
  loop: true,
  responsiveClass: true,
  dots: false,
  nav: true,
  navText: "",
  margin: 20,
  autoWidth: true,
  items: 4,
});

