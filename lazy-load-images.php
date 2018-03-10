<?php
/**
 * Plugin Name: Lazy Load Images
 * Plugin URI: https://github.com/kylereicks/lazy-load-images
 * Description: Lazy load images, with a color block SVG placeholder.
 * Version: 1.0.0
 * Author: Kyle Reicks
 * Author URI: https://github.com/kylereicks/
 *
 * @package LazyLoadImages
 * @since 1.0.0
 */

namespace LazyLoadImages;

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( __NAMESPACE__ . '\VERSION', get_file_data( __file__, array( 'Version' => 'Version' ) )['Version'] );

require_once trailingslashit( plugin_dir_path( __file__ ) ) . 'includes/image-functions.php';

// Register scripts.
add_action(
	'init', function() {
		wp_register_script( 'lazy-load-images', plugins_url( 'lazy-load-images/assets/js/lazy-load-images.js' ), array(), VERSION, false );
	}
);

// Set image data.
add_action( 'edit_attachment', __NAMESPACE__ . '\save_image_color_data_as_post_meta' );
add_action( 'add_attachment', __NAMESPACE__ . '\save_image_color_data_as_post_meta' );

// Filter HTML and replace images.
add_filter( 'the_content', __NAMESPACE__ . '\replace_images_with_placeholders', 20 );
add_filter( 'post_thumbnail_html', __NAMESPACE__ . '\replace_images_with_placeholders', 20 );
add_filter( 'get_header_image_tag', __NAMESPACE__ . '\replace_images_with_placeholders', 20 );
