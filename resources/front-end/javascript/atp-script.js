/* global jQuery:false */

if( !window.atp_isotop_settings ) window.atp_isotop_settings = {};

(function($) {
	'use strict';

	if( window.atbb ) {
		var flex_sldiers = $( '.atp-carousel-portfolio, .atp-slider-portfolio' );
		flex_sldiers.each(function() {
			if( $(this).find('li').length > 1 ) {
				atbb.sliders.push( $(this) );
			}
		});
	}

	$(window).load( function() {
		var portfolios = $('.atp-portfolio-wrapper');

		if( portfolios.length === 0 ) return;

		portfolios.each( function() {
			var type = $(this).attr('data-type');
			switch( type ) {
				case 'grid':
					new GridPortfolio( $(this) );
				break;

				case 'slider':
					new SliderPortfolio( $(this) );
				break;

				case 'carousel':
					new CarouselPortfolio( $(this) );
				break;
			}
			
		});
	});



	function GridPortfolio( portfolio ) {
		if( !portfolio.isotope ) return;

		var gallery = portfolio.find( '.atp-projects' );
		//var projects = portfolio.find( '.atp-project' );
		var filters = portfolio.find( '.atp-filter' );
		var columnWidth = '.grid-sizer';
		var gutter = '.gutter-sizer';

		var defaults = {
			isOriginLeft: $('body').hasClass('rtl') ? false : true,
			itemSelector: '.atp-project',
			masonry: {
				columnWidth: columnWidth,
				gutter: gutter
			}
		};
		var settings = $.extend( true, {}, defaults, window.atp_isotop_settings );
			gallery.isotope(settings);

			gallery.trigger('atp:isotope:ready');

		$(window).off('smartresize', resize);
		$(window).on('smartresize', resize);

		filters.eq(0).addClass('atp-active-filter');
		filters.on('click', function() {
			var filter = $(this).attr('data-filter');

			filters.removeClass('atp-active-filter').filter('[data-filter="'+filter+'"]').addClass('atp-active-filter');

			gallery.isotope({
				filter: filter !== 'all' ? filter : '.atp-project'
			});
		});

		function resize() {
			gallery.isotope();
		}
	}



	function SliderPortfolio( portfolio ) {
		if( !portfolio.flexslider ) return;

		portfolio.flexslider({
			selector: '.atp-projects > li',
			animation: portfolio.attr('data-animation') || 'fade',
			smoothHeight: true,
			controlNav: Boolean( portfolio.attr('data-navcontrols') ) || false,
			directionNav: Boolean( portfolio.attr('data-dircontrols') ) || false,
			prevText: '',
			nextText: '',
			slideshowSpeed: (parseFloat( portfolio.attr('data-slidespeed') ) * 1000) || 6000,
			animationSpeed: (parseFloat( portfolio.attr('data-animspeed') ) * 1000) || 0.6,
			useCSS: true,
			start: function() {
				portfolio.addClass('atp-slider-ready');
				$('body').trigger('flexslider.ready');
			}
		});

		//console.log( Boolean( portfolio.attr('data-navcontrols') ) || false );
	}



	function CarouselPortfolio( portfolio ) {
		if( !portfolio.flexslider ) return;

		var lis = portfolio.find('.atp-projects > li.atp-project');
		var margin = parseInt( portfolio.find('.gutter-sizer').width() );
		var width = parseInt( portfolio.find('.grid-sizer').width() );
		var cols = parseInt( portfolio.attr('data-columns') ) || 3;

		lis.css({ 'margin-right': margin });

		portfolio.flexslider({
			animation: 'slide',
			selector: '.atp-projects > li.atp_project',

			animationLoop: false,
			slideshow: false,

			itemWidth: Math.floor( width ),
			itemMargin: Math.floor( margin ),
			maxItems: cols,

			controlNav: Boolean( portfolio.attr('data-navcontrols') ) || false,
			directionNav: lis.length <= cols ? false : (Boolean( portfolio.attr('data-dircontrols') ) || false),

			prevText: '',
			nextText: '',

			animationSpeed: 600,
			useCSS: true,

			start: function() {
				portfolio.addClass('atp-slider-ready');
				$('body').trigger('flexslider.ready');
			}
		});

		//console.log( Boolean( portfolio.attr('data-navcontrols') ) || false );
	}
})(jQuery);