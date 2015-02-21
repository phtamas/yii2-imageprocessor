<?php
namespace phtamas\yii2\imageprocessor\test\double;

use Imagine\Draw\DrawerInterface;
use Imagine\Effects\EffectsInterface;
use Imagine\Exception\OutOfBoundsException;
use Imagine\Exception\RuntimeException;
use Imagine\Image\BoxInterface;
use Imagine\Image\Fill\FillInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\InvalidArgumentException;
use Imagine\Image\LayersInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Metadata\MetadataBag;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\PointInterface;
use Imagine\Image\ProfileInterface;
use phtamas\yii2\imageprocessor\test\double\TestSpyTrait;
use yii\base\Exception;

class ImageInterfaceSpy implements ImageInterface
{
    use TestSpyTrait;

    private $size;

    /** @var  null|\Imagine\Image\Metadata\MetadataBag */
    private $metadata;

    public function __construct(BoxInterface $size = null)
    {
        $this->size = $size;
    }


    public function get($format, array $options = array())
    {

    }


    public function __toString()
    {
        return '';
    }

    public function draw()
    {

    }

    public function effects()
    {

    }

    public function getSize()
    {
        return $this->size;
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
        if (!isset($this->metadata)) {
            throw new \Exception('Metadata is not set.');
        }
        return $this->metadata;
    }

    public function copy()
    {

    }

    public function crop(PointInterface $start, BoxInterface $size)
    {
        $this->testSpyRecordMethodCall([
            'startX' => $start->getX(),
            'startY' => $start->getY(),
            'sizeWidth' => $size->getWidth(),
            'sizeHeight' => $size->getHeight(),
        ]);
    }

    public function resize(BoxInterface $size, $filter = ImageInterface::FILTER_UNDEFINED)
    {
        $this->testSpyRecordMethodCall([
            'sizeWidth' => $size->getWidth(),
            'sizeHeight' => $size->getHeight(),
            'filter' => $filter,
        ]);
    }

    public function rotate($angle, ColorInterface $background = null)
    {
        $this->testSpyRecordMethodCall();
    }

    public function paste(ImageInterface $image, PointInterface $start)
    {
        $this->testSpyRecordMethodCall([
            'image' => $image,
            'startX' => $start->getX(),
            'startY' => $start->getY(),
        ]);
    }

    public function save($path = null, array $options = array())
    {
        $this->testSpyRecordMethodCall();
    }

    public function show($format, array $options = array())
    {
        $this->testSpyRecordMethodCall();
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

    public function setMetadata(MetadataBag $metadata)
    {
        $this->metadata = $metadata;
    }
}