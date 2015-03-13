<?php
namespace phtamas\yii2\imageprocessor\test\unit;

use Yii;
use yii\web\Response;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Metadata\MetadataBag;
use PHPUnit_Framework_TestCase;
use phtamas\yii2\imageprocessor\Component;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceDummy;
use phtamas\yii2\imageprocessor\test\double\AbstractImagineSpy;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceSpy;
use phtamas\yii2\imageprocessor\test\double\AbstractImagineStub;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceStub;

class ComponentTest extends PHPUnit_Framework_TestCase
{
    private $optionsConfiguration = [
        'jpegQuality' => 90,
        'pngCompression' => 7,
    ];

    private $imagineOptions = [
        'jpeg_quality' => 90,
        'png_compression_level' => 7,
    ];

    private $processConfiguration = [
        ['resize', 'width' => 100, 'height' => 100],
    ];

    private $processMethodCall = [
        'methodName' => 'resize',
        'arguments' => [
            'sizeWidth' => 100,
            'sizeHeight' => 100,
            'filter' => ImageInterface::FILTER_UNDEFINED,
        ],
    ];

    public function testCreateFromFile()
    {
        $imageMock = new ImageInterfaceDummy();
        $imagineSpy = new AbstractImagineSpy($imageMock);
        $component = new Component(['imagine' => $imagineSpy]);
        $image = $component->create('/path/to/file');
        $this->assertEquals(1, $imagineSpy->testSpyGetMethodCallCount('open'));
        $this->assertEquals([['/path/to/file']], $imagineSpy->testSpyGetMethodCallArguments('open'));
        $this->assertSame($imageMock, $image);
    }

    public function testCreateFromAlias()
    {
        $imageMock = new ImageInterfaceDummy();
        $imagineSpy = new AbstractImagineSpy($imageMock);
        $component = new Component(['imagine' => $imagineSpy]);
        Yii::setAlias('alias', '/path/to/file');
        $image = $component->create('@alias');
        $this->assertEquals(1, $imagineSpy->testSpyGetMethodCallCount('open'));
        $this->assertEquals([['/path/to/file']], $imagineSpy->testSpyGetMethodCallArguments('open'));
        $this->assertSame($imageMock, $image);
        Yii::setAlias('alias', null);
    }

    public function testCreateFromBinaryData()
    {
        $imageMock = new ImageInterfaceDummy();
        $imagineSpy = new AbstractImagineSpy($imageMock);
        $component = new Component(['imagine' => $imagineSpy]);
        $image = $component->create(['data' => 'binary data as string']);
        $this->assertEquals(1, $imagineSpy->testSpyGetMethodCallCount('load'));
        $this->assertEquals([['binary data as string']], $imagineSpy->testSpyGetMethodCallArguments('load'));
        $this->assertSame($imageMock, $image);
    }

    public function testCreateFromResource()
    {
        $imageMock = new ImageInterfaceDummy();
        $imagineSpy = new AbstractImagineSpy($imageMock);
        $component = new Component(['imagine' => $imagineSpy]);
        $resource = fopen('php://stdin', 'r');
        $image = $component->create($resource);
        $this->assertEquals(1, $imagineSpy->testSpyGetMethodCallCount('read'));
        $this->assertEquals([[$resource]], $imagineSpy->testSpyGetMethodCallArguments('read'));
        $this->assertSame($imageMock, $image);
        fclose($resource);
    }

    public function testCreateFromSize()
    {
        $imageMock = new ImageInterfaceDummy();
        $imagineSpy = new AbstractImagineSpy($imageMock);
        $component = new Component(['imagine' => $imagineSpy]);
        $image = $component->create([
            'width' => 300,
            'height' => 200,
        ]);
        $this->assertEquals(1, $imagineSpy->testSpyGetMethodCallCount('create'));
        $this->assertEquals([[300, 200]], $imagineSpy->testSpyGetMethodCallArguments('create'));
        $this->assertSame($imageMock, $image);
    }

    public function testCreateFromImageInstance()
    {
        $imageMock = new ImageInterfaceDummy();
        $component = new Component();
        $this->assertEquals($imageMock, $component->create($imageMock));
    }

    public function testCreateFromInvalidSource()
    {
        $component = new Component();
        $this->setExpectedException('\InvalidArgumentException');
        $component->create([]);
    }

    public function testProcessWithPredefinedProcessing()
    {
        $imageSpy = new ImageInterfaceSpy();
        $component = new Component([
            'define' => [
                'my_definition' => [
                    'process' => $this->processConfiguration,
                ],
            ],
        ]);
        $result = $component->process($imageSpy, 'my_definition');
        $this->assertSame($imageSpy, $result);
        $this->assertEquals(1, $imageSpy->testSpyGetMethodCallCount('resize'));
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1));
    }

    public function testProcessWithNonExistingPredefinedProcessing()
    {
        $imageSpy = new ImageInterfaceSpy();
        $component = new Component();
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Definition not found for processing: "non_existing_definition".'
        );
        $component->process($imageSpy, 'non_existing_definition');
    }

    public function testProcessWithAdHocProcesing()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component();
        $result = $component->process(
            $imageSpy,
            $this->processConfiguration
        );
        $this->assertSame($imageSpy, $result);
        $this->assertEquals(1, $imageSpy->testSpyGetMethodCallCount('resize'));
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1));

    }

    public function testProcessWithTransformationThatCreatesNewImageInstance()
    {
        $originalImageDummy = new ImageInterfaceDummy();
        $newImageDummy = new ImageInterfaceDummy();
        $component = new Component([
            'transformations' => [
                'stub' => [
                    'class' => '\phtamas\yii2\imageprocessor\test\double\TransformationStub',
                    'transformReturnValue' => $newImageDummy,
                    'width' => 150,
                    'height' => 100,
                ],
            ],
        ]);
        $this->assertSame($newImageDummy, $component->process($originalImageDummy, [['stub']]));
    }

    public function testSaveWithUndefinedOptions()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component();
        $component->save($imageSpy, '/destination/path');
        $this->assertEquals(1, $imageSpy->testSpyGetMethodCallCount('save'));
        $this->assertEquals(
            [
                ['/destination/path', []],
            ],
            $imageSpy->testSpyGetMethodCallArguments('save')
        );
    }

    public function testSaveWithDefaultOptions()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component($this->optionsConfiguration);
        $component->save($imageSpy, '/destination/path');
        $this->assertEquals(1, $imageSpy->testSpyGetMethodCallCount('save'));
        $this->assertEquals(
            [
                ['/destination/path', $this->imagineOptions],
            ],
            $imageSpy->testSpyGetMethodCallArguments('save')
        );
    }

    public function testSaveWithPredefinedOptionsAndProcessing()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component([
            'define' => [
                'my_definition' => array_merge($this->optionsConfiguration, [
                    'process' => $this->processConfiguration,
                ]),
            ],
        ]);
        $component->save($imageSpy, '/destination/path', 'my_definition');
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1), 'processing applied');
        $this->assertEquals(
            [
                'methodName' => 'save',
                'arguments' => [
                    '/destination/path',
                    $this->imagineOptions,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image saved'
        );
    }

    public function testSaveWithAdHocOptionsAndProcessing()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component();
        $component->save($imageSpy, '/destination/path', array_merge($this->optionsConfiguration, [
            'process' => $this->processConfiguration,
        ]));
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1), 'processing applied' );
        $this->assertEquals(
            [
                'methodName' => 'save',
                'arguments' => [
                    '/destination/path',
                    $this->imagineOptions,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image saved'
        );
    }

    public function testSaveAcceptsAliasAsDestinationPath()
    {
        Yii::setAlias('images', '/path/to/images');
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component();
        $component->save($imageSpy, '@images/image.jpg');
        $this->assertEquals(
            [
                'methodName' => 'save',
                'arguments' => ['/path/to/images/image.jpg', []],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testSendWithUndefinedOptions()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component();
        $component->send($imageSpy, 'jpg');
        $this->assertEquals(
            [
                'methodName' => 'get',
                'arguments' => ['jpg', []],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
        $this->assertEquals(Response::FORMAT_RAW, Yii::$app->response->format);
        $this->assertEquals('image/jpeg', Yii::$app->response->headers->get('Content-Type'));
        $this->assertEquals('12345678', Yii::$app->response->content);
    }

    public function testSendWithDefaultOptions()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component($this->optionsConfiguration);
        $component->send($imageSpy, 'jpg');
        $this->assertEquals(
            [
                'methodName' => 'get',
                'arguments' => ['jpg', $this->imagineOptions],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testSendWithPredefinedOptionsAndProcessing()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component([
            'define' => [
                'my_definition' => array_merge($this->optionsConfiguration, [
                    'process' => $this->processConfiguration,
                ]),
            ],
        ]);
        $component->send($imageSpy, 'jpg', 'my_definition');
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1), 'processing applied');
        $this->assertEquals(
            [
                'methodName' => 'get',
                'arguments' => ['jpg', $this->imagineOptions],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image sent'
        );
    }

    public function testSendWithAdHocOptionsAndProcessing()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component();
        $component->send($imageSpy, 'jpeg', array_merge($this->optionsConfiguration, [
            'process' => $this->processConfiguration,
        ]));
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1), 'processing applied');
        $this->assertEquals(
            [
                'methodName' => 'get',
                'arguments' => ['jpeg', $this->imagineOptions],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image sent'
        );
    }

    public function testSaveAndSendWithUndefinedOptions()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component();
        $component->saveAndSend($imageSpy, '/destination/path', 'jpg');
        $this->assertEquals(
            [
                'methodName' => 'get',
                'arguments' => ['jpg', []],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'image sent'
        );
        $this->assertEquals(
            [
                'methodName' => 'save',
                'arguments' => ['/destination/path', []],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image saved'
        );
    }

    public function testSaveAndSendWithDefaultOptions()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component($this->optionsConfiguration);
        $component->saveAndSend($imageSpy, '/destination/path', 'jpg');
        $this->assertEquals(
            [
                'methodName' => 'get',
                'arguments' => ['jpg', $this->imagineOptions],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1),
            'image sent'
        );
        $this->assertEquals(
            [
                'methodName' => 'save',
                'arguments' => ['/destination/path', $this->imagineOptions],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image saved'
        );
    }

    public function testSaveAndSendWithPredefinedOptionsAndProcessing()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component([
            'define' => [
                'my_definition' => array_merge($this->optionsConfiguration, [
                    'process' => $this->processConfiguration,
                ]),
            ],
        ]);
        $component->saveAndSend($imageSpy, '/destination/path' , 'jpg', 'my_definition');
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1), 'processing applied');
        $this->assertEquals(
            [
                'methodName' => 'get',
                'arguments' => [
                    'jpg',
                    $this->imagineOptions,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image sent'
        );
        $this->assertEquals(
            [
                'methodName' => 'save',
                'arguments' => [
                    '/destination/path',
                    $this->imagineOptions,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(3),
            'image saved'
        );

    }

    public function testSaveAndSendWithAdHocOptionsAndProcessing()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component();
        $component->saveAndSend($imageSpy, '/destination/path' , 'jpg', array_merge($this->optionsConfiguration, [
            'process' => $this->processConfiguration,
        ]));
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1), 'processing applied');
        $this->assertEquals(
            [
                'methodName' => 'get',
                'arguments' => [
                    'jpg',
                    $this->imagineOptions,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image sent'
        );
        $this->assertEquals(
            [
                'methodName' => 'save',
                'arguments' => [
                    '/destination/path',
                    $this->imagineOptions,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(3),
            'image saved'
        );

    }

    public function testSaveAndSendAcceptsAliasAsDestinationPath()
    {
        Yii::setAlias('images', '/path/to/images');
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component();
        $component->saveAndSend($imageSpy, '@images/image.jpg');
        $this->assertEquals(
            [
                'methodName' => 'save',
                'arguments' => ['/path/to/images/image.jpg', []],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2)
        );
    }

    public function testSaveAndSendWithImplicitType()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setBinaryData('12345678');
        $component = new Component();
        $component->saveAndSend($imageSpy, '/destination/directory/image.jpg');
        $this->assertEquals(
            [
                'methodName' => 'get',
                'arguments' => ['jpg', []],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testProcessWithCustomTransformation()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component([
            'transformations' => [
                'custom' => '\phtamas\yii2\imageprocessor\test\double\TransformationStub',
            ]
        ]);
        $component->process($imageSpy, [['custom', 'width' => 100, 'height' => 100]]);
        $this->assertEquals(
            [
                'methodName' => 'resize',
                'arguments' => [
                    'sizeWidth' => 100,
                    'sizeHeight' => 100,
                    'filter' => ImageInterface::FILTER_UNDEFINED,
                ],

            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testBuiltInTransformationWithConfiguredDefaults()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component([
            'transformations' => [
                'resize' => ['width' => 100, 'height' => 100],
            ]
        ]);
        $component->process($imageSpy, [['resize', 'height' => 50]]);
        $this->assertEquals(
            [
                'methodName' => 'resize',
                'arguments' => [
                    'sizeWidth' => 100,
                    'sizeHeight' => 50,
                    'filter' => ImageInterface::FILTER_UNDEFINED,
                ],

            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testCustomTransformationWithConfiguredDefaults()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component([
            'transformations' => [
                'custom' => [
                    'class' => '\phtamas\yii2\imageprocessor\test\double\TransformationStub',
                    'width' => 100,
                    'height' => 100
                ],
            ]
        ]);
        $component->process($imageSpy, [['custom', 'height' => 50]]);
        $this->assertEquals(
            [
                'methodName' => 'resize',
                'arguments' => [
                    'sizeWidth' => 100,
                    'sizeHeight' => 50,
                    'filter' => ImageInterface::FILTER_UNDEFINED,
                ],

            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testCustomTransformationOverridesBuiltInTransformationClass()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component([
            'transformations' => [
                'crop' => '\phtamas\yii2\imageprocessor\test\double\TransformationStub',
            ]
        ]);
        $component->process($imageSpy, [['crop', 'width' => 100, 'height' => 50]]);
        $this->assertEquals(
            [
                'methodName' => 'resize',
                'arguments' => [
                    'sizeWidth' => 100,
                    'sizeHeight' => 50,
                    'filter' => ImageInterface::FILTER_UNDEFINED,
                ],

            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testAutorotateViaMethodCall()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $imageSpy->setMetadata(new MetadataBag(['ifd0.Orientation' => 3]));
        $component = new Component();
        $this->assertInstanceOf('\Imagine\Image\ImageInterface', $component->autorotate($imageSpy));
        $this->assertEquals(
            [
                'methodName' => 'rotate',
                'arguments' => [180],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testCropViaMethodCall()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component();
        $this->assertInstanceOf('\Imagine\Image\ImageInterface', $component->crop($imageSpy, [
            'x' => 10,
            'y' => 10,
            'width' => 100,
            'height' => 50,
        ]));
        $this->assertEquals(
            [
                'methodName' => 'crop',
                'arguments' => [
                    'startX' => 10,
                    'startY' => 10,
                    'sizeWidth' => 100,
                    'sizeHeight' => 50,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testResizeViaMethodCall()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component();
        $this->assertInstanceOf('\Imagine\Image\ImageInterface', $component->resize($imageSpy, [
            'width' => 100,
            'height' => 50,
        ]));
        $this->assertEquals(
            [
                'methodName' => 'resize',
                'arguments' => [
                    'sizeWidth' => 100,
                    'sizeHeight' => 50,
                    'filter' => ImageInterface::FILTER_UNDEFINED,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testWatermarkViaMethodCall()
    {
        $imagineStub = new AbstractImagineStub();
        $watermarkImageStub = new ImageInterfaceStub();
        $watermarkImageStub->setSize(new Box(100, 50));
        $imagineStub->setImage($watermarkImageStub);
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component(['imagine' => $imagineStub]);
        $this->assertInstanceOf('\Imagine\Image\ImageInterface', $component->watermark($imageSpy, [
            'path' => '/path/to/watermark/image',
            'align' => 'top-left',
        ]));
        $this->assertSame(
            [
                'methodName' => 'paste',
                'arguments' => [
                    'image' => $watermarkImageStub,
                    'startX' => 0,
                    'startY' => 0,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }
} 