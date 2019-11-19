<?php
namespace phtamas\yii2\imageprocessor\transformation;

use yii\base\BaseObject;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Imagine\Image\Box;
use phtamas\yii2\imageprocessor\TransformationInterface;

/**
 * Crops the image to the specified width and height
 */
class Crop extends BaseObject implements TransformationInterface
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

    /** @var  int */
    private $imageWidth;

    /** @var  int */
    private $imageHeight;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function transform(ImageInterface $image, ImagineInterface $imagine)
    {
        $this->imageWidth = $image->getSize()->getWidth();
        $this->imageHeight = $image->getSize()->getHeight();
        $x = $this->parseX($this->x);
        $y = $this->parseY($this->y);

        $width = $this->width;
        $height = $this->height;

        if ($x + $width > $image->getSize()->getWidth()) {
            $width -= ($x + $width) - ($image->getSize()->getWidth());
        }
        if ($y + $height > $image->getSize()->getHeight()) {
            $height -= ($y + $height) - ($image->getSize()->getHeight());
        }
        $image->crop(new Point($x, $y), new Box($width, $height));
    }

    /**
     * @param int|string $expression
     * @throws \InvalidArgumentException
     * @return int|bool
     */
    private function parseX($expression)
    {
        $x = $this->doParseX($expression);
        if ($x === false) {
            return false;
        }
        if ($x > $this->imageWidth - 1) {
            return $this->imageWidth - 1;
        }
        if ($x < 0) {
            return 0;
        }
        return $x;
    }

    /**
     * @param int|string $expression
     * @throws \InvalidArgumentException
     * @return int
     */
    private function parseY($expression)
    {
        $y = $this->doParseY($expression);
        if ($y === false) {
            return false;
        }
        if ($y > $this->imageHeight - 1) {
            return $this->imageHeight - 1;
        }
        if ($y < 0) {
            return 0;
        }
        return $y;
    }

    /**
     * @param mixed $expression
     * @return bool|int
     */
    private function doParseX($expression)
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
            return intval(round($this->imageWidth / 2)) - 1;
        }
        if ($expression === 'right') {
            return $this->imageWidth - 1;
        }
        if (false !== ($parsed = $this->parseXAsSumExpression($expression))) {
            return $parsed;
        }
        if (false !== ($parsed = $this->parseXAsDifferenceExpression($expression))) {
            return $parsed;
        }
        return false;
    }

    private function parseXAsSumExpression($expression)
    {
        $operatorPosition = strpos($expression, '+');
        if ($operatorPosition === false) {
            return false;
        }
        $leftExpression = trim(substr($expression, 0, $operatorPosition));
        $rigthExpression = trim(substr($expression, $operatorPosition + 1));
        if ($leftExpression === 'left' && ctype_digit($rigthExpression)) {
            return intval($rigthExpression);
        }
        if ($leftExpression === 'center' && ctype_digit($rigthExpression)) {
            return intval(round($this->imageWidth / 2)) - 1 + intval($rigthExpression);
        }
        return false;
    }

    private function parseXAsDifferenceExpression($expression)
    {
        $operatorPosition = strpos($expression, '-');
        if ($operatorPosition === false) {
            return false;
        }
        $leftExpression = trim(substr($expression, 0, $operatorPosition));
        $rigthExpression = trim(substr($expression, $operatorPosition + 1));
        if ($leftExpression === 'right' && ctype_digit($rigthExpression)) {
            return $this->imageWidth - intval($rigthExpression) - 1;
        }
        if ($leftExpression === 'center' && ctype_digit($rigthExpression)) {
            return intval(round($this->imageWidth / 2)) - intval($rigthExpression) - 1;
        }
        return false;
    }

    private function doParseY($expression)
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
            return $this->imageHeight - 1;
        }
        if ($expression === 'center') {
            return intval(round($this->imageHeight / 2) - 1);
        }
        if ($expression === 'top') {
            return 0;
        }
        if (false !== ($y = $this->parseYAsSumExpression($expression))) {
            return $y;
        }
        if (false !== ($y = $this->parseYAsDifferenceExpression($expression))) {
            return $y;
        }
        return false;
    }

    private function parseYAsSumExpression($expression)
    {
        $operatorPosition = strpos($expression, '+');
        if ($operatorPosition === false) {
            return false;
        }
        $leftExpression = trim(substr($expression, 0, $operatorPosition));
        $rigthExpression = trim(substr($expression, $operatorPosition + 1));
        if ($leftExpression === 'top' && ctype_digit($rigthExpression)) {
            return intval($rigthExpression);
        }
        if ($leftExpression === 'center' && ctype_digit($rigthExpression)) {
            return intval(round($this->imageHeight / 2)) - 1 + intval($rigthExpression);
        }
        return false;
    }

    private function parseYAsDifferenceExpression($expression)
    {
        $operatorPosition = strpos($expression, '-');
        if ($operatorPosition === false) {
            return false;
        }
        $leftExpression = trim(substr($expression, 0, $operatorPosition));
        $rigthExpression = trim(substr($expression, $operatorPosition + 1));
        if ($leftExpression === 'bottom' && ctype_digit($rigthExpression)) {
            return $this->imageHeight - 1 - intval($rigthExpression);
        }
        if ($leftExpression === 'center' && ctype_digit($rigthExpression)) {
            return intval(round($this->imageHeight / 2)) - 1 - intval($rigthExpression);
        }
        return false;
    }
}