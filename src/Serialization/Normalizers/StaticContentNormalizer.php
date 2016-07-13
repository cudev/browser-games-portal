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
