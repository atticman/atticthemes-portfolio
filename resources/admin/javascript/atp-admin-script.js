/* global console: false, atp_raw_data:false */

var atp_data;

(function($) {
	'use strict';
	atp_data = JSON.parse( atp_raw_data );
	//console.log(atticthemes_box_builder);

	//check if "BoxBuilderWidget" is defined, if not, just return, do not run the rest.
	if( window.BoxBuilderWidget === undefined ) {
		return;
	}

	/* Add widgets */
	new BoxBuilderWidget( 'Portfolio', {
		title: 'Portfolio Widget',
		'class': 'portfolio fa fa-briefcase',
		editor: function( widget ) {
			widget.editor.title.text( 'Portfolio Widget Settings' );
			var data = widget.settings;
			
			//------------------------------------------
			var available_projects_wrapper = $('<div/>', {
				'class': 'available-projects-wrapper atp-section',
				'data-section': 'available'
			});
				available_projects_wrapper.appendTo( widget.editor );

			$('<h2/>',{'class': 'atbb-widget-option-heading'}).text('Available Projects').appendTo(available_projects_wrapper);
			$('<em/>').html('Click on projects to add them to the selected list. Projects that are selected will be used in this portfolio set.').appendTo(available_projects_wrapper);

			

			//-----
			if( atp_data.posts ) {
				var available_projects = $('<ul/>', {'class': 'atp-available-projects'});
				$.each(atp_data.posts, function( id, post ) {
					var available_project = $('<li/>', { 'data-id': id });
					var thumb = $('<div/>', { 'class': 'atp-thumbnail', title: post.title }).appendTo( available_project );
					var thumb_url = post.sizes && post.sizes.thumb && post.sizes.thumb[0] ? post.sizes.thumb[0] : post.thumb;

					if( thumb_url ) {
						thumb.css({
							'background-image': 'url('+ thumb_url +')'
						});
					}

					available_project.appendTo( available_projects );

					available_project.on('click', function() {
						if( $(this).hasClass('atp-disabled') ) return;

						addProject({ id: id, title: post.title, thumb: thumb_url });
					});

					
				});
				available_projects.appendTo( available_projects_wrapper );
			}
			//-----

			





			//---------------------------------------------------
			var selected_projects_wrapper = $('<div/>', {
				'class': 'selected-projects-wrapper atp-section',
				'data-section': 'selected'
			});
				selected_projects_wrapper.appendTo( widget.editor );

			$('<h2/>',{'class': 'atbb-widget-option-heading'}).text('Selected Projects').appendTo(selected_projects_wrapper);
			$('<em/>').html('Projects in this list will be used in this portfolio set. You may reorder or remove projects.').appendTo(selected_projects_wrapper);

			var selected_projects = new widget.SortableContainer( '[selected-projects]' );
				selected_projects.addClass('atp-selected-projects').appendTo( selected_projects_wrapper );

			function addProject( values ) {
				if( selected_projects.has('[data-id="'+values.id+'"]').length ) {
					return;
				}
				var project = selected_projects.addGroup();
					project.attr({ 'data-id': values.id });

				var thumb = $('<div/>', { 'class': 'atp-thumbnail atbb-widget-options-group-handle', title: values.title }).appendTo( project );

				if( values.thumb ) {
					thumb.css({
						'background-image': 'url('+ values.thumb +')'
					});
				}

				var remove_button = $('<span/>', {'class': 'fa fa-times atp-remove-button'}).appendTo(thumb);
					remove_button.on('click', function() {
						project.Remove();
						updateOnAddRemove();
					});

				project.addOption({
					title: 'Project ID',
					description: '',
					type: 'text',
					name: '[id]',
					value: values.id ? values.id : ''
				});

				updateOnAddRemove();
			}
			//--------------------------

			





			//---------------------------------------------------
			var categories_wrapper = $('<div/>', {
				'class': 'categories-wrapper atp-section',
				'data-section': 'categories'
			});
				categories_wrapper.appendTo( widget.editor );

			$('<h2/>',{'class': 'atbb-widget-option-heading'}).text('Categories').appendTo(categories_wrapper);
			$('<em/>').html('This list contains the categories that are asigned to the selected projects.').appendTo(categories_wrapper);

			var categories = new widget.SortableContainer( '[selected-categories]' );
				categories.addClass('atp-categories').appendTo( categories_wrapper );

			function addCategory( values ) {
				var category = categories.addGroup();
					category.attr({ 'data-id': values.id });

				var cat = $('<div/>', {
					'class': 'atp-cat atbb-widget-options-group-handle',
					title: values.title
				}).text(values.title).appendTo( category );

				/*var remove_button = $('<span/>', {'class': 'fa fa-times atp-remove-button'}).appendTo(thumb);
					remove_button.on('click', function() {
						category.Remove();
					});*/

				category.addOption({
					title: 'Category ID',
					description: '',
					type: 'text',
					name: '[id]',
					value: values.id ? values.id : ''
				});

				//console.log( 'added category' );
			}
			//--------------------------




			//--------------------------
			function updateOnAddRemove() {
				var cat_ids = [];

				available_projects.children().removeClass( 'atp-disabled' );
				selected_projects.children().each(function() {
					var project = $(this).data( 'group' );
					var project_id = $(this).attr( 'data-id' );

					$.each(atp_data.posts[ project_id ].terms, function( id ) {
						if( $.inArray( id, cat_ids ) === -1 ) {
							cat_ids.push( id );
						}
					});
					
					available_projects.children('[data-id="'+ project_id +'"]').addClass( 'atp-disabled' );
					//console.log( cat_ids );
				});

				categories.children('li').each(function() {
					var category = $(this).data( 'group' );
						category.Remove();
				});

				if( data['selected-categories'] ) {
					$.each(data['selected-categories'], function( c ) {
						if( data['selected-categories'][ c ] ) {
							var id = data['selected-categories'][ c ].id;
							if( atp_data.terms[id] ) {
								var values = {
									id: id,
									title: atp_data.terms[id].title
								};
								if( $.inArray(id, cat_ids) !== -1 ) {
									addCategory( values );
								}
							}
							//console.log( atp_data.terms[id].title );
						}
					});
				}

				$.each(cat_ids, function( index, cat_id ) {
					//console.log( cat_id );
					var category = categories.children('[data-id="'+ cat_id +'"]');
					if( category.length === 0 && atp_data.terms[cat_id] ) {
						var values = {
							id: cat_id,
							title: atp_data.terms[cat_id].title
						};
						addCategory( values );
					}// END IF
				});

				//console.log( cat_ids );
			}
			//--------------------------












			//--------------------------
			var settings_wrapper = $('<div/>', {
				'class': 'settings-wrapper atp-section',
				'data-section': 'settings'
			});
			settings_wrapper.appendTo( widget.editor );

			$('<h2/>',{'class': 'atbb-widget-option-heading'}).text('Portfolio Settings').appendTo(settings_wrapper);
			$('<em/>').html('Customize the portfolio to your needs by choosing the culomn layout, whether or not to use the filter/categories.').appendTo(settings_wrapper);

			$('<hr/>').appendTo(settings_wrapper);

			var portfolio_type = new widget.Option({
				title: 'Portfolio Type',
				description: 'Select portfolio type. The "Grid Portfolio" is a standard portfolio with specific number of columns, where a "Slider Portfolio" is a slideshow of images (baner rotator) and has no columns property.',
				type: 'select',
				options: {
					'grid': 'Grid Portfolio',
					'slider': 'Slider Portfolio',
					'carousel': 'Carousel Portfolio'
				},
				name: '[type]',
				value: widget.settings.type ? widget.settings.type : 'grid'
			});
			portfolio_type.appendTo( settings_wrapper );


			var settings_cont = $('<div/>').appendTo( settings_wrapper );
			portfolio_type.on('fieldchange', function() {
				var type_settings = widget.getSettings();

					settings_cont.children().remove();

				switch( type_settings.type ) {
					case 'grid':
						new widget.Option({
							title: 'Columns',
							description: 'Select the number of columns',
							type: 'select',
							options: {
								'1': 'One Column',
								'2': 'Two Columns',
								'3': 'Three Columns',
								'4': 'Four Columns'
							},
							name: '[columns]',
							value: widget.settings.columns ? widget.settings.columns : '3'
						}).appendTo( settings_cont );


						//------
						new widget.Option({
							title: 'Categories / Filters',
							description: 'Check the box if you whish to use the filtering functionality.',
							type: 'checkbox',
							name: '[use_filters]',
							value: widget.settings.use_filters ? widget.settings.use_filters : false
						}).appendTo( settings_cont );

						new widget.Option({
							title: 'Show All Button Label',
							description: 'Type in a label for "Show all projects" button in filters.',
							type: 'text',
							name: '[filter_all_label]',
							value: widget.settings.filter_all_label ? widget.settings.filter_all_label : 'Show All'
						}).appendTo( settings_cont );
						//------
						
						new widget.Option({
							title: 'No Gutter',
							description: 'Check the box to remove any spacing between the portfolio items.',
							type: 'checkbox',
							name: '[no_gutter]',
							value: widget.settings.no_gutter ? widget.settings.no_gutter : false
						}).appendTo( settings_cont );
					break;





					case 'slider':
						new widget.Option({
							title: 'Animation Type',
							description: 'Set the slideshow animation type.',
							type: 'select',
							options: {
								'fade': 'Fade',
								'slide': 'Slide'
							},
							name: '[animation_type]',
							value: widget.settings.animation_type ? widget.settings.animation_type : 'fade'
						}).appendTo( settings_cont );

						new widget.Option({
							title: 'Slideshow Speed',
							description: 'Set the pause time between slides (in seconds).',
							type: 'number',
							name: '[slideshow_speed]',
							step: 0.1,
							min: 0,
							value: widget.settings.slideshow_speed ? widget.settings.slideshow_speed : 6
						}).appendTo( settings_cont );

						new widget.Option({
							title: 'Animation Speed',
							description: 'Set the animation duration (in seconds).',
							type: 'number',
							name: '[animation_speed]',
							step: 0.1,
							min: 0,
							value: widget.settings.animation_speed ? widget.settings.animation_speed : 0.6
						}).appendTo( settings_cont );

						new widget.Option({
							title: 'Use Directional Controls',
							description: 'Check the box if you wish to have next and previous buttons for the slider.',
							type: 'checkbox',
							name: '[use_controls]',
							value: widget.settings.use_controls ? widget.settings.use_controls : false
						}).appendTo( settings_cont );

						new widget.Option({
							title: 'Use Pagination Controls',
							description: 'Check the box if you wish to have the pagination buttons ("Dots Naviagtion") for the slider.',
							type: 'checkbox',
							name: '[use_paginate_controls]',
							value: widget.settings.use_paginate_controls ? widget.settings.use_paginate_controls : false
						}).appendTo( settings_cont );
					break;





					case 'carousel':
						new widget.Option({
							title: 'Columns',
							description: 'Select the number of columns',
							type: 'select',
							options: {
								'2': 'Two Columns',
								'3': 'Three Columns',
								'4': 'Four Columns'
							},
							name: '[columns]',
							value: widget.settings.columns ? widget.settings.columns : '3'
						}).appendTo( settings_cont );

						new widget.Option({
							title: 'Use Directional Controls',
							description: 'Check the box if you wish to have next and previous buttons for the slider.',
							type: 'checkbox',
							name: '[use_controls]',
							value: widget.settings.use_controls ? widget.settings.use_controls : false
						}).appendTo( settings_cont );

						new widget.Option({
							title: 'Use Pagination Controls',
							description: 'Check the box if you wish to have the pagination buttons ("Dots Naviagtion") for the slider.',
							type: 'checkbox',
							name: '[use_paginate_controls]',
							value: widget.settings.use_paginate_controls ? widget.settings.use_paginate_controls : false
						}).appendTo( settings_cont );

						new widget.Option({
							title: 'No Gutter',
							description: 'Check the box to remove any spacing between the portfolio items.',
							type: 'checkbox',
							name: '[no_gutter]',
							value: widget.settings.no_gutter ? widget.settings.no_gutter : false
						}).appendTo( settings_cont );
					break;
				}
			});
			portfolio_type.trigger( 'fieldchange' );

			$('<hr/>').appendTo( settings_wrapper );

			var all_projects_checkbox = new widget.Option({
				title: 'Add All Projects',
				description: 'Check the box if you wish to add all projects to this portfolio automatically.',
				type: 'checkbox',
				name: '[all_projects]',
				value: widget.settings.all_projects ? widget.settings.all_projects : false,
			}).appendTo( settings_wrapper );

			new widget.Option({
				title: 'Ignore Project Thumbnail',
				description: 'Check the box if you wish to use the "Featured Image" of a project as its thumbnail instead of the "Project Thumbnail" option field.',
				type: 'checkbox',
				name: '[ignore_thumb]',
				value: widget.settings.ignore_thumb ? widget.settings.ignore_thumb : false,
			}).appendTo( settings_wrapper );

			new widget.Option({
				title: 'Additional CSS Class',
				description: 'This option puts the content into an additional element and applies the class-name set below, allowing you to apply specific styling via CSS.',
				type: 'text',
				name: '[class]',
				value: widget.settings['class'] ? widget.settings['class'] : '',
				placeholder: 'Example: awesome-css-class'
			}).appendTo( settings_wrapper );
			//--------------------------



			//--------------------------
			if( data['selected-projects'] ) {
				$.each(data['selected-projects'], function( p ) {
					if( data['selected-projects'][ p ] ) {
						var id = data['selected-projects'][ p ].id;
						if( atp_data.posts[id] ) {
							var values = {
								id: id,
								title: atp_data.posts[id].title,
								thumb: atp_data.posts[id].sizes.thumb[0]
							};
							addProject( values );
						}
					}
				});
			}
			//--------------------------




			//--------------------------
			var settings_tab = new widget.editor.HeaderControl( 'fa-cogs atp-tab' );
				settings_tab.attr({ 'data-section': 'settings' }).setLabel( 'Settings' );
			//--------------------------

			new widget.editor.HeaderControl( 'atbb-widget-editor-header-separator' );

			//--------------------------
			var categories_list_tab = new widget.editor.HeaderControl( 'fa-bookmark atp-tab' );
				categories_list_tab.attr({ 'data-section': 'categories' }).setLabel( 'Categories' );
			//--------------------------

			//--------------------------
			var selected_list_tab = new widget.editor.HeaderControl( 'fa-check atp-tab' );
				selected_list_tab.attr({ 'data-section': 'selected' }).setLabel( 'Selected' );
			//--------------------------


			//--------------------------
			var available_list_tab = new widget.editor.HeaderControl( 'fa-paperclip atp-tab' );
				available_list_tab.attr({ 'data-section': 'available' }).setLabel( 'Available' );
			//--------------------------


			widget.find('.atp-tab').on('click', function() {
				//console.log('atp-tab');
				var section = $(this).attr('data-section');

				widget.find('.atp-tab').removeClass( 'atp-active-tab' );
				$(this).addClass( 'atp-active-tab' );

				widget.editor.find('.atp-section').removeClass( 'atp-active-section' );
				widget.editor.find('.atp-section[data-section="'+ section +'"]').addClass( 'atp-active-section' );
			});

			available_list_tab.trigger('click');
			//--------------------------
			
			

			all_projects_checkbox.on('change', function() {
				var checked = $(this).find('[type="checkbox"]').prop('checked');
				if( checked ) {
					automatically_add_projects();
					selected_projects.addClass('atp-disable-removes');
				} else {
					selected_projects.removeClass('atp-disable-removes');
				}
			});
			all_projects_checkbox.trigger('change');


			//--------------------------
			widget.on('onsave', function() {
				automatically_add_projects();

				var settings = widget.getSettings();
				//console.log( settings );
				var projects_obj = settings['selected-projects'] ? settings['selected-projects'] : {};

				var content = $('<div/>', {'class': 'atp-portfolio-content'});
				var portfolio = $('<ul/>', {'class': 'atp-portfolio atp-portfolio-'+ settings.type +' atp-columns-'+ (settings.columns ? settings.columns : 1)}).appendTo( content );
				
				
				$.each(projects_obj, function( i, obj ) {
					var thumb = atp_data.posts[obj.id].thumb;

					if( (parseInt(settings.columns) < 3) || settings.type === 'slider' ) {
						thumb = atp_data.posts[obj.id].sizes.large[0] || atp_data.posts[obj.id].thumb;
					}

					$('<li/>').css({
						'background-image': 'url('+ thumb +')'
					}).appendTo( portfolio );
				});
				
				widget.setContent( $('<div/>').append( content ).html() );
			});






			function applyFlexSlider() {
				var settings = widget.getSettings();
				var portfolio = widget.content.find('.atp-portfolio-content')

				var lis = portfolio.find('.atp-portfolio > li');
				var margin = parseInt( lis.eq(0).css('margin-right') );
				var width = lis.eq(0).width();
				var cols = parseInt( settings.columns ) || 3;



				if( portfolio.flexslider ) {
					if( settings.type === 'slider' ) {
						lis.css({
							height: portfolio.width() * 70 / 100,
						});
						portfolio.flexslider({
							namespace: "atp-preview-",
							selector: '.atp-portfolio > li',
							animation: 'slide',
							smoothHeight: true,
							controlNav: true,
							directionNav: false,
							prevText: '',
							nextText: '',
							slideshowSpeed: 6000,
							animationSpeed: 500,
							useCSS: true,
							slideshow: false,
							start: function() {}
						});
					} else if( settings.type === 'carousel' ) {
						portfolio.flexslider({
							namespace: "atp-preview-",
							selector: '.atp-portfolio > li',
							animation: 'slide',

							animationLoop: false,
							itemWidth: Math.floor( width ),
							itemMargin: margin,
							maxItems: cols,

							smoothHeight: false,
							controlNav: true,
							directionNav: false,
							prevText: '',
							nextText: '',
							slideshowSpeed: 6000,
							animationSpeed: 500,
							useCSS: true,
							slideshow: false,
							start: function() {
								lis.css({
									height: width,
									'margin-right': margin
								});
							}
						});
					}
				}//END if flexslider
			}




			

			
			
			function automatically_add_projects() {
				if( atp_data.posts ) {
					$.each(atp_data.posts, function( id, post ) {
						var thumb_url = post.sizes && post.sizes.thumb && post.sizes.thumb[0] ? post.sizes.thumb[0] : post.thumb;

						if( widget.settings.all_projects ) {
							addProject({ id: id, title: post.title, thumb: thumb_url });
						}
					});
				}
			}
			automatically_add_projects();



			//--------------------------
			widget.on('oncontent', function() {
				applyFlexSlider();
			});
		},
		output: function( widget, section ) {
			var output = ''; //must be a [String object]
			var settings = widget.getSettings();

			//console.log( section );

			var shortcode = atp_data.shortcodes[settings.type] ? atp_data.shortcodes[settings.type] : false;
			if (!shortcode) return output;
			var classes = (settings['class'] ? settings['class'] : '');
			var ignore_thumb = settings.ignore_thumb ? 'ignorethumb="true" ' : '';
			var all_projects = settings.all_projects ? 'allprojects="true" ' : '';

			var projects_obj = settings['selected-projects'] ? settings['selected-projects'] : {};
			var categories_obj = settings['selected-categories'] ? settings['selected-categories'] : {};

			var projects = [];
			var categories = [];

			//var layout_column_class = $('.atbb-layout-columns').children().has(widget).attr('class');

			$.each(projects_obj, function( i, obj ) {
				projects.push( obj.id );
			});

			$.each(categories_obj, function( i, obj ) {
				categories.push( obj.id );
			});

			if( projects.length ) {
				//output += '<div class="atp-portfolio-wrapper'+ classes +'">';
				switch( settings.type ) {
					case 'grid':
						var columns = settings.columns ? 'columns="'+ settings.columns +'" ' : 'columns="3" ';
						var filters = settings.use_filters ? 'filters="true" ' : '';
						var show_all_label = settings.filter_all_label ? 'showall="'+settings.filter_all_label+'" ' : '';
						var gutter = settings.no_gutter ? 'nogutter="true" ' : '';

						output += '['+shortcode+' type="'+settings.type+'" ';
						output += 'projects="' + projects.join(',') + '" ';
						output += 'categories="' + categories.join(',') + '" ';
						output += columns + filters + show_all_label + gutter + ignore_thumb + all_projects;
						output += section.mode ? 'section_width="' + section.mode + '" ' : '';
						output += section.row_mode ? 'row_width="' + section.row_mode + '" ' : '';
						output += classes !== '' ? 'classes="'+ classes +'"' : '';
						output += '/]';
					break;
					case 'slider':
						var animation_type = settings.animation_type ? 'animation="'+ settings.animation_type +'" ' : 'animation="fade" ';
						var slideshow_speed = settings.slideshow_speed ? 'slidespeed="'+settings.slideshow_speed+'" ' : 'slidespeed="6" ';
						var animation_speed = settings.animation_speed ? 'animspeed="'+settings.animation_speed+'" ' : 'animspeed="0.6" ';

						var use_controls = settings.use_controls ? 'dircontrols="true" ' : '';
						var use_paginate_controls = settings.use_paginate_controls ? 'navcontrols="true" ' : '';

						output += '['+shortcode+' type="'+settings.type+'" ';
						output += 'projects="' + projects.join(',') + '" ';
						output += 'categories="' + categories.join(',') + '" ';
						output += animation_type + slideshow_speed + animation_speed + use_controls + use_paginate_controls + ignore_thumb + all_projects;
						output += section.mode ? 'section_width="' + section.mode + '" ' : '';
						output += section.row_mode ? 'row_width="' + section.row_mode + '" ' : '';
						output += classes !== '' ? 'classes="'+ classes +'"' : '';
						output += '/]';
					break;
					case 'carousel':
						var columns = settings.columns ? 'columns="'+ settings.columns +'" ' : 'columns="3" ';
						var use_controls = settings.use_controls ? 'dircontrols="true" ' : '';
						var use_paginate_controls = settings.use_paginate_controls ? 'navcontrols="true" ' : '';
						var gutter = settings.no_gutter ? 'nogutter="true" ' : '';

						output += '['+shortcode+' type="'+settings.type+'" ';
						output += 'projects="' + projects.join(',') + '" ';
						output += 'categories="' + categories.join(',') + '" ';
						output += columns + use_controls + use_paginate_controls + gutter + ignore_thumb + all_projects;
						output += section.mode ? 'section_width="' + section.mode + '" ' : '';
						output += section.row_mode ? 'row_width="' + section.row_mode + '" ' : '';
						output += classes !== '' ? 'classes="'+ classes +'"' : '';
						output += '/]';
					break;
				}
				//output += '</div>';
			}// END if

			return output;
		}
	});

	



	/* Helper function */

})(jQuery);






















/* Project metabox */
(function($){
	'use strict';

	$('.atp-meta-option-thumb').on('click', function() {
		var button = $(this);
		var option = $('.atp-meta-option').has(this);
		var field = option.find('input');

		var media_window = new BoxBuilderMediaWindow({
			title: button.attr('title'),
			button: button.attr('title'),
			library: 'image'
		});

		media_window.on('mediaselect', function(e, attachment) {
			option.addClass('atp-has-image');
			field.val( attachment.id );

			button.find('img').remove();
			$('<img/>', { src: attachment.url }).appendTo( button );
		});
	});

	$('.atp-remove-thumb').on('click', function() {
		var button = $(this);
		var option = $('.atp-meta-option').has(this);
		var field = option.find('input');

		option.removeClass('atp-has-image').find('img').remove();

		field.val('');
	});

})(jQuery);