<?php
/**
 * Image functions
 *
 * @package LazyLoadImages
 * @since 1.0.0
 */

namespace LazyLoadImages;

require_once trailingslashit( dirname( plugin_dir_path( __file__ ) ) ) . 'includes/class-image-data-imagick.php';
require_once trailingslashit( dirname( plugin_dir_path( __file__ ) ) ) . 'includes/class-image-data-gd.php';

/**
 * Save image color data as post meta.
 *
 * Parse an attachment image and save color data as post meta.
 *
 * @since 1.0.0
 *
 * @see LazyLoadImages\Image_Data
 *
 * @param integer $attachment_id Attachment ID.
 */
function save_image_color_data_as_post_meta( $attachment_id ) {
	$image_data = apply_filters( 'lazy_load_images_image_data_class', false, $attachment_id );
	if ( false === $image_data && class_exists( '\Imagick' ) ) {
		$image_data = new Image_Data_Imagick( $attachment_id );
	} elseif ( false === $image_data && function_exists( 'gd_info' ) ) {
		$image_data = new Image_Data_GD( $attachment_id );
	}
	update_post_meta( $attachment_id, 'average-color', $image_data->average_color() );
	update_post_meta( $attachment_id, 'average-grayscale', $image_data->average_grayscale() );
	update_post_meta( $attachment_id, 'image-is-dark', $image_data->is_dark() );
	update_post_meta( $attachment_id, 'horizontal-stripes', $image_data->color_stripes_horizontal() );
	update_post_meta( $attachment_id, 'grid', $image_data->color_grid() );
}

/**
 * Replace images with placeholders.
 *
 * Filters HTML. Replaces the image src with an SVG placeholder, if available.
 *
 * @since 1.0.0
 *
 * @see LazyLoadImages\url_to_attachment_id
 *
 * @param string $content HTML string.
 * @return string HTML.
 */
function replace_images_with_placeholders( $content ) {
	if ( ! preg_match_all( '/<img [^>]+>/', $content, $matches ) ) {
		return $content;
	}
	foreach ( $matches[0] as $image ) {
		$attachment_id = null;
		$image_attr    = preg_match_all( '/\s([\w-]+)([=\s]([\'\"])((?!\3).+?[^\\\])\3)?/', $image, $match_attr ) ? array_combine( array_map( 'esc_attr', $match_attr[1] ), array_map( 'esc_attr', $match_attr[4] ) ) : array();
		if ( ! empty( $image_attr['class'] ) && preg_match( '/wp-image-([0-9]+)/i', $image_attr['class'], $class_id ) ) {
			$attachment_id = absint( $class_id[1] );
		}
		if ( empty( $attachment_id ) ) {
			$attachment_id = url_to_attachment_id( $image_attr['src'] );
		}
		if ( empty( $attachment_id ) ) {
			continue;
		}
		$svg_string = get_placeholder_svg( $attachment_id, $image_attr, apply_filters( 'lazy_load_images_svg_placeholder_style', 'color-block-grid', $image, $image_attr, $attachment_id ) );
		if ( false === $svg_string ) {
			continue;
		}
		wp_enqueue_script( 'lazy-load-images' );
		if ( isset( $image_attr['src'] ) ) {
			$image_attr['data-src'] = $image_attr['src'];
			$image_attr['src']      = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode( $svg_string );
		}
		if ( isset( $image_attr['srcset'] ) ) {
			$image_attr['data-srcset'] = $image_attr['srcset'];
			unset( $image_attr['srcset'] );
		}
		if ( isset( $image_attr['sizes'] ) ) {
			$image_attr['data-sizes'] = $image_attr['sizes'];
			unset( $image_attr['sizes'] );
		}
		$content = str_replace(
			$image, apply_filters(
				'lazy_load_images_placeholder_image', '<img ' . implode(
					' ', array_map(
						function( $key, $value ) {
								return esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
						}, array_keys( $image_attr ), $image_attr
					)
				) . ' />', $image, $image_attr, $svg_string
			), $content
		);
	}
	return $content;
}

/**
 * Get placeholder SVG.
 *
 * Generate and return a placeholder SVG.
 *
 * @since 1.0.0
 *
 * @param integer $attachment_id Attachment ID.
 * @param array   $image_attr Optional. Image attributes. Key => Value.
 * @param string  $style Optional. The style of the placeholder.
 * @return string SVG string.
 */
function get_placeholder_svg( $attachment_id, $image_attr = array(), $style = 'color-block-grid' ) {
	$svg_string = '';
	switch ( $style ) {
		case 'color-block-grid':
			$grid          = get_post_meta( $attachment_id, 'grid', true );
			$average_color = get_post_meta( $attachment_id, 'average-color', true );
			if ( $grid ) {
				$svg_string = '<svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"' . ( isset( $image_attr['width'] ) ? ' width="' . absint( $image_attr['width'] ) . '"' : '' ) . ( isset( $image_attr['height'] ) ? ' height="' . absint( $image_attr['height'] ) . '"' : '' ) . ' viewBox="0 0 ' . absint( count( $grid ) ) . ' ' . absint( count( $grid[0] ) ) . '"' . ( ! empty( $average_color ) ? ' style="background:' . esc_attr( Abstract_Image_Data::get_rgba_css_string( $average_color ) ) . ';"' : '' ) . '>' . implode(
					'', array_map(
						function( $row, $row_index ) {
								return implode(
									'', array_map(
										function( $color, $column_index ) use ( $row_index ) {
											return '<rect x="' . absint( $row_index ) . '" y="' . absint( $column_index ) . '" width="1" height="1" stroke="none" fill="' . esc_attr( Abstract_Image_Data::get_rgba_css_string( $color ) ) . '" />';
										}, $row, array_keys( $row )
									)
								);
						}, $grid, array_keys( $grid )
					)
				) . '</svg>';
			}
			break;
		case 'color-block-horizontal':
			$stripes = get_post_meta( $attachment_id, 'horizontal-stripes', true );
			if ( $stripes ) {
				$stripe_width = 100 / count( $stripes );
				$svg_string   = '<svg xmlns="http://www.w3.org/2000/svg"' . ( isset( $image_attr['width'] ) ? ' width="' . absint( $image_attr['width'] ) . '"' : '' ) . ( isset( $image_attr['height'] ) ? ' height="' . absint( $image_attr['height'] ) . '"' : '' ) . ' style="background: linear-gradient(' . implode(
					',', array_map(
						function( $color, $index ) use ( $stripe_width ) {
								return esc_attr( Abstract_Image_Data::get_rgba_css_string( $color ) ) . ' ' . absint( $index * $stripe_width ) . '%,' . esc_attr( Abstract_Image_Data::get_rgba_css_string( $color ) ) . ' ' . absint( ( $index + 1 ) * $stripe_width ) . '%';
						}, $stripes, array_keys( $stripes )
					)
				) . ')"></svg>';
			}
			break;
		case 'average-color':
			$color = get_post_meta( $attachment_id, 'average-color', true );
			if ( $color ) {
				$svg_string = '<svg xmlns="http://www.w3.org/2000/svg"' . ( isset( $image_attr['width'] ) ? ' width="' . absint( $image_attr['width'] ) . '"' : '' ) . ( isset( $image_attr['height'] ) ? ' height="' . absint( $image_attr['height'] ) . '"' : '' ) . ' style="background: rgba(' . esc_attr( Abstract_Image_Data::get_rgba_css_string( $color ) ) . ');"></svg>';
			}
			break;
		case 'average-grayscale':
			$color = get_post_meta( $attachment_id, 'average-grayscale', true );
			if ( $color ) {
				$svg_string = '<svg xmlns="http://www.w3.org/2000/svg"' . ( isset( $image_attr['width'] ) ? ' width="' . absint( $image_attr['width'] ) . '"' : '' ) . ( isset( $image_attr['height'] ) ? ' height="' . absint( $image_attr['height'] ) . '"' : '' ) . ' style="background: rgba(' . esc_attr( Abstract_Image_Data::get_rgba_css_string( $color ) ) . ');"></svg>';
			}
			break;
	}
	$svg_string = apply_filters( 'lazy_load_images_svg_placeholder', apply_filters( 'lazy_load_images_svg_placeholder_' . esc_attr( $style ), $svg_string, $attachment_id, $image_attr, $style ), $attachment_id, $image_attr, $style );
	return ! empty( $svg_string ) ? $svg_string : false;
}

/**
 * URL to Attachment ID.
 *
 * Description.
 *
 * @since 1.0.0
 *
 * @global \wpdb $wpdb WordPress database object.
 *
 * @param string $image_url Image URL.
 * @return integer Attachment ID.
 */
function url_to_attachment_id( $image_url ) {
	global $wpdb;
	$original_image_url = $image_url;
	$image_url          = preg_replace( '/^(.+?)(?:-e\d+)?(?:-\d+x\d+)?\.(jpg|jpeg|png|gif)(?:(?:\?|#).+)?$/i', '$1.$2', $image_url );
	$cached_id          = wp_cache_get( md5( $image_url ), 'url_to_id' );
	if ( ! empty( $cached_id ) ) {
		return $cached_id;
	}
	$cached_id = wp_cache_get( md5( $original_image_url ), 'url_to_id', false, $found );
	if ( $found ) {
		return $cached_id;
	}
	$attachment_id = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE guid=%s;', $image_url ) );
	if ( ! empty( $attachment_id ) ) {
		wp_cache_set( md5( $image_url ), $attachment_id[0], 'url_to_id' );
		return $attachment_id[0];
	}
	if ( $image_url !== $original_image_url ) {
		$attachment_id = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE guid=%s;', $original_image_url ) );
		if ( ! empty( $attachment_id ) ) {
			wp_cache_set( md5( $original_image_url ), $attachment_id[0], 'url_to_id' );
			return $attachment_id[0];
		}
	}
	wp_cache_set( md5( $original_image_url ), false, 'url_to_id' );
	return false;
}
