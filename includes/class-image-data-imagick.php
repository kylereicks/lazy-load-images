<?php
/**
 * Image_Data_Imagick class
 *
 * Image data class implemented with Imagick. Process images and retrieve data.
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
 * @see \Imagick
 */
class Image_Data_Imagick extends Abstract_Image_Data {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $attachment_id Attachment ID.
	 * @return \LazyLoadImages\Image_Data Image_Data object.
	 */
	public function __construct( $attachment_id = null ) {
		if ( ! class_exists( '\Imagick' ) ) {
			return;
		}
		return parent::__construct( $attachment_id );
	}

	/**
	 * Get the Imagick image object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return \Imagick Imagick object.
	 */
	protected function get_image() {
		if ( ! empty( $this->file ) ) {
			return new \Imagick( $this->file );
		}
		return new \Imagick( trailingslashit( wp_upload_dir()['basedir'] ) . wp_get_attachment_metadata( $this->attachment_id )['file'] );
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
			$image->modulateImage( 100, 0, 100 );
		}
		$image->scaleImage( 1, 1 );
		$pixel = $image->getImagePixelColor( 0, 0 );
		$rgba  = $pixel->getColor();
		$rgba  = self::get_pixel_color_with_normalized_alpha_channel( $pixel );
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
	 * Get pixel color rgba array.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param \ImagickPixel $pixel Imagick pixel.
	 * @return array Array of rgba integer values.
	 */
	protected function get_pixel_color_with_normalized_alpha_channel( $pixel ) {
		$rgba = $pixel->getColor();
		if ( $this->get_image()->getImageAlphaChannel() ) {
			$rgba['a'] = $pixel->getColor( true )['a'];
		} else {
			$rgba['a'] = 1;
		}
		return $rgba;
	}

	/**
	 * Retrieve the colors of five horizontal sections of the image.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of rgba color arrays.
	 */
	protected function get_horizontal_stripes() {
		$stripes     = 5;
		$color_array = array();
		$image       = $this->get_image();
		$image->scaleImage( 1, $stripes );
		for ( $i = 0; $i < $stripes; $i++ ) {
			$pixel             = $image->getImagePixelColor( 0, $i );
			$color_array[ $i ] = self::get_pixel_color_with_normalized_alpha_channel( $pixel );
		}
		return $color_array;
	}

	/**
	 * Retrieve a grid of colors for the image.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of arrays of rgba color arrays.
	 */
	protected function get_grid() {
		$max_width_height     = 16;
		$min_max_width_height = 8;
		$image                = $this->get_image();
		$width                = $image->getImageWidth();
		$height               = $image->getImageHeight();
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
		}
		$color_array = array();
		$image->scaleImage( $rows, $columns );
		$columns = $image->getImageWidth();
		$rows    = $image->getImageHeight();
		for ( $i = 0; $i < $columns; $i++ ) {
			$color_array[ $i ] = array();
			for ( $j = 0; $j < $rows; $j++ ) {
				$pixel                   = $image->getImagePixelColor( $i, $j );
				$color_array[ $i ][ $j ] = self::get_pixel_color_with_normalized_alpha_channel( $pixel );
			}
		}
		return $color_array;
	}

	/**
	 * Retrieve the average color by format.
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
	 * Retrieve the average grayscale by format.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Average grayscale value rgba array.
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
		return self::color_is_dark( $this->get( 'average_color' ) );
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
