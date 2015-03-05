<?php
namespace phtamas\yii2\imageprocessor\test\unit;

use Yii;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use PHPUnit_Framework_TestCase;
use phtamas\yii2\imageprocessor\Component;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceDummy;
use phtamas\yii2\imageprocessor\test\double\AbstractImagineSpy;
use phtamas\yii2\imageprocessor\test\double\ImageInterfaceSpy;

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
        $image = $component->create(['file' => '/path/to/file']);
        $this->assertEquals(1, $imagineSpy->testSpyGetMethodCallCount('open'));
        $this->assertEquals([['/path/to/file']], $imagineSpy->testSpyGetMethodCallArguments('open'));
        $this->assertSame($imageMock, $image);
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
        $image = $component->create(['resource' => 'resource']);
        $this->assertEquals(1, $imagineSpy->testSpyGetMethodCallCount('read'));
        $this->assertEquals([['resource']], $imagineSpy->testSpyGetMethodCallArguments('read'));
        $this->assertSame($imageMock, $image);
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
        $component = new Component();
        $component->send($imageSpy, 'jpg');
        $this->assertEquals(
            [
                'methodName' => 'show',
                'arguments' => ['jpg', []],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testSendWithDefaultOptions()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component($this->optionsConfiguration);
        $component->send($imageSpy, 'jpg');
        $this->assertEquals(
            [
                'methodName' => 'show',
                'arguments' => ['jpg', $this->imagineOptions],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(1)
        );
    }

    public function testSendWithPredefinedOptionsAndProcessing()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
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
                'methodName' => 'show',
                'arguments' => [
                    'jpg',
                    $this->imagineOptions,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image sent'
        );
    }

    public function testSendWithAdHocOptionsAndProcessing()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component();
        $component->send($imageSpy, 'jpeg', array_merge($this->optionsConfiguration, [
            'process' => $this->processConfiguration,
        ]));
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1), 'processing applied');
        $this->assertEquals(
            [
                'methodName' => 'show',
                'arguments' => [
                    'jpeg',
                    $this->imagineOptions,
                ],
            ],
            $imageSpy->testSpyGetMethodCallAtPosition(2),
            'image sent'
        );
    }

    public function testSaveAndSendWithUndefinedOptions()
    {
        $imageSpy = new ImageInterfaceSpy(new Box(300, 200));
        $component = new Component();
        $component->saveAndSend($imageSpy, '/destination/path', 'jpg');
        $this->assertEquals(
            [
                'methodName' => 'show',
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
        $component = new Component($this->optionsConfiguration);
        $component->saveAndSend($imageSpy, '/destination/path', 'jpg');
        $this->assertEquals(
            [
                'methodName' => 'show',
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
                'methodName' => 'show',
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
        $component = new Component();
        $component->saveAndSend($imageSpy, '/destination/path' , 'jpg', array_merge($this->optionsConfiguration, [
            'process' => $this->processConfiguration,
        ]));
        $this->assertEquals($this->processMethodCall, $imageSpy->testSpyGetMethodCallAtPosition(1), 'processing applied');
        $this->assertEquals(
            [
                'methodName' => 'show',
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
        $component = new Component();
        $component->saveAndSend($imageSpy, '/destination/directory/image.jpg');
        $this->assertEquals(
            [
                'methodName' => 'show',
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
} 