<?php
/**
 * Interface_Image_Data class
 *
 * Image data interface. Returns image color data.
 *
 * @package LazyLoadImages
 * @since 1.3.0
 */

namespace LazyLoadImages\Classes;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Image Data.
 *
 * Parse images and retrieve color data.
 *
 * @since 1.3.0
 */
interface Interface_Image_Data {

	/**
	 * Retrieve the average color by format.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Average color value rgba array.
	 */
	public function average_color();

	/**
	 * Retrieve the average grayscale by format.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Average grayscale value rgba array.
	 */
	public function average_grayscale();

	/**
	 * Retrieve the "is dark" value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return boolean Image is dark.
	 */
	public function is_dark();

	/**
	 * Retrieve an array of average colors in horizontal sections.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of color strings.
	 */
	public function color_stripes_horizontal();

	/**
	 * Retrieve an array grid of average colors.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of arrays color strings.
	 */
	public function color_grid();
}
