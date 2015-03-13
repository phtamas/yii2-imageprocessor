# yii2-imageprocessor
Application component for [Yii2](https://github.com/yiisoft/yii2). Its main use case is to process images with a chain of _preconfigured_ transformations. A simple DSL inspired by [WideImage's smart coordinates](http://wideimage.sourceforge.net/documentation/smart-coordinates) is used to add some flexibility to the configuration. WideImage library itself isn't used, all actual image processing is delegated to [Imagine](https://github.com/avalanche123/Imagine).

Ad-hoc processing is also supported but if heavy and complex image manipulation is a key feature of your application it's probably better to use Imagine directly.

## Getting started
Install via Composer:

```json
    "require": {
        "phtamas/yii2-imageprocessor": "0.5.*"
    }
```
Configure it in the 'components' section of your application configuration:

```php
'imageProcessor' => [
  'class' => '\phtamas\yii2\imageprocessor\Component',
  // Default for all JPEG images
  'jpegQuality' => 90,
  // Default for all PNG images
  'pngCompression' => 7,
  
  // Create named image categories with their own configuration.
  // You can refer them by name in application code.
  'define' => [
  
    'userAvatar' => [
      // Add transformations. They will be applied in the order they were defined.
      'process' => [
        // Fix images with embedded orientation metadata
        ['autorotate'],
        // Preapre image to crop by resizing it to cover a 160*160 square
        ['resize', 'width' => 160, 'height' => 160, 'scaleTo' => 'cover'],
        // Crop it
        ['crop', 'x' => 'center - 80', 'y' => 'center - 80', 'width' => 160, 'height' => 160],
      ],
    ],
    
    'galleryImage' => [
       // Override default to save some disk space and bandwidth
      'jpegQuality' => 80,
      'process' => [
        // Resize proportionally to fit a 600*600 square but only if too large
        ['resize', 'width' => 600, 'height' => 600, 'scaleTo' => 'fit', 'only' => 'down'],
        // Mark your property
        ['watermark', 'path' => '@path/to/wmark.png', 'align' => 'top-left', 'margin' => 20],
      ],
    ],

  ],
],
```
And use it anywhere in your application:

```php
// Process uploaded image and save as a JPEG file
$path = '@image/user/avatar/' . uniqid() . '.jpg';
Yii::$app->imageProcessor->save($uploadedFile->tempName, $path, 'userAvatar');

// Process image (stored as BLOB in the DB) and send it to the HTTP client
Yii::$app->imageProcessor->send(['data' => 'binary string'], 'jpg', 'galleryImage');

// Ad-hoc processing is possible, too
Yii::$app->imageProcessor->saveAndSend('@images/image.jpg', $path, 'jpg', [
    'process' => [['resize', 'width' => 300]], // Resize proportionally to 300 px width
]);

```
## Learn more
 * [Component API](doc/component-api.md)
 * [Transformations](doc/transformations.md)