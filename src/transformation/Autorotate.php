<?php
namespace phtamas\yii2\imageprocessor\transformation;

use yii\base\BaseObject;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;
use phtamas\yii2\imageprocessor\TransformationInterface;

/**
 * Rotates an image automatically based on orientation metadata.
 * Requires Exif PHP extension to be installed and enabled otherwise transformation will fail silently
 * and image will be kept untouched.
 */
class Autorotate extends BaseObject implements TransformationInterface
{
    public function transform(ImageInterface $image, ImagineInterface $imagine)
    {
        // Imagine comes with a built-in Autorotate filter, but it seems to be simpler to reimplement it
        // without bothering with the color.
        $metadata = $image->metadata();
        if (!isset($metadata['ifd0.Orientation'])) {
            return;
        }
        $orientation = $metadata['ifd0.Orientation'];
        if ($orientation == 3) {
            $image->rotate(180);
        } elseif ($orientation == 6) {
            $image->rotate(90);
        } elseif ($orientation == 8) {
            $image->rotate(-90);
        }
    }
}