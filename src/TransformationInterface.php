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
     * @return ImageInterface The transformed image
     * Some transformations cannot be applied to the original instance and to make the API consistent it's required to
     * return the transformed image even in cases when it's the same image instance as the original.
     */
    public function transform(ImageInterface $image, ImagineInterface $imagine);
} 