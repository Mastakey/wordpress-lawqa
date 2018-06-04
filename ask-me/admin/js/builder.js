jQuery(function() {
	jQuery(".builder_select").live('mouseup',function () {
		jQuery(this).select();
	});
	
	function uploaded_image() {
		jQuery(".adv-label").each(function () {
			var adv_label = jQuery(this);
			if (jQuery("input[type='radio']:checked",adv_label).val() == "custom_image") {
				jQuery(".image-url",adv_label.parent()).show(10);
				jQuery(".adv-url",adv_label.parent()).show(10);
				jQuery(".adv-code",adv_label.parent()).hide(10);
			}else if (jQuery("input[type='radio']:checked",adv_label).val() == "display_code") {
				jQuery(".image-url",adv_label.parent()).hide(10);
				jQuery(".adv-url",adv_label.parent()).hide(10);
				jQuery(".adv-code",adv_label.parent()).show(10);
			}
			jQuery("input[type='radio']",adv_label).click(function () {
				if (jQuery(this).val() == "custom_image") {
					jQuery(".image-url",jQuery(this).parent().parent()).slideDown(500);
					jQuery(".adv-url",jQuery(this).parent().parent()).slideDown(500);
					jQuery(".adv-code",jQuery(this).parent().parent()).slideUp(500);
				}else if (jQuery(this).val() == "display_code") {
					jQuery(".image-url",jQuery(this).parent().parent()).slideUp(500);
					jQuery(".adv-url",jQuery(this).parent().parent()).slideUp(500);
					jQuery(".adv-code",jQuery(this).parent().parent()).slideDown(500);
				}
			});
		});
	}
	
    jQuery("#expand-all .expand-all2").live("click" ,function () {
    	jQuery(".widget-content").slideUp(300);
    	jQuery(".builder-toggle-close").css("display","none");
    	jQuery(".builder-toggle-open").css("display","block");
    	jQuery(".expand-all").css("display","block");
    	jQuery(".expand-all2").css("display","none");
    });
    
    jQuery("#add_badge").click(function() {
    	var badge_name = jQuery('#badge_name').val();
    	var badge_points = jQuery('#badge_points').val();
    	var badge_color = jQuery('#badge_color').val();
    	var intRegex = /^\d+$/;
    	if (badge_name == "") {
    		alert("Please write the name !");
    	}else if (badge_points == "") {
    		alert("Please write the points !");
    	}else if (!intRegex.test(badge_points)) {
    		alert("Sorry not number !");
    	}else if (badge_color == "") {
    		alert("Please write the color !");
    	}else {
    		var badges_list = jQuery("#badges_list > li").length;
    		badges_list++;
    		jQuery('#badges_list').append('<li class="badges_last"><a class="del-builder-item del-badge-item">x</a><div class="widget-head">'+badge_name+'</div><div class="widget-content"><h4 class="heading">Badge name</h4><input name="badges['+badges_list+'][badge_name]" type="text" value="'+badge_name+'"><div class="clear"></div><h4 class="heading">Badge points</h4><input name="badges['+badges_list+'][badge_points]" type="text" value="'+badge_points+'"><div class="clear"></div><h4 class="heading">Badge color</h4><input class="of-color badge_color" name="badges['+badges_list+'][badge_color]" type="text" value="'+badge_color+'"><div class="clear"></div></div></li>');
    		jQuery('.badges_last .badge_color').wpColorPicker();
    		jQuery('#badge_name').val("");
    		jQuery('#badge_points').val("");
    		jQuery('#badge_color').val("");
    		jQuery('.badges_last').removeClass('badges_last');
    	}
    });
    
    /* Add a new element */
    
    jQuery(".add_element").on("click",function () {
    	var add_element = jQuery(this);
    	if (!add_element.hasClass("not_add_element")) {
    		var ask_theme_var = ask_theme;
    		var data_id	 = add_element.attr("data-id");
    		var data_add_to = add_element.parent().find(".all_elements ul").attr("data-to");
    		var data_id_name = "["+data_id+"]";
    		var data_add_to_name = "["+data_add_to+"]";
    		
    		if (add_element.hasClass("no_ask_theme")) {
    			var ask_theme_var = "";
    			var data_id_name = data_id;
    			var data_add_to_name = data_add_to;
    		}
    		
    		var data_title  = add_element.attr("data-title");
    		if (data_add_to !== undefined && data_add_to !== false) {
    			var add_element_j = jQuery("#"+data_add_to+" li").length;
    			add_element_j++;
    			var element_id = "elements_"+data_add_to+"_"+add_element_j;
    			add_element.parent().find(".all_elements ul li").clone().attr("id",element_id).appendTo('#'+data_add_to);
    			jQuery("html,body").animate({scrollTop: jQuery("#"+element_id).offset().top-35},"slow");
    		}else {
    			var add_element_j = add_element.parent().find("."+data_id+"_j").attr("data-js");
    			var element_id = "elements_"+data_id+"_"+add_element_j;
    			add_element.parent().find(".all_elements ul li").clone().attr("id",element_id).appendTo('#'+data_id);
    			jQuery("html,body").animate({scrollTop: jQuery("#"+element_id).offset().top-35},"slow");
    		}
    		
    		if (data_title !== undefined && data_title !== false) {
    			jQuery("#"+element_id+" .del-builder-item").wrap("<div class='widget-head'>"+jQuery(add_element.parent().find(".all_elements ul li input[data-title='"+data_title+"']")).val()+"</div>");
    		}
    		
    		jQuery("#"+element_id+" .widget-content select").each(function () {
    			var this_each = jQuery(this);
    			if (data_add_to !== undefined && data_add_to !== false) {
    				var last_id   = ask_theme_var+"_"+data_add_to+"_"+add_element_j+"_"+this_each.attr("data-attr");
    				var last_name = ask_theme_var+data_add_to_name+"["+add_element_j+"]["+this_each.attr("data-attr")+"]";
    			}else {
    				var last_id   = ask_theme_var+"_"+data_id+"_"+add_element_j+"_"+this_each.attr("data-attr");
    				var last_name = ask_theme_var+data_id_name+"["+add_element_j+"]["+this_each.attr("data-attr")+"]";
    			}
    			this_each.attr("name",last_name).attr("id",last_id);
    		});
    			
    		jQuery("#"+element_id+" .widget-content input,#"+element_id+" .widget-content textarea").each(function () {
    			var this_each = jQuery(this);
    			if (data_add_to !== undefined && data_add_to !== false) {
    				var last_id   = ask_theme_var+"_"+data_add_to+"_"+add_element_j+"_"+this_each.attr("data-attr");
    				var last_name = ask_theme_var+data_add_to_name+"["+add_element_j+"]["+this_each.attr("data-attr")+"]";
    			}else {
    				var last_id   = ask_theme_var+"_"+data_id+"_"+add_element_j+"_"+this_each.attr("data-attr");
    				var last_name = ask_theme_var+data_id_name+"["+add_element_j+"]["+this_each.attr("data-attr")+"]";
    			}
    			this_each.attr("name",last_name).attr("id",last_id);
    			
    			if (this_each.parent().hasClass("image_element")) {
    				this_each.next("img").attr("onclick","document.getElementById('"+last_id+"').checked=true;");
    				
    				this_each.next("img").click(function(){
    					jQuery(this).parent().find('.of-radio-img-radio').removeAttr('checked');
    					jQuery(this).parent().find('.of-radio-img-img').removeClass('of-radio-img-selected');
    					jQuery(this).addClass('of-radio-img-selected').prev("input.of-radio-img-radio").click().attr('checked','checked');
    				});
    			}
    			
    			if (this_each.parent().find("div.v_slidersui").length) {
    				var obj   = this_each.next("div.v_slidersui");
    				obj.attr("id",last_id+"-slider").attr("data-id",last_id);
    				obj.removeClass('v_slidersui').addClass('v_sliderui');
    				
    				var sId   = "#" + obj.data('id');
    				var val   = parseInt(obj.data('val'));
    				var min   = parseInt(obj.data('min'));
    				var max   = parseInt(obj.data('max'));
    				var step  = parseInt(obj.data('step'));
    				
    				obj.slider({
    					value: val,
    					min: min,
    					max: max,
    					step: step,
    					range: "min",
    					slide: function( event, ui ) {
    						jQuery(sId).val( ui.value );
    					}
    				});
    			}
    			
    		});
    		
    		if (data_add_to !== undefined && data_add_to !== false) {
    			jQuery("#"+element_id).append('<input name="'+ask_theme_var+'['+data_add_to+']['+add_element_j+'][getthe]" value="'+data_add_to+'" type="hidden">');
    		}
    		if (!add_element.parent().find(".all_elements ul li input").is(':radio') && !add_element.parent().find(".all_elements ul li input").is(':checkbox')) {
    			add_element.parent().find(".all_elements ul li input").val("");
    		}
    		if (!add_element.parent().find(".all_elements ul li textarea")) {
    			add_element.parent().find(".all_elements ul li textarea").val("");
    		}
    		add_element_j++;
    		add_element.parent().find("."+data_id+"_j").attr("data-js",add_element_j);
    		
    		jQuery('#'+element_id+' .of-colors').wpColorPicker();
    		jQuery('#'+element_id+' .builder-datepicker').removeClass("builder-datepicker").removeClass("hasDatepicker").addClass("of-datepicker").datepicker();
    	}
    });
    
    jQuery("#add_coupon").click(function() {
    	var coupon_name = jQuery('#coupon_name').val();
    	var coupon_type = jQuery('#coupon_type').val();
    	var coupon_amount = jQuery('#coupon_amount').val();
    	var coupon_date = jQuery('#coupon_date').val();
    	var intRegex = /^\d+$/;
    	if (coupon_name == "") {
    		alert("Please write the name !");
    	}else if (coupon_type == "") {
    		alert("Please write the coupon type !");
    	}else if (coupon_amount == "") {
    		alert("Please write the amount !");
    	}else if (!intRegex.test(coupon_amount)) {
    		alert("Sorry not number !");
    		jQuery('#coupon_amount').val("");
    	}else {
    		var coupons_list = jQuery("#coupons_list > li").length;
    		coupons_list++;
    		jQuery('#coupons_list').append('<li class="coupons_last"><a class="del-builder-item del-coupon-item">x</a><div class="widget-content"><h4 class="heading">Coupon name</h4><input name="coupons['+coupons_list+'][coupon_name]" type="text" value="'+coupon_name+'" class="coupon_name"><div class="clear"></div><h4 class="heading">Discount type</h4><div class="styled-select"><select class="coupon_type" name="coupons['+coupons_list+'][coupon_type]"><option value="discount"'+(coupon_type == "discount"?" selected='selected'":"")+'>Discount</option><option value="percent"'+(coupon_type == "percent"?" selected='selected'":"")+'>% Percent</option></select></div><div class="clear"></div><h4 class="heading">Amount</h4><input name="coupons['+coupons_list+'][coupon_amount]" class="coupon_amount" type="text" value="'+coupon_amount+'"><div class="clear"></div><h4 class="heading">Expiry date</h4><input name="coupons['+coupons_list+'][coupon_date]" class="of-datepicker coupon_date" type="text" value="'+coupon_date+'"><div class="clear"></div></div></li>');
    		jQuery('.coupons_last .of-datepicker').datepicker();
    		jQuery('#coupon_name').val("");
    		jQuery('#coupon_amount').val("");
    		jQuery('#coupon_date').val("");
    		jQuery('.coupons_last').removeClass('coupons_last');
    	}
    });
    
    jQuery("#sidebar_add").click(function() {
    	var sidebar_name = jQuery('#sidebar_name').val();
    	if (sidebar_name != "" ) {
    		if( sidebar_name.length > 0){
    			jQuery('#sidebars_list').append('<li><div class="widget-head">'+sidebar_name+' <input id="sidebars" name="sidebars[]" type="hidden" value="'+sidebar_name+'"><a class="del-builder-item del-sidebar-item">x</a></div></li>');
    		}
    	}else {
    		alert("Please write the name !");
    	}
    	jQuery('#sidebar_name').val("");
    });
	
	jQuery("#role_add").click(function() {
		var role_name = jQuery('#role_name').val();
		if (role_name != "" ) {
			if( role_name.length > 0){
				jQuery('#roles_list').append('<li><div class="widget-head">'+role_name+'<a class="del-builder-item del-role-item">x</a></div><div class="widget-content"><div class="widget-content-div"><label for="roles['+ roles_j +'][group]">Type here the group name .</label><input id="roles['+ roles_j +'][group]" type="text" name="roles['+ roles_j +'][group]" value="'+role_name+'"><input type="hidden" class="group_id" name="roles['+ roles_j +'][id]" value="group_'+ roles_j +'"><div class="clearfix"></div><label class="switch" for="roles['+ roles_j +'][ask_question]"><input id="roles['+ roles_j +'][ask_question]" type="checkbox" name="roles['+ roles_j +'][ask_question]"><label for="roles['+ roles_j +'][ask_question]" data-on="'+builder_on+'" data-off="'+builder_off+'"></label></label><label for="roles['+ roles_j +'][ask_question]">Select ON to can add a question.</label><div class="clearfix"></div><label class="switch" for="roles['+ roles_j +'][show_question]"><input id="roles['+ roles_j +'][show_question]" type="checkbox" name="roles['+ roles_j +'][show_question]"><label for="roles['+ roles_j +'][show_question]" data-on="'+builder_on+'" data-off="'+builder_off+'"></label></label><label for="roles['+ roles_j +'][show_question]">Select ON to can show questions.</label><div class="clearfix"></div><label class="switch" for="roles['+ roles_j +'][add_answer]"><input id="roles['+ roles_j +'][add_answer]" type="checkbox" name="roles['+ roles_j +'][add_answer]"><label for="roles['+ roles_j +'][add_answer]" data-on="'+builder_on+'" data-off="'+builder_off+'"></label></label><label for="roles['+ roles_j +'][add_answer]">Select ON to can add a answer.</label><div class="clearfix"></div><label class="switch" for="roles['+ roles_j +'][show_answer]"><input id="roles['+ roles_j +'][show_answer]" type="checkbox" name="roles['+ roles_j +'][show_answer]"><label for="roles['+ roles_j +'][show_answer]" data-on="'+builder_on+'" data-off="'+builder_off+'"></label></label><label for="roles['+ roles_j +'][show_answer]">Select ON to can show answers.</label><div class="clearfix"></div><label class="switch" for="roles['+ roles_j +'][add_post]"><input id="roles['+ roles_j +'][add_post]" type="checkbox" name="roles['+ roles_j +'][add_post]"><label for="roles['+ roles_j +'][add_post]" data-on="'+builder_on+'" data-off="'+builder_off+'"></label></label><label for="roles['+ roles_j +'][add_post]">Select ON to can add a post.</label><div class="clearfix"></div><label class="switch" for="roles['+ roles_j +'][send_message]"><input id="roles['+ roles_j +'][send_message]" type="checkbox" name="roles['+ roles_j +'][send_message]"><label for="roles['+ roles_j +'][send_message]" data-on="'+builder_on+'" data-off="'+builder_off+'"></label></label><label for="roles['+ roles_j +'][send_message]">Select ON to can send a message.</label><div class="clearfix"></div><label class="switch" for="roles['+ roles_j +'][upload_files]"><input id="roles['+ roles_j +'][upload_files]" type="checkbox" name="roles['+ roles_j +'][upload_files]"><label for="roles['+ roles_j +'][upload_files]" data-on="'+builder_on+'" data-off="'+builder_off+'"></label></label><label for="roles['+ roles_j +'][upload_files]">Select ON to can upload files.</label><div class="clearfix"></div></div></div></li>');
				roles_j ++ ;
			}
		}else {
			alert("Please write the name !");
		}
		jQuery('#role_name').val("");

	});
    
	var categories_select = jQuery('#categories_select').html();
	jQuery(".add-item").live("click" , function() {
		var builder_item = jQuery(this).attr("add-item");
		if (builder_item == "add_slide") {
			jQuery('#vbegy_slideshow_post ul').append('<li id="builder_slide_'+ builder_slide_j +'" class="ui-state-default"><div class="widget-head text"><span class="vpanel'+ builder_slide_j +'">Slide item - '+ builder_slide_j +'</span><a class="builder-toggle-open" style="display:none">+</a><a class="builder-toggle-close" style="display:block">-</a></div><div class="widget-content" style="display:block"><label for="builder_slide_item['+ builder_slide_j +'][image_url]"><span>Image URL :</span><input id="builder_slide_item['+ builder_slide_j +'][image_url]" name="builder_slide_item['+ builder_slide_j +'][image_url]" placeholder="No file chosen" type="text" class="upload upload_image_'+ builder_slide_j +'"><input class="upload_image_button button upload-button-2" rel="'+ builder_slide_j +'" type="button" value="Upload"><input type="hidden" class="image_id" name="builder_slide_item['+ builder_slide_j +'][image_id]" value=""><div class="clear"></div></label><label for="builder_slide_item['+ builder_slide_j +'][slide_link]"><span>Slide Link :</span><input id="builder_slide_item['+ builder_slide_j +'][slide_link]" name="builder_slide_item['+ builder_slide_j +'][slide_link]" value="#" type="text"></label></div><a class="del-builder-item">x</a></li>');
		}
		if (builder_item == "add_slide") {
			jQuery('#builder_slide_'+ builder_slide_j).hide().fadeIn();
			builder_slide_j ++ ;
		}
		jQuery('.tooltip_s').tipsy({gravity: 's'});
		return false;
	});
	
	
	jQuery(".del-builder-item").live("click" , function() {
		if (jQuery(this).hasClass("del-sidebar-item")) {
			jQuery(this).parent().parent().addClass('removered').fadeOut(function() {
				jQuery(this).remove();
			});
		}else if (jQuery(this).hasClass("del-role-item")) {
			var group = jQuery(this);
			roles_j = roles_j-1;
			var answer = confirm("If you press will delete group !");
			if (answer) {
				var group_id = jQuery(this).parent().parent().find(".group_id").val();
				var defaults = "group_id="+group_id+"&action=delete_group";
				jQuery.post(builder_ajax,defaults,function (data) {
					group.parent().parent().addClass('removered').fadeOut(function() {
						jQuery(this).remove();
					});
				});
			}
		}else {
			jQuery(this).parent().addClass('removered').fadeOut(function() {
				jQuery(this).remove();
			});
		}
		return false;
	});
	
	uploaded_image();
	
	jQuery( "#question_poll_item" ).sortable({placeholder: "ui-state-highlight"});
	
	jQuery("#upload_add_ask").click(function() {
		jQuery('#question_poll_item').append('<li id="listItem_'+ nextCell +'" class="ui-state-default"><div class="widget-content option-item"><div class="rwmb-input"><input id="ask['+ nextCell +'][title]" class="ask" name="ask['+ nextCell +'][title]" value="" type="text"><input id="ask['+ nextCell +'][value]" name="ask['+ nextCell +'][value]" value="" type="hidden"><input id="ask['+ nextCell +'][id]" name="ask['+ nextCell +'][id]" value="'+ nextCell +'" type="hidden"><a class="del-cat">x</a></div></div></li>');
		nextCell ++ ;
		return false;
	});
	
	jQuery(".del-cat").live("click" , function() {
		jQuery(this).parent().parent().addClass('removered').fadeOut(function() {
			jQuery(this).remove();
		});
	});

	var question_poll = jQuery("#vpanel_question_poll:checked").length;
	if (question_poll == 1) {
		jQuery(".vpanel_poll_options").slideDown(500);
	}else {
		jQuery(".vpanel_poll_options").slideUp(500);
	}
	
	jQuery("#vpanel_question_poll").click(function() {
		var vpanel_question_poll = jQuery("#vpanel_question_poll:checked").length;
		if (vpanel_question_poll == 1) {
			jQuery(".vpanel_poll_options").slideDown(500);
		}else {
			jQuery(".vpanel_poll_options").slideUp(500);
		}
	});
	
	/* Add new category */
	
	jQuery(".add-item.add-item-2.add-item-6").on("click",function () {
		var add_item = jQuery(this);
		var add_item_parent = add_item.parent();
		var addto = jQuery(this).data("addto");
		var add_to_jquery = add_item_parent.find(".category_tabs > ul");
		var item_name = jQuery(this).data("name");
		var select_val = add_item_parent.find("select").val();
		var select_val_array = '['+select_val+']';
		if (addto !== undefined && addto !== false) {
			add_to_jquery = jQuery("#"+addto);
			var number_id = add_to_jquery.find(" > li").length;
			number_id++;
			if (number_id > 0) {
				var i_count = 0;
				while (i_count < number_id) {
					if (add_to_jquery.find(" > li.category_tabs_cat_"+number_id).length) {
						number_id++;
					}
					i_count++;
				}
			}else {
				number_id++;
			}
			item_name = addto+"["+number_id+"][cat]";
			select_val_array = '';
		}
		var item_id = jQuery(this).data("id");
		var select_text = add_item_parent.find("select option:selected").text();
		
		if (add_to_jquery.find("#"+item_id+'_'+select_val).length) {
			add_to_jquery.find("#"+item_id+'_'+select_val).addClass("removered").slideUp(function() {
				jQuery(this).slideDown().removeClass("removered");
			});
		}else {
			add_to_jquery.append('<li id="'+item_id+'_'+select_val+'" class="ui-state-default'+(number_id !== undefined && number_id !== false?" category_tabs_cat_"+number_id:"")+'"><div class="widget-head ui-sortable-handle"><span>'+select_text+'</span></div><input name="'+item_name+select_val_array+'" value="'+select_val+'" type="hidden"><a class="del-builder-item"><span class="dashicons dashicons-trash"></span></a></li>');
		}
	});
	
});