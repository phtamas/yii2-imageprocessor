<?php
namespace phtamas\yii2\imageprocessor\test\unit\transformation;

use PHPUnit_Framework_TestCase;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceSpy;
use Imagine\Image\Metadata\MetadataBag;
use phtamas\yii2\imageprocessor\test\double\ImagineInterfaceDummy;
use phtamas\yii2\imageprocessor\transformation\Autorotate;

class AutorotateTest extends PHPUnit_Framework_TestCase
{
    public function testFlip()
    {
        $imageSpy = new ImageInterfaceSpy();
        $imageSpy->setMetadata(new MetadataBag(['ifd0.Orientation' => 3]));
        $autorotate = new Autorotate();
        $autorotate->transform($imageSpy, new ImagineInterfaceDummy());
        $this->assertEquals(
            [
                'methodName' => 'rotate',
                'arguments' => [180]
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testRotateLeft()
    {
        $imageSpy = new ImageInterfaceSpy();
        $imageSpy->setMetadata(new MetadataBag(['ifd0.Orientation' => 6]));
        $autorotate = new Autorotate();
        $autorotate->transform($imageSpy, new ImagineInterfaceDummy());
        $this->assertEquals(
            [
                'methodName' => 'rotate',
                'arguments' => [90]
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testRotateRight()
    {
        $imageSpy = new ImageInterfaceSpy();
        $imageSpy->setMetadata(new MetadataBag(['ifd0.Orientation' => 8]));
        $autorotate = new Autorotate();
        $autorotate->transform($imageSpy, new ImagineInterfaceDummy());
        $this->assertEquals(
            [
                'methodName' => 'rotate',
                'arguments' => [-90]
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }
} 