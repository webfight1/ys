(function ($, root, undefined) {
	$(function () {
		'use strict';

		$(document).ready(function () {
			$(".scroll_down_btn").on('click', function() {
				$('html, body').animate({
					scrollTop: $(".post_content").offset().top
				}, 1000);
			});

			$('.mobile-menu-btn').on('click', function () {
				if ($(this).hasClass('active') == false) {
					$('header').addClass('activeMenu');
					$(this).addClass('active');
					$('.menu_container').addClass('active');
				} else {
					$('header').removeClass('activeMenu');
					$(this).removeClass('active');
					$('.menu_container').removeClass('active');
				}
			});

			$('.single-product .see_more').on('click', function () {
				if($(this).parents('.text').hasClass('open')) {
					$(this).parents('.text').removeClass('open');
				} else {
					$(this).parents('.text').addClass('open');
				}
			});

			$(".wpcf7").on( 'wpcf7:invalid', function( event ){
				$('.wpcf7 input').each(function() {
					if($(this).hasClass('wpcf7-not-valid')) {
						var error = $(this).siblings('.wpcf7-not-valid-tip').html();

						$(this).val('').attr('placeholder', error);
					}
				});
			});

			$('.zoom').magnify();

			// Cookies
			$('#accept_cookies').click(function () {
				$.cookie('ysse_cookie', 2, { expires: 365, path: '/' });

				getAnalytics();

				$('#cookie_notification_container').fadeOut(300);

				$('html, body').css({
					'height': 'initial',
					'overflow': 'initial'
				});
			});

			$('#deny_cookies').click(function () {
				$('#cookie_notification_container').fadeOut(300);

				$('html, body').css({
					'height': 'initial',
					'overflow': 'initial'
				});
			});

			var cookieValue = $.cookie('ysse_cookie');
			if(cookieValue !== '1' && cookieValue !== '2') {
				$('#cookie_notification_container').fadeIn(300);
				$('html, body').css({
					'height': 'initial',
					'overflow': 'initial'
				});
			} else if(cookieValue == '2') {
				getAnalytics();
				getHeaderScripts();
			}

			$('.ysse-slider').slick({
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: true,
				prevArrow: $('.prev'),
				nextArrow: $('.next'),
				fade: false,
				asNavFor: '.custom-pager'
			});

			var sliderNav = $('.slick-track');
			var maxItems = Math.round(sliderNav.parent('div').width() / 100);

			if(sliderNav.children('div').length < maxItems) {
				maxItems = sliderNav.children('div').length;
			}
			//console.log(maxItems);
			
			$('.custom-pager').slick({
				//slidesToShow: maxItems,
				slidesToShow: 7,
				slidesToScroll: 1,
				swipeToSlide: true,
				asNavFor: '.ysse-slider',
				arrows: false,
				dots: false,
				centerMode: true,
				focusOnSelect: true
			});

			$('.ysse-product-slider').slick({
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: true,
				prevArrow: $('.prev'),
				nextArrow: $('.next'),
				asNavFor: '.product-custom-pager'
			});

			$('.product-custom-pager').slick({
				//slidesToShow: maxItems,
				slidesToShow: 5,
				slidesToScroll: 1,
				swipeToSlide: true,
				asNavFor: '.ysse-product-slider',
				arrows: false,
				dots: false,
				centerMode: true,
				focusOnSelect: true
			});

			$('img.svg').each(function () {
				var $img = $(this);
				var imgID = $img.attr('id');
				var imgClass = $img.attr('class');
				var imgURL = $img.attr('src');

				$.get(imgURL, function (data) {
					// Get the SVG tag, ignore the rest
					var $svg = $(data).find('svg');

					// Add replaced image's ID to the new SVG
					if (typeof imgID !== 'undefined') {
						$svg = $svg.attr('id', imgID);
					}
					// Add replaced image's classes to the new SVG
					if (typeof imgClass !== 'undefined') {
						$svg = $svg.attr('class', imgClass + ' replaced-svg');
					}

					// Remove any invalid XML tags
					$svg = $svg.removeAttr('xmlns:a');

					$svg.find('*').attr('fill', 'none');
					// $svg.find('*').attr('stroke', 'none');

					// Check if the viewport is set, if the viewport is not set the SVG wont't scale.
					if (!$svg.attr('viewBox') && $svg.attr('height') && $svg.attr('width')) {
						$svg.attr('viewBox', '0 0 ' + $svg.attr('height') + ' ' + $svg.attr('width'))
					}

					// Replace image with new SVG
					$img.replaceWith($svg);

				}, 'xml');
			});
		});

		$(window).load(function() {
			var luup = $('.luup_img').html();
			$('.magnify .magnify-lens').append(luup);
		});

		function getAnalytics() {
			$.ajax({
				type: 'get',
				url: '/wp-admin/admin-ajax.php',
				data: {
					action: 'getAnalytics'
				},
				success: function(data) {
					$('body').append(data);
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					console.log("Status: " + textStatus);
					console.log("Error: " + errorThrown);
				}
			});
		}

		function getHeaderScripts() {
			$.ajax({
				type: 'get',
				url: '/wp-admin/admin-ajax.php',
				data: {
					action: 'getHeaderScripts'
				},
				success: function(data) {
					$('head').append(data);
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					console.log("Status: " + textStatus);
					console.log("Error: " + errorThrown);
				}
			});
		}
	});
})(jQuery, this);
