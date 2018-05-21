<?php
/**
 * Abstract_Image_Data class
 *
 * Abstract Image data class. Process images and retrieve data.
 *
 * @package LazyLoadImages
 * @since 1.0.0
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
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $attachment_id Attachment ID.
	 * @return \LazyLoadImages\Image_Data Image_Data object.
	 */
	public function __construct( int $attachment_id ) {
		$this->attachment_id = absint( $attachment_id );
		$this->file          = get_attached_file( $attachment_id );
		return $this;
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
	public static function color_is_dark( array $rgb ) : bool {
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
	public static function integer_to_two_character_hex( int $integer ) : string {
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
	public static function rgb_array_to_hex_string( array $rgb ) : string {
		return self::integer_to_two_character_hex( $rgb['r'] ) . self::integer_to_two_character_hex( $rgb['g'] ) . self::integer_to_two_character_hex( $rgb['b'] );
	}

	/**
	 * Convert a three or six character hex string to an array of rgba values.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @param string $hex_color A three or six character hex string.
	 * @return array An array of rgba values. The alpha value will always be 1.
	 */
	public static function hex_string_to_rgba_array( string $hex_color ) : array {
		$hex_color_no_hash = sanitize_hex_color_no_hash( $hex_color );
		$rgba              = array(
			'r' => 0,
			'g' => 0,
			'b' => 0,
			'a' => 1,
		);
		if ( $hex_color_no_hash ) {
			if ( 3 === strlen( $hex_color_no_hash ) ) {
				$rgba['r'] = hexdec( $hex_color_no_hash[0] . $hex_color_no_hash[0] );
				$rgba['g'] = hexdec( $hex_color_no_hash[1] . $hex_color_no_hash[1] );
				$rgba['b'] = hexdec( $hex_color_no_hash[2] . $hex_color_no_hash[2] );
			} else {
				$rgba['r'] = hexdec( substr( $hex_color_no_hash, 0, 2 ) );
				$rgba['g'] = hexdec( substr( $hex_color_no_hash, 2, 2 ) );
				$rgba['b'] = hexdec( substr( $hex_color_no_hash, 4, 2 ) );
			}
		}

		return $rgba;
	}

	/**
	 * Convert a three or six character hex string to an rgba CSS value.
	 *
	 * @since 1.0.1
	 * @access public
	 *
	 * @param mixed $color A hex color or rgba array.
	 * @return string A array of rgba values.
	 */
	public static function get_rgba_css_string( $color ) : string {
		$rgba_array = array(
			'r' => 0,
			'g' => 0,
			'b' => 0,
			'a' => 1,
		);
		if ( is_array( $color ) ) {
			foreach ( $rgba_array as $channel => $value ) {
				if ( ! $color[ $channel ] ) {
					continue;
				}
				$rgba_array[ $channel ] = $color[ $channel ];
			}
		} elseif ( sanitize_hex_color_no_hash( $color ) ) {
			$rgba_array = self::hex_string_to_rgba_array( $color );
		}

		return 'rgba(' . absint( $rgba_array['r'] ) . ',' . absint( $rgba_array['g'] ) . ',' . absint( $rgba_array['b'] ) . ',' . floatval( $rgba_array['a'] ) . ')';
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
	public static function greatest_common_divisor( int $a, int $b ) : int {
		if ( ! $b ) {
			return $a;
		}
		return self::greatest_common_divisor( $b, $a % $b );
	}
}
