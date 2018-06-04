jQuery( function( $ )
{
	'use strict';
	
	jQuery('.rwmb-slider').each(function() {
		
		var obj   = jQuery(this);
		var sId   = "#" + obj.attr('id');
		var val    = parseInt(obj.parent().find(".rwmb-slider-value-label > span").text());
		var min   = parseInt(obj.data('min'));
		var max   = parseInt(obj.data('max'));
		var step  = parseInt(obj.data('step'));
		
		//slider init
		obj.slider({
			value: val,
			min: min,
			max: max,
			step: step,
			range: "min",
			slide: function( event, ui ) {
				jQuery(sId).parent().find(".rwmb-slider-value-label > span").text( ui.value );
				jQuery(sId).parent().find(".rwmb-slider-value").val( ui.value );
			}
		});
		
	});
} );