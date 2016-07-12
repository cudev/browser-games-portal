<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Game\Tag;
use Ludos\Entity\Provider\Category;
use Ludos\Entity\Provider\Provider;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Traversable;

class CategoryNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Category $object */
        $tags = $object->getTags() ?? [];
        $provider = $object->getProvider();
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'provider' => $provider ? $this->serializer->normalize($provider, $format, $context) : null,
            'tags' => array_reduce(
                $tags instanceof Traversable ? iterator_to_array($tags) : $tags,
                function ($result, Tag $tag) use ($format, $context) {
                    $result[] = $this->serializer->normalize($tag, $format, $context);
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
        return $data instanceof Category;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var Category $category */
        $category = $this->fetchEntity($data, Category::class);
        $tags = $data['tags'] ?? [];
        $provider = $data['provider'] ?? null;
        $category->setName($data['name'])
            ->setTags(
                array_map(
                    function (array $tag) use ($format, $context) {
                        return $this->serializer->denormalize($tag, Tag::class, $format);
                    },
                    $tags instanceof Traversable ? iterator_to_array($tags) : $tags
                )
            );
        if ($provider !== null) {
            $category->setProvider($this->serializer->denormalize($provider, Provider::class, $format));
        }
        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Category::class;
    }
}
