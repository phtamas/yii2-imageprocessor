# Component API
## Configuration
Configurable properties of the Component.
### $drivers
An array of driver names. Valid names: gmagick, imagick, gd. If omitted, the component will try to find the best available driver automatically.
```php
// Prefer GD over others
'drivers' => ['gd', 'gmagick', 'imagick'],

// Do not use Imagick, even if it's available
'drivers' => ['gmagick', 'gd'],
```
### $jpegQuality
Integer between 0 and 100. If omitted, Imagine's default will be used. Can be overridden in [image category definitions](#image-category-definition).
```php
'jpegQuality' => 95,
```
### $pngCompression
Integer between 0 and 9. If omitted, Imagine's default will be used. Can be overridden in [image category definitions](#image-category-definition).
```php
'pngCompression' => 7,
```
### $define
An array of [image category definitions](#image-category-definition).
```php
'define' => [

    'galleryThumbnail' => [
        'jpegQuality' => 80,
        'process' => [
            ['resize', 'width' => 200, 'height' => 200, 'scaleTo' => 'cover'],
            ['crop', 'x' => 'center-100', 'y' => 'center-100', 'width' => 200, 'height' => 200],
        ],
    ],
    
    'galleryFullSize' => [
    	'process' => [
        	['resize', 'width' => 600, 'height' => 600, 'scaleTo' => 'fit']
        ],
    ],
],
```
### $transformations
[Custom transformations](transformations.md#custom-transformations) can be added here. Array of transformation name => class name pairs.
```php
'transformations' => [
    'mytransformation' => '\application\imageprocessing\transformations\MyTransformation',
],
```
## Main API methods
In a typical web application developers usually want to either process and save an uploaded image or process a stored image and send it to an HTTP client. Main API methods are here to cover these use cases.
### save()
Creates an image instance from the source, processes it with predefined or ad-hoc transformations and saves it as a file.
```php
/**
 * @param array|\Imagine\Image\ImageInterface $source
 * @param string $path
 * @param string|array|null $as
 */
public function save($source, $path, $as = null)
```
#### Parameters
##### $source
A valid [image source](#image-source).
##### $path
Destination path or alias as string.
##### $as
Name of an [image category definition](#image-category-definition) as string or an [ad-hoc definition](#ad-hoc-definition) as array.
### send()
Creates an image instance from the source, processes it with predefined or ad-hoc transformations and sends it as HTTP output.
```php
/**
 * @param array|\Imagine\Image\ImageInterface $source
 * @param string $type
 * @param string|array|null $as
 */
public function send($source, $type, $as = null)
```

#### Parameters
##### $source
A valid [image source](#image-source).
##### $type
A valid [image type](#image-types) as string.
##### $as
Name of an [image category definition](#image-category-definition) as string or an [ad-hoc definition](#ad-hoc-definition) as array.
### saveAndSend()
Creates an image instance from the source, processes it with predefined or ad-hoc transformations, saves it as a file and sends it as HTTP output.
```php
/**
 * @param array|\Imagine\Image\ImageInterface $source
 * @param string $path
 * @param string|null $type
 * @param string|array|null $as
 */
public function saveAndSend($source, $path, $type = null, $as = null)
```
#### Parameters
##### $source
A valid [image source](#image-source).
##### $path
Destination path or alias as string.
##### $type
A valid [image type](#image-types) as string.
##### $as
Name of an [image category definition](#image-category-definition) as string or an [ad-hoc definition](#ad-hoc-definition) as array.
## Additional API methods
These methods were designed mainly for internal use but I made them public to support non-typical use cases.
### create()
Creates an image object instance.
```php
/**
 * @param array|\Imagine\Image\ImageInterface $source
 * @throws \InvalidArgumentException On unsupported source type
 * @return \Imagine\Image\ImageInterface
 */
public function create($source)
```
#### Parameters
##### $source
A valid [image source](#image-source).
### process()
Creates an image instance from the source and processes it with predefined or ad-hoc transformations.
```php
/**
 * @param array|\Imagine\Image\ImageInterface $source
 * @param string|array|null $as
 * @throws \InvalidArgumentException
 * @return \Imagine\Image\ImageInterface
*/
public function process($source, $as = null)
```
#### Parameters
##### $source
A valid [image source](#image-source).
##### $as
Name of an [image category definition](#image-category-definition) as string or a list of transformations as array.
## Glossary
### Image source
The first parameter of all API methods. Can be one of the following formats.
#### File
A filesystem path or alias to an existing image file.
```php
// Absolute path
['file' => '/path/to/image.jpg']

// Alias
['file' => '@images/image.jpg']

// Uploaded file
$uploadedFile = \yii\web\UploadedFile::getInstanceByName('image');
['file' => $uploadedFile->tempName]
```
#### Image data (as binary string)
Useful when e.g. you store images in a database as BLOBs.
```php
['data' => 'data as binary string']
```
#### Image resource
An already existing image resource. In normal circumstances you don't need to create resources manually. Implemented mainly for completeness and to support extreme use cases.
```php
['resource' => imagecreatefromjpeg('/path/to/image.jpg')]
```
#### Size
A new, empty image will be created with the specified width and height.
```php
['width' => 400, 'height' => 300]
```
#### Object
An object instance that implements \Imagine\Image\ImageInterface.
### Image category definition
A named sub-configuration for a particular group of images where you can both override component's default setting and specify processing steps (transformations) for the group. A category definition can be referred by its name when calling API methods.
```php
'productImage' => [
    // Override Component's default
    'jpegQuality' => 85,
    
    // Apply transformations
    'process' => [
        ['resize', 'width' => 800],
        ['watermark', 'path' => '/path/to/watermark.png'],
    ],
],

// Usage exmple: process image.jpg as productImage and send it to the HTTP client
Yii::$app->imageProcessor->send(['file' => '@images/image.jpg'], 'jpg', 'productImage');
```

#### Ad-hoc definition

### Image type

Supported types: gif, jpeg (or jpg), png, wbmp, xbm.