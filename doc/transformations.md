# Transformations
A transformation is a class that implements \phtamas\yii2\imageprocessor\TransformationInterface.
## Built-in transformations
The component comes with a few built in transformations for the most common tasks.
### Resize
Resizes the image to the specified width and/or height.
#### Properties
##### $width
The width in pixels to resize to as integer. If null/omitted, image will be resized proportionally to the given height.
```php
// Resize proportionally to 300px width
['resize', 'width' => 300]
```
##### $height
The height in pixels to resize to as integer. If null/omitted, image will be resized proportionally to the given width.
```php
// Resize proportionally to 200px height
['resize', 'height' => 200]
```
#### $scaleTo
How to interpret the given width and height. Applies only when both width and height are set.

* `(string) fit`: Image will be resized proportionally to the largest size that fits the given width and height.
* `(string) cover`: Image will be resized proportionally to the smallest size that covers the given width and height
* `null/omitted`: Image will be stretched to the given with and height.
 
```php
// Fit
['resize', 'width' => 300, 'height' => 200, 'scaleTo' => 'fit']

// Cover
['resize', 'width' => 300, 'height' => 200, 'scaleTo' => 'cover']

// Stretch
['resize', 'width' => 300, 'height' => 200]
```
#### $only
When to resize image depending on its original size.

* `(string) up`: Image will only be resized when it's smaller than the given width and/or height.
* `(string) down`: Image will only be resized when it's larger than the given width and/or height.
* `null/omitted`: Image will always be resized regardless of its original size.

```php
// Only if larger
['resize', 'width' => 300, 'only' => 'down']

// Only if smaller
['resize', 'width' => 300, 'only' => 'up']

// Always
['resize', 'width' => 300]
```
### Crop
Crops the image to the specified width and height
#### Properties
##### $x
Horizontal coordinate of the top-left corner of the area to crop, in pixels as integer or string.

* `(string) right - 100`: -100 px from the right edge of image
* `(string) center + 100` or `(string) center - 100`: +/- 100px from the center of image
* `(string) left + 100`: Same as (integer) 100.

##### $y
Vertical coordinate of the top-left corner of the area to crop, in pixels as integer or string.

* `(string) bottom - 100`: -100 px from the bottom edge of image
* `(string) center + 100` or (string) center - 100: +/- 100px from the center of image
* `(string) top + 100`: Same as (integer) 100.

##### $width
Width of the area to crop, in pixels as integer.
##### $height
height of the area to crop, in pixels as integer.

```php
// Absolute coordinates
['crop', 'x' => 20, 'y' => 30, 'width' => 100, 'height' => 50]

// Keep the bottom-right corner of the image
['crop', 'x' => 'right - 100', 'y' => 'bottom - 50', 'width' => 100, 'height' => 50]

```
### Watermark
Applies a watermark image to the original image. Watermark will be scaled down to fit image (with the specified margins) when necessary.
#### Properties
##### $path
Path to the watermark image file. Can be specified as an alias.
```php
// Apply a watermark with default alignment and no margin
['watermark', 'path' => '/path/to/watermark.png']
```
##### $align
How to align watermark on the image as string. Defaults to bottom-left.

* `(string) top-center`
* `(string) top-right`
* `(string) bottom-left`
* `(string) bottom-center`
* `(string) bottom-right`
* `(string) center`

```php
// Apply watermark to the bottom-right corner
['watermark', 'align' => 'bottom-right']
```
##### $margin
Distance from the edges of image as integer.
### Autorotate
Rotates an image automatically based on orientation metadata. Requires Exif PHP extension to be installed and enabled otherwise transformation will fail silently and image will be kept untouched. This transformation has no configurable properties.
## Custom transformations

Implement TransformationInterface 
```php
namespace myapp\imageprocessing\transformations;

use phtamas\yii2\imageprocessor\TransformationInterface;

class CustomTransformation implements TransformationInterface
{
	/** @var int */
	public $property1;
    
    /** @var bool **/
    public $property2;
    
	public function transform(ImageInterface $image, ImagineInterface $imagine)
    {
    	// Release your creativity here.
    }
}
```
add your class to the configuration
```php
'imageTransformer' => [
	'class' => '\phtamas\yii2\imagetransformer\Component',
    'transformations' => [
    	'custom' => '\myapp\imageprocessing\CustomTransformation',
    ],
    'define' => [
    	'anImageCategory' => [
        	'process' => [
            	['custom', 'property1' => 135, 'property2' => false],
            ],
        ],
    	'anotherImageCategory' => [
        	'process' => [
            	['custom', 'property1' => 44, 'property2' => true],
            ],
        ],
    ],
],
```
done.
