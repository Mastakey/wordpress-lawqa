<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Elements_Field' ) )
{
	class RWMB_Elements_Field extends RWMB_Field
	{
		/**
		 * Show begin HTML markup for fields
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function begin_html( $meta, $field )
		{
			$option_name = $output = '';
			$output .= '<div class="all_elements">
				<ul class="sort-sections not-sort not-add-item'.(isset($field['hide']) && $field['hide'] == "yes"?" ask_hidden":"").'"'.(isset($field['addto']) && $field['addto'] != ""?" data-to='".$field['addto']."'":"").'>
					<li>';
						if (isset($field["title"]) && $field["title"] != "") {
							$output .= (isset($field['addto']) && $field['addto'] != ""?"":'<a class="widget-handle"><span class="dashicons dashicons-editor-justify"></span></a>').'<a class="del-builder-item del-sidebar-item"><span class="dashicons dashicons-trash"></span></a>';
						}else {
							$output .= '<div>'.(isset($field['addto']) && $field['addto'] != ""?"":'<a class="widget-handle"><span class="dashicons dashicons-editor-justify"></span></a>').'<a class="del-builder-item del-sidebar-item"><span class="dashicons dashicons-trash"></span></a></div>';
						}
						$output .= '<div class="widget-content">';
							foreach ($field['options'] as $key_e => $value_e) {
								$output .= '<h4 class="heading">'.$value_e["name"].'</h4>';
								if ($value_e["type"] == "images") {
									$output .= '<div class="image_element">'.
									ask_option_images($field['id'],'','',$value_e["options"],$value_e["std"],'',$option_name,'no',$value_e["id"]).
									'</div>';
								}else if ($value_e["type"] == "select") {
									$output .= '<div class="styled-select"><select data-attr="'.$value_e["id"].'" class="of-input" '.(isset($value_e['multiple']) && $value_e['multiple'] != ""?"multiple":"").'>';
									foreach ($value_e['options'] as $key => $option ) {
										$output .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
									}
									$output .= '</select></div>';
								}else if ($value_e["type"] == "textarea") {
									$output .= '<div class="rwmb-input"><textarea data-attr="'.$value_e["id"].'" class="of-input"></textarea></div>';
								}else {
									if ($value_e["type"] == "slider") {
										$output .= '<div class="section-sliderui">';
									}
									if ($value_e["type"] != "color" && $value_e["type"] != "slider") {
										$output .= '<div class="rwmb-input">';
									}
									$output .= '<input'.(isset($field['title']) && $field['title'] != ""?" data-title='".$field['title']."'":"").($value_e["type"] == "color"?" class='of-colors'":"").($value_e["type"] == "slider"?" value='".(isset($value_e['value']) && $value_e['value'] != ""?$value_e['value']:"")."' class='mini'":"").' data-attr="'.$value_e["id"].'" data-value="'.(isset($value_e['value']) && $value_e['value'] != ""?$value_e['value']:"").'" type="text">';
									if ($value_e["type"] != "color" && $value_e["type"] != "slider") {
										$output .= '</div>';
									}
									if ($value_e["type"] == "slider") {
										$data = 'data-id="slider-id" data-val="'.$value_e['value'].'" data-min="'.$value_e['min'].'" data-max="'.$value_e['max'].'" data-step="'.$value_e['step'].'"';
										$output .= '<div id="slider-id-slider" class="v_slidersui" '. $data .'></div></div>';
									}
								}
								$output .= '<div class="clear"></div>';
							}
						$output .= '</div>
					</li>
				</ul>
			</div>
			<input class="add_element no_ask_theme" type="button" value="'.(isset($field['button']) && $field['button'] != ""?$field['button']:"+ Add new element").'"'.(isset($field['id']) && $field['id'] != ""?" data-id='".$field['id']."'":"").(isset($field['title']) && $field['title'] != ""?" data-title='".$field['title']."'":"").'>
			<div class="clear"></div>
			<ul class="sort-sections sort-sections-with sort-sections-ul" id="'.(isset($field['id']) && $field['id'] != ""?$field['id']:"").'">';
				$i = 0;
				if($meta && empty($field['addto'])) {
					foreach ($meta as $key_a => $value_a) {
						$i++;
						$output .= '<li id="elements_'.$field['id'].'_'.$i.'">';
							if (isset($field["title"]) && $field["title"] != "") {
								$output .= '<div class="widget-head"><span>'.esc_attr($value_a["name"]).'</span><a class="widget-handle"><span class="dashicons dashicons-editor-justify"></span></a><a class="del-builder-item del-sidebar-item"><span class="dashicons dashicons-trash"></span></a></div>';
							}else {
								$output .= '<div><a class="widget-handle"><span class="dashicons dashicons-editor-justify"></span></a><a class="del-builder-item del-sidebar-item"><span class="dashicons dashicons-trash"></span></a></div>';
							}
							$output .= '<div class="widget-content">';
								foreach ($field['options'] as $key_l => $value_l) {
									$last_val = $value_a[$value_l['id']];
									$output .= '<h4 class="heading">'.$value_l["name"].'</h4>';
									if ($value_l["type"] == "images") {
										$output .= '<div class="image_element">'.
										ask_option_images(esc_attr($option_name).'_'.$field['id'].'_'.$i.'_'.$value_l['id'],'','',$value_l["options"],$last_val,'',esc_attr($option_name).$field['id'].'['.$i.']['.$value_l['id'].']','',$value_l["id"],'no').
										'</div>';
									}else if ($value_l["type"] == "select") {
										$output .= '<div class="styled-select"><select data-attr="'.$value_l["id"].'" class="of-input" '.(isset($value_l['multiple']) && $value_l['multiple'] != ""?"multiple":"").' name="'.esc_attr($option_name).$field['id'].'['.$i.']['.$value_l['id'].']'.(isset($value_l['multiple']) && $value_l['multiple'] != ""?"[]":"") . '" id="' . esc_attr( $value_l['id'] ) . '">';
										foreach ($value_l['options'] as $key => $option ) {
											$output .= '<option'. (isset($value_l['multiple']) && $value_l['multiple'] != ""?(isset($last_val) && is_array($last_val) && in_array($key,$last_val)?' selected="selected"':""):selected( $last_val, $key, false )) .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
										}
										$output .= '</select></div>';
									}else if ($value_l["type"] == "textarea") {
										$output .= '<div class="rwmb-input"><textarea data-attr="'.$value_l["id"].'" class="of-input" name="'.esc_attr($option_name).$field['id'].'['.$i.']['.$value_l['id'].']" id="' . esc_attr( $value_l['id'] ) . '">'.$last_val.'</textarea></div>';
									}else if ($value_l["type"] == "slider") {
										$output .= '<div class="section-sliderui">'.
										ask_option_sliderui($value_l["min"],$value_l["max"],$value_l["step"],'',$last_val,$field['id'].'['.$i.']['.$value_l['id'],esc_attr($option_name),esc_attr($option_name).'_'.$field['id'].'_'.$i.'_'.$value_l['id'],'remove_it').
										'</div>';
									}else {
										if ($value_l["type"] != "color") {
											$output .= '<div class="rwmb-input">';
										}
										$output .= '<input'.($value_l["type"] == "color"?" class='of-color'":"").' name="'.esc_attr($option_name).$field['id'].'['.$i.']['.$value_l['id'].']" type="text" value="'.$last_val.'">';
										if ($value_l["type"] != "color") {
											$output .= '</div>';
										}
									}
									$output .= '<div class="clear"></div>';
								}
							$output .= '</div>
						</li>';
					}
				}
			$output .= '</ul>
			<script type="text/javascript" data-js="'.esc_js($i+1).'" class="'.$field['id'].'_j">'.$field['id'].'_j = '.esc_js($i+1).';</script>';
			return $output;
		}

		/**
		 * Show end HTML markup for fields
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function end_html( $meta, $field )
		{
			return '';
		}
	}
}?>