Lazy Load Images with SVG Placeholders
======================================

Add SVG image placeholders for images and load the full image when it comes into view.

## Full image
![Full Image]()
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
