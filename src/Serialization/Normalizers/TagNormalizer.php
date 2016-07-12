<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Game\Tag;
use Ludos\Entity\Game\TagName;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Traversable;

class TagNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Tag $object */
        $tagNames = $object->getTagNames();
        return [
            'id' => $object->getId(),
            'enabled' => $object->isEnabled(),
            'featured' => $object->isFeatured(),
            'tagNames' => array_reduce(
                $tagNames instanceof Traversable ? iterator_to_array($tagNames) : $tagNames,
                function ($result, TagName $tagName) use ($format, $context) {
                    $result[$tagName->getLocale()->getLanguage()] = $this->serializer->normalize(
                        $tagName,
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
        return $data instanceof Tag;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var Tag $tag */
        $tag = $this->fetchEntity($data, Tag::class);
        $tag->setFeatured((boolean)$data['featured'])
            ->setEnabled((boolean)$data['enabled'])
            ->setTagNames(array_map(
                function (array $tagName) use ($format, $context) {
                    return $this->serializer->denormalize($tagName, TagName::class, $format);
                },
                $data['tagNames'] instanceof Traversable ? iterator_to_array($data['tagNames']) : $data['tagNames']
            ));
        return $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Tag::class;
    }
}
