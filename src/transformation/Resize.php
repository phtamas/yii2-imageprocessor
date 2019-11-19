<?php
namespace phtamas\yii2\imageprocessor\transformation;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\BoxInterface;
use Imagine\Image\Box;
use phtamas\yii2\imageprocessor\TransformationInterface;

/**
 * Resizes an image.
 */
class Resize extends BaseObject implements TransformationInterface
{
    /**
     * @var  int|null The width in pixels to resize to. If null/omitted, image will be resized proportionally
     * to the given height.
     *
     */
    public $width;

    /**
     * @var  int|null The height in pixels to resize to. If null/omitted, image will be resized proportionally
     * to the given width.
     */
    public $height;

    /**
     * @var  string|null How to interpret the given width and height. Applies only when both width and height are set.
     * Valid values:
     * - (string) fit: Image will be resized proportionally to the largest size that fits the given width and height.
     * - (string) cover: Image will be resized proportionally to the smallest size that covers the given width and
     * height
     * - null/omitted: Image will be stretched to the given with and height.
     */
    public $scaleTo;

    /**
     * @var  string|null When to resize image depending on its original size.
     * Valid values:
     * - (string) up: Image will only be resized when it's smaller than the given width and/or height.
     * - (string) down: Image will only be resized when it's larger than the given width and/or height.
     * - null/omitted: Image will always be resized regardless of its original size.
     */
    public $only;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function transform(ImageInterface $image, ImagineInterface $imagine)
    {
        if (!(isset($this->width) || isset($this->height))) {
            throw new InvalidConfigException('At least one of the properties "width" and "height" must be set.');
        }
        if (!in_array($this->scaleTo, ['fit', 'cover', null])) {
            throw new InvalidConfigException(sprintf(
                'Invalid value (%s) %s for property "scaleTo". Valid values: (string) fit, (string) cover, null.',
                gettype($this->scaleTo),
                is_scalar($this->scaleTo) ? $this->scaleTo : '?'
            ));
        }
        if (!in_array($this->only, ['up', 'down', null])) {
            throw new InvalidConfigException(sprintf(
                'Invalid value (%s) %s for property "only". Valid values: (string) up, (string) down, null.',
                gettype($this->only),
                is_scalar($this->only) ? $this->only: '?'
            ));
        }

        if (is_null($this->width)) {
            $size = $image->getSize()->heighten($this->height);
        } elseif (is_null($this->height)) {
            $size = $image->getSize()->widen($this->width);
        } else {
            $size = new Box($this->width, $this->height);
            if ($this->scaleTo === 'fit') {
                $size = $this->scaleToFit($size, $image);
            } elseif ($this->scaleTo === 'cover') {
                $size = $this->scaleToCover($size, $image);
            }
        }
        if ($this->only === 'up' && $image->getSize()->contains($size)) {
            return;
        }
        if ($this->only === 'down' && $size->contains($image->getSize())) {
            return;
        }
        $image->resize($size);
    }

    /**
     * @param BoxInterface $size
     * @param ImageInterface $image
     * @return BoxInterface
     */
    private function scaleToFit(BoxInterface $size, ImageInterface $image)
    {
        if ($image->getSize()->getWidth() / $image->getSize()->getHeight() > $size->getWidth() / $size->getHeight()) {
            return $image->getSize()->widen($size->getWidth());
        } else {
            return $image->getSize()->heighten($size->getHeight());
        }
    }

    /**
     * @param BoxInterface $size
     * @param ImageInterface $image
     * @return BoxInterface
     */
    private function scaleToCover(BoxInterface $size, ImageInterface $image)
    {
        if ($image->getSize()->getWidth() / $image->getSize()->getHeight() > $size->getWidth() / $size->getHeight()) {
            return $image->getSize()->heighten($size->getHeight());
        } else {
            return $image->getSize()->widen($size->getWidth());
        }
    }
}