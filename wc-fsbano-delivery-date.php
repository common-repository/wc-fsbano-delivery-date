<?php
/**
 * Plugin Name:       Fabio Sbano Delivery Date and Time for WooCommerce
 * Plugin URI:        https://fsbano.io/plugins/wc-fsbano-delivery-date/
 * Description:       Delivery Date and Time for WooCommerce in the billing form
 * Version:           1.0.1
 * Requires PHP:      7.0
 * Author:            Fabio Sbano
 * Author URI:        https://fsbano.io/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-fsbano-delivery-date
 * Domain Path:       /languages
 * 
 */
 
namespace fsbano;

/**
 * If this file is called directly, then abort execution.
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Check if WooCoomerce plugin is active
 */
add_action( 'admin_init', function() {
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
  if (!is_plugin_active('woocommerce/woocommerce.php')) {
    deactivate_plugins('wc-fsbano-delivery-date/wc-fsbano-delivery-date.php');
    add_action('admin_notices', function() {
      echo "<div class='error'><p>Plugin deactivated. Please active WooComerce plugin !</p></div>";
    });
  }
});

/**
 * Load translated text for the current language
 */
add_action('plugins_loaded', function() {
  load_plugin_textdomain( 'wc-fsbano-delivery-date', false, basename( dirname( __FILE__ ) ) . '/languages' );
});


/**
 * Jquery
 */

add_action( 'wp_enqueue_scripts', function() {
  wp_enqueue_script('jquery');
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-datepicker');
  wp_enqueue_script('fsbano-js', plugin_dir_url(__FILE__).'/public/js/fsbano.js', false );
  wp_register_style('jquery-ui', plugin_dir_url(__FILE__).'/public/css/jquery-ui.css' );
  wp_enqueue_style( 'jquery-ui' );  
});

/**
 * WooCommerce Calendar Date and Hour on Billing Form
 */

add_action('woocommerce_after_checkout_billing_form', function( $checkout ) {
  woocommerce_form_field( 'delivery_date', array(
    'type'          => 'text',
    'class'         => array('form-row-first'),
    'id'            => 'fsbano-js-ui-datepicker',
    'required'      => true,
    'label'         => esc_html("Delivery Date", "wc-fsbano-delivery-date"),
    'custom_attributes' => array( 'autocomplete' => 0, 'readonly' => true )
  ),$checkout->get_value( 'delivery_date' ));

  woocommerce_form_field( 'delivery_hour', array(
    'type'          => 'select',
    'clear'         => true,
    'class'         => array( 'form-row-last' ),
    'label'         => esc_html("Delivery Hour", "wc-fsbano-delivery-date"),
    'required'      => true,
    'options'       => array(
      'empty'   => __( '  -  ', 'woocommerce' ),
      '08:00'   => __( '08:00', 'woocommerce' ),
      '09:00'   => __( '09:00', 'woocommerce' ),
      '10:00'   => __( '10:00', 'woocommerce' ),
      '11:00'   => __( '11:00', 'woocommerce' ),
      '12:00'   => __( '12:00', 'woocommerce' ),
      '13:00'   => __( '13:00', 'woocommerce' ),
      '14:00'   => __( '14:00', 'woocommerce' ),
      '15:00'   => __( '15:00', 'woocommerce' ),
      '16:00'   => __( '16:00', 'woocommerce' ),
      '17:00'   => __( '17:00', 'woocommerce' ),                               
      '18:00'   => __( '18:00', 'woocommerce' ),
      '19:00'   => __( '19:00', 'woocommerce' ),
      '20:00'   => __( '20:00', 'woocommerce' ))
  ),$checkout->get_value( 'delivery_hour' ));

});

add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id ) {
  if ( ! empty( $_POST['delivery_date'] ) ) {
        update_post_meta( $order_id, 'delivery_date', sanitize_text_field( $_POST['delivery_date'] ) );
    }
  if ( ! empty( $_POST['delivery_hour'] ) ) {
        update_post_meta( $order_id, 'delivery_hour', sanitize_text_field( $_POST['delivery_hour'] ) );
    }
});

add_action ( 'woocommerce_order_details_after_order_table', function( $order ) {
  echo '<p><strong>'. esc_html("Delivery Date", "wc-fsbano-delivery-date").': </strong>'. sanitize_text_field(get_post_meta($order->id, 'delivery_date', true)). '</p>';
  echo '<p><strong>'. esc_html("Delivery Hour", "wc-fsbano-delivery-date").': </strong>'. sanitize_text_field(get_post_meta($order->id, 'delivery_hour', true)). '</p>';
});


add_filter( 'woocommerce_email_order_meta_fields', 'custom_fsbano_delivery_date_for_woocommerce_email_order_meta_fields', 10, 3 );
function custom_fsbano_delivery_date_for_woocommerce_email_order_meta_fields ( $fields, $sent_to_admin, $order ) {
  $fields['delivery_date'] = array(
    'label' => esc_html("Delivery Date", "wc-fsbano-delivery-date"),
    'value' => sanitize_text_field(get_post_meta( $order->id, 'delivery_date', true )),
  );
  $fields['delivery_hour'] = array(
    'label' => esc_html("Delivery Hour", "wc-fsbano-delivery-date"),
    'value' => sanitize_text_field(get_post_meta( $order->id, 'delivery_hour', true )),
  );
  return $fields;
};

add_action('woocommerce_checkout_process', function() {
  if ( ! $_POST['delivery_date'] )
    wc_add_notice( __( '<b>Billing Delivery Date</b> is a required field.' ), 'error' );
  if ( $_POST['delivery_hour'] == 'empty' )
    wc_add_notice( __( '<b>Billing Delivery Hour</b> is a required field.' ), 'error' );
});
