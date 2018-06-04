<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Sort_Field' ) )
{
	class RWMB_Sort_Field extends RWMB_Field
	{
		/**
		 * Get field HTML
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			$meta = (array) $meta;
			$sort_sections = $meta[0];
			$output = '<ul id="'.$field['id'].'" class="sort-sections">';
				if (empty($sort_sections)) {
					$sort_sections = $field['options'];
				}else {
					if (isset($field['merge']) && !empty($field['merge']) && is_array($field['merge'])) {
						foreach ($field['merge'] as $key_merge => $value_merge) {
							$sort_sections = (!in_array($value_merge,$sort_sections)?array_merge($sort_sections,array($value_merge)):$sort_sections);
						}
					}
				}
				
				$array_not_found = $field['options'];
				foreach ($array_not_found as $key_not => $value_not) {
					if (!in_array($value_not,$sort_sections)) {
						array_push($sort_sections,$value_not);
					}
				}
				
				if (isset($sort_sections) && is_array($sort_sections)) {
					foreach ($sort_sections as $key => $value_for) {
						if (!isset($value_for["cat"]) && !isset($value_for["value"]) && !isset($value_for["name"])) {
							unset($sort_sections[$key]);
						}
					}
				}
				
				$i = 0;
				if (isset($sort_sections) && is_array($sort_sections)) {
					foreach ($sort_sections as $key => $value_for) {
						$i++;
						if (isset($value_for["cat"]) && $value_for["cat"] > 0) {
							$term_cat = get_term_by('id',$value_for["cat"],ask_question_category);
						}
						$output .= '<li id="'.esc_attr((isset($value_for["cat"])?'vbegy_categories_show_categories_'.$value_for["cat"]:$value_for["value"])).'" class="'.esc_attr('category_tabs_cat_'.$i).' ui-state-default">
							<div class="widget-head"><span>'.esc_attr((isset($value_for["cat"])?($value_for["cat"] > 0?(isset($term_cat->name)?$term_cat->name:""):__('All Categories','vbegy')):$value_for["name"])).'</span></div>';
							foreach ($value_for as $key_a => $value_a) {
								$output .= '<input name="'.esc_attr( $field['id'] . '['.esc_attr($i).']['.$key_a.']' ).'" value="'.esc_attr($value_for[$key_a]).'" type="hidden">'.($key_a == "cat"?'<a class="del-builder-item"><span class="dashicons dashicons-trash"></span></a>':'');
							}
						$output .= '</li>';
					}
				}
			$output .= '</ul>';
			
			return $output;
		}
		
		/**
		 * Get meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
		 *
		 * @see "save" method for better understanding
		 *
		 * @param $post_id
		 * @param $saved
		 * @param $field
		 *
		 * @return array
		 */
		static function meta( $post_id, $saved, $field )
		{
			$meta = get_post_meta( $post_id, $field['id'], $field['clone'] );
			$meta = ( !$saved && '' === $meta || array() === $meta ) ? $field['std'] : $meta;
			$meta = $meta;
			
			return $meta;
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field['multiple']   = true;
			$field['field_name'] = $field['id'];
			if ( !$field['clone'] )
				$field['field_name'] .= '[]';
			return $field;
		}
	}
}
