<?php
/**
 * Image Data functions
 *
 * Moved from includes/image-functions.php
 *
 * @package LazyLoadImages
 * @since 1.3.0
 */

namespace LazyLoadImages\Functions\ImageData;

use \LazyLoadImages\Classes\Image_Data_Imagick as Image_Data_Imagick;
use \LazyLoadImages\Classes\Image_Data_GD as Image_Data_GD;

/**
 * Save image color data as post meta.
 *
 * Filters HTML. Replaces the image src with an SVG placeholder, if available.
 *
 * @since 1.0.0
 *
 * @param integer $attachment_id Attachment ID.
 */
function save_image_color_data_as_post_meta( int $attachment_id ) {
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
