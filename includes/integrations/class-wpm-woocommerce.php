<?php
/**
 * Class for capability with WooCommerce
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_WooCommerce
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_WooCommerce {

	private $attribute_taxonomies_config = array();

	/**
	 * WPM_WooCommerce constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_get_name', 'wpm_translate_string' );
		add_filter( 'woocommerce_product_get_description', 'wpm_translate_string' );
		add_filter( 'woocommerce_product_get_short_description', 'wpm_translate_string' );
		add_filter( 'woocommerce_shortcode_products_query', array( $this, 'remove_filter' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_frontend' ) );
		add_filter( 'woocommerce_cart_shipping_method_full_label', 'wpm_translate_string' );
		add_filter( 'woocommerce_shipping_instance_form_fields_flat_rate', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_free_shipping', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_flat_rate', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_free_shipping', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_international_delivery', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_local_delivery', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_legacy_local_pickup', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_instance_form_fields_local_pickup', 'wpm_translate_value' );
		add_filter( 'woocommerce_shipping_free_shipping_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_flat_rate_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_flat_rate_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_free_shipping_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_international_delivery_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_local_delivery_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_legacy_local_pickup_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_local_pickup_instance_settings_values', array( $this, 'update_shipping_settings' ), 10, 2 );
		add_filter( 'woocommerce_shipping_zone_shipping_methods', array( $this, 'translate_zone_shipping_methods' ) );
		add_filter( 'woocommerce_gateway_method_title', 'wpm_translate_string' );
		add_filter( 'woocommerce_gateway_method_description', 'wpm_translate_string' );
		add_filter( 'wpm_role_translator_capability_types', array( $this, 'add_capabilities_types' ) );
		add_filter( 'wpm_taxonomies_config', array( $this, 'add_attribute_taxonomies' ) );
		add_filter( 'woocommerce_attribute_label', 'wpm_translate_string' );
		add_filter( 'woocommerce_attribute_taxonomies', array( $this, 'translate_attribute_taxonomies' ) );
		add_action( 'admin_head', array( $this, 'set_translation_for_attribute_taxonomies' ) );
	}


	/**
	 * Add script for reload cart after change language
	 */
	public function enqueue_js_frontend() {
		if ( did_action( 'wpm_changed_language' ) ) {
			wp_add_inline_script( 'wc-cart-fragments', "
				jQuery( function ( $ ) {
					$( document.body ).trigger( 'wc_fragment_refresh' );
				});
			");
		}
	}

	/**
	 * Set translate in settings
	 *
	 * @param array $settings
	 * @param object $shipping
	 *
	 * @return array
	 */
	public function update_shipping_settings( $settings, $shipping ) {

		$old_settings = get_option( $shipping->get_instance_option_key(), array() );

		$setting_config = array(
			'title' => array(),
		);

		$settings = wpm_set_new_value( $old_settings, $settings, $setting_config );

		return $settings;
	}

	/**
	 * Translate methods for zone
	 *
	 * @param array $methods
	 *
	 * @return array
	 */
	public function translate_zone_shipping_methods( $methods ) {

		foreach ( $methods as &$method ) {
			$method->title = wpm_translate_string( $method->title );
		}

		return $methods;
	}

	/**
	 * Remove translation result query for products in shortcode
	 *
	 * @param array $query_args
	 *
	 * @return array
	 */
	public function remove_filter( $query_args ) {

		$query_args['suppress_filters'] = true;

		return $query_args;
	}

	/**
	 * Add capability for translating products for user role
	 *
	 * @since 2.0.0
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public function add_capabilities_types( $types ) {

		$types[] = 'product';

		return $types;
	}

	/**
	 * Set attribute taxonomies for translate
	 *
	 * @param $taxonomies_config
	 *
	 * @return array
	 */
	public function add_attribute_taxonomies( $taxonomies_config ) {

		if ( ! $this->attribute_taxonomies_config ) {

			if ( ! $attribute_taxonomies = get_transient( 'wc_attribute_taxonomies' ) ) {
				$attribute_taxonomies = array();
			}

			foreach ( $attribute_taxonomies as $tax ) {
				if ( $name = wc_attribute_taxonomy_name( $tax->attribute_name ) ) {
					$this->attribute_taxonomies_config[ $name ] = array();
				}
			}
		}

		return wpm_array_merge_recursive( $this->attribute_taxonomies_config, $taxonomies_config );
	}

	/**
	 * Translate attribute taxonomies
	 *
	 * @param $attribute_taxonomies
	 *
	 * @return mixed
	 */
	public function translate_attribute_taxonomies( $attribute_taxonomies ) {

		foreach ( $attribute_taxonomies as &$tax ) {
			$tax->attribute_label = wpm_translate_string( $tax->attribute_label );
		}

		return $attribute_taxonomies;
	}

	/**
	 * Filter action for save and add attribute taxonomies
	 */
	public function set_translation_for_attribute_taxonomies() {
		$action = '';

		// Action to perform: add, edit, delete or none
		if ( ! empty( $_POST['add_new_attribute'] ) ) {
			$action = 'add';
		} elseif ( ! empty( $_POST['save_attribute'] ) && ! empty( $_GET['edit'] ) ) {
			$action = 'edit';
		}

		switch ( $action ) {
			case 'add':
				$this->process_add_attribute();
				break;
			case 'edit':
				$this->process_edit_attribute();
				break;
		}
	}

	/**
	 * Add new attribute with translate
	 */
	private function process_add_attribute() {
		check_admin_referer( 'woocommerce-add-new_attribute' );

		$label = '';

		if ( isset( $_POST['attribute_label'] ) ) {
			$label = wpm_set_new_value( '', wc_clean( stripslashes( $_POST['attribute_label'] ) ) );
		}

		$_POST['attribute_label'] = $label;
	}

	/**
	 * Save new attribute with translate
	 */
	private function process_edit_attribute() {
		$attribute_id = absint( $_GET['edit'] );
		check_admin_referer( 'woocommerce-save-attribute_' . $attribute_id );

		$label = '';

		if ( isset( $_POST['attribute_label'] ) ) {
			$attribute = wc_get_attribute( $attribute_id );
			$label     = wpm_set_new_value( $attribute->name, wc_clean( stripslashes( $_POST['attribute_label'] ) ) );
		}

		$_POST['attribute_label'] = $label;
	}
}
