<?php
namespace phtamas\yii2\imageprocessor\test\unit\transformation;

use PHPUnit_Framework_TestCase;
use phtamas\yii2\imageprocessor\transformation\Crop;
use Imagine\Image\Box;
use phtamas\yii2\imageprocessor\test\double\ImagineInterfaceDummy;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceSpy;

class CropTest extends PHPUnit_Framework_TestCase
{
    public function testCrop()
    {
        $crop = new Crop();
        $crop->x = 10;
        $crop->y = 10;
        $crop->width = 100;
        $crop->height = 50;
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $crop->transform($imageSpy, new ImagineInterfaceDummy());
        $this->assertEquals(1, $imageSpy->testSpyGetMethodCallCount('crop'));
        $this->assertEquals(
            [
                'startX' => 10,
                'startY' => 10,
                'sizeWidth' => 100,
                'sizeHeight' => 50,
            ],
            $imageSpy->testSpyGetMethodCallArguments('crop')[0]
        );
    }

    public function testCroppedAreaMustNotBeLargerThanImage()
    {
        $crop = new Crop();
        $crop->x = 10;
        $crop->y = 10;
        $crop->width = 191;
        $crop->height = 91;
        $imageSpy = new ImageInterfaceSpy(new Box(200, 100));
        $crop->transform($imageSpy, new ImagineInterfaceDummy());
        $this->assertEquals(1, $imageSpy->testSpyGetMethodCallCount('crop'));
        $this->assertEquals(
            [
                'startX' => 10,
                'startY' => 10,
                'sizeWidth' => 190,
                'sizeHeight' => 90,
            ],
            $imageSpy->testSpyGetMethodCallArguments('crop')[0]
        );
    }

    public function testCropCenter()
    {
        $crop = new Crop();
        $crop->x = 'center - 40';
        $crop->y = 'center - 25';
        $crop->width = 80;
        $crop->height = 50;
        $imageSpy = new ImageInterfaceSpy(new Box(200, 100));
        $crop->transform($imageSpy, new ImagineInterfaceDummy());
        $this->assertEquals(1, $imageSpy->testSpyGetMethodCallCount('crop'));
        $this->assertEquals(
            [
                'startX' => 59,
                'startY' => 24,
                'sizeWidth' => 80,
                'sizeHeight' => 50,
            ],
            $imageSpy->testSpyGetMethodCallArguments('crop')[0]
        );
    }

    public function testCropTopLeft()
    {
        $crop = new Crop();
        $crop->x = 'left + 10';
        $crop->y = 'top + 20';
        $crop->width = 80;
        $crop->height = 50;
        $imageSpy = new ImageInterfaceSpy(new Box(200, 100));
        $crop->transform($imageSpy, new ImagineInterfaceDummy());
        $this->assertEquals(1, $imageSpy->testSpyGetMethodCallCount('crop'));
        $this->assertEquals(
            [
                'startX' => 10,
                'startY' => 20,
                'sizeWidth' => 80,
                'sizeHeight' => 50,
            ],
            $imageSpy->testSpyGetMethodCallArguments('crop')[0]
        );
    }

    public function testCropBottomRight()
    {
        $crop = new Crop();
        $crop->x = 'right - 80';
        $crop->y = 'bottom - 50';
        $crop->width = 80;
        $crop->height = 50;
        $imageSpy = new ImageInterfaceSpy(new Box(200, 100));
        $crop->transform($imageSpy, new ImagineInterfaceDummy());
        $this->assertEquals(1, $imageSpy->testSpyGetMethodCallCount('crop'));
        $this->assertEquals(
            [
                'startX' => 119,
                'startY' => 49,
                'sizeWidth' => 80,
                'sizeHeight' => 50,
            ],
            $imageSpy->testSpyGetMethodCallArguments('crop')[0]
        );
    }
} 