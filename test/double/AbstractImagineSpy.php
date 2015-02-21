<?php
namespace phtamas\yii2\imageprocessor\test\double;

use Imagine\Image\AbstractImagine;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use phtamas\yii2\imageprocessor\test\double\TestSpyTrait;

class AbstractImagineSpy extends AbstractImagine
{
    use TestSpyTrait;

    private $image;

    public function __construct(ImageInterface $image)
    {
        $this->image = $image;
    }

    public function create(BoxInterface $size, ColorInterface $color = null)
    {
        $this->testSpyRecordMethodCall([
            $size->getWidth(),
            $size->getHeight(),
        ]);
        return $this->image;
    }

    public function open($path)
    {
        $this->testSpyRecordMethodCall();
        return $this->image;
    }

    public function load($string)
    {
        $this->testSpyRecordMethodCall();
        return $this->image;
    }

    public function read($resource)
    {
        $this->testSpyRecordMethodCall();
        return $this->image;
    }

    public function font($file, $size, ColorInterface $color)
    {

    }
}