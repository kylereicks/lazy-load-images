Lazy Load Images with SVG Placeholders
======================================

Add SVG image placeholders for images and load the full image when it comes into view.

## Key Features

* Generate SVG placeholders with color data saved when the image is added or updated.
* Use Intersection Observers to determine when an image is in view.
* As a fallback load all images immediately when IntersectionObserver is not available.
* Load ES6 scripts via sript type [module and nomodule](https://github.com/kylereicks/wp-script-module-nomodule).

# Examples

## Full image
<img alt="Full Image" title="Full Image" width="525" height="394" src="example-images/full-image.jpg" />

## Color Block Grid (Default)
![Block Grid Image](example-images/color-block-grid.svg)
```PHP
add_filter( 'lazy_load_images_svg_placeholder_style', function( $style, $image, $image_attr, $attachment_id ) {
	return 'color-block-grid';
}, 10, 4 );
```

## Color Block Horizontal
![Color Block Horizontal](example-images/color-block-horizontal.svg)
```PHP
add_filter( 'lazy_load_images_svg_placeholder_style', function( $style, $image, $image_attr, $attachment_id ) {
	return 'color-block-horizontal';
}, 10, 4 );
```

## Average Color
![Average Color](example-images/average-color.svg)
```PHP
add_filter( 'lazy_load_images_svg_placeholder_style', function( $style, $image, $image_attr, $attachment_id ) {
	return 'average-color';
}, 10, 4 );
```

## Average Grayscale
![Average Grayscale](example-images/average-grayscale.svg)
```PHP
add_filter( 'lazy_load_images_svg_placeholder_style', function( $style, $image, $image_attr, $attachment_id ) {
	return 'average-grayscale';
}, 10, 4 );
```
