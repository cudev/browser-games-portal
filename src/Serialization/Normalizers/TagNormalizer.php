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
