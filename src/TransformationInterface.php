<?php
namespace phtamas\yii2\imageprocessor;

use Imagine\Image\ImagineInterface;
use Imagine\Image\ImageInterface;

interface TransformationInterface 
{
    /**
     * Processes the image.
     *
     * @param \Imagine\Image\ImageInterface $image
     * @param \Imagine\Image\ImagineInterface $imagine
     */
    public function transform(ImageInterface $image, ImagineInterface $imagine);
} 