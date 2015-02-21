<?php
namespace phtamas\yii2\imageprocessor\test\double;

use Imagine\Image\AbstractImagine;
use Imagine\Image\ImageInterface;
use Imagine\Image\BoxInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use \Exception;

class AbstractImagineStub extends AbstractImagine
{
    /** @var  ImageInterface */
    private $image;

    public function setImage(ImageInterface $image)
    {
        $this->image = $image;
    }

    public function create(BoxInterface $size, ColorInterface $color = null)
    {

    }

    public function open($path)
    {
        if (!isset($this->image)) {
            throw new Exception(sprintf(
                '%s cannot return image instance: no image set via setImage().',
                __METHOD__
            ));
        }
        return $this->image;
    }

    public function load($string)
    {

    }

    public function read($resource)
    {

    }

    public function font($file, $size, ColorInterface $color)
    {

    }
}