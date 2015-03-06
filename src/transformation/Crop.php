<?php
namespace phtamas\yii2\imageprocessor\transformation;

use yii\base\Object;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Imagine\Image\Box;
use phtamas\yii2\imageprocessor\TransformationInterface;

/**
 * Crops the image to the specified width and height
 */
class Crop extends Object implements TransformationInterface
{
    /**
     * @var int|string Horizontal coordinate of the top-left corner of the area to crop, in pixels.
     * Can be specified as a simple expression in one of the following formats:
     * - (string) right - 100: -100px from the right edge of the image
     * - (string) center + 100 or (string) center - 100: +/- 100px from the center of the image
     * - (string) left + 100: Same as (integer) 100.
     */
    public $x;

    /**
     * @var int|string Vertical coordinate of the top-left corner of the area to crop, in pixels.
     * Can be specified as a simple expression in one of the following formats:
     * - (string) bottom - 100: -100px from the bottom edge of the image
     * - (string) center + 100 or (string) center - 100: +/- 100px from the center of the image
     * - (string) top + 100: Same as (integer) 100.
     */
    public $y;

    /**
     * @var int Width of the area to crop, in pixels.
     */
    public $width;

    /**
     * @var int Height of the area to crop, in pixels.
     */
    public $height;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function transform(ImageInterface $image, ImagineInterface $imagine)
    {
        $imageWidth = $image->getSize()->getWidth();
        $imageHeight = $image->getSize()->getHeight();

        $width = $this->width;
        $height = $this->height;

        if (isset($this->x)) {
            $x = $this->parseX($this->x, $imageWidth);
        } elseif ($imageWidth > $width) {
            $x = intval(round(($imageWidth - $width) / 2)) - 1;
        } else {
            $x = 0;
        }

        if ($x > $imageWidth - 1) {
            $x = $imageWidth - 1;
        } elseif ($x < 0) {
            $x = 0;
        }

        if (isset($this->y)) {
            $y = $this->parseY($this->y, $imageHeight);
        } elseif ($imageHeight > $height) {
            $y = intval(round(($imageHeight - $height) / 2)) - 1;
        } else {
            $y = 0;
        }

        if ($y > $imageHeight - 1) {
            $y = $imageHeight - 1;
        } elseif ($y < 0) {
            $y = 0;
        }

        if ($x + $width > $imageWidth) {
            $width -= $x + $width - $imageWidth;
        }
        if ($y + $height > $imageHeight) {
            $height -= $y + $height - $imageHeight;
        }
        $image->crop(new Point($x, $y), new Box($width, $height));
    }

    private function parseX($expression, $imageWidth)
    {
        if (is_integer($expression) && $expression >= 0) {
            return $expression;
        }
        if (!is_string($expression)) {
            return false;
        }
        if (ctype_digit($expression)) {
            return intval($expression);
        }
        if ($expression === 'left') {
            return 0;
        }
        if ($expression === 'center') {
            return intval(round($imageWidth / 2)) - 1;
        }
        if ($expression === 'right') {
            return $imageWidth - 1;
        }
        if (preg_match('/^left ?\+ ?(\d+)$/', $expression, $matches)) {
            return intval($matches[1]);
        }
        if (preg_match('/^center ?\+ ?(\d+)$/', $expression, $matches)) {
            return intval(round($imageWidth / 2)) - 1 + intval($matches[1]);
        }
        if (preg_match('/^right ?- ?(\d+)$/', $expression, $matches)) {
            return $imageWidth - intval($matches[1]) - 1;
        }
        if (preg_match('/^center ?- ?(\d+)$/', $expression, $matches)) {
            return intval(round($imageWidth / 2)) - intval($matches[1]) - 1;
        }
        return false;
    }

    private function parseY($expression, $imageHeight)
    {
        if (is_integer($expression) && $expression >= 0) {
            return $expression;
        }
        if (!is_string($expression)) {
            return false;
        }
        if (ctype_digit($expression)) {
            return intval($expression);
        }
        if ($expression === 'bottom') {
            return $imageHeight - 1;
        }
        if ($expression === 'center') {
            return intval(round($imageHeight / 2) - 1);
        }
        if ($expression === 'top') {
            return 0;
        }
        if (preg_match('/^top ?\+ ?(\d+)$/', $expression, $matches)) {
            return intval($matches[1]);
        }
        if (preg_match('/^center ?\+ ?(\d+)$/', $expression, $matches)) {
            return intval(round($imageHeight / 2)) - 1 + intval($matches[1]);
        }
        if (preg_match('/^bottom ?- ?(\d+)$/', $expression, $matches)) {
            return $imageHeight - 1 - intval($matches[1]);
        }
        if (preg_match('/^center ?- ?(\d+)$/', $expression, $matches)) {
            return intval(round($imageHeight / 2)) - 1 - intval($matches[1]);
        }
        return false;
    }
}