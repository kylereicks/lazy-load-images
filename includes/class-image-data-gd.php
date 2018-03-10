<?php
/**
 * Image_Data_GD class
 *
 * Image data class implemented with GD. Process images and retrieve data.
 *
 * @package LazyLoadImages
 * @since 1.0.0
 */

namespace LazyLoadImages;

require_once trailingslashit( dirname( plugin_dir_path( __file__ ) ) ) . 'includes/class-abstract-image-data.php';

/**
 * Image Data.
 *
 * Parse images and retrieve color data.
 *
 * @since 1.0.0
 *
 * @see \gd_info
 */
class Image_Data_GD extends Abstract_Image_Data {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $attachment_id Attachment ID.
	 * @return \LazyLoadImages\Image_Data Image_Data object.
	 */
	public function __construct( $attachment_id = null ) {
		if ( ! function_exists( 'gd_info' ) ) {
			return;
		}
		return parent::__construct( $attachment_id );
	}

	/**
	 * Get the GD image resource.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return resource GD image resource.
	 */
	protected function get_image() {
		if ( ! empty( $this->file ) ) {
			$image_file = $this->file;
		} else {
			$image_file = trailingslashit( wp_upload_dir()['basedir'] ) . wp_get_attachment_metadata( $this->attachment_id )['file'];
		}
		$image_extension = strtolower( pathinfo( $image_file, PATHINFO_EXTENSION ) );
		if ( 'jpg' === $image_extension ) {
			$image_extension = 'jpeg';
		}
		if ( function_exists( 'imagecreatefrom' . $image_extension ) ) {
			return call_user_func_array( 'imagecreatefrom' . $image_extension, array( $image_file ) );
		}
	}

	/**
	 * Get the pixel rgba color.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param resource $image GD image resource.
	 * @param integer  $x x pixel.
	 * @param integer  $y y pixel.
	 * @return array Array of rgba values.
	 */
	public static function get_pixel_rgba( $image, $x = 0, $y = 0 ) {
		$color_value          = imagecolorat( $image, $x, $y );
		$red_green_blue_alpha = imagecolorsforindex( $image, $color_value );
		return array(
			'r' => $red_green_blue_alpha['red'],
			'g' => $red_green_blue_alpha['green'],
			'b' => $red_green_blue_alpha['blue'],
			'a' => 1 - ( $red_green_blue_alpha['alpha'] / 127 ),
		);
	}

	/**
	 * Set average color.
	 *
	 * Retrieve the average color for the image.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param boolean $grayscale Retrieve grayscale value.
	 * @return mixed HEX color string or an array of rgba integer values.
	 */
	protected function set_average_color( $grayscale = false ) {
		$image = $this->get_image();
		if ( true === $grayscale ) {
			imagefilter( $image, IMG_FILTER_GRAYSCALE );
		}
		$image = imagescale( $image, 1, 1 );
		$rgba  = self::get_pixel_rgba( $image, 0, 0 );
		if ( true === $grayscale ) {
			$this->average_grayscale = $rgba;
		} else {
			$this->average_color = $rgba;
		}
		return $rgba;
	}

	/**
	 * Retrieve average color rgba array.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array rgba color array.
	 */
	protected function get_average_color() {
		return $this->set_average_color();
	}

	/**
	 * Retrieve average grayscale rgba array.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array rgba grayscale array.
	 */
	protected function get_average_grayscale() {
		return $this->set_average_color( true );
	}

	/**
	 * Retrieve the colors of five horizontal sections of the image.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of hex color strings.
	 */
	protected function get_horizontal_stripes() {
		$stripes     = 5;
		$color_array = array();
		$image       = $this->get_image();
		$image       = imagescale( $image, 1, $stripes );
		for ( $i = 0; $i < $stripes; $i++ ) {
			$color_array[ $i ] = self::get_pixel_rgba( $image, 0, $i );
		}
		return $color_array;
	}

	/**
	 * Retrieve a grid of colors for the image.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of arrays of hex color strings.
	 */
	protected function get_grid() {
		$max_width_height     = 16;
		$min_max_width_height = 8;
		$image                = $this->get_image();
		$width                = imagesx( $image );
		$height               = imagesy( $image );
		$rows                 = $height < $width ? min( $max_width_height, $width / self::greatest_common_divisor( $width, $height ) ) : 0;
		$columns              = $width < $height ? min( $max_width_height, $height / self::greatest_common_divisor( $width, $height ) ) : 0;
		if ( 0 !== $rows ) {
			while ( $rows < $min_max_width_height ) {
				$rows = $rows * 2;
			}
		}
		if ( 0 !== $columns ) {
			while ( $columns < $min_max_width_height ) {
				$columns = $columns * 2;
			}
		}
		if ( 0 === $rows && 0 === $columns ) {
			$rows    = $max_width_height;
			$columns = $max_width_height;
		} elseif ( 0 === $rows && 0 < $columns ) {
			$rows = ( $columns * ( $width / $height ) );
		} elseif ( 0 < $rows && 0 === $columns ) {
			$columns = round( $rows * ( $height / $width ) );
		}
		$color_array = array();
		$image       = imagescale( $image, $rows, $columns );
		$columns     = imagesx( $image );
		$rows        = imagesy( $image );
		for ( $i = 0; $i < $columns; $i++ ) {
			$color_array[ $i ] = array();
			for ( $j = 0; $j < $rows; $j++ ) {
				$color_array[ $i ][ $j ] = self::get_pixel_rgba( $image, $i, $j );
			}
		}
		return $color_array;
	}

	/**
	 * Retrieve the average color.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Average color value rgba array.
	 */
	public function average_color() {
		return $this->get( 'average_color' );
	}

	/**
	 * Retrieve the average grayscale.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return mixed Average grayscale value hex string or rgba array.
	 */
	public function average_grayscale() {
		return $this->get( 'average_grayscale' );
	}

	/**
	 * Retrieve the "is dark" value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return boolean Image is dark.
	 */
	public function is_dark() {
		return self::color_is_dark( $this->get( 'average_color_rgba' ) );
	}

	/**
	 * Retrieve an array of average colors in horizontal sections.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of color strings.
	 */
	public function color_stripes_horizontal() {
		return $this->get( 'horizontal_stripes' );
	}

	/**
	 * Retrieve an array grid of average colors.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of arrays color strings.
	 */
	public function color_grid() {
		return $this->get( 'grid' );
	}
}
