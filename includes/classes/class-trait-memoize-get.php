<?php
/**
 * Memoize get trait.
 *
 * Gets and memoizes class variables.
 *
 * @package LazyLoadImages
 * @since 1.3.0
 */

namespace LazyLoadImages\Classes;

/**
 * Memoize get trait.
 *
 * Gets and memoizes class variables.
 *
 * @since 1.3.0
 */
trait Trait_Memoize_Get {

	/**
	 * Get class attribute.
	 *
	 * A memoizing method for class attributes.
	 * Always return a clone of an attribute object.
	 *
	 * @since 1.3.0
	 * @access protected
	 *
	 * @param string $key Attribute name.
	 * @return mixed Attribute value.
	 */
	protected function get( string $key ) {
		if ( null === $this->$key && is_callable( [ $this, 'get_' . $key ] ) ) {
			$this->$key = call_user_func_array( [ $this, 'get_' . $key ], [] );
		}

		return is_object( $this->$key ) ? clone $this->$key : $this->$key;
	}
}
