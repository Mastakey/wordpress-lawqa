<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Categories_Field' ) )
{
	class RWMB_Categories_Field extends RWMB_Field
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
			return '<div class="rwmb-label">
				<label for="'.(isset($field['id']) && $field['id'] != ""?$field['id']:"").'">'.(isset($field['name']) && $field['name'] != ""?$field['name']:"").'</label>
			</div>
			<div class="rwmb-input">
				<div class="vpanel_checklist vpanel_scroll"><ul class="categorychecklist vpanel_categorychecklist">'.
					ask_categories_checklist(array("taxonomy" => (isset($field['taxonomy']) && $field['taxonomy'] != ""?$field['taxonomy']:"category"),"id" => (isset($field['id']) && $field['id'] != ""?$field['id']:""),"name" => (isset($field['id']) && $field['id'] != ""?$field['id']:""),"selected_cats" => (isset($meta) && is_array($meta)?$meta:""))).
				'</ul></div>
			</div>';
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