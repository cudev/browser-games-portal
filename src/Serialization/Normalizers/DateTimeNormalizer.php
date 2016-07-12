<?php

namespace Ludos\Serialization\Normalizers;

use Carbon\Carbon;
use DateTime;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * DateTime Normalizer
 */
class DateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * Date format
     *
     * @var string
     */
    protected $dateFormat = DateTime::ISO8601;

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof DateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === DateTime::class || $type === Carbon::class;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var DateTime $object */
        return $object->format($this->dateFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return Carbon::parse($data);
    }
}
