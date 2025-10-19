/**
 * YT Random Background Generator - JavaScript
 *
 * @package YT_Random_Background_Generator
 * @version 1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Random Background Generator Handler
	 */
	var RandomBackgroundGenerator = {

		/**
		 * Color picker instances.
		 */
		colorPickers: [],

		/**
		 * Media uploader instance.
		 */
		mediaUploader: null,

		/**
		 * Initialize the plugin.
		 */
		init: function() {
			this.bindEvents();
			this.initializeColorPickers();
			this.handleBackgroundTypeChange();
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents: function() {
			var self = this;

			// Add color button.
			$(document).on('click', '#yt-rbg-add-color', function(e) {
				e.preventDefault();
				self.addColorItem();
			});

			// Add image button.
			$(document).on('click', '#yt-rbg-add-image', function(e) {
				e.preventDefault();
				self.addImageItem();
			});

			// Remove item button.
			$(document).on('click', '.yt-rbg-remove-item', function(e) {
				e.preventDefault();
				self.removeItem($(this));
			});

			// Background type change.
			$(document).on('change', '#yt-rbg-background-type', function() {
				self.handleBackgroundTypeChange();
			});

			// Generate preview button.
			$(document).on('click', '#yt-rbg-generate-preview', function(e) {
				e.preventDefault();
				self.generatePreview();
			});

			// Form submission.
			$('form').on('submit', function() {
				self.cleanupColorPickers();
			});
		},

		/**
		 * Initialize color pickers.
		 */
		initializeColorPickers: function() {
			var self = this;

			$('.yt-rbg-color-picker').each(function() {
				self.initColorPicker($(this));
			});
		},

		/**
		 * Initialize single color picker.
		 *
		 * @param {jQuery} $input Color input element.
		 */
		initColorPicker: function($input) {
			if ($input.hasClass('wp-color-picker-initialized')) {
				return;
			}

			$input.addClass('wp-color-picker-initialized');

			$input.wpColorPicker({
				change: function(event, ui) {
					// Update preview if needed.
				},
				clear: function() {
					// Handle clear.
				}
			});

			this.colorPickers.push($input);
		},

		/**
		 * Cleanup color pickers before form submit.
		 */
		cleanupColorPickers: function() {
			// Remove color picker UI to avoid duplicate field submissions.
			$('.wp-picker-container').each(function() {
				var $container = $(this);
				var $input = $container.find('.wp-color-picker');
				if ($input.length) {
					$container.after($input);
				}
			});
		},

		/**
		 * Add color item.
		 */
		addColorItem: function() {
			var $list = $('#yt-rbg-colors-list');
			var index = $list.find('.yt-rbg-color-item').length;

			var $item = $('<div>', {
				'class': 'yt-rbg-item yt-rbg-color-item'
			});

			var $input = $('<input>', {
				'type': 'text',
				'name': 'yt_rbg_options[colors][]',
				'value': '#3498db',
				'class': 'yt-rbg-color-picker'
			});

			var $removeBtn = $('<button>', {
				'type': 'button',
				'class': 'button yt-rbg-remove-item',
				'text': ytRbgData.strings.addColor || 'Remove'
			});

			$item.append($input, $removeBtn);
			$list.append($item);

			// Initialize color picker for new item.
			this.initColorPicker($input);

			// Animate.
			$item.hide().fadeIn(300);
		},

		/**
		 * Add image item.
		 */
		addImageItem: function() {
			var self = this;

			// Create media uploader if not exists.
			if (this.mediaUploader) {
				this.mediaUploader.open();
				return;
			}

			this.mediaUploader = wp.media({
				title: ytRbgData.strings.selectImage || 'Select Background Image',
				button: {
					text: ytRbgData.strings.useImage || 'Use This Image'
				},
				multiple: false
			});

			this.mediaUploader.on('select', function() {
				var attachment = self.mediaUploader.state().get('selection').first().toJSON();
				self.createImageItem(attachment.url);
			});

			this.mediaUploader.open();
		},

		/**
		 * Create image item in list.
		 *
		 * @param {string} imageUrl Image URL.
		 */
		createImageItem: function(imageUrl) {
			var $list = $('#yt-rbg-images-list');

			var $item = $('<div>', {
				'class': 'yt-rbg-item yt-rbg-image-item'
			});

			var $preview = $('<div>', {
				'class': 'yt-rbg-image-preview',
				'css': {
					'background-image': 'url(' + imageUrl + ')'
				}
			});

			var $hiddenInput = $('<input>', {
				'type': 'hidden',
				'name': 'yt_rbg_options[images][]',
				'value': imageUrl
			});

			var $textInput = $('<input>', {
				'type': 'text',
				'value': imageUrl,
				'readonly': true,
				'class': 'regular-text'
			});

			var $removeBtn = $('<button>', {
				'type': 'button',
				'class': 'button yt-rbg-remove-item',
				'text': 'Remove'
			});

			$item.append($preview, $hiddenInput, $textInput, $removeBtn);
			$list.append($item);

			// Animate.
			$item.hide().fadeIn(300);
		},

		/**
		 * Remove item from list.
		 *
		 * @param {jQuery} $button Remove button.
		 */
		removeItem: function($button) {
			if (!confirm(ytRbgData.strings.confirmDelete || 'Are you sure?')) {
				return;
			}

			var $item = $button.closest('.yt-rbg-item');

			// Cleanup color picker if needed.
			var $colorInput = $item.find('.wp-color-picker');
			if ($colorInput.length && $colorInput.wpColorPicker) {
				$colorInput.wpColorPicker('destroy');
			}

			$item.fadeOut(300, function() {
				$(this).remove();
			});
		},

		/**
		 * Handle background type change.
		 */
		handleBackgroundTypeChange: function() {
			var type = $('#yt-rbg-background-type').val();
			var $colorsSection = $('.yt-rbg-colors-section');
			var $imagesSection = $('.yt-rbg-images-section');

			// Show/hide sections based on type.
			if (type === 'color') {
				$colorsSection.show();
				$imagesSection.hide();
			} else if (type === 'image') {
				$colorsSection.hide();
				$imagesSection.show();
			} else {
				// Mixed mode - show both.
				$colorsSection.show();
				$imagesSection.show();
			}
		},

		/**
		 * Generate preview.
		 */
		generatePreview: function() {
			var self = this;
			var $preview = $('#yt-rbg-preview');
			var $button = $('#yt-rbg-generate-preview');

			// Show loading state.
			$button.prop('disabled', true).text('Generating...');
			$preview.addClass('yt-rbg-loading');

			// Get random background.
			var background = this.getRandomBackground();

			// Apply background.
			this.applyBackgroundToPreview(background);

			// Reset button.
			setTimeout(function() {
				$button.prop('disabled', false).text('Generate Random Preview');
				$preview.removeClass('yt-rbg-loading');
			}, 500);
		},

		/**
		 * Get random background from current settings.
		 *
		 * @return {object} Background data.
		 */
		getRandomBackground: function() {
			var type = $('#yt-rbg-background-type').val();
			var background = {};

			if (type === 'color' || (type === 'mixed' && Math.random() < 0.5)) {
				background = this.getRandomColor();
			} else {
				background = this.getRandomImage();
			}

			return background;
		},

		/**
		 * Get random color from list.
		 *
		 * @return {object} Color data.
		 */
		getRandomColor: function() {
			var colors = [];

			$('#yt-rbg-colors-list .yt-rbg-color-picker').each(function() {
				var color = $(this).val();
				if (color) {
					colors.push(color);
				}
			});

			if (colors.length === 0) {
				// Fallback color.
				var fallback = $('input[name="yt_rbg_options[fallback_color]"]').val() || '#ffffff';
				return {
					type: 'color',
					value: fallback
				};
			}

			var randomIndex = Math.floor(Math.random() * colors.length);

			return {
				type: 'color',
				value: colors[randomIndex]
			};
		},

		/**
		 * Get random image from list.
		 *
		 * @return {object} Image data.
		 */
		getRandomImage: function() {
			var images = [];

			$('#yt-rbg-images-list input[name="yt_rbg_options[images][]"]').each(function() {
				var image = $(this).val();
				if (image) {
					images.push(image);
				}
			});

			if (images.length === 0) {
				// Fallback to color.
				return this.getRandomColor();
			}

			var randomIndex = Math.floor(Math.random() * images.length);

			return {
				type: 'image',
				value: images[randomIndex]
			};
		},

		/**
		 * Apply background to preview area.
		 *
		 * @param {object} background Background data.
		 */
		applyBackgroundToPreview: function(background) {
			var $preview = $('#yt-rbg-preview');
			var css = {};

			if (background.type === 'color') {
				css = {
					'background-color': background.value,
					'background-image': 'none'
				};
			} else {
				var imageSize = $('select[name="yt_rbg_options[image_size]"]').val() || 'cover';
				var imagePosition = $('select[name="yt_rbg_options[image_position]"]').val() || 'center center';
				var imageRepeat = $('select[name="yt_rbg_options[image_repeat]"]').val() || 'no-repeat';

				css = {
					'background-image': 'url(' + background.value + ')',
					'background-size': imageSize,
					'background-position': imagePosition,
					'background-repeat': imageRepeat,
					'background-color': 'transparent'
				};
			}

			$preview.css(css);

			// Update text color for contrast.
			this.updatePreviewTextColor($preview, background);
		},

		/**
		 * Update preview text color for better contrast.
		 *
		 * @param {jQuery} $preview Preview element.
		 * @param {object} background Background data.
		 */
		updatePreviewTextColor: function($preview, background) {
			if (background.type === 'color') {
				var brightness = this.getBrightness(background.value);
				var textColor = brightness > 128 ? '#1d2327' : '#ffffff';
				$preview.css('color', textColor);
			} else {
				// For images, use a semi-transparent overlay effect.
				$preview.css('color', '#ffffff');
				$preview.css('text-shadow', '0 2px 4px rgba(0, 0, 0, 0.5)');
			}
		},

		/**
		 * Calculate brightness of hex color.
		 *
		 * @param {string} hex Hex color code.
		 * @return {number} Brightness value (0-255).
		 */
		getBrightness: function(hex) {
			// Remove # if present.
			hex = hex.replace('#', '');

			// Convert to RGB.
			var r = parseInt(hex.substr(0, 2), 16);
			var g = parseInt(hex.substr(2, 2), 16);
			var b = parseInt(hex.substr(4, 2), 16);

			// Calculate brightness.
			return (r * 299 + g * 587 + b * 114) / 1000;
		},

		/**
		 * Handle AJAX preview request.
		 */
		ajaxPreview: function() {
			var self = this;

			$.ajax({
				url: ytRbgData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'yt_rbg_preview_background',
					nonce: ytRbgData.nonce
				},
				success: function(response) {
					if (response.success) {
						self.applyBackgroundToPreview(response.data.background);
					} else {
						alert(ytRbgData.strings.previewError || 'Preview error');
					}
				},
				error: function() {
					alert(ytRbgData.strings.previewError || 'Preview error');
				}
			});
		},

		/**
		 * Add drag and drop sorting.
		 */
		initSortable: function() {
			var $colorsList = $('#yt-rbg-colors-list');
			var $imagesList = $('#yt-rbg-images-list');

			if (typeof $.fn.sortable !== 'undefined') {
				$colorsList.sortable({
					placeholder: 'yt-rbg-sortable-placeholder',
					handle: '.yt-rbg-drag-handle',
					opacity: 0.7,
					start: function(e, ui) {
						ui.item.addClass('yt-rbg-dragging');
					},
					stop: function(e, ui) {
						ui.item.removeClass('yt-rbg-dragging');
					}
				});

				$imagesList.sortable({
					placeholder: 'yt-rbg-sortable-placeholder',
					handle: '.yt-rbg-drag-handle',
					opacity: 0.7,
					start: function(e, ui) {
						ui.item.addClass('yt-rbg-dragging');
					},
					stop: function(e, ui) {
						ui.item.removeClass('yt-rbg-dragging');
					}
				});
			}
		},

		/**
		 * Validate form before submission.
		 *
		 * @return {boolean} Whether form is valid.
		 */
		validateForm: function() {
			var type = $('#yt-rbg-background-type').val();
			var hasColors = $('#yt-rbg-colors-list .yt-rbg-color-item').length > 0;
			var hasImages = $('#yt-rbg-images-list .yt-rbg-image-item').length > 0;

			if (type === 'color' && !hasColors) {
				alert('Please add at least one color.');
				return false;
			}

			if (type === 'image' && !hasImages) {
				alert('Please add at least one image.');
				return false;
			}

			if (type === 'mixed' && !hasColors && !hasImages) {
				alert('Please add at least one color or image.');
				return false;
			}

			return true;
		},

		/**
		 * Export settings as JSON.
		 */
		exportSettings: function() {
			var settings = {
				colors: [],
				images: []
			};

			$('#yt-rbg-colors-list .yt-rbg-color-picker').each(function() {
				settings.colors.push($(this).val());
			});

			$('#yt-rbg-images-list input[name="yt_rbg_options[images][]"]').each(function() {
				settings.images.push($(this).val());
			});

			var json = JSON.stringify(settings, null, 2);

			// Create download link.
			var blob = new Blob([json], { type: 'application/json' });
			var url = URL.createObjectURL(blob);
			var a = document.createElement('a');
			a.href = url;
			a.download = 'random-backgrounds-settings.json';
			a.click();
			URL.revokeObjectURL(url);
		},

		/**
		 * Import settings from JSON.
		 *
		 * @param {File} file JSON file.
		 */
		importSettings: function(file) {
			var self = this;
			var reader = new FileReader();

			reader.onload = function(e) {
				try {
					var settings = JSON.parse(e.target.result);

					// Clear existing items.
					$('#yt-rbg-colors-list').empty();
					$('#yt-rbg-images-list').empty();

					// Import colors.
					if (settings.colors && Array.isArray(settings.colors)) {
						settings.colors.forEach(function(color) {
							self.addColorItem();
							$('#yt-rbg-colors-list .yt-rbg-color-picker').last().val(color).trigger('change');
						});
					}

					// Import images.
					if (settings.images && Array.isArray(settings.images)) {
						settings.images.forEach(function(image) {
							self.createImageItem(image);
						});
					}

					alert('Settings imported successfully!');
				} catch (error) {
					alert('Invalid JSON file.');
				}
			};

			reader.readAsText(file);
		},

		/**
		 * Show keyboard shortcuts help.
		 */
		showKeyboardHelp: function() {
			var helpText = 'Keyboard Shortcuts:\n\n';
			helpText += 'Ctrl/Cmd + S: Save settings\n';
			helpText += 'Ctrl/Cmd + P: Generate preview\n';
			helpText += 'Ctrl/Cmd + N: Add new color\n';
			helpText += 'Ctrl/Cmd + M: Add new image\n';

			alert(helpText);
		},

		/**
		 * Add keyboard shortcuts.
		 */
		addKeyboardShortcuts: function() {
			var self = this;

			$(document).on('keydown', function(e) {
				// Only on settings page.
				if (!$('.yt-rbg-settings-page').length) {
					return;
				}

				// Ctrl/Cmd + P: Generate preview.
				if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
					e.preventDefault();
					$('#yt-rbg-generate-preview').click();
				}

				// Ctrl/Cmd + N: Add color.
				if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
					e.preventDefault();
					$('#yt-rbg-add-color').click();
				}

				// Ctrl/Cmd + M: Add image.
				if ((e.ctrlKey || e.metaKey) && e.key === 'm') {
					e.preventDefault();
					$('#yt-rbg-add-image').click();
				}

				// Ctrl/Cmd + /: Show help.
				if ((e.ctrlKey || e.metaKey) && e.key === '/') {
					e.preventDefault();
					self.showKeyboardHelp();
				}
			});
		}
	};

	/**
	 * Initialize when DOM is ready.
	 */
	$(document).ready(function() {
		// Check if we're on the settings page.
		if ($('.yt-rbg-settings-page').length > 0) {
			RandomBackgroundGenerator.init();
			RandomBackgroundGenerator.addKeyboardShortcuts();
		}
	});

	// Expose to global scope for external use.
	window.RandomBackgroundGenerator = RandomBackgroundGenerator;

})(jQuery);
