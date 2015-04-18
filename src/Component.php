<?php
namespace phtamas\yii2\imageprocessor;

use Yii;
use yii\base\Component as BaseComponent;
use yii\web\Response;
use yii\base\NotSupportedException;
use Imagine\Gmagick\Imagine as ImagineGmagick;
use Imagine\Imagick\Imagine as ImagineImagick;
use Imagine\Gd\Imagine as ImagineGd;
use Imagine\Image\AbstractImagine;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use Imagine\Image\Metadata\ExifMetadataReader;
use InvalidArgumentException;

/**
 * @method ImageInterface autorotate(ImageInterface $image)
 * @method ImageInterface crop(ImageInterface $image, array $configuration)
 * @method ImageInterface resize(ImageInterface $image, array $configuration)
 * @method ImageInterface watermark(ImageInterface $image, array $configuration)
 */
class Component extends BaseComponent
{
    /**
     * @var  array
     */
    public $drivers = ['gmagick', 'imagick', 'gd'];

    /**
     * @var int Default quality for JPEG images.
     * Between 0 and 100. If omitted, Imagine's default will be used. Can be overridden in image category definitions.
     */
    public $jpegQuality;

    /**
     * @var int Default compression level for PNG images.
     * Between 0 and 9. If omitted, Imagine's default will be used. Can be overridden in image category definitions.
     */
    public $pngCompression;

    /**
     * @var array Image category definitions
     * Key: (string) category name, value: (array) definition
     */
    public $define = [];

    /**
     * @var array Custom transformations.
     * Key: (string) transformation name, value: (string) classname or (array) class configuration.
     */
    public $transformations = [];

    /**
     * @var  \Imagine\Image\ImagineInterface
     */
    public $imagine;

    /**
     * @var array
     */
    private $builtInTransformations = [
        'autorotate' => '\phtamas\yii2\imageprocessor\transformation\Autorotate',
        'crop' => '\phtamas\yii2\imageprocessor\transformation\Crop',
        'resize' => '\phtamas\yii2\imageprocessor\transformation\Resize',
        'watermark' => '\phtamas\yii2\imageprocessor\transformation\Watermark',
    ];

    private $supportedTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'png'  => 'image/png',
        'wbmp' => 'image/vnd.wap.wbmp',
        'xbm'  => 'image/xbm',
    ];

    public function __call($name, $params)
    {
        if ($transformation = $this->createTransformation($name, isset($params[1]) ? $params[1] : [])) {
            return $transformation->transform($params[0], $this->imagine);
        }
        return parent::__call($name, $params);
    }

    public function init()
    {
        parent::init();
        if (!isset($this->imagine)) {
            $this->imagine = $this->createImagine();
        }
    }

    /**
     * Creates an image object
     *
     * @param array|\Imagine\Image\ImageInterface $source
     * @throws \InvalidArgumentException On unsupported source type
     * @return \Imagine\Image\ImageInterface
     */
    public function create($source)
    {
        if ($source instanceof ImageInterface) {
            return $source;
        }
        if (is_string($source)) {
            return $this->imagine->open(Yii::getAlias($source));
        }
        if (is_resource($source)) {
            return $this->imagine->read($source);
        }
        if (isset($source['data'])) {
            return $this->imagine->load($source['data']);
        }
        if (isset($source['width']) && isset($source['height'])) {
            return $this->imagine->create(new Box($source['width'], $source['height']));
        }
        throw new InvalidArgumentException();
    }

    /**
     * Processes an image with predefined or ad-hoc transformations
     *
     * @param array|\Imagine\Image\ImageInterface $source
     * @param string|array|null $as
     * @throws \InvalidArgumentException
     * @return \Imagine\Image\ImageInterface
     */
    public function process($source, $as = null)
    {
        $image = $this->create($source);
        if (is_string($as)) {
            if (!isset($this->define[$as]['process'])) {
                throw new InvalidArgumentException(sprintf(
                    'Definition not found for processing: "%s".',
                    $as
                ));
            }
            $as = $this->define[$as]['process'];
        }
        if (is_array($as)) {
            foreach ($as as $transformationDefinition) {
                $transformation = $this->createTransformation(array_shift($transformationDefinition), $transformationDefinition);
                $image = $transformation->transform($image, $this->imagine);
            }
        }
        return $image;
    }

    /**
     * Processes an image with predefined or ad-hoc transformations and saves the result as a file.
     *
     * @param array|\Imagine\Image\ImageInterface $source
     * @param string $path
     * @param string|array|null $as
     */
    public function save($source, $path, $as = null)
    {
        $definition = $this->resolveDefinition($as);
        $processAs = isset($definition['process']) ? $definition['process'] : null;
        $image = $this->process($source, $processAs);
        $options = $this->mergeOptions($definition);
        $image->save(Yii::getAlias($path), $options);
    }

    /**
     * Processes an image with predefined or ad-hoc transformations and sends the result as HTTP output.
     *
     * @param array|\Imagine\Image\ImageInterface $source
     * @param string $type
     * @param string|array|null $as
     */
    public function send($source, $type, $as = null)
    {
        $definition = $this->resolveDefinition($as);
        $processAs = isset($definition['process']) ? $definition['process'] : null;
        $image = $this->process($source, $processAs);
        $options = $this->mergeOptions($definition);
        $this->sendImage($image, $type, $options);
    }

    /**
     * Processes an image with predefined or ad-hoc transformations, saves the result as a file
     * and sends it as HTTP output.
     *
     * @param array|\Imagine\Image\ImageInterface $source
     * @param string $path
     * @param string|null $type
     * @param string|array|null $as
     */
    public function saveAndSend($source, $path, $type = null, $as = null)
    {
        $definition = $this->resolveDefinition($as);
        $processAs = isset($definition['process']) ? $definition['process'] : null;
        $image = $this->process($source, $processAs);
        $options = $this->mergeOptions($definition);
        $path = Yii::getAlias($path);
        if (!isset($type)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
        }
        $this->sendImage($image, $type, $options);
        $image->save(Yii::getAlias($path), $options);
    }

    /**
     * @return \Imagine\Image\AbstractImagine
     * @throws \yii\base\NotSupportedException
     */
    private function createImagine()
    {
        $driver = null;
        while (!$driver && list($index, $driverName) = each($this->drivers)) {
            if ($driverName === 'gmagick' && class_exists('Gmagick', false)) {
                $driver = new ImagineGmagick();
            } elseif ($driverName === 'imagick' && class_exists('Imagick', false)) {
                $driver = new ImagineImagick();
            } elseif ($driverName === 'gd' && function_exists('gd_info')) {
                $driver = new ImagineGd();
            }
        }
        if (!$driver) {
            throw new NotSupportedException();
        }
        /* @var $driver AbstractImagine */
        if (function_exists('exif_read_data')) {
            $driver->setMetadataReader(new ExifMetadataReader());
        }
        return $driver;
    }

    /**
     * @param string $name
     * @param array $definition
     * @return TransformationInterface|bool
     */
    private function createTransformation($name, array $definition)
    {
        if (isset($this->builtInTransformations[$name])) {
            $configuration = ['class' => $this->builtInTransformations[$name]];
            if (isset($this->transformations[$name])) {
                $configuration = array_merge(
                    $configuration,
                    is_string($this->transformations[$name])
                        ? ['class' => $this->transformations[$name]]
                        : $this->transformations[$name]
                );
            }
            $configuration = array_merge($configuration, $definition);
        } elseif (!isset($this->transformations[$name])) {
            return false;
        } else  {
            $configuration = is_string($this->transformations[$name])
                ? ['class' => $this->transformations[$name]]
                : $this->transformations[$name];
            $configuration = array_merge($configuration, $definition);
        }
        return Yii::createObject($configuration);
    }

    /**
     * @param array $options
     * @return array
     */
    private function mergeOptions(array $options)
    {
        $mergedOptions = [];

        if (isset($options['jpegQuality'])) {
            $mergedOptions['jpeg_quality'] = $options['jpegQuality'];
        } elseif (isset($this->jpegQuality)) {
            $mergedOptions['jpeg_quality'] = $this->jpegQuality;
        }
        if (isset($options['pngCompression'])) {
            $mergedOptions['png_compression_level'] = $options['pngCompression'];
        } elseif (isset($this->pngCompression)) {
            $mergedOptions['png_compression_level'] = $this->pngCompression;
        }

        return $mergedOptions;
    }

    /**
     * @param null|string|array $definition
     * @throws \InvalidArgumentException
     * @return array
     */
    private function resolveDefinition($definition)
    {
        if (is_null($definition)) {
            return [];
        }
        if (is_string($definition)) {
            return $this->define[$definition];
        }
        if (is_array($definition)) {
            return $definition;
        }
        throw new InvalidArgumentException();
    }

    private function sendImage(ImageInterface $image, $type, $options)
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', $this->supportedTypes[$type]);
        Yii::$app->response->data = $image->get($type, $options);
    }
}