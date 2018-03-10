/**
 * @summary   Lazy load images.
 *
 * @since     0.1.0
 */
 
/**
 * @summary Lazy load setup.
 *
 * Initialize lazy loading.
 *
 * @since 0.1.0
 *
 * @param {event} event Event object.
 */
const lazyLoadSetup = event => {
	if ( window.IntersectionObserver ) {

		/**
		 * @summary Intersection Observer.
		 *
		 * Watch image elements and load when they are in view.
		 *
		 * @since 0.1.0
		 */
		const observer = new IntersectionObserver( function( entries ) {
			entries.forEach( entry => {
				if ( entry.isIntersecting ) {
					const preload = document.createElement( 'img' ),
					imageLoadedHandler = function() {
						entry.target.setAttribute( 'src', this.getAttribute( 'src' ) );
						entry.target.removeAttribute( 'data-src' );
						if ( this.hasAttribute( 'srcset' ) ) {
							entry.target.setAttribute( 'srcset', this.getAttribute( 'srcset' ) );
							entry.target.removeAttribute( 'data-srcset' );
						}
						if ( this.hasAttribute( 'sizes' ) ) {
							entry.target.setAttribute( 'sizes', this.getAttribute( 'sizes' ) );
							entry.target.removeAttribute( 'data-sizes' );
						}
					};

					this.unobserve( entry.target );
					preload.setAttribute( 'src', entry.target.getAttribute( 'data-src' ) );
					if ( entry.target.hasAttribute( 'data-srcset' ) ) {
						preload.setAttribute( 'srcset', entry.target.getAttribute( 'data-srcset' ) );
					}
					if ( entry.target.hasAttribute( 'data-sizes' ) ) {
						preload.setAttribute( 'sizes', entry.target.getAttribute( 'data-sizes' ) );
					}
					if ( preload.width ) {
						imageLoadedHandler.call( preload );
					}else if ( preload.addEventListener ) {
						preload.addEventListener( 'load', imageLoadedHandler  );
					}
				}
			} );
		});
		[].forEach.call( document.getElementsByTagName( 'img' ), image => {
			if ( image.hasAttribute( 'data-src' ) ) {
				observer.observe(image);
			}
		} );
	} else {
		fallback();
	}
},


/**
 * @summary Fallback.
 *
 * If we do not have an observer, load the images immediately.
 *
 * @since 0.1.0
 *
 * @param {event} event Optional. Event object.
 */
fallback = event => {
	[].forEach.call( document.getElementsByTagName( 'img' ), image => {
		if ( image.hasAttribute( 'data-src' ) ) {
			image.setAttribute( 'src', image.getAttribute( 'data-src' ) );
		}
		if ( image.hasAttribute( 'data-srcset' ) ) {
			image.setAttribute( 'srcset', image.getAttribute( 'data-srcset' ) );
		}
		if ( image.hasAttribute( 'data-sizes' ) ) {
			image.setAttribute( 'sizes', image.getAttribute( 'data-sizes' ) );
		}
	} );
};

// Run the lazyLoadSetup if the document is already loaded.
if ( 'complete' === document.readyState ) {
	lazyLoadSetup();

// Else, wait for the DOM to finish loading.
} else if ( document.addEventListener ) {
	document.addEventListener( 'DOMContentLoaded', lazyLoadSetup ); 
} else if ( document.attachEvent ) {
	document.attachEvent( 'onload', lazyLoadSetup ); 
}
