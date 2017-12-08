<?php
/**
* Plugin Name: Simple Smugmug
* Plugin URI: https://renrizzolo.github.com/simple-smugmug
* Description: Smugmug gallery feed widget using the Smugmug REST api.
* Version: 1.5
* Author: Ren Rizzolo
* Author URI: https://renrizzolo.github.com/
* License:
*/
?>
<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('PLUGIN_DIR', trailingslashit( plugin_dir_path(__FILE__) ) );

// add include files using Wordpress enqueue functions

function simple_smugmug_scripts(){
  if (!is_admin()) {
    wp_register_style( 'simple-smugmug-css', plugin_dir_url(__FILE__) . '/assets/css/simple-smugmug.css' );
    wp_register_script( 'simple-smugmug-js', plugin_dir_url(__FILE__) . '/assets/js/simple-smugmug.js',  array('jquery'), '', true );
    // Localize the script with options data
    $options = get_option('smug_settings');
    wp_localize_script( 'simple-smugmug-js', 'simple_smugmug_options', $options );
    //$gallery = array('gallery_id' => '');
    //wp_localize_script( 'simple-smugmug-js', 'gallery', $gallery );
  }
}
//add include files using Wordpress enqueue functions
function simple_smug_lightGallery_scripts(){
    wp_register_style('lightGalleryCss',  plugin_dir_url(__FILE__) . '/assets/vendor/lightGallery/css/lightgallery.css' );
    wp_register_script( 'lightGallery',  plugin_dir_url(__FILE__) . '/assets/vendor/lightGallery/js/lightgallery.min.js', array('jquery'), '', true );
    wp_register_script('lightGalleryThumb',  plugin_dir_url(__FILE__) . '/assets/vendor/lightGallery/js/lg-thumbnail.min.js', array('lightGallery'), '', true);
    wp_register_script('lightGallerySmugLink',  plugin_dir_url(__FILE__) . '/assets/js/lg-smugLink.js', array('lightGallery'), '', true);
}


//hook lightGallery js into the header of the wp-theme
function simple_smug_lgng_addScript(){
    $post_content = get_the_content();
    $hasGallery = preg_match('/\[simple_smugmug.*gallery_id=.(.*).\]/', $post_content);
    $showInGallery = preg_match('/\[simple_smugmug.*display_in_lightgallery="1".*\]/', $post_content);

  if ( is_singular() && $hasGallery && ( get_option('smug_settings')['display_in_lightgallery'] || $showInGallery ) ) {
    wp_enqueue_style('lightGalleryCss');
    wp_enqueue_script('lightGallery');
    wp_enqueue_script('lightGalleryThumb');
    wp_enqueue_script('lightGallerySmugLink');

  }
}



add_action( 'wp_enqueue_scripts', 'simple_smugmug_scripts' );
add_shortcode('simple_smugmug','display_smugmug');

add_action( 'wp_enqueue_scripts', 'simple_smug_lightGallery_scripts' );
add_action('wp_head', 'simple_smug_lgng_addScript');


function display_smugmug($atts = []){
  static $i = 0;
  $atts = array_change_key_case((array)$atts, CASE_LOWER);
  if ( !is_null($atts['gallery_id']) ) {
     wp_localize_script( "simple-smugmug-js", "gallery_".$i."", $atts );
  }
  //load the scripts
  wp_enqueue_style("simple-smugmug-css");
  //idk why you wouldn't have jqueezy already
  wp_enqueue_script('jquery');
  wp_enqueue_script("simple-smugmug-js");

  //output buffering
  ob_start();
  echo'<div class="smug-inner" id="smug-shortcode-'.$i.'">';
  echo '<div id="simple-smugmug-loader">';
  echo    get_loader();
  echo '</div>';
  echo'</div>';
  $request = ob_get_clean();
  $i++;
  return $request;
}

function get_loader() {
  $loader = '<svg version="1.1" class="simple-smugmug-loader" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">';
  $loader .='<path fill="#000" d="M25.251,6.461c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615V6.461z" transform="rotate(320 25 25)">';
  $loader .=  '<animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"></animateTransform>';
  $loader .=  '</path>';
  $loader .='</svg>';
  //let loader be overriden
  $loader = apply_filters('simple_smugmug_loader', $loader);
  
  return $loader;

}


// add settings link to plugins page
function plugin_settings_link($links) {
  $url = get_admin_url() . 'options-general.php?page=simple_smugmug';
  $settings_link = '<a href="'.$url.'">' . __( 'Settings', 'wordpress' ) . '</a>';
  array_unshift( $links, $settings_link );
  return $links;
}

if ( is_admin() ) {
  add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'plugin_settings_link' );

  // Load tinymce class
  include(PLUGIN_DIR.'lib/tinymce.php');

  // Load admin class
  include(PLUGIN_DIR.'lib/admin.php');
}

?>