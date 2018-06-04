jQuery(document).ready(function($){

	var optionsframework_upload;
	var optionsframework_selector;

	function optionsframework_add_file(event, selector) {

		var upload = $(".uploaded-file"), frame;
		var upload_2 = $(".uploaded-file-2"), frame;
		var $el = $(this);
		optionsframework_selector = selector;

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( optionsframework_upload ) {
			optionsframework_upload.open();
		} else {
			// Create the media frame.
			optionsframework_upload = wp.media.frames.optionsframework_upload =  wp.media({
				// Set the title of the modal.
				title: $el.data('choose'),

				// Customize the submit button.
				button: {
					// Set the text of the button.
					text: $el.data('update'),
					// Tell the button not to close the modal, since we're
					// going to refresh the page when the image is selected.
					close: false
				}
			});

			// When an image is selected, run a callback.
			optionsframework_upload.on( 'select', function() {
				// Grab the selected attachment.
				var attachment = optionsframework_upload.state().get('selection').first();
				attachment = attachment.toJSON();
				optionsframework_upload.close();
				optionsframework_selector.find('.upload').val(attachment.url);
				if (optionsframework_selector.hasClass("upload-button-2")) {
					optionsframework_selector.parent().find('.upload').val(attachment.url);
					optionsframework_selector.parent().find('.image_id').val(attachment.id);
				}
				if (optionsframework_selector.find('.upload').attr("id") == "logo_img") {
					jQuery('#logo_height').val(attachment.height);
					jQuery('#logo_width').val(attachment.width);
				}
				if (optionsframework_selector.find('.upload').attr("id") == "products_logo_img") {
					jQuery('#products_logo_height').val(attachment.height);
					jQuery('#products_logo_width').val(attachment.width);
				}
				if (optionsframework_selector.find('.upload').attr("id") == "questions_logo_img") {
					jQuery('#questions_logo_height').val(attachment.height);
					jQuery('#questions_logo_width').val(attachment.width);
				}
				if ( attachment.type == 'image' ) {
					optionsframework_selector.find('.screenshot').empty().hide().append('<img src="' + attachment.url + '"><a class="remove-image">Remove</a>').slideDown('fast');
				}
				optionsframework_selector.find('.upload-button').unbind().addClass('remove-file').removeClass('upload-button').val(optionsframework_l10n.remove);
				optionsframework_selector.find('.of-background-properties').slideDown();
				optionsframework_selector.find('.remove-image, .remove-file').on('click', function() {
					optionsframework_remove_file( $(this).parents('.section') );
				});
			});

		}

		// Finally, open the modal.
		optionsframework_upload.open();
	}

	function optionsframework_remove_file(selector) {
		selector.find('.remove-image').hide();
		selector.find('.upload').val('');
		selector.find('.of-background-properties').hide();
		selector.find('.screenshot').slideUp();
		selector.find('.remove-file').unbind().addClass('upload-button').removeClass('remove-file').val(optionsframework_l10n.upload);
		// We don't display the upload button if .upload-notice is present
		// This means the user doesn't have the WordPress 3.5 Media Library Support
		if ( $('.section-upload .upload-notice').length > 0 ) {
			$('.upload-button').remove();
		}
		selector.find('.upload-button').on('click', function(event) {
			optionsframework_add_file(event, $(this).parents('.section'));
		});
	}

	$('.remove-image, .remove-file').on('click', function() {
		optionsframework_remove_file( $(this).parents('.section') );
    });

    $('.upload-button').click( function( event ) {
    	optionsframework_add_file(event, $(this).parents('.section'));
    });
    $('.upload-button-2').live( "click", function( event ) {
    	optionsframework_add_file(event, $(this));
    });

});