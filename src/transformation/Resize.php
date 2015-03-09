<?php
namespace phtamas\yii2\imageprocessor\transformation;

use Yii;
use yii\base\InvalidConfigException;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\BoxInterface;
use Imagine\Image\Box;
use Imagine\Gd\Image as GdImage;
use phtamas\yii2\imageprocessor\TransformationInterface;

/**
 * Resizes an image.
 */
class Resize implements TransformationInterface
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
     * @var string|null Name of interpolation filter to use.
     * Valid values:
     * - (string) point
     * - (string) box
     * - (string) triangle
     * - (string) hermite
     * - (string) hanning
     * - (string) hamming
     * - (string) blackman
     * - (string) gaussian
     * - (string) quadratic
     * - (string) cubic
     * - (string) catrom
     * - (string) mitchell
     * - (string) lanczos
     * - (string) bessel
     * - (string) sinc
     * - null/omitted: No filter will be applied.
     */
    public $filter;

    private $filters = [
        'point' => ImageInterface::FILTER_POINT,
        'box' => ImageInterface::FILTER_BOX,
        'triangle' => ImageInterface::FILTER_TRIANGLE,
        'hermite' => ImageInterface::FILTER_HERMITE,
        'hanning' => ImageInterface::FILTER_HANNING,
        'hamming' => ImageInterface::FILTER_HAMMING,
        'blackman' => ImageInterface::FILTER_BLACKMAN,
        'gaussian' => ImageInterface::FILTER_GAUSSIAN,
        'quadratic' => ImageInterface::FILTER_QUADRATIC,
        'cubic' => ImageInterface::FILTER_CUBIC,
        'catrom' => ImageInterface::FILTER_CATROM,
        'mitchell' => ImageInterface::FILTER_MITCHELL,
        'lanczos' => ImageInterface::FILTER_LANCZOS,
        'bessel' => ImageInterface::FILTER_BESSEL,
        'sinc' => ImageInterface::FILTER_SINC,
    ];

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

        $originalSize = $image->getSize();
        if (is_null($this->width)) {
            $newSize = $originalSize->heighten($this->height);
        } elseif (is_null($this->height)) {
            $newSize = $originalSize->widen($this->width);
        } else {
            $newSize = new Box($this->width, $this->height);
            if ($this->scaleTo === 'fit') {
                $newSize = $this->scaleToFit($newSize, $originalSize);
            } elseif ($this->scaleTo === 'cover') {
                $newSize = $this->scaleToCover($newSize, $originalSize);
            }
        }
        if ($this->only === 'up' && $originalSize->contains($newSize)) {
            return;
        }
        if ($this->only === 'down' && $newSize->contains($originalSize)) {
            return;
        }
        if (is_null($this->filter) || $image instanceof GdImage) {
            $image->resize($newSize);
        } elseif (!array_key_exists($this->filter, $this->filters)) {
            throw new InvalidConfigException(sprintf(
                'Invalid filter type: %s. Valid types: %s.',
                is_scalar($this->filter) ? $this->filter : gettype($this->filter),
                implode(', ', array_keys($this->filters))
            ));
        }
        else {
            $image->resize($newSize, $this->filters[$this->filter]);
        }

    }

    /**
     * @param BoxInterface $newSize
     * @param BoxInterface $originalSize
     * @return BoxInterface
     */
    private function scaleToFit(BoxInterface $newSize, BoxInterface $originalSize)
    {
        if ($originalSize->getWidth() / $originalSize->getHeight() > $newSize->getWidth() / $newSize->getHeight()) {
            return $originalSize->widen($newSize->getWidth());
        } else {
            return $originalSize->heighten($newSize->getHeight());
        }
    }

    /**
     * @param BoxInterface $newSize
     * @param BoxInterface $originalSize
     * @return BoxInterface
     */
    private function scaleToCover(BoxInterface $newSize, BoxInterface $originalSize)
    {
        if ($originalSize->getWidth() / $originalSize->getHeight() > $newSize->getWidth() / $newSize->getHeight()) {
            return $originalSize->heighten($newSize->getHeight());
        } else {
            return $originalSize->widen($newSize->getWidth());
        }
    }
}