<?php
namespace phtamas\yii2\imageprocessor\test\double;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use phtamas\yii2\imageprocessor\TransformationInterface;

class TransformationStub implements TransformationInterface
{
    public $width;

    public $height;

    public function transform(ImageInterface $image, ImagineInterface $imagine)
    {
        $image->resize(new Box($this->width, $this->height));
    }
}