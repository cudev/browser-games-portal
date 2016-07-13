<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Cudev Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Game\Description;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Game\Meta;
use Ludos\Entity\Game\Tag;
use Ludos\Entity\Provider\Category;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Traversable;

class GameNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Game $object */
        $tags = $object->getTags() ?? [];
        $meta = $object->getMeta() ?? [];
        $categories = $object->getCategories() ?? [];
        $descriptions = $object->getDescriptions() ?? [];
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'enabled' => $object->isEnabled(),
            'slug' => $object->getSlug(),
            'type' => $object->getType(),
            'url' => $object->getUrl(),
            'width' => $object->getWidth(),
            'height' => $object->getHeight(),
            'plays' => $object->getPlays(),
            'thumbnail' => $object->getThumbnail(),
            'descriptions' => array_reduce(
                $descriptions instanceof Traversable ? iterator_to_array($descriptions) : $descriptions,
                function ($result, Description $description) use ($format, $context) {
                    $result[$description->getLocale()->getLanguage()] = $this->serializer->normalize(
                        $description,
                        $format,
                        $context
                    );
                    return $result;
                }
            ),
            'tags' => array_reduce(
                $tags instanceof Traversable ? iterator_to_array($tags) : $tags,
                function ($result, Tag $tag) use ($format, $context) {
                    $result[] = $this->serializer->normalize($tag, $format, $context);
                    return $result;
                },
                []
            ),
            'meta' => array_reduce(
                $meta instanceof Traversable ? iterator_to_array($meta) : $meta,
                function ($result, Meta $meta) use ($format, $context) {
                    $result[] = $this->serializer->normalize($meta, $format, $context);
                    return $result;
                }
            ),
            'categories' => array_reduce(
                $categories instanceof Traversable ? iterator_to_array($categories) : $categories,
                function ($result, Category $category) use ($format, $context) {
                    $result[] = $this->serializer->normalize($category, $format, $context);
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
        return $data instanceof Game;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $tags = $data['tags'] ?? [];
        $descriptions = $data['descriptions'] ?? [];
        $meta = $data['meta'] ?? [];
        $categories = $data['categories'] ?? [];

        /** @var Game $game */
        $game = $this->fetchEntity($data, Game::class);
        $game->setName($data['name'])
            ->setEnabled((boolean)$data['enabled'])
            ->setSlug($data['slug'])
            ->setType($data['type'])
            ->setUrl($data['url'])
            ->setWidth($data['width'])
            ->setHeight($data['height'])
            ->setPlays($data['plays'])
            ->setThumbnail($data['thumbnail'])
            ->setDescriptions(
                array_map(
                    function (array $description) use ($format, $context) {
                        return $this->serializer->denormalize($description, Description::class, $format);
                    },
                    $descriptions instanceof Traversable ? iterator_to_array($descriptions) : $descriptions
                )
            )
            ->setMeta(
                array_map(
                    function (array $meta) use ($format, $context) {
                        return $this->serializer->denormalize($meta, Meta::class, $format);
                    },
                    $meta instanceof Traversable ? iterator_to_array($meta) : $meta
                )
            )
            ->setTags(
                array_map(
                    function (array $tag) use ($format, $context) {
                        return $this->serializer->denormalize($tag, Tag::class, $format);
                    },
                    $tags instanceof Traversable ? iterator_to_array($tags) : $tags
                )
            )
            ->setCategories(
                array_map(
                    function (array $category) use ($format, $context) {
                        return $this->serializer->denormalize($category, Category::class, $format);
                    },
                    $categories instanceof Traversable ? iterator_to_array($categories) : $categories
                )
            );
        $this->mapTagsToCategories($game);
        return $game;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Game::class;
    }

    private function mapTagsToCategories(Game $game)
    {
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        $categories = $game->getCategories();
        foreach ($categories as $category) {
            /** @var Category $existingCategory */
            $existingCategory = $categoryRepository->findOneByName($category->getName());
            if (null !== $existingCategory) {
                foreach ($existingCategory->getTags() as $tag) {
                    $game->addTag($tag);
                }
            }
        }
    }
}
