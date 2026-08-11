@if ($campaign->is_ended())
    <style>
        @import url(https://fonts.googleapis.com/css?family=Sigmar+One);

        .congrats {
            width: 550px;
            height: 100px;
            padding: 20px 10px;
            text-align: center;
            margin: 0 auto;
			position: relative;
			top: -20px;
        }

        .congrats h1 {
            transform-origin: 50% 50%;
            font-size: 50px;
            font-family: 'Sigmar One', cursive;
            cursor: pointer;
            z-index: 2;
            top: 0;
            text-align: center;
            width: 100%;
        }

        .blob {
            height: 50px;
            width: 50px;
            color: #ffcc00;
            position: absolute;
            top: 45%;
            left: 45%;
            z-index: 1;
            font-size: 30px;
            display: none;
        }
    </style>
    <div class="congrats">
        <h1>Сбор средств завершен!</h1>
    </div>

@section('page-js')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.18.0/TweenMax.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.2/underscore-min.js"></script>
    <script>
		$(function () {
			var numberOfStars = 200;

			for (var i = 0; i < numberOfStars; i++) {
				$('.congrats').append('<div class="blob fa fa-star ' + i + '"></div>');
			}

			animateText();

			animateBlobs();
		});

		$('.congrats').click(function () {
			reset();

			animateText();

			animateBlobs();
		});

		function reset() {
			$.each($('.blob'), function (i) {
				TweenMax.set($(this), {x: 0, y: 0, opacity: 1});
			});

			TweenMax.set($('.congrats h1'), {scale: 1, opacity: 1, rotation: 0});
		}

		function animateText() {
			TweenMax.from($('.congrats h1'), 0.8, {
				scale: 0.4,
				opacity: 0,
				rotation: 15,
				ease: Back.easeOut.config(4),
			});
		}

		function animateBlobs() {

			var xSeed = _.random(350, 380);
			var ySeed = _.random(120, 170);

			$.each($('.blob'), function (i) {
				var $blob = $(this);
				var speed = _.random(1, 5);
				var rotation = _.random(5, 100);
				var scale = _.random(0.8, 1.5);
				var x = _.random(-xSeed, xSeed);
				var y = _.random(-ySeed, ySeed);

				TweenMax.to($blob, speed, {
					x: x,
					y: y,
					ease: Power1.easeOut,
					opacity: 0,
					rotation: rotation,
					scale: scale,
					onStartParams: [$blob],
					onStart: function ($element) {
						$element.css('display', 'block');
					},
					onCompleteParams: [$blob],
					onComplete: function ($element) {
						$element.css('display', 'none');
					}
				});
			});
		}
    </script>
@endsection
@endif
