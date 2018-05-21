<?php
/**
 * Image_Data_Imagick class
 *
 * Image data class implemented with Imagick. Process images and retrieve data.
 *
 * @package LazyLoadImages
 * @since 1.0.0
 */

namespace LazyLoadImages\Classes;

/**
 * Image Data.
 *
 * Parse images and retrieve color data.
 *
 * @since 1.0.0
 *
 * @see \Imagick
 */
class Image_Data_Imagick extends Abstract_Image_Data implements Interface_Image_Data {
	use Trait_Memoize_Get;

	/**
	 * Image object.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var mixed $image Image object.
	 */
	protected $image = null;

	/**
	 * Average color RGBA value.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $average_color_rgba Average color rgba value.
	 */
	protected $average_color = null;

	/**
	 * Average grayscale RGBA value.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $average_grayscale_rgba Average grayscale rgba value.
	 */
	protected $average_grayscale = null;

	/**
	 * Array of rgba color arrays.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array $horizontal_stripes Array of rgba color arrays.
	 */
	protected $horizontal_stripes = null;

	/**
	 * Array of arrays of rgba color arrays.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array $grid Array of arrays of rgba color arrays.
	 */
	protected $grid = null;

	/**
	 * Get the Imagick image object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return \Imagick Imagick object.
	 */
	protected function get_image() {
		if ( empty( $this->file ) ) {
			return false;
		}
		return new \Imagick( $this->file );
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
	protected function get_pixel_color_with_normalized_alpha_channel( \ImagickPixel $pixel ) : array {
		$rgba = $pixel->getColor();
		if ( $this->get_image()->getImageAlphaChannel() ) {
			$rgba['a'] = $pixel->getColor( true )['a'];
		} else {
			$rgba['a'] = 1;
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
	protected function get_average_color() : array {
		$image = $this->get( 'image' );
		if ( ! $image ) {
			return [];
		}
		$image->scaleImage( 1, 1 );
		$pixel = $image->getImagePixelColor( 0, 0 );
		return $this->get_pixel_color_with_normalized_alpha_channel( $pixel );
	}

	/**
	 * Retrieve average grayscale rgba array.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array rgba grayscale array.
	 */
	protected function get_average_grayscale() : array {
		$image = $this->get( 'image' );
		if ( ! $image ) {
			return [];
		}
		$image->modulateImage( 100, 0, 100 );
		$image->scaleImage( 1, 1 );
		$pixel = $image->getImagePixelColor( 0, 0 );
		return $this->get_pixel_color_with_normalized_alpha_channel( $pixel );
	}

	/**
	 * Retrieve the colors of five horizontal sections of the image.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of rgba color arrays.
	 */
	protected function get_horizontal_stripes() : array {
		$stripes     = 5;
		$color_array = [];
		$image       = $this->get_image();
		$image->scaleImage( 1, $stripes );
		for ( $i = 0; $i < $stripes; $i++ ) {
			$pixel             = $image->getImagePixelColor( 0, $i );
			$color_array[ $i ] = $this->get_pixel_color_with_normalized_alpha_channel( $pixel );
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
	protected function get_grid() : array {
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
		$color_array = [];
		$image->scaleImage( $rows, $columns );
		$columns = $image->getImageWidth();
		$rows    = $image->getImageHeight();
		for ( $i = 0; $i < $columns; $i++ ) {
			$color_array[ $i ] = [];
			for ( $j = 0; $j < $rows; $j++ ) {
				$pixel                   = $image->getImagePixelColor( $i, $j );
				$color_array[ $i ][ $j ] = $this->get_pixel_color_with_normalized_alpha_channel( $pixel );
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
	public function average_color() : array {
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
