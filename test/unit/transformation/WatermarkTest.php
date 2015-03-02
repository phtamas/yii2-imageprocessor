<?php
namespace phtamas\yii2\imageprocessor\test\unit\transformation;

use Imagine\Image\ImageInterface;
use PHPUnit_Framework_TestCase;
use Imagine\Image\Box;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceDummy;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceSpy;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceStub;
use phtamas\yii2\imageprocessor\test\double\ImagineInterfaceDummy;
use phtamas\yii2\imageprocessor\test\double\AbstractImagineStub;
use phtamas\yii2\imageprocessor\transformation\Watermark;

class WatermarkTest extends PHPUnit_Framework_TestCase
{
    public function testWithNoPath()
    {
        $watermark = new Watermark();
        $this->setExpectedException('yii\base\InvalidConfigException');
        $watermark->transform(new ImageInterfaceDummy(), new ImagineInterfaceDummy());
    }

    public function testAlign()
    {
        $imagineStub = new AbstractImagineStub();
        $watermarkImageStub = new ImageInterfaceStub();
        $watermarkImageStub->setSize(new Box(100, 50));
        $imagineStub->setImage($watermarkImageStub);
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $watermark = new Watermark(['path' => '/path/to/watermark/image']);

        $watermark->align = 'top-left';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 0,
                    'startY' => 0,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'top-left'
        );

        $imageSpy->testSpyReset();
        $watermark->align = 'top-center';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 99,
                    'startY' => 0,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'top-center'
        );

        $imageSpy->testSpyReset();
        $watermark->align = 'top-right';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 199,
                    'startY' => 0,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'top-right'
        );

        $imageSpy->testSpyReset();
        $watermark->align = 'bottom-left';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 0,
                    'startY' => 149,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'bottom-left'
        );

        $imageSpy->testSpyReset();
        $watermark->align = 'bottom-center';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 99,
                    'startY' => 149,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'bottom-center'
        );

        $imageSpy->testSpyReset();
        $watermark->align = 'bottom-right';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 199,
                    'startY' => 149,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'bottom-right'
        );

        $imageSpy->testSpyReset();
        $watermark->align = 'center';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 99,
                    'startY' => 74,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'center'
        );
    }

    public function testInvalidAlign()
    {
        $imagineStub = new AbstractImagineStub();
        $watermarkImageStub = new ImageInterfaceStub();
        $watermarkImageStub->setSize(new Box(100, 50));
        $imagineStub->setImage($watermarkImageStub);
        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $watermark = new Watermark();
        $watermark->align = 'invalid align';
        $this->setExpectedException('\yii\base\InvalidConfigException');
        $watermark->transform($imageStub, $imagineStub);
    }

    public function testMarginAsInteger()
    {
        $imagineStub = new AbstractImagineStub();
        $watermarkImageStub = new ImageInterfaceStub();
        $watermarkImageStub->setSize(new Box(100, 50));
        $imagineStub->setImage($watermarkImageStub);
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $watermark = new Watermark(['path' => '/path/to/watermark/image']);
        $watermark->margin = 10;

        $watermark->align = 'top-left';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 10,
                    'startY' => 10,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'top-left'
        );

        $imageSpy->testSpyReset();
        $watermark->align = 'bottom-right';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 189,
                    'startY' => 139,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'bottom-right'
        );
    }

    public function testWatermarkMustNotExceedImageSize()
    {
        $imagineStub = new AbstractImagineStub();
        $watermarkImageSpy = new ImageInterfaceSpy(new Box(200, 100));
        $imagineStub->setImage($watermarkImageSpy);
        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(100, 50));
        $watermark = new Watermark(['path' => '/path/to/watermark/image']);
        $watermark->margin = 10;

        $watermark->align = 'top-left';
        $watermark->transform($imageStub, $imagineStub);
        $this->assertEquals(
            [
                'methodName' => 'resize',
                'arguments' => [
                    'sizeWidth' => 60,
                    'sizeHeight' => 30,
                    'filter' => ImageInterface::FILTER_UNDEFINED,
                ],
            ],
            $watermarkImageSpy->testSpyGetMethodCallAtPosition(1),
            'top-left'
        );

        $watermarkImageSpy->testSpyReset();
        $watermark->align = 'bottom-right';
        $watermark->transform($imageStub, $imagineStub);
        $this->assertEquals(
            [
                'methodName' => 'resize',
                'arguments' => [
                    'sizeWidth' => 60,
                    'sizeHeight' => 30,
                    'filter' => ImageInterface::FILTER_UNDEFINED,
                ],
            ],
            $watermarkImageSpy->testSpyGetMethodCallAtPosition(1),
            'bottom-right'
        );

        $watermarkImageSpy->testSpyReset();
        $watermark->align = 'center';
        $watermark->transform($imageStub, $imagineStub);
        $this->assertEquals(
            [
                'methodName' => 'resize',
                'arguments' => [
                    'sizeWidth' => 60,
                    'sizeHeight' => 30,
                    'filter' => ImageInterface::FILTER_UNDEFINED,
                ],
            ],
            $watermarkImageSpy->testSpyGetMethodCallAtPosition(1),
            'center'
        );
    }

    /**
     * Issue #4
     */
    public function testResizedWatermarkIsAlignedProperly()
    {
        $imagineStub = new AbstractImagineStub();
        $watermarkImageStub = new ImageInterfaceStub();
        $watermarkImageStub->setSize(new Box(200, 100));
        $imagineStub->setImage($watermarkImageStub);
        $imageSpy = new ImageInterfaceSpy(new Box(100, 80));
        $watermark = new Watermark(['path' => '/path/to/watermark/image']);
        $watermark->margin = 10;

        $watermark->align = 'top-left';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertEquals(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 10,
                    'startY' => 10,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'top-left'
        );

        $imageSpy->testSpyReset();
        $watermark->align = 'bottom-right';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertEquals(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 10,
                    'startY' => 29,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'bottom-right aligned properly'
        );

        $imageSpy->testSpyReset();
        $watermark->align = 'center';
        $watermark->transform($imageSpy, $imagineStub);
        $this->assertEquals(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 10,
                    'startY' => 19,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'center aligned properly'
        );
    }
}