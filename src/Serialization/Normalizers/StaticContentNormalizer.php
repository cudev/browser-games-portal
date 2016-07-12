<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\StaticContent;
use Ludos\Entity\StaticContentData;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Traversable;

class StaticContentNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var StaticContent $object */
        $staticContentData = $object->getStaticContentData() ?? [];
        return [
            'id' => $object->getId(),
            'accessKey' => $object->getAccessKey(),
            'pageName' => $object->getPageName(),
            'staticContentData' => array_reduce(
                $staticContentData instanceof Traversable ? iterator_to_array($staticContentData) : $staticContentData,
                function ($result, StaticContentData $staticContentData) use ($format, $context) {
                    $result[$staticContentData->getLocale()->getLanguage()] = $this->serializer->normalize(
                        $staticContentData,
                        $format,
                        $context
                    );
                    return $result;
                }
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof StaticContent;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var StaticContent $staticContent */
        $staticContent = $this->fetchEntity($data, StaticContent::class);
        $staticContentData = $data['staticContentData'];
        $staticContent->setAccessKey($data['accessKey'])
            ->setPageName($data['pageName']);
        if ($staticContentData) {
            foreach ($staticContentData as $item) {
                $staticContent->addStaticContentData($this->serializer->denormalize(
                    $item,
                    StaticContentData::class,
                    $format,
                    $context
                ));
            }
        }
        return $staticContent;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === StaticContent::class;
    }
}
