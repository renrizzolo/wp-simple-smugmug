<?php

/**
 * A simple smugmug gallery plugin using the Smugmug REST API v2
 *
 * @link              https://renrizzolo.github.com/wp-simple-smugmug
 * @since             1.0.0
 * @package           Simple_Smugmug
 *
 * @wordpress-plugin
 * Plugin Name: Simple Smugmug
 * Plugin URI: https://renrizzolo.github.com/wp-simple-smugmug
 * Description: Smugmug gallery feed widget using the Smugmug REST api.
 * Version: 1.6.2
 * Author: Ren Rizzolo
 * Author URI: https://renrizzolo.github.com/
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: simple_smugmug
 **/

?>
<?php

// Exit if accessed directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

/**
 * Register plugin assets.
 */
function simple_smugmug_scripts() {
	if ( ! is_admin() ) {
		wp_register_style( 'simple-smugmug-css', plugin_dir_url( __FILE__ ) . 'public/css/simple-smugmug.css' );
		wp_register_script( 'simple-smugmug-js', plugin_dir_url( __FILE__ ) . 'public/js/simple-smugmug.js', array( 'jquery' ), '', true );
		// Localize the script with options data.
		$options = get_option( 'smug_settings' );
		wp_localize_script( 'simple-smugmug-js', 'simple_smugmug_options', $options );
	}
}

/**
 * Register lightgallery
 */
function simple_smug_lightgallery_scripts() {
		wp_register_style( 'lightGalleryCss', plugin_dir_url( __FILE__ ) . 'public/vendor/lightGallery/css/lightgallery.css' );
		wp_register_script( 'lightGallery', plugin_dir_url( __FILE__ ) . 'public/vendor/lightGallery/js/lightgallery.min.js', array( 'jquery' ), '', true );
		wp_register_script( 'lightGalleryThumb', plugin_dir_url( __FILE__ ) . 'public/vendor/lightGallery/js/lg-thumbnail.min.js', array( 'lightGallery' ), '', true );
		wp_register_script( 'lightGallerySmugLink', plugin_dir_url( __FILE__ ) . 'public/js/lg-smugLink.js', array( 'lightGallery' ), '', true );
}


/**
 * Enqueue lightgallery.
 */
function simple_smug_lg_add_scripts() {

	wp_enqueue_style( 'lightGalleryCss' );
	wp_enqueue_script( 'lightGallery' );
	wp_enqueue_script( 'lightGalleryThumb' );
	wp_enqueue_script( 'lightGallerySmugLink' );

}


// Register everything.
add_action( 'wp_enqueue_scripts', 'simple_smugmug_scripts' );
add_action( 'wp_enqueue_scripts', 'simple_smug_lightgallery_scripts' );


add_shortcode( 'simple_smugmug', 'display_smugmug' );

/**
 * The shortcode.
 *
 * @param array $atts The shortcode attributes.
 **/
function display_smugmug( $atts ) {

	// Counter for each shortcode usage.
	static $i = 0;
	$atts     = array_change_key_case( (array) $atts, CASE_LOWER );

	// Localize a js object for each use of the shortcode.
	wp_localize_script( 'simple-smugmug-js', 'gallery_' . $i . '', $atts );

	// Load the scripts.
	wp_enqueue_style( 'simple-smugmug-css' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'simple-smugmug-js' );

	// Enqueue the lightgallery files if needed.
	if ( get_option( 'smug_settings' )['display_in_lightgallery'] || $atts['display_in_lightgallery'] ) {
		simple_smug_lg_add_scripts();
	}

	// Output buffering.
	ob_start();
	echo '<div class="smug-inner" id="smug-shortcode-' . $i . '">';
	echo '<div id="simple-smugmug-loader">';
	echo get_loader();
	echo '</div>';
	echo '</div>';
	$request = ob_get_clean();

	// Increment counter.
	$i++;
	return $request;
}


/**
 * Loading spinner.
 **/
function get_loader() {

	$loader  = '<svg version="1.1" class="simple-smugmug-loader" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">';
	$loader .= '<path fill="#000" d="M25.251,6.461c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615V6.461z">';
	$loader .= '</path>';
	$loader .= '</svg>';

	// Let loader be overriden.
	$loader = apply_filters( 'simple_smugmug_loader', $loader );

	return $loader;

}


/**
 * Add settings link to plugins page.
 *
 * @param array $links plugin settings links.
 */
function plugin_settings_link( $links ) {
	$url           = get_admin_url() . 'options-general.php?page=simple_smugmug';
	$settings_link = '<a href="' . $url . '">' . __( 'Settings', 'simple_smugmug' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

if ( is_admin() ) {
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'plugin_settings_link' );

	// Load media button class.
	include PLUGIN_DIR . 'admin/class-simple-smugmug-add-gallery.php';

	// Load admin class.
	include PLUGIN_DIR . 'admin/class-simple-smugmug-admin.php';
}
