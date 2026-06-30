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
				var isOpen = $(this).hasClass('active');

				if (!isOpen) {
					$('header').addClass('activeMenu');
					$(this).addClass('active').attr('aria-expanded', 'true');
					$('.menu_container').addClass('active');
				} else {
					$('header').removeClass('activeMenu');
					$(this).removeClass('active').attr('aria-expanded', 'false');
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

			if ($(".wpcf7").length) {
				$(".wpcf7").on( 'wpcf7:invalid', function( event ){
					$('.wpcf7 input').each(function() {
						if($(this).hasClass('wpcf7-not-valid')) {
							var error = $(this).siblings('.wpcf7-not-valid-tip').html();

							$(this).val('').attr('placeholder', error);
						}
					});
				});
			}

			if ($.fn.magnify) {
				$('.zoom').magnify();
			}

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

			var ysseGalleryAutoplay = {
				autoplay: true,
				autoplaySpeed: 5000,
				pauseOnHover: true,
				pauseOnFocus: true
			};

			if ($.fn.slick && $('#content.gallery-page .ysse-slider').length) {
				var $galleryPage = $('#content.gallery-page');
				var $slider = $galleryPage.find('.ysse-slider');
				var $pagerWrap = $galleryPage.find('.gallery-pager-wrap');
				var $pager = $galleryPage.find('.custom-pager');
				var $nav = $galleryPage.find('.slides-navigation');

				$slider.slick($.extend({}, ysseGalleryAutoplay, {
					slidesToShow: 1,
					slidesToScroll: 1,
					arrows: false,
					fade: false,
					adaptiveHeight: false,
					infinite: true,
					asNavFor: $pager
				}));

				$nav.find('.gallery-arrow-prev').on('click', function () {
					$slider.slick('slickPrev');
				});

				$nav.find('.gallery-arrow-next').on('click', function () {
					$slider.slick('slickNext');
				});

				$pager.slick({
					slidesToShow: 7,
					slidesToScroll: 1,
					swipeToSlide: false,
					swipe: true,
					draggable: true,
					touchMove: true,
					waitForAnimate: true,
					speed: 400,
					cssEase: 'ease-out',
					touchThreshold: 12,
					edgeFriction: 0.5,
					arrows: $pagerWrap.length > 0,
					prevArrow: $pagerWrap.find('.gallery-pager-arrow-prev'),
					nextArrow: $pagerWrap.find('.gallery-pager-arrow-next'),
					dots: false,
					centerMode: true,
					focusOnSelect: false,
					responsive: [
						{
							breakpoint: 991,
							settings: {
								slidesToShow: 3,
								swipeToSlide: false,
								draggable: true,
								touchMove: true,
								waitForAnimate: true,
								speed: 400,
								touchThreshold: 12
							}
						}
					]
				});

				$pager.on('click', 'button.block', function () {
					if ($pager.data('suppress-click')) {
						$pager.data('suppress-click', false);
						return;
					}

					var idx = parseInt($(this).attr('data-slide-index'), 10);

					if (!isNaN(idx)) {
						$slider.slick('slickGoTo', idx);
					}
				});

				$pager.on('mousedown touchstart', '.slick-list', function () {
					$pager.data('was-dragging', false);
					$pager.data('drag-start-x', null);
				});

				$pager.on('mousemove touchmove', '.slick-list', function (event) {
					var point = event.type.indexOf('touch') === 0 ? event.originalEvent.touches[0] : event;
					var startX = $pager.data('drag-start-x');

					if (startX === null || startX === undefined) {
						$pager.data('drag-start-x', point.clientX);
						return;
					}

					if (Math.abs(point.clientX - startX) > 8) {
						$pager.data('was-dragging', true);
					}
				});

				$pager.on('mouseup touchend', '.slick-list', function () {
					if ($pager.data('was-dragging')) {
						$pager.data('suppress-click', true);
					}

					$pager.data('was-dragging', false);
					$pager.data('drag-start-x', null);
				});
			}

			function loadGalleryBackground($containers) {
				$containers.each(function () {
					var $el = $(this);
					var bg = $el.data('bg');

					if (bg && !$el.data('bg-loaded')) {
						$el.css('background-image', 'url("' + bg + '")');
						$el.data('bg-loaded', true);
					}
				});
			}

			function ysseIsMobileProductGallery() {
				return window.matchMedia('(max-width: 991px)').matches;
			}

			function ysseFixProductGallery($gallery) {
				var $slider = $gallery.find('.ysse-product-slider');

				// Slick peab olema initsialiseeritud JA data-objekt olemas.
				// Kui slick init ebaõnnestus vaikselt (nt 0 slide-i), siis class
				// võib olla lisatud, aga `.slick(...)` käsu kutsumine viskab TypeError.
				if (!$slider.length || !$slider.hasClass('slick-initialized') || !$slider.data('slick')) {
					return;
				}

				var isMobile = ysseIsMobileProductGallery();
				var slideHeight = isMobile ? 280 : Math.max($slider.outerHeight() || 0, 400);

				$slider.css({
					height: slideHeight + 'px',
					minHeight: slideHeight + 'px'
				});
				$slider.find('.slick-list, .slick-track').css({
					height: slideHeight + 'px',
					minHeight: slideHeight + 'px'
				});
				$slider.find('.slick-slide').css({
					height: slideHeight + 'px',
					minHeight: slideHeight + 'px',
					width: '100%'
				});
				$slider.find('.slick-slide > div, .slide').css({
					height: slideHeight + 'px',
					minHeight: slideHeight + 'px',
					width: '100%'
				});
				$slider.find('.img-container').css({
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center',
					height: slideHeight + 'px',
					minHeight: slideHeight + 'px',
					width: '100%'
				});
				$slider.find('.img-container img').css({
					display: 'block',
					opacity: 1,
					visibility: 'visible',
					width: 'auto',
					height: 'auto',
					maxHeight: (slideHeight - 20) + 'px',
					maxWidth: isMobile ? 'calc(100% - 48px)' : '100%'
				});
				$slider.find('.slick-slide.slick-active').css({
					opacity: 1,
					visibility: 'visible',
					zIndex: 2
				});
				try {
					$slider.slick('setPosition');
				} catch (e) {
					// Slick instance broken — ignore. Galerii visuaalsuse jaoks
					// pole setPosition kriitiline (CSS height-id on juba kohaldatud).
				}
			}

			function ysseInitProductGallery($gallery) {
				var $slider = $gallery.find('.ysse-product-slider');
				var $pager = $gallery.find('.product-custom-pager');
				var isMobile = ysseIsMobileProductGallery();

				if ($slider.hasClass('slick-initialized')) {
					$slider.off('.ysseGallery');
					$slider.slick('unslick');
				}

				if ($pager.hasClass('slick-initialized')) {
					$pager.off('.ysseGallery');
					$pager.slick('unslick');
				}

				$slider.on('init.ysseGallery reInit.ysseGallery setPosition.ysseGallery afterChange.ysseGallery', function () {
					ysseFixProductGallery($gallery);
				});

				$slider.slick($.extend({}, ysseGalleryAutoplay, {
					slidesToShow: 1,
					slidesToScroll: 1,
					arrows: false,
					infinite: true,
					fade: false,
					adaptiveHeight: false,
					asNavFor: isMobile ? null : $pager
				}));

				$slider.find('img').on('load.ysseGallery', function () {
					ysseFixProductGallery($gallery);
				}).each(function () {
					if (this.complete) {
						$(this).trigger('load');
					}
				});

				$gallery.find('.product-gallery-arrow-prev').off('click.ysseGallery').on('click.ysseGallery', function () {
					$slider.slick('slickPrev');
				});

				$gallery.find('.product-gallery-arrow-next').off('click.ysseGallery').on('click.ysseGallery', function () {
					$slider.slick('slickNext');
				});

				$pager.slick({
					slidesToShow: 5,
					slidesToScroll: 1,
					swipeToSlide: true,
					asNavFor: isMobile ? null : $slider,
					arrows: false,
					dots: false,
					centerMode: true,
					focusOnSelect: true,
					responsive: [
						{
							breakpoint: 991,
							settings: {
								slidesToShow: 3
							}
						}
					]
				});

				if (isMobile) {
					$pager.off('click.ysseGallery').on('click.ysseGallery', '.slick-slide', function () {
						var index = $(this).data('slick-index');

						if (typeof index !== 'undefined') {
							$slider.slick('slickGoTo', index);
						}
					});
				}

				ysseFixProductGallery($gallery);
				window.setTimeout(function () {
					ysseFixProductGallery($gallery);
				}, 100);
				window.setTimeout(function () {
					ysseFixProductGallery($gallery);
				}, 500);
			}

			if ($.fn.slick && $('.ysse-product-slider').length) {
				$('.product-gallery').each(function () {
					ysseInitProductGallery($(this));
				});

				var ysseGalleryResizeTimer;
				$(window).on('resize', function () {
					window.clearTimeout(ysseGalleryResizeTimer);
					ysseGalleryResizeTimer = window.setTimeout(function () {
						$('.product-gallery').each(function () {
							ysseInitProductGallery($(this));
						});
					}, 200);
				});
			}

			function ysseGetSvgFillColor($img) {
				if ($img.closest('footer').length) {
					return '#FFFFFF';
				}

				if ($('body').hasClass('single-products') && $img.closest('header').length) {
					return '#0094AA';
				}

				return '#FFFFFF';
			}

			function ysseApplySvgFill($svg, fillColor) {
				$svg.attr('fill', fillColor);
				$svg.find('*').attr('fill', fillColor);
			}

			function ysseReplaceSvgImages($context) {
				var $images = $context ? $context.find('img.svg') : $('img.svg');

				if (!$images.length) {
					return;
				}

				$images.each(function () {
					var $img = $(this);
					var imgID = $img.attr('id');
					var imgClass = $img.attr('class');
					var imgURL = $img.attr('src');
					var fillColor = ysseGetSvgFillColor($img);

					$.get(imgURL, function (data) {
						var $svg = $(data).find('svg');

						if (typeof imgID !== 'undefined') {
							$svg = $svg.attr('id', imgID);
						}
						if (typeof imgClass !== 'undefined') {
							$svg = $svg.attr('class', imgClass + ' replaced-svg');
						}

						$svg = $svg.removeAttr('xmlns:a');
						ysseApplySvgFill($svg, fillColor);

						if (!$svg.attr('viewBox') && $svg.attr('height') && $svg.attr('width')) {
							$svg.attr('viewBox', '0 0 ' + $svg.attr('height') + ' ' + $svg.attr('width'))
						}

						$img.replaceWith($svg);
					}, 'xml');
				});
			}

			function ysseInitScrollReveal() {
				if (!$('body').hasClass('ysse-scroll-reveal-enabled')) {
					return;
				}

				var selectors = [
					'#content.front-page .front_product',
					'#content.front-page .top_block',
					'#content.front-page .ask_for_offer',
					'#content.front-page .ask_for_offer_wide',
					'#content.single-product .title_container',
					'#content.single-product > .text_container',
					'#content.single-product .color_card_container',
					'#content.single-product .blocks .block',
					'#content.default-post .post_content',
					'#content.default-page .page_content',
					'#content.company-page .content_body .page_content',
					'#content.company-page .windows_and_doors',
					'#content.contact-page .content_body .page_content',
					'#content.contact-page .offices .office',
					'#content.products-page .products .product',
					'#content.catalog-page .page_table',
					'.ask_for_offer',
					'.ask_for_offer_wide'
				];

				var $items = $(selectors.join(',')).not('.ysse-scroll-reveal-ready');

				if (!$items.length) {
					return;
				}

				$items.addClass('ysse-scroll-reveal ysse-scroll-reveal-ready');

				if (!('IntersectionObserver' in window)) {
					$items.addClass('is-visible');
					return;
				}

				var observer = new IntersectionObserver(function (entries) {
					entries.forEach(function (entry) {
						if (!entry.isIntersecting) {
							return;
						}

						entry.target.classList.add('is-visible');
						observer.unobserve(entry.target);
					});
				}, {
					root: null,
					rootMargin: '0px 0px -10% 0px',
					threshold: 0.12
				});

				$items.each(function () {
					observer.observe(this);
				});
			}

			ysseInitScrollReveal();

			var ysseScheduleIdle = window.requestIdleCallback || function (callback) {
				window.setTimeout(callback, 1);
			};

			if ($('body').hasClass('single-products') && $('header img.svg').length) {
				ysseReplaceSvgImages($('header'));
			}

			ysseScheduleIdle(function () { ysseReplaceSvgImages(); });
		});

		$(window).on('load', function() {
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

		// === Tehtud tööd: "Vaata rohkem" laeb 6 pilti kaupa ===
		$('.completed-works').each(function () {
			var $section = $(this);
			var batch = parseInt($section.data('cw-batch'), 10) || 6;
			var $btn = $section.find('.completed-works__more');
			$btn.on('click', function () {
				var $hidden = $section.find('.completed-works__item.is-hidden');
				$hidden.slice(0, batch).removeClass('is-hidden');
				if ($section.find('.completed-works__item.is-hidden').length === 0) {
					$btn.addClass('is-done');
				}
			});
		});

		// === Tehtud tööd: lightbox ===
		(function () {
			var $box = null;
			var images = [];
			var idx = 0;

			function build() {
				if ($box) { return; }
				$box = $('<div class="ysse-lightbox" role="dialog" aria-modal="true" aria-label="Pildigalerii">' +
					'<button type="button" class="ysse-lightbox__close" aria-label="Sulge">&times;</button>' +
					'<button type="button" class="ysse-lightbox__prev" aria-label="Eelmine pilt">&lsaquo;</button>' +
					'<button type="button" class="ysse-lightbox__next" aria-label="Järgmine pilt">&rsaquo;</button>' +
					'<div class="ysse-lightbox__stage">' +
					'<img class="ysse-lightbox__img" alt="" />' +
					'<p class="ysse-lightbox__caption"></p>' +
					'</div>' +
					'</div>').appendTo('body');

				$box.find('.ysse-lightbox__close').on('click', close);
				$box.find('.ysse-lightbox__prev').on('click', prev);
				$box.find('.ysse-lightbox__next').on('click', next);
				$box.on('click', function (e) { if (e.target === this) { close(); } });
			}

			function show() {
				var img = images[idx];
				$box.find('.ysse-lightbox__img').attr('src', img.src).attr('alt', img.caption || '');
				$box.find('.ysse-lightbox__caption').text(img.caption || '').toggle(!!img.caption);
				var multi = images.length > 1;
				$box.find('.ysse-lightbox__prev, .ysse-lightbox__next').toggle(multi);
			}

			function open(items, startIdx) {
				build();
				images = items;
				idx = startIdx || 0;
				show();
				$box.addClass('is-open');
				$('body').addClass('ysse-lightbox-open');
				$(document).on('keydown.ysselb', onKey);
			}

			function close() {
				if (!$box) { return; }
				$box.removeClass('is-open');
				$('body').removeClass('ysse-lightbox-open');
				$(document).off('keydown.ysselb');
			}

			function prev() { idx = (idx - 1 + images.length) % images.length; show(); }
			function next() { idx = (idx + 1) % images.length; show(); }

			function onKey(e) {
				if (e.key === 'Escape') { close(); }
				else if (e.key === 'ArrowLeft') { prev(); }
				else if (e.key === 'ArrowRight') { next(); }
			}

			$('.completed-works').on('click', '.completed-works__btn', function () {
				var $btn = $(this);
				var $section = $btn.closest('.completed-works');
				var $btns = $section.find('.completed-works__btn');
				var items = $btns.map(function () {
					return { src: $(this).data('full'), caption: $(this).data('caption') };
				}).get();
				open(items, $btns.index($btn));
			});
		})();
	});
})(jQuery, this);
