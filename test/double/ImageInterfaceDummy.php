<?php
namespace phtamas\yii2\imageprocessor\test\double;

use Imagine\Image\BoxInterface;
use Imagine\Image\Fill\FillInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Metadata;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\PointInterface;
use Imagine\Image\ProfileInterface;

class ImageInterfaceDummy implements ImageInterface
{
    public function get($format, array $options = array())
    {

    }

    /**
     * @return string
     */
    public function __toString()
    {

    }

    public function draw()
    {

    }

    public function effects()
    {

    }

    public function getSize()
    {

    }

    public function mask()
    {

    }

    public function histogram()
    {

    }

    public function getColorAt(PointInterface $point)
    {

    }

    public function layers()
    {

    }

    public function interlace($scheme)
    {

    }

    public function palette()
    {

    }

    public function usePalette(PaletteInterface $palette)
    {

    }

    public function profile(ProfileInterface $profile)
    {

    }

    public function metadata()
    {

    }

    public function copy()
    {

    }

    public function crop(PointInterface $start, BoxInterface $size)
    {

    }

    public function resize(BoxInterface $size, $filter = ImageInterface::FILTER_UNDEFINED)
    {

    }

    public function rotate($angle, ColorInterface $background = null)
    {

    }

    public function paste(ImageInterface $image, PointInterface $start)
    {

    }

    public function save($path = null, array $options = array())
    {

    }

    public function show($format, array $options = array())
    {

    }

    public function flipHorizontally()
    {

    }

    public function flipVertically()
    {

    }

    public function strip()
    {

    }

    public function thumbnail(BoxInterface $size, $mode = self::THUMBNAIL_INSET, $filter = ImageInterface::FILTER_UNDEFINED)
    {

    }

    public function applyMask(ImageInterface $mask)
    {

    }

    public function fill(FillInterface $fill)
    {

    }
}