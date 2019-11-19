<?php
namespace phtamas\yii2\imageprocessor\transformation;

use Imagine\Image\Box;
use yii\base\BaseObject;
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
class Watermark extends BaseObject implements TransformationInterface
{
    /**
     * @var string Path to the watermark image file. Can be specified as an alias.
     */
    public $path;

    /**
     * @var int|int[]|null
     */
    public $margin;

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

    /** @var  int */
    private $imageWidth;

    /** @var  int */
    private $imageHeight;

    /** @var  int */
    private $watermarkImageWidth;

    /** @var  int */
    private $watermarkImageHeight;

    public function getStartXLeft()
    {
        return (int) $this->margin;
    }
    public function getStartXCenter()
    {
        $x = intval(round(($this->imageWidth - $this->watermarkImageWidth) / 2)) - 1;
        if ($x < $this->margin) {
            $x = $this->margin;
        }
        return $x;
    }

    public function getStartXRight()
    {
        $x = $this->imageWidth - $this->watermarkImageWidth - 1 - (int)$this->margin;
        if ($x < $this->margin) {
            $x = $this->margin;
        }
        return $x;
    }

    public function getStarYTop()
    {
        return (int)$this->margin;
    }

    public function getStartYCenter()
    {
        $y = intval(round(($this->imageHeight - $this->watermarkImageHeight) / 2)) - 1;
        if ($y < $this->margin) {
            $y = $this->margin;
        }
        return $y;
    }

    public function getStartYBottom()
    {
        $y = $this->imageHeight - $this->watermarkImageHeight - 1 - (int)$this->margin;
        if ($y < $this->margin) {
            $y = $this->margin;
        }
        return $y;
    }

    public function transform(ImageInterface $image, ImagineInterface $imagine)
    {
        if (!isset($this->path)) {
            throw new InvalidConfigException(sprintf(
                'Path to the watermark file must be specified in %s::$path.',
                self::className()
            ));
        }
        $this->imageWidth = $image->getSize()->getWidth();
        $this->imageHeight = $image->getSize()->getHeight();

        $watermarkImageMaxWidth = $this->imageWidth - (2 * $this->margin);
        if ($watermarkImageMaxWidth < 1) {
            return;
        }
        $watermarkImageMaxHeight = $this->imageHeight - (2 * $this->margin);
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

        $this->watermarkImageWidth = $watermarkImage->getSize()->getWidth();
        $this->watermarkImageHeight = $watermarkImage->getSize()->getHeight();

        switch ($this->align) {
            case 'top-left':
                $start = new Point($this->getStartXLeft(), $this->getStarYTop());
                break;
            case 'top-center':
                $start = new Point($this->getStartXCenter(), $this->getStarYTop());
                break;
            case 'top-right':
                $start = new Point($this->getStartXRight(), $this->getStarYTop());
                break;
            case 'bottom-left':
                $start = new Point($this->getStartXLeft(), $this->getStartYBottom());
                break;
            case 'bottom-center':
                $start = new Point($this->getStartXCenter(), $this->getStartYBottom());
                break;
            case 'bottom-right':
                $start = new Point($this->getStartXRight(), $this->getStartYBottom());
                break;
            case 'center':
                $start = new Point($this->getStartXCenter(), $this->getStartYCenter());
                break;
            default:
                throw new InvalidConfigException(sprintf(
                    'Invalid value for property "align": %s.',
                    is_scalar($this->align) ? $this->align : gettype($this->align)
                ));
        }

        $image->paste($watermarkImage, $start);
    }
}