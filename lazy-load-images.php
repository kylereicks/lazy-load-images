<?php
/**
 * Plugin Name: Lazy Load Images
 * Plugin URI: https://github.com/kylereicks/lazy-load-images
 * Description: Lazy load images, with a color block SVG placeholder.
 * Version: 1.3.0
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

// Class autoloader.
spl_autoload_register(
	function ( string $class ) : void {
		$prefix         = __NAMESPACE__ . '\\Classes\\';
		$base_directory = __DIR__ . '/includes/classes/';
		$prefix_length  = strlen( $prefix );

		if ( 0 !== strncmp( $prefix, $class, $prefix_length ) ) {
			return;
		}

		$relative_class = substr( $class, $prefix_length );
		$file           = $base_directory . str_replace( [ '\\', '_' ], [ '/', '-' ], preg_replace( '/([^\\\]+$)/', 'class-$1', strtolower( $relative_class ) ) ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

define( __NAMESPACE__ . '\VERSION', get_file_data( __file__, array( 'Version' => 'Version' ) )['Version'] );

require_once trailingslashit( plugin_dir_path( __file__ ) ) . 'includes/functions/image-data.php';
require_once trailingslashit( plugin_dir_path( __file__ ) ) . 'includes/functions/image-view.php';

// Register scripts.
add_action(
	'init', function() : void {
		$dependencies = array();
		// See https://github.com/kylereicks/wp-script-module-nomodule.
		if ( function_exists( '\WordPress\Script\ModuleNoModule\add_module_nomodule' ) ) {
			wp_register_script( 'lazy-load-images-es6', plugins_url( 'lazy-load-images/assets/js/lazy-load-images-es6.min.js' ), array(), VERSION, false );
			wp_script_add_data( 'lazy-load-images-es6', 'type', 'module' );
			$dependencies[] = 'lazy-load-images-es6';
		}
		wp_register_script( 'lazy-load-images', plugins_url( 'lazy-load-images/assets/js/lazy-load-images.min.js' ), $dependencies, VERSION, false );
		wp_script_add_data( 'lazy-load-images', 'nomodule', true );
	}
);

// Set image data.
add_action( 'edit_attachment', __NAMESPACE__ . '\Functions\ImageData\save_image_color_data_as_post_meta' );
add_action( 'add_attachment', __NAMESPACE__ . '\Functions\ImageData\save_image_color_data_as_post_meta' );

// Filter HTML and replace images.
add_filter( 'the_content', __NAMESPACE__ . '\Functions\ImageView\replace_images_with_placeholders', 20 );
add_filter( 'post_thumbnail_html', __NAMESPACE__ . '\Functions\ImageView\replace_images_with_placeholders', 20 );
add_filter( 'get_header_image_tag', __NAMESPACE__ . '\Functions\ImageView\replace_images_with_placeholders', 20 );
