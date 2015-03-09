<?php
namespace phtamas\yii2\imageprocessor\transformation;

use Imagine\Image\Box;
use yii\base\InvalidConfigException;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use phtamas\yii2\imageprocessor\TransformationInterface;
use phtamas\yii2\imageprocessor\transformation\Resize;

/**
 * Applies a watermark image to the original image.
 * Watermark will be scaled down to fit image (with the specified margins) when necessary.
 */
class Watermark implements TransformationInterface
{
    /**
     * @var string Path to the watermark image file. Can be specified as an alias.
     */
    public $path;

    /**
     * @var int
     */
    public $margin = 0;

    /**
     * @var string How to align watermark on the image
     * Valid values (they are self-explanatory):
     * top-left
     * top-center
     * top-right
     * bottom-left
     * bottom-center
     * bottom-right
     * center
     */
    public $align = 'bottom-left';

    public function transform(ImageInterface $image, ImagineInterface $imagine)
    {
        if (!isset($this->path)) {
            throw new InvalidConfigException(sprintf(
                'Path to the watermark file must be specified in %s::$path.',
                get_class($this)
            ));
        }
        $imageWidth = $image->getSize()->getWidth();
        $imageHeight = $image->getSize()->getHeight();

        $watermarkImageMaxWidth = $imageWidth - (2 * $this->margin);
        if ($watermarkImageMaxWidth < 1) {
            return;
        }
        $watermarkImageMaxHeight = $imageHeight - (2 * $this->margin);
        if ($watermarkImageMaxHeight < 1) {
            return;
        }

        $watermarkImage = $imagine->open(\Yii::getAlias($this->path));

        if (!(new Box($watermarkImageMaxWidth, $watermarkImageMaxHeight))->contains($watermarkImage->getSize())) {
            $resize = new Resize();
            $resize->width = $watermarkImageMaxWidth;
            $resize->height = $watermarkImageMaxHeight;
            $resize->scaleTo = 'fit';
            $resize->transform($watermarkImage, $imagine);
        }

        $watermarkImageWidth = $watermarkImage->getSize()->getWidth();
        $watermarkImageHeight = $watermarkImage->getSize()->getHeight();

        switch ($this->align) {
            case 'top-left':
                $x = $this->margin;
                $y = $this->margin;
                break;
            case 'top-center':
                $x = $this->calculateStartXCenter($imageWidth, $watermarkImageWidth);
                $y = $this->margin;
                break;
            case 'top-right':
                $x = $this->calculateStartXRight($imageWidth, $watermarkImageWidth);
                $y = $this->margin;
                break;
            case 'bottom-left':
                $x = $this->margin;
                $y = $this->calculateStartYBottom($imageHeight, $watermarkImageHeight);
                break;
            case 'bottom-center':
                $x = $this->calculateStartXCenter($imageWidth, $watermarkImageWidth);
                $y = $this->calculateStartYBottom($imageHeight, $watermarkImageHeight);
                break;
            case 'bottom-right':
                $x = $this->calculateStartXRight($imageWidth, $watermarkImageWidth);
                $y = $this->calculateStartYBottom($imageHeight, $watermarkImageHeight);
                break;
            case 'center':
                $x = $this->calculateStartXCenter($imageWidth, $watermarkImageWidth);
                $y = $this->calculateStartYCenter($imageHeight, $watermarkImageHeight);
                break;
            default:
                throw new InvalidConfigException(sprintf(
                    'Invalid value for property "align": %s.',
                    is_scalar($this->align) ? $this->align : gettype($this->align)
                ));
        }

        if ($x < $this->margin) {
            $x = $this->margin;
        }

        if ($y < $this->margin) {
            $y = $this->margin;
        }
        $image->paste($watermarkImage, new Point($x, $y));
    }

    private function calculateStartXCenter($imageWidth, $watermarkImageWidth)
    {
        return intval(round(($imageWidth - $watermarkImageWidth) / 2)) - 1;
    }

    private function calculateStartXRight($imageWidth, $watermarkImageWidth)
    {
        return $imageWidth - $watermarkImageWidth - 1 - $this->margin;
    }

    private function calculateStartYCenter($imageHeight, $watermarkImageHeight)
    {
        return intval(round(($imageHeight - $watermarkImageHeight) / 2)) - 1;
    }

    private function calculateStartYBottom($imageHeight, $watermarkImageHeight)
    {
        return $imageHeight - $watermarkImageHeight - 1 - $this->margin;
    }
}