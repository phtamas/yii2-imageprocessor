<?php
namespace phtamas\yii2\imageprocessor\test\unit\transformation;

use Imagine\Image\ImageInterface;
use PHPUnit_Framework_TestCase;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceSpy;
use phtamas\yii2\imageprocessor\test\double\ImagineInterfaceDummy;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceStub;
use Imagine\Image\Box;
use phtamas\yii2\imageprocessor\transformation\Resize;

class ResizeTest extends PHPUnit_Framework_TestCase
{
    public function testResizeWidthNoWidthAndHeight()
    {
        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize = new Resize();
        $this->setExpectedException(
            'yii\base\InvalidConfigException',
            'At least one of the properties "width" and "height" must be set.'
        );
        $resize->transform($imageStub, new ImagineInterfaceDummy());
    }

    public function testResizeWithInvalidScaleTo()
    {
        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize = new Resize();
        $resize->width = 150;
        $resize->height = 100;
        $resize->scaleTo = 'invalid scaleTo';
        $this->setExpectedException(
            'yii\base\InvalidConfigException',
            'Invalid value (string) invalid scaleTo for property "scaleTo". Valid values: (string) fit, (string) cover, null.'
        );
        $resize->transform($imageStub, new ImagineInterfaceDummy());
    }

    public function testTransformWithInvalidOnly()
    {
        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize = new Resize();
        $resize->width = 150;
        $resize->only = 'invalid only';
        $this->setExpectedException(
            'yii\base\InvalidConfigException',
            'Invalid value (string) invalid only for property "only". Valid values: (string) up, (string) down, null.'
        );
        $resize->transform($imageStub, new ImagineInterfaceDummy());
    }

    public function testResizeByWidth()
    {
        $resize = new Resize();
        $resize->width = 150;

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getWidth(), 'width sized down');
        $this->assertEquals(100, $imageStub->getSize()->getHeight(), 'height sized down proportionally');

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(100, 50));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getWidth(), 'width sized up');
        $this->assertEquals(75, $imageStub->getSize()->getHeight(), 'height sized up proportionally');
    }

    public function testResizeByHeight()
    {
        $resize = new Resize();
        $resize->height = 150;

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getHeight(), 'height sized down');
        $this->assertEquals(225, $imageStub->getSize()->getWidth(), 'width sized down proportionally');

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(200, 100));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getHeight(), 'height sized up');
        $this->assertEquals(300, $imageStub->getSize()->getWidth(), 'width sized up proportionally');
    }

    public function testResizeOnlyUpByWidth()
    {
        $resize = new Resize();
        $resize->width = 150;
        $resize->only = 'up';

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(100, 50));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getWidth(), 'width sized up');
        $this->assertEquals(75, $imageStub->getSize()->getHeight(), 'height sized up proportionally');

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(300, $imageStub->getSize()->getWidth(), 'width has not been changed');
        $this->assertEquals(200, $imageStub->getSize()->getHeight(), 'height has not been changed');
    }

    public function testResizeOnlyUpByHeight()
    {
        $resize = new Resize();
        $resize->height = 150;
        $resize->only = 'up';

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(200, 100));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getHeight(), 'height sized up');
        $this->assertEquals(300, $imageStub->getSize()->getWidth(), 'width sized up proportionally');

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(200, $imageStub->getSize()->getHeight(), 'height has not been changed');
        $this->assertEquals(300, $imageStub->getSize()->getWidth(), 'width has not been changed');
    }

    public function testResizeOnlyDownByWidth()
    {
        $resize = new Resize();
        $resize->width = 150;
        $resize->only = 'down';

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getWidth(), 'width sized down');
        $this->assertEquals(100, $imageStub->getSize()->getHeight(), 'height sized down proportionally');

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(100, 50));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(100, $imageStub->getSize()->getWidth(), 'width has not been changed');
        $this->assertEquals(50, $imageStub->getSize()->getHeight(), 'height has not been changed');
    }

    public function testResizeOnlyDownByHeight()
    {
        $resize = new Resize();
        $resize->height = 150;
        $resize->only = 'down';

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getHeight(), 'height sized down');
        $this->assertEquals(225, $imageStub->getSize()->getWidth(), 'width sized down proportionally');

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(200, 100));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(100, $imageStub->getSize()->getHeight(), 'height has not been changed');
        $this->assertEquals(200, $imageStub->getSize()->getWidth(), 'width has not been changed');
    }

    public function testResizeScaleToFit()
    {
        $resize = new Resize();
        $resize->width = 150;
        $resize->height = 100;
        $resize->scaleTo = 'fit';

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(200, 300));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(100, $imageStub->getSize()->getHeight(), 'height sized down to fit');
        $this->assertEquals(67, $imageStub->getSize()->getWidth(), 'width sized down proportionally');

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(450, 200));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getWidth(), 'width sized down to fit');
        $this->assertEquals(67, $imageStub->getSize()->getHeight(), 'height sized down proportinally');

    }

    public function testResizeScaleToCover()
    {
        $resize = new Resize();
        $resize->width = 150;
        $resize->height = 100;
        $resize->scaleTo = 'cover';

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(200, 300));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(150, $imageStub->getSize()->getWidth(), 'width sized down to cover');
        $this->assertEquals(225, $imageStub->getSize()->getHeight(), 'height sized down proportinally');

        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(450, 200));
        $resize->transform($imageStub, new ImagineInterfaceDummy());
        $this->assertEquals(100, $imageStub->getSize()->getHeight(), 'height sized down to cover');
        $this->assertEquals(225, $imageStub->getSize()->getWidth(), 'width sized down proportinally');
    }

    public function testResizeWithFilter()
    {
        $resize = new Resize();
        $resize->width = 150;
        $resize->height = 100;

        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imagineDummy = new ImagineInterfaceDummy();

        $resize->filter = 'point';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_POINT],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'point'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'box';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_BOX],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'box'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'triangle';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_TRIANGLE],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'triangle'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'hermite';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_HERMITE],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'hermite'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'hanning';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_HANNING],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'hanning'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'hamming';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_HAMMING],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'hamming'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'blackman';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_BLACKMAN],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'blackman'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'gaussian';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_GAUSSIAN],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'gaussian'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'quadratic';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_QUADRATIC],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'quadratic'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'cubic';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_CUBIC],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'cubic'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'catrom';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_CATROM],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'catrom'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'mitchell';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_MITCHELL],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'mitchell'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'lanczos';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_LANCZOS],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'lanczos'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'bessel';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_BESSEL],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'bessel'
        );

        $imageSpy->testSpyReset();
        $resize->filter = 'sinc';
        $resize->transform($imageSpy, $imagineDummy);
        $this->assertEquals(
            ['sizeWidth' => 150, 'sizeHeight' => 100, 'filter' => ImageInterface::FILTER_SINC],
            $imageSpy->testSpyGetMethodCallArguments('resize')[0],
            'sinc'
        );
    }

    public function testResizeWithInvalidFilter()
    {
        $imageStub = new ImageInterfaceStub();
        $imageStub->setSize(new Box(300, 200));
        $resize = new Resize();
        $resize->width = 150;
        $resize->height = 100;
        $resize->filter = 'invalid filter';
        $this->setExpectedException('\yii\base\InvalidConfigException');
        $resize->transform($imageStub, new ImagineInterfaceDummy());
    }
} 