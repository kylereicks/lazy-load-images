<?php
/**
 * Abstract_Image_Data class
 *
 * Abstract Image data class. Process images and retrieve data.
 *
 * @package LazyLoadImages
 * @since 1.0.0
 */

namespace LazyLoadImages;

/**
 * Image Data.
 *
 * Parse images and retrieve color data.
 *
 * @since 1.0.0
 */
abstract class Abstract_Image_Data {
	/**
	 * Attachment ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var integer $attachment_id Attachment ID.
	 */
	protected $attachment_id = null;

	/**
	 * File string.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $file File string.
	 */
	protected $file = null;

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
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $attachment_id Attachment ID.
	 * @return \LazyLoadImages\Image_Data Image_Data object.
	 */
	public function __construct( $attachment_id = null ) {
		if ( null === $attachment_id ) {
			return;
		}
		$this->attachment_id = absint( $attachment_id );
		$this->file          = get_attached_file( $attachment_id );
		return $this;
	}

	/**
	 * Get class attribute.
	 *
	 * A memoizing method for class attributes.
	 * Always return a clone of an attribute object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $key Attribute name.
	 * @return mixed Attribute value.
	 */
	protected function get( $key ) {
		if ( ! is_string( $key ) ) {
			return null;
		}
		if ( null !== $this->$key ) {
			return is_object( $this->$key ) ? clone $this->$key : $this->$key;
		}
		if ( is_callable( array( $this, 'get_' . $key ) ) ) {
			$this->$key = call_user_func_array( array( $this, 'get_' . $key ), array() );
			return is_object( $this->$key ) ? clone $this->$key : $this->$key;
		}
		return null;
	}

	/**
	 * Retrieve true if the color is dark, and might benefit from light overlay text.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $rgb color array.
	 * @return boolean Color is dark.
	 */
	public static function color_is_dark( $rgb ) {
		if ( $rgb['r'] + $rgb['g'] + $rgb['b'] > 382 ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Convert an integer 0 - 255 to a two character hex value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $integer A color value integer 0 - 255.
	 * @return string A two character hex string.
	 */
	public static function integer_to_two_character_hex( $integer ) {
		return substr( '00' . dechex( $integer ), -2 );
	}

	/**
	 * Convert an array of rgb values to a six character hex string.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $rgb An array of rgb values.
	 * @return string A six character hex string.
	 */
	public static function rgb_array_to_hex_string( $rgb ) {
		return self::integer_to_two_character_hex( $rgb['r'] ) . self::integer_to_two_character_hex( $rgb['g'] ) . self::integer_to_two_character_hex( $rgb['b'] );
	}

	/**
	 * Calculate the greatest common divisor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $a An integer.
	 * @param integer $b An integer.
	 * @return integer The greatest common divisor for the suplied integers.
	 */
	public static function greatest_common_divisor( $a, $b ) {
		if ( ! $b ) {
			return $a;
		}
		return self::greatest_common_divisor( $b, $a % $b );
	}

	/**
	 * Retrieve the average color.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Average color value rgba array.
	 */
	abstract public function average_color();

	/**
	 * Retrieve the average grayscale.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Average grayscale value rgba array.
	 */
	abstract public function average_grayscale();

	/**
	 * Retrieve the "is dark" value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return boolean Image is dark.
	 */
	abstract public function is_dark();

	/**
	 * Retrieve an array of average colors in horizontal sections.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of color strings.
	 */
	abstract public function color_stripes_horizontal();

	/**
	 * Retrieve an array grid of average colors.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of arrays color strings.
	 */
	abstract public function color_grid();
}
