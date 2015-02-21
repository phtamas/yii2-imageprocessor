<?php
namespace phtamas\yii2\imageprocessor\test\double;

use Imagine\Image\BoxInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\Color\ColorInterface;

class ImagineInterfaceDummy implements ImagineInterface
{
    public function create(BoxInterface $size, ColorInterface $color = null)
    {

    }

    public function open($path)
    {

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